<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php';
require_once __DIR__ . '/../inc/migrator.php';
page_title('Faturalar');

// Hızlı migration uygula aksiyonu (alert butonundan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['islem'] ?? '') === 'migrasyon_uygula_hizli') {
    if (csrf_check($_POST['csrf'] ?? null)) {
        $M = new Migrator(__DIR__ . '/..');
        $r = $M->bekleyenleri_uygula();
        if ($r['ok']) {
            $sayi = count($r['uygulananlar']);
            flash_set('ok', $sayi ? "$sayi migration uygulandı, tablolar oluşturuldu." : "Bekleyen migration yok.");
            log_yaz('migrasyon_hizli', "$sayi migration", (int)$_kul['id']);
            $M->sentinel_kaydet();
        } else {
            flash_set('err', 'Hata: ' . ($r['hatalar'][0]['hata'] ?? '?'));
        }
        if (function_exists('opcache_reset')) @opcache_reset();
    }
    redirect('faturalar.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $cari_id   = (int)($_POST['cari_id'] ?? 0);
        $fatura_no = clean($_POST['fatura_no'] ?? '');
        $tip       = in_array($_POST['tip'] ?? '', ['satis','alis','iade_satis','iade_alis'], true) ? $_POST['tip'] : 'satis';
        $tarih     = $_POST['tarih'] ?? date('Y-m-d');
        $vade      = $_POST['vade_tarihi'] ?: null;
        $iskonto   = (float)str_replace(',', '.', (string)($_POST['iskonto'] ?? 0));
        $notlar    = clean($_POST['notlar'] ?? '');

        if (!$cari_id || !$fatura_no) {
            flash_set('err', 'Cari ve fatura no zorunludur.');
            redirect(SITE_URL . '/admin/faturalar.php' . ($id ? '?duzenle=' . $id : '?ekle=1'));
        }

        $kalemler = [];
        $adlar = $_POST['kalem_ad']      ?? [];
        $miks  = $_POST['kalem_miktar']  ?? [];
        $bims  = $_POST['kalem_birim']   ?? [];
        $bfs   = $_POST['kalem_birim_fiyat'] ?? [];
        $isks  = $_POST['kalem_iskonto'] ?? [];
        $kdvs  = $_POST['kalem_kdv']     ?? [];
        $upids = $_POST['kalem_urun_id'] ?? [];
        $ara_top = 0; $kdv_top = 0;
        foreach ($adlar as $i => $ad) {
            $ad = clean((string)$ad);
            if (!$ad) continue;
            $miktar = (float)str_replace(',', '.', (string)($miks[$i] ?? 1));
            $birim  = clean((string)($bims[$i] ?? 'Adet'));
            $bf     = (float)str_replace(',', '.', (string)($bfs[$i] ?? 0));
            $isk    = (float)str_replace(',', '.', (string)($isks[$i] ?? 0));
            $kdv    = (int)($kdvs[$i] ?? 20);
            $up     = (int)($upids[$i] ?? 0) ?: null;
            $brut   = $miktar * $bf;
            $netto  = $brut * (1 - $isk/100);
            $kdv_t  = $netto * ($kdv/100);
            $top    = $netto + $kdv_t;
            $kalemler[] = compact('ad','miktar','birim','bf','isk','kdv','up','top');
            $ara_top += $netto;
            $kdv_top += $kdv_t;
        }
        $genel = ($ara_top - $iskonto) + $kdv_top;

        if (empty($kalemler)) {
            flash_set('err', 'En az 1 kalem giriniz.');
            redirect($_SERVER['REQUEST_URI']);
        }

        $eski_odenen = $id ? (float)(db_get("SELECT odenen FROM faturalar WHERE id=?", [$id])['odenen'] ?? 0) : 0;
        $odeme_durumu = $eski_odenen <= 0 ? 'odenmedi' : ($eski_odenen >= $genel ? 'odendi' : 'kismi');

        if ($id) {
            $eski = db_get("SELECT cari_id, tip, genel_toplam FROM faturalar WHERE id=?", [$id]);
            db_run("UPDATE faturalar SET cari_id=?, fatura_no=?, tip=?, tarih=?, vade_tarihi=?, ara_toplam=?, iskonto=?, kdv_toplam=?, genel_toplam=?, odeme_durumu=?, notlar=? WHERE id=?",
                [$cari_id, $fatura_no, $tip, $tarih, $vade, $ara_top, $iskonto, $kdv_top, $genel, $odeme_durumu, $notlar, $id]);
            db_run("DELETE FROM fatura_kalemleri WHERE fatura_id=?", [$id]);
            if ($eski) {
                $eski_delta = in_array($eski['tip'], ['satis','iade_alis'], true) ? -$eski['genel_toplam'] : $eski['genel_toplam'];
                db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$eski_delta, $eski['cari_id']]);
            }
            db_run("DELETE FROM cari_hareketler WHERE belge_tip='fatura' AND belge_id=?", [$id]);
            log_yaz('fatura_guncelle', "Fatura: $fatura_no (#$id)", (int)$_kul['id']);
        } else {
            db_run("INSERT INTO faturalar (cari_id, fatura_no, tip, tarih, vade_tarihi, ara_toplam, iskonto, kdv_toplam, genel_toplam, odeme_durumu, notlar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                [$cari_id, $fatura_no, $tip, $tarih, $vade, $ara_top, $iskonto, $kdv_top, $genel, 'odenmedi', $notlar, (int)$_kul['id']]);
            $id = (int)db()->lastInsertId();
            log_yaz('fatura_ekle', "Fatura: $fatura_no (#$id)", (int)$_kul['id']);
        }

        $stmt = db()->prepare("INSERT INTO fatura_kalemleri (fatura_id, urun_id, ad, miktar, birim, birim_fiyat, iskonto_yuzde, kdv_orani, toplam) VALUES (?,?,?,?,?,?,?,?,?)");
        foreach ($kalemler as $k) {
            $stmt->execute([$id, $k['up'], $k['ad'], $k['miktar'], $k['birim'], $k['bf'], $k['isk'], $k['kdv'], $k['top']]);
        }

        $hareket_tip = match($tip) {
            'satis'      => 'borc',
            'alis'       => 'alacak',
            'iade_satis' => 'alacak',
            'iade_alis'  => 'borc',
        };
        db_run("INSERT INTO cari_hareketler (cari_id, tarih, tip, belge_tip, belge_id, belge_no, aciklama, tutar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?)",
            [$cari_id, $tarih, $hareket_tip, 'fatura', $id, $fatura_no, "Fatura: $fatura_no", $genel, (int)$_kul['id']]);
        $delta = in_array($tip, ['satis','iade_alis'], true) ? $genel : -$genel;
        db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$delta, $cari_id]);

        flash_set('ok', 'Fatura kaydedildi.');
        redirect(SITE_URL . '/admin/faturalar.php?duzenle=' . $id);
    }

    if ($islem === 'sil' && $id) {
        $f = db_get("SELECT * FROM faturalar WHERE id=?", [$id]);
        if ($f) {
            $delta = in_array($f['tip'], ['satis','iade_alis'], true) ? -$f['genel_toplam'] : $f['genel_toplam'];
            db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$delta, $f['cari_id']]);
            db_run("DELETE FROM cari_hareketler WHERE belge_tip='fatura' AND belge_id=?", [$id]);
            db_run("DELETE FROM fatura_kalemleri WHERE fatura_id=?", [$id]);
            db_run("DELETE FROM faturalar WHERE id=?", [$id]);
            log_yaz('fatura_sil', "Fatura silindi: {$f['fatura_no']} (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Fatura silindi.');
        }
        redirect(SITE_URL . '/admin/faturalar.php');
    }

    if ($islem === 'odeme_al' && $id) {
        $f = db_get("SELECT * FROM faturalar WHERE id=?", [$id]);
        $miktar = (float)str_replace(',', '.', (string)($_POST['miktar'] ?? 0));
        $yontem = $_POST['yontem'] ?? 'nakit';
        if ($f && $miktar > 0) {
            $kalan = $f['genel_toplam'] - $f['odenen'];
            if ($miktar > $kalan) $miktar = $kalan;
            $yeni_odenen = $f['odenen'] + $miktar;
            $durum = $yeni_odenen >= $f['genel_toplam'] ? 'odendi' : 'kismi';
            db_run("UPDATE faturalar SET odenen=?, odeme_durumu=? WHERE id=?", [$yeni_odenen, $durum, $id]);

            $fis_no = 'TH-' . date('Ymd-His');
            db_run("INSERT INTO fisler (cari_id, fis_no, tip, tarih, aciklama, ara_toplam, kdv_toplam, genel_toplam, odeme_yontemi, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?,?)",
                [$f['cari_id'], $fis_no, 'tahsilat', date('Y-m-d'), "Fatura ödemesi: {$f['fatura_no']}", $miktar, 0, $miktar, $yontem, (int)$_kul['id']]);
            $fis_id = (int)db()->lastInsertId();

            db_run("INSERT INTO cari_hareketler (cari_id, tarih, tip, belge_tip, belge_id, belge_no, aciklama, tutar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?)",
                [$f['cari_id'], date('Y-m-d'), 'tahsilat', 'fis', $fis_id, $fis_no, "Tahsilat: {$f['fatura_no']}", $miktar, (int)$_kul['id']]);
            db_run("UPDATE cariler SET bakiye = bakiye - ? WHERE id=?", [$miktar, $f['cari_id']]);
            log_yaz('fatura_odeme', "Tahsilat: {$f['fatura_no']} - " . tl($miktar), (int)$_kul['id']);
            flash_set('ok', 'Tahsilat alındı, fiş oluşturuldu.');
        }
        redirect(SITE_URL . '/admin/faturalar.php?duzenle=' . $id);
    }
    redirect($_SERVER['REQUEST_URI']);
}

$ekle    = isset($_GET['ekle']);
$duzenle = (int)($_GET['duzenle'] ?? 0);
$mod     = ($ekle || $duzenle) ? 'form' : 'liste';

require_once __DIR__ . '/_header.php';

/* ===== FORM ===== */
if ($mod === 'form') {
    $f = $duzenle ? db_get("SELECT * FROM faturalar WHERE id=?", [$duzenle]) : null;
    if ($duzenle && !$f) { flash_set('err','Fatura bulunamadı.'); redirect(SITE_URL.'/admin/faturalar.php'); }
    $kalemler = $duzenle ? db_all("SELECT * FROM fatura_kalemleri WHERE fatura_id=? ORDER BY id", [$duzenle]) : [];
    $cariler = db_all("SELECT id, cari_kodu, unvan FROM cariler WHERE aktif=1 ORDER BY unvan");
    $urunler = db_all("SELECT id, ad, fiyat, kdv_orani FROM urunler WHERE aktif=1 ORDER BY ad");
    $sonraki_no = 'F-' . date('Ymd') . '-' . str_pad((string)((int)(db_get("SELECT COUNT(*) c FROM faturalar WHERE DATE(olusturma_tarihi)=CURDATE()")['c'] ?? 0) + 1), 3, '0', STR_PAD_LEFT);
?>
<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $duzenle ? 'Fatura: ' . e($f['fatura_no']) : 'Yeni Fatura' ?></h1>
        <?php if ($duzenle): ?>
            <p class="page-sub">
                <span class="badge badge-info"><?= ucfirst($f['tip']) ?></span>
                <span class="badge badge-<?= $f['odeme_durumu']==='odendi'?'ok':($f['odeme_durumu']==='kismi'?'warn':'danger') ?>"><?= ucfirst($f['odeme_durumu']) ?></span>
                Toplam: <strong><?= tl((float)$f['genel_toplam']) ?></strong> · Ödenen: <?= tl((float)$f['odenen']) ?> · Kalan: <strong style="color:var(--c-red)"><?= tl((float)$f['genel_toplam'] - (float)$f['odenen']) ?></strong>
            </p>
        <?php endif; ?>
    </div>
    <a href="faturalar.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye</a>
</div>

<?php if ($duzenle && $f['odeme_durumu'] !== 'odendi'): ?>
<div class="card" style="border-left:3px solid var(--c-orange)">
    <h3><i class="fas fa-money-bill-wave" style="color:var(--c-orange)"></i> Tahsilat Al</h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="odeme_al">
        <input type="hidden" name="id" value="<?= $f['id'] ?>">
        <div class="form-row cols-3">
            <div class="field"><label>Tahsilat Tutarı (₺)</label><input class="input" type="number" name="miktar" step="0.01" min="0.01" max="<?= (float)$f['genel_toplam'] - (float)$f['odenen'] ?>" value="<?= (float)$f['genel_toplam'] - (float)$f['odenen'] ?>"></div>
            <div class="field"><label>Yöntem</label>
                <select name="yontem">
                    <option value="nakit">Nakit</option>
                    <option value="kart">Kart</option>
                    <option value="havale">Havale/EFT</option>
                    <option value="cek">Çek</option>
                    <option value="senet">Senet</option>
                </select>
            </div>
            <div class="field" style="display:flex;align-items:flex-end"><button class="btn btn-pri" style="width:100%"><i class="fas fa-money-bill-wave"></i> Tahsilat Al & Fiş Üret</button></div>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <form method="post" id="faturaForm">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="kaydet">
        <input type="hidden" name="id" value="<?= $f['id'] ?? 0 ?>">

        <div class="form-row cols-3">
            <div class="field"><label>Cari *</label>
                <select name="cari_id" required>
                    <option value="">-- Seçin --</option>
                    <?php foreach ($cariler as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($f['cari_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['cari_kodu']) ?> — <?= e($c['unvan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Fatura No *</label><input class="input" type="text" name="fatura_no" value="<?= e($f['fatura_no'] ?? $sonraki_no) ?>" required maxlength="40"></div>
            <div class="field"><label>Tip</label>
                <select name="tip">
                    <option value="satis"      <?= ($f['tip'] ?? 'satis')==='satis'?'selected':'' ?>>Satış</option>
                    <option value="alis"       <?= ($f['tip'] ?? '')==='alis'?'selected':'' ?>>Alış</option>
                    <option value="iade_satis" <?= ($f['tip'] ?? '')==='iade_satis'?'selected':'' ?>>İade (Satış)</option>
                    <option value="iade_alis"  <?= ($f['tip'] ?? '')==='iade_alis'?'selected':'' ?>>İade (Alış)</option>
                </select>
            </div>
        </div>

        <div class="form-row cols-3">
            <div class="field"><label>Tarih *</label><input class="input" type="date" name="tarih" value="<?= e($f['tarih'] ?? date('Y-m-d')) ?>" required></div>
            <div class="field"><label>Vade Tarihi</label><input class="input" type="date" name="vade_tarihi" value="<?= e($f['vade_tarihi'] ?? '') ?>"></div>
            <div class="field"><label>Genel İskonto (₺)</label><input class="input" type="number" name="iskonto" step="0.01" min="0" value="<?= e((string)($f['iskonto'] ?? 0)) ?>"></div>
        </div>

        <h3 style="margin:20px 0 10px">Kalemler</h3>
        <div class="tbl-wrap">
        <table class="tbl" id="kalemTbl">
            <thead>
                <tr><th style="min-width:200px">Ürün/Hizmet *</th><th>Miktar</th><th>Birim</th><th>Birim Fiyat</th><th>İsk %</th><th>KDV %</th><th style="text-align:right">Toplam</th><th></th></tr>
            </thead>
            <tbody id="kalemBody">
                <?php foreach ($kalemler as $k): ?>
                <tr>
                    <td>
                        <input type="hidden" name="kalem_urun_id[]" value="<?= (int)$k['urun_id'] ?>" class="urun_id">
                        <input class="input kalem_ad" type="text" name="kalem_ad[]" value="<?= e($k['ad']) ?>" required list="urunListe">
                    </td>
                    <td><input class="input m" type="number" name="kalem_miktar[]" step="0.001" value="<?= e((string)$k['miktar']) ?>" style="width:90px"></td>
                    <td><input class="input" type="text" name="kalem_birim[]" value="<?= e($k['birim']) ?>" style="width:80px"></td>
                    <td><input class="input bf" type="number" name="kalem_birim_fiyat[]" step="0.01" value="<?= e((string)$k['birim_fiyat']) ?>" style="width:110px"></td>
                    <td><input class="input isk" type="number" name="kalem_iskonto[]" step="0.01" value="<?= e((string)$k['iskonto_yuzde']) ?>" style="width:70px"></td>
                    <td>
                        <select name="kalem_kdv[]" class="kdv" style="width:70px">
                            <?php foreach ([0,1,8,10,18,20] as $v): ?><option value="<?= $v ?>" <?= (int)$k['kdv_orani']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
                        </select>
                    </td>
                    <td class="num top" style="text-align:right">0,00 ₺</td>
                    <td><button type="button" class="btn btn-danger btn-sm sil"><i class="fas fa-times"></i></button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <button type="button" id="kalemEkle" class="btn btn-out btn-sm" style="margin-top:8px"><i class="fas fa-plus"></i> Kalem Ekle</button>

        <datalist id="urunListe">
            <?php foreach ($urunler as $u): ?>
                <option data-id="<?= $u['id'] ?>" data-fiyat="<?= $u['fiyat'] ?>" data-kdv="<?= $u['kdv_orani'] ?>" value="<?= e($u['ad']) ?>"></option>
            <?php endforeach; ?>
        </datalist>

        <div style="margin-top:24px;padding:18px;background:var(--c-bg);border-radius:10px;border:1px solid var(--c-line)">
            <div style="display:flex;justify-content:space-between;font-size:.95rem;margin-bottom:6px"><span style="color:var(--c-muted)">Ara Toplam</span><strong id="araTop" class="num">0,00 ₺</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.95rem;margin-bottom:6px"><span style="color:var(--c-muted)">Genel İskonto</span><strong id="iskTop" class="num">0,00 ₺</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.95rem;margin-bottom:6px"><span style="color:var(--c-muted)">KDV Toplam</span><strong id="kdvTop" class="num">0,00 ₺</strong></div>
            <hr style="margin:10px 0;border:0;border-top:1px solid var(--c-line)">
            <div style="display:flex;justify-content:space-between;font-size:1.2rem"><span>GENEL TOPLAM</span><strong id="genelTop" class="num" style="color:var(--c-orange)">0,00 ₺</strong></div>
        </div>

        <div class="form-row" style="margin-top:14px"><div class="field"><label>Notlar</label><textarea class="input" name="notlar" rows="2"><?= e($f['notlar'] ?? '') ?></textarea></div></div>

        <div class="form-actions">
            <button class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
            <a href="faturalar.php" class="btn btn-out">İptal</a>
        </div>
    </form>
    <?php if ($duzenle): ?>
    <form method="post" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="sil">
        <input type="hidden" name="id" value="<?= $f['id'] ?>">
        <button class="btn btn-danger btn-sm" data-onay="Fatura silinsin mi? Cari hareketleri ve kalemler de silinecek."><i class="fas fa-trash"></i> Faturayı Sil</button>
    </form>
    <?php endif; ?>
</div>

<script>
(function(){
    const body = document.getElementById('kalemBody');
    const formatTL = n => n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' ₺';

    function hesapla(){
        let ara = 0, kdvT = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const m = parseFloat((tr.querySelector('.m')?.value || '0').replace(',', '.')) || 0;
            const bf = parseFloat((tr.querySelector('.bf')?.value || '0').replace(',', '.')) || 0;
            const isk = parseFloat((tr.querySelector('.isk')?.value || '0').replace(',', '.')) || 0;
            const kdv = parseFloat(tr.querySelector('.kdv')?.value || '0') || 0;
            const brut = m * bf;
            const netto = brut * (1 - isk/100);
            const k = netto * (kdv/100);
            const top = netto + k;
            tr.querySelector('.top').textContent = formatTL(top);
            ara += netto;
            kdvT += k;
        });
        const iskGenel = parseFloat((document.querySelector('[name=iskonto]')?.value || '0').replace(',', '.')) || 0;
        document.getElementById('araTop').textContent = formatTL(ara);
        document.getElementById('iskTop').textContent = formatTL(iskGenel);
        document.getElementById('kdvTop').textContent = formatTL(kdvT);
        document.getElementById('genelTop').textContent = formatTL(ara - iskGenel + kdvT);
    }

    function bagla(tr){
        tr.querySelectorAll('input,select').forEach(el => el.addEventListener('input', hesapla));
        tr.querySelector('.sil').addEventListener('click', () => { tr.remove(); hesapla(); });
        const ad = tr.querySelector('.kalem_ad');
        ad.addEventListener('change', () => {
            const opts = document.querySelectorAll('#urunListe option');
            for (const opt of opts) {
                if (opt.value === ad.value) {
                    tr.querySelector('.urun_id').value = opt.dataset.id || 0;
                    if (!tr.querySelector('.bf').value || tr.querySelector('.bf').value == '0') tr.querySelector('.bf').value = opt.dataset.fiyat || 0;
                    tr.querySelector('.kdv').value = opt.dataset.kdv || 20;
                    hesapla();
                    break;
                }
            }
        });
    }

    document.getElementById('kalemEkle').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="hidden" name="kalem_urun_id[]" value="0" class="urun_id"><input class="input kalem_ad" type="text" name="kalem_ad[]" required list="urunListe"></td>
            <td><input class="input m" type="number" name="kalem_miktar[]" step="0.001" value="1" style="width:90px"></td>
            <td><input class="input" type="text" name="kalem_birim[]" value="Adet" style="width:80px"></td>
            <td><input class="input bf" type="number" name="kalem_birim_fiyat[]" step="0.01" value="0" style="width:110px"></td>
            <td><input class="input isk" type="number" name="kalem_iskonto[]" step="0.01" value="0" style="width:70px"></td>
            <td><select name="kalem_kdv[]" class="kdv" style="width:70px"><option>0</option><option>1</option><option>8</option><option>10</option><option>18</option><option value="20" selected>20</option></select></td>
            <td class="num top" style="text-align:right">0,00 ₺</td>
            <td><button type="button" class="btn btn-danger btn-sm sil"><i class="fas fa-times"></i></button></td>`;
        body.appendChild(tr);
        bagla(tr);
        tr.querySelector('.kalem_ad').focus();
    });

    body.querySelectorAll('tr').forEach(bagla);
    document.querySelector('[name=iskonto]').addEventListener('input', hesapla);
    if (body.querySelectorAll('tr').length === 0) document.getElementById('kalemEkle').click();
    hesapla();
})();
</script>

<?php require_once __DIR__ . '/_footer.php'; return; }

/* ===== LİSTE ===== */
$arama   = clean($_GET['q'] ?? '');
$tip     = $_GET['tip'] ?? '';
$durum   = $_GET['durum'] ?? '';
$cari_id = (int)($_GET['cari'] ?? 0);
$bas     = $_GET['bas'] ?? '';
$bit     = $_GET['bit'] ?? '';
$sayfa   = max(1, (int)($_GET['sayfa'] ?? 1));
$limit   = 25; $ofset = ($sayfa-1)*$limit;

$where = "1=1"; $params = [];
if ($arama)   { $where .= " AND (f.fatura_no LIKE ? OR c.unvan LIKE ?)"; $w="%$arama%"; array_push($params,$w,$w); }
if ($tip && in_array($tip, ['satis','alis','iade_satis','iade_alis'], true)) { $where .= " AND f.tip=?"; $params[] = $tip; }
if ($durum && in_array($durum, ['odenmedi','kismi','odendi'], true)) { $where .= " AND f.odeme_durumu=?"; $params[] = $durum; }
if ($cari_id) { $where .= " AND f.cari_id=?"; $params[] = $cari_id; }
if ($bas)     { $where .= " AND f.tarih>=?"; $params[] = $bas; }
if ($bit)     { $where .= " AND f.tarih<=?"; $params[] = $bit; }

$tablo_hatasi = null; $toplam = 0; $toplam_sayfa = 1; $rows = []; $ozet = ['toplam_satis'=>0, 'bekleyen'=>0];
try {
    $toplam = (int)db_get("SELECT COUNT(*) c FROM faturalar f LEFT JOIN cariler c ON c.id=f.cari_id WHERE $where", $params)['c'];
    $toplam_sayfa = max(1, (int)ceil($toplam / $limit));
    $rows = db_all("SELECT f.*, c.unvan, c.cari_kodu FROM faturalar f LEFT JOIN cariler c ON c.id=f.cari_id WHERE $where ORDER BY f.tarih DESC, f.id DESC LIMIT $limit OFFSET $ofset", $params);
    $ozet = db_get("SELECT
        COALESCE(SUM(CASE WHEN tip='satis' THEN genel_toplam ELSE 0 END),0) toplam_satis,
        COALESCE(SUM(CASE WHEN tip='satis' THEN genel_toplam-odenen ELSE 0 END),0) bekleyen
    FROM faturalar f LEFT JOIN cariler c ON c.id=f.cari_id WHERE $where", $params);
} catch (Throwable $e) {
    $tablo_hatasi = $e->getMessage();
}
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Faturalar</h1>
        <p class="page-sub"><?= $toplam ?> fatura · Toplam satış: <strong><?= tl((float)$ozet['toplam_satis']) ?></strong> · Bekleyen tahsilat: <strong style="color:var(--c-red)"><?= tl((float)$ozet['bekleyen']) ?></strong></p>
    </div>
    <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Fatura</a>
</div>

<?php if ($tablo_hatasi): ?>
<div class="alert alert-err">
    <strong><i class="fas fa-database"></i> Tablolar henüz oluşturulmamış.</strong>
    DB migrasyonları uygulanmamış. Aşağıdaki butona tıkla — tablolar tek tıkta oluşur, sayfa yenilenir.
    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
        <form method="post" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="migrasyon_uygula_hizli">
            <button class="btn btn-pri"><i class="fas fa-database"></i> Migrasyonları Şimdi Uygula</button>
        </form>
        <a href="sistem-tani.php" class="btn btn-out"><i class="fas fa-stethoscope"></i> Sistem Tanı</a>
        <a href="guncelleme.php" class="btn btn-out"><i class="fas fa-cloud-arrow-down"></i> Güncelleme</a>
    </div>
    <details style="margin-top:8px"><summary style="cursor:pointer;color:var(--c-muted);font-size:.85rem">Teknik detay</summary><pre style="font-size:.78rem;color:#fca5a5;margin-top:6px;font-family:monospace"><?= e($tablo_hatasi) ?></pre></details>
</div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="filters">
        <input class="input" type="search" name="q" value="<?= e($arama) ?>" placeholder="Fatura no / cari…">
        <select name="tip">
            <option value="">Tüm tipler</option>
            <option value="satis"      <?= $tip==='satis'?'selected':'' ?>>Satış</option>
            <option value="alis"       <?= $tip==='alis'?'selected':'' ?>>Alış</option>
            <option value="iade_satis" <?= $tip==='iade_satis'?'selected':'' ?>>İade Satış</option>
            <option value="iade_alis"  <?= $tip==='iade_alis'?'selected':'' ?>>İade Alış</option>
        </select>
        <select name="durum">
            <option value="">Tüm durumlar</option>
            <option value="odenmedi" <?= $durum==='odenmedi'?'selected':'' ?>>Ödenmedi</option>
            <option value="kismi"    <?= $durum==='kismi'?'selected':'' ?>>Kısmi</option>
            <option value="odendi"   <?= $durum==='odendi'?'selected':'' ?>>Ödendi</option>
        </select>
        <input class="input" type="date" name="bas" value="<?= e($bas) ?>" style="max-width:150px">
        <input class="input" type="date" name="bit" value="<?= e($bit) ?>" style="max-width:150px">
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i></button>
        <?php if ($arama || $tip || $durum || $bas || $bit || $cari_id): ?><a href="faturalar.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
    <thead><tr><th>No</th><th>Cari</th><th>Tarih</th><th>Tip</th><th style="text-align:right">Tutar</th><th style="text-align:right">Kalan</th><th>Durum</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="8" class="empty"><i class="fas fa-file-invoice-dollar" style="font-size:2rem;display:block;margin-bottom:8px"></i>Fatura bulunamadı.</td></tr>
    <?php else: foreach ($rows as $r):
        $kalan = (float)$r['genel_toplam'] - (float)$r['odenen'];
    ?>
        <tr>
            <td><code><?= e($r['fatura_no']) ?></code></td>
            <td><a href="cariler.php?detay=<?= $r['cari_id'] ?>" style="color:var(--c-orange);text-decoration:none"><?= e((string)$r['unvan']) ?></a></td>
            <td class="num"><?= tarih_tr($r['tarih']) ?></td>
            <td><span class="badge badge-info"><?= ucfirst(str_replace('_',' ',$r['tip'])) ?></span></td>
            <td class="num" style="text-align:right"><?= tl((float)$r['genel_toplam']) ?></td>
            <td class="num" style="text-align:right;color:<?= $kalan>0?'var(--c-red)':'var(--c-green)' ?>;font-weight:600"><?= tl($kalan) ?></td>
            <td><span class="badge badge-<?= $r['odeme_durumu']==='odendi'?'ok':($r['odeme_durumu']==='kismi'?'warn':'danger') ?>"><?= ucfirst($r['odeme_durumu']) ?></span></td>
            <td><a href="?duzenle=<?= $r['id'] ?>" class="btn btn-out btn-sm"><i class="fas fa-pen"></i></a></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/faturalar.php?' . http_build_query(array_filter(['q'=>$arama,'tip'=>$tip,'durum'=>$durum,'bas'=>$bas,'bit'=>$bit,'cari'=>$cari_id]));
    $base .= ($base[strlen($base)-1] === '?') ? '' : '&';
?>
<nav class="pager">
    <?php if ($sayfa > 1): ?><a href="<?= $base ?>sayfa=<?= $sayfa-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($p=1;$p<=$toplam_sayfa;$p++): ?>
        <a href="<?= $base ?>sayfa=<?= $p ?>" class="<?= $p===$sayfa?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($sayfa < $toplam_sayfa): ?><a href="<?= $base ?>sayfa=<?= $sayfa+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
