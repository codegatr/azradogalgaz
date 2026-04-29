<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php';
require_once __DIR__ . '/../inc/migrator.php';
page_title('Teklifler');

// Hızlı migration aksiyonu (alert butonu için)
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
    redirect('teklifler.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $cari_id  = (int)($_POST['cari_id'] ?? 0) ?: null;
        $tek_no   = clean($_POST['teklif_no'] ?? '');
        $musteri  = clean($_POST['musteri_ad'] ?? '');
        $tel      = clean($_POST['musteri_telefon'] ?? '');
        $eposta   = clean($_POST['musteri_eposta'] ?? '');
        $adres    = clean($_POST['musteri_adres'] ?? '');
        $konu     = clean($_POST['konu'] ?? '');
        $tarih    = $_POST['teklif_tarihi'] ?? date('Y-m-d');
        $gecerli  = $_POST['gecerlilik_tarihi'] ?? date('Y-m-d', strtotime('+15 days'));
        $iskonto  = (float)str_replace(',', '.', (string)($_POST['iskonto'] ?? 0));
        $notlar   = trim($_POST['notlar'] ?? '');
        $sartlar  = trim($_POST['sartlar'] ?? '');

        if (!$tek_no || !$musteri || !$konu) {
            flash_set('err', 'Teklif no, müşteri adı ve konu zorunludur.');
            redirect(SITE_URL . '/admin/teklifler.php' . ($id ? '?duzenle=' . $id : '?ekle=1'));
        }

        // Kalemleri topla
        $kalemler = [];
        $aciks = $_POST['kalem_aciklama']    ?? [];
        $miks  = $_POST['kalem_miktar']      ?? [];
        $bims  = $_POST['kalem_birim']       ?? [];
        $bfs   = $_POST['kalem_birim_fiyat'] ?? [];
        $isks  = $_POST['kalem_iskonto']     ?? [];
        $kdvs  = $_POST['kalem_kdv']         ?? [];
        $upids = $_POST['kalem_urun_id']     ?? [];

        $ara_top = 0; $kdv_top = 0;
        $sira = 0;
        foreach ($aciks as $i => $ac) {
            $ac = clean((string)$ac);
            if (!$ac) continue;
            $miktar = (float)str_replace(',', '.', (string)($miks[$i] ?? 1));
            $birim  = clean((string)($bims[$i] ?? 'Adet'));
            $bf     = (float)str_replace(',', '.', (string)($bfs[$i] ?? 0));
            $isk    = (float)str_replace(',', '.', (string)($isks[$i] ?? 0));
            $kdv    = (float)($kdvs[$i] ?? 20);
            $up     = (int)($upids[$i] ?? 0) ?: null;
            $brut   = $miktar * $bf;
            $netto  = $brut * (1 - $isk/100);
            $kdv_t  = $netto * ($kdv/100);
            $top    = $netto + $kdv_t;
            $kalemler[] = ['sira'=>++$sira, 'ad'=>$ac, 'miktar'=>$miktar, 'birim'=>$birim, 'bf'=>$bf, 'isk'=>$isk, 'kdv'=>$kdv, 'up'=>$up, 'top'=>$top];
            $ara_top += $netto;
            $kdv_top += $kdv_t;
        }
        $genel = ($ara_top - $iskonto) + $kdv_top;

        if (empty($kalemler)) {
            flash_set('err', 'En az 1 kalem giriniz.');
            redirect($_SERVER['REQUEST_URI']);
        }

        if ($id) {
            db_run("UPDATE teklifler SET cari_id=?, teklif_no=?, musteri_ad=?, musteri_telefon=?, musteri_eposta=?, musteri_adres=?, konu=?, teklif_tarihi=?, gecerlilik_tarihi=?, ara_toplam=?, iskonto_tutar=?, kdv_toplam=?, genel_toplam=?, notlar=?, sartlar=? WHERE id=?",
                [$cari_id, $tek_no, $musteri, $tel ?: null, $eposta ?: null, $adres ?: null, $konu, $tarih, $gecerli, $ara_top, $iskonto, $kdv_top, $genel, $notlar ?: null, $sartlar ?: null, $id]);
            db_run("DELETE FROM teklif_kalemleri WHERE teklif_id=?", [$id]);
            log_yaz('teklif_guncelle', "Teklif: $tek_no (#$id)", (int)$_kul['id']);
        } else {
            // Public token üret
            $token = bin2hex(random_bytes(16));
            db_run("INSERT INTO teklifler (teklif_no, cari_id, musteri_ad, musteri_telefon, musteri_eposta, musteri_adres, konu, teklif_tarihi, gecerlilik_tarihi, durum, ara_toplam, iskonto_tutar, kdv_toplam, genel_toplam, notlar, sartlar, public_token, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$tek_no, $cari_id, $musteri, $tel ?: null, $eposta ?: null, $adres ?: null, $konu, $tarih, $gecerli, 'taslak', $ara_top, $iskonto, $kdv_top, $genel, $notlar ?: null, $sartlar ?: null, $token, (int)$_kul['id']]);
            $id = (int)db()->lastInsertId();
            db_run("INSERT INTO teklif_log (teklif_id, olay, aciklama) VALUES (?,?,?)", [$id, 'olusturuldu', "Teklif oluşturuldu: $tek_no"]);
            log_yaz('teklif_ekle', "Teklif: $tek_no (#$id)", (int)$_kul['id']);
        }

        // Kalemleri ekle
        $stmt = db()->prepare("INSERT INTO teklif_kalemleri (teklif_id, sira, urun_id, aciklama, miktar, birim, birim_fiyat, iskonto_yuzde, kdv_orani, toplam) VALUES (?,?,?,?,?,?,?,?,?,?)");
        foreach ($kalemler as $k) {
            $stmt->execute([$id, $k['sira'], $k['up'], $k['ad'], $k['miktar'], $k['birim'], $k['bf'], $k['isk'], $k['kdv'], $k['top']]);
        }

        flash_set('ok', 'Teklif kaydedildi.');
        redirect(SITE_URL . '/admin/teklifler.php?duzenle=' . $id);
    }

    if ($islem === 'durum_degistir' && $id) {
        $yeni_durum = $_POST['yeni_durum'] ?? '';
        $izinli_durumlar = ['taslak','gonderildi','goruntulendi','kabul','red','iptal','faturalandi'];
        if (in_array($yeni_durum, $izinli_durumlar, true)) {
            db_run("UPDATE teklifler SET durum=? WHERE id=?", [$yeni_durum, $id]);
            db_run("INSERT INTO teklif_log (teklif_id, olay, aciklama) VALUES (?,?,?)", [$id, 'durum_degisti', "Yeni durum: $yeni_durum"]);
            log_yaz('teklif_durum', "Teklif #$id → $yeni_durum", (int)$_kul['id']);
            flash_set('ok', 'Durum güncellendi.');
        }
        redirect(SITE_URL . '/admin/teklifler.php?duzenle=' . $id);
    }

    if ($islem === 'sil' && $id) {
        $t = db_get("SELECT teklif_no FROM teklifler WHERE id=?", [$id]);
        if ($t) {
            db_run("DELETE FROM teklif_kalemleri WHERE teklif_id=?", [$id]);
            db_run("DELETE FROM teklif_log WHERE teklif_id=?", [$id]);
            db_run("DELETE FROM teklifler WHERE id=?", [$id]);
            log_yaz('teklif_sil', "Teklif silindi: {$t['teklif_no']} (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Teklif silindi.');
        }
        redirect(SITE_URL . '/admin/teklifler.php');
    }

    if ($islem === 'faturaya_donustur' && $id) {
        $t = db_get("SELECT * FROM teklifler WHERE id=?", [$id]);
        if ($t && $t['cari_id']) {
            $kalemler = db_all("SELECT * FROM teklif_kalemleri WHERE teklif_id=? ORDER BY sira", [$id]);
            $fno = 'F-' . date('Ymd') . '-' . str_pad((string)((int)(db_get("SELECT COUNT(*) c FROM faturalar WHERE DATE(olusturma)=CURDATE()")['c'] ?? 0) + 1), 3, '0', STR_PAD_LEFT);
            db_run("INSERT INTO faturalar (cari_id, fatura_no, tip, tarih, ara_toplam, iskonto, kdv_toplam, genel_toplam, odeme_durumu, notlar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                [$t['cari_id'], $fno, 'satis', date('Y-m-d'), $t['ara_toplam'], $t['iskonto_tutar'], $t['kdv_toplam'], $t['genel_toplam'], 'odenmedi', "Teklif #{$t['teklif_no']} dönüştürüldü. " . ($t['notlar'] ?? ''), (int)$_kul['id']]);
            $fid = (int)db()->lastInsertId();
            $stmt = db()->prepare("INSERT INTO fatura_kalemleri (fatura_id, urun_id, ad, miktar, birim, birim_fiyat, iskonto_yuzde, kdv_orani, toplam) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($kalemler as $k) {
                $stmt->execute([$fid, $k['urun_id'], $k['aciklama'], $k['miktar'], $k['birim'], $k['birim_fiyat'], $k['iskonto_yuzde'], $k['kdv_orani'], $k['toplam']]);
            }
            db_run("INSERT INTO cari_hareketler (cari_id, tarih, tip, belge_tip, belge_id, belge_no, aciklama, tutar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?)",
                [$t['cari_id'], date('Y-m-d'), 'borc', 'fatura', $fid, $fno, "Fatura: $fno (Teklif {$t['teklif_no']})", $t['genel_toplam'], (int)$_kul['id']]);
            db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$t['genel_toplam'], $t['cari_id']]);
            db_run("UPDATE teklifler SET durum='faturalandi' WHERE id=?", [$id]);
            db_run("INSERT INTO teklif_log (teklif_id, olay, aciklama) VALUES (?,?,?)", [$id, 'faturalandi', "Fatura #$fid: $fno"]);
            log_yaz('teklif_fatura', "Teklif → Fatura: $fno", (int)$_kul['id']);
            flash_set('ok', "Fatura oluşturuldu: $fno");
            redirect(SITE_URL . '/admin/faturalar.php?duzenle=' . $fid);
        } else {
            flash_set('err', 'Faturaya dönüştürmek için cari seçili olmalı. Önce müşteriyi cariye dönüştürün.');
            redirect(SITE_URL . '/admin/teklifler.php?duzenle=' . $id);
        }
    }

    redirect($_SERVER['REQUEST_URI']);
}

$ekle    = isset($_GET['ekle']);
$duzenle = (int)($_GET['duzenle'] ?? 0);
$mod     = ($ekle || $duzenle) ? 'form' : 'liste';

require_once __DIR__ . '/_header.php';

/* ===== FORM ===== */
if ($mod === 'form') {
    $t = $duzenle ? db_get("SELECT * FROM teklifler WHERE id=?", [$duzenle]) : null;
    if ($duzenle && !$t) { flash_set('err','Teklif bulunamadı.'); redirect(SITE_URL.'/admin/teklifler.php'); }
    $kalemler = $duzenle ? db_all("SELECT * FROM teklif_kalemleri WHERE teklif_id=? ORDER BY sira", [$duzenle]) : [];
    $cariler = db_all("SELECT id, cari_kodu, unvan, telefon, eposta, adres FROM cariler WHERE aktif=1 ORDER BY unvan");
    $urunler = db_all("SELECT id, ad, fiyat, kdv_orani FROM urunler WHERE aktif=1 ORDER BY ad");
    $loglar  = $duzenle ? db_all("SELECT * FROM teklif_log WHERE teklif_id=? ORDER BY id DESC LIMIT 30", [$duzenle]) : [];
    $sonraki_no = 'TKL-' . date('Ymd') . '-' . str_pad((string)((int)(db_get("SELECT COUNT(*) c FROM teklifler WHERE DATE(olusturma)=CURDATE()")['c'] ?? 0) + 1), 3, '0', STR_PAD_LEFT);
    $vars_gun = (int)(ayar('teklif_varsayilan_gecerlilik_gun', '15'));
    $vars_kdv = (int)(ayar('teklif_varsayilan_kdv', '20'));
    $vars_sartlar = (string)ayar('teklif_varsayilan_sartlar', '');

    $public_url = $t && $t['public_token'] ? SITE_URL . '/teklif/' . $t['public_token'] : '';
?>
<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $duzenle ? 'Teklif: ' . e($t['teklif_no']) : 'Yeni Teklif' ?></h1>
        <?php if ($duzenle):
            $durum_renk = ['taslak'=>'info','gonderildi'=>'warn','goruntulendi'=>'warn','kabul'=>'ok','red'=>'danger','iptal'=>'danger','faturalandi'=>'ok'];
            $durum_label = ['taslak'=>'Taslak','gonderildi'=>'Gönderildi','goruntulendi'=>'Görüntülendi','kabul'=>'KABUL','red'=>'Reddedildi','iptal'=>'İptal','faturalandi'=>'Faturalandı'];
        ?>
            <p class="page-sub">
                <span class="badge badge-<?= $durum_renk[$t['durum']] ?? 'info' ?>"><?= $durum_label[$t['durum']] ?? $t['durum'] ?></span>
                Toplam: <strong><?= tl((float)$t['genel_toplam']) ?></strong>
                · Geçerlilik: <?= tarih_tr($t['gecerlilik_tarihi']) ?>
                <?php
                    $kalan_gun = (int)((strtotime($t['gecerlilik_tarihi']) - time()) / 86400);
                    if ($t['durum'] === 'gonderildi' || $t['durum'] === 'goruntulendi'):
                ?>
                    · <?php if ($kalan_gun >= 0): ?>
                        <strong style="color:var(--c-orange)"><?= $kalan_gun ?> gün kaldı</strong>
                    <?php else: ?>
                        <strong style="color:var(--c-red)">SÜRESİ DOLDU</strong>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <?php if ($duzenle): ?>
            <a href="teklif-yazdir.php?id=<?= $t['id'] ?>" target="_blank" class="btn btn-out"><i class="fas fa-print"></i> Yazdır</a>
        <?php endif; ?>
        <a href="teklifler.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye</a>
    </div>
</div>

<?php if ($duzenle && $public_url): ?>
<div class="card" style="border-left:3px solid var(--c-orange);background:rgba(255,140,0,.05)">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
        <div>
            <h3 style="margin:0 0 4px"><i class="fas fa-link" style="color:var(--c-orange)"></i> Müşteri Linki</h3>
            <p style="margin:0;color:var(--c-muted);font-size:.88rem">Bu linki müşteriye gönderin — kendi tarafında teklifi görebilir, kabul/red edebilir.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <input type="text" class="input" readonly value="<?= e($public_url) ?>" id="publicUrl" style="min-width:300px;font-family:monospace;font-size:.85rem">
            <button type="button" class="btn btn-pri btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('publicUrl').value);this.innerHTML='<i class=\'fas fa-check\'></i> Kopyalandı';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i> Kopyala',2000)"><i class="fas fa-copy"></i> Kopyala</button>
            <a href="<?= e($public_url) ?>" target="_blank" class="btn btn-out btn-sm"><i class="fas fa-external-link-alt"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <h3 style="margin:0 0 10px"><i class="fas fa-arrows-rotate" style="color:var(--c-orange)"></i> Durum İşlemleri</h3>
    <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="durum_degistir">
        <input type="hidden" name="id" value="<?= $t['id'] ?>">
        <select name="yeni_durum" class="input" style="max-width:200px">
            <option value="taslak"        <?= $t['durum']==='taslak'?'selected':'' ?>>Taslak</option>
            <option value="gonderildi"    <?= $t['durum']==='gonderildi'?'selected':'' ?>>Gönderildi</option>
            <option value="goruntulendi"  <?= $t['durum']==='goruntulendi'?'selected':'' ?>>Görüntülendi</option>
            <option value="kabul"         <?= $t['durum']==='kabul'?'selected':'' ?>>Kabul Edildi</option>
            <option value="red"           <?= $t['durum']==='red'?'selected':'' ?>>Reddedildi</option>
            <option value="iptal"         <?= $t['durum']==='iptal'?'selected':'' ?>>İptal</option>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-save"></i> Durumu Güncelle</button>

        <?php if ($t['durum'] === 'kabul' && $t['cari_id']): ?>
        </form>
        <form method="post" style="display:inline-block;margin-left:8px">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="faturaya_donustur">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <button class="btn btn-pri btn-sm" data-onay="Bu teklif için fatura oluşturulsun mu?"><i class="fas fa-file-invoice-dollar"></i> Faturaya Dönüştür</button>
        </form>
        <?php elseif ($t['durum'] === 'kabul' && !$t['cari_id']): ?>
        </form>
        <span style="color:var(--c-muted);font-size:.85rem;margin-left:8px"><i class="fas fa-info-circle"></i> Faturaya dönüştürmek için müşteriyi önce cari kayıt olarak ekleyin.</span>
        <?php else: ?>
        </form>
        <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="post" id="teklifForm">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="kaydet">
        <input type="hidden" name="id" value="<?= $t['id'] ?? 0 ?>">

        <h3 style="margin:0 0 10px"><i class="fas fa-user"></i> Müşteri Bilgileri</h3>
        <div class="form-row cols-3">
            <div class="field"><label>Mevcut Cari (opsiyonel)</label>
                <select name="cari_id" id="cariSec">
                    <option value="">-- Yeni / cariye bağlı değil --</option>
                    <?php foreach ($cariler as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            data-unvan="<?= e($c['unvan']) ?>"
                            data-tel="<?= e((string)$c['telefon']) ?>"
                            data-eposta="<?= e((string)$c['eposta']) ?>"
                            data-adres="<?= e((string)$c['adres']) ?>"
                            <?= ($t['cari_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['cari_kodu']) ?> — <?= e($c['unvan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Teklif No *</label><input class="input" type="text" name="teklif_no" value="<?= e($t['teklif_no'] ?? $sonraki_no) ?>" required maxlength="40"></div>
            <div class="field"><label>Konu *</label><input class="input" type="text" name="konu" value="<?= e($t['konu'] ?? '') ?>" required maxlength="255" placeholder="Ör: Daire kombi montajı"></div>
        </div>

        <div class="form-row cols-3">
            <div class="field"><label>Müşteri Adı / Ünvan *</label><input class="input" type="text" name="musteri_ad" id="musteriAd" value="<?= e($t['musteri_ad'] ?? '') ?>" required maxlength="200"></div>
            <div class="field"><label>Telefon</label><input class="input" type="text" name="musteri_telefon" id="musteriTel" value="<?= e($t['musteri_telefon'] ?? '') ?>" maxlength="40"></div>
            <div class="field"><label>E-posta</label><input class="input" type="email" name="musteri_eposta" id="musteriEposta" value="<?= e($t['musteri_eposta'] ?? '') ?>" maxlength="150"></div>
        </div>

        <div class="form-row">
            <div class="field"><label>Adres</label><textarea class="input" name="musteri_adres" id="musteriAdres" rows="2"><?= e($t['musteri_adres'] ?? '') ?></textarea></div>
        </div>

        <h3 style="margin:20px 0 10px"><i class="fas fa-calendar"></i> Tarihler</h3>
        <div class="form-row cols-3">
            <div class="field"><label>Teklif Tarihi *</label><input class="input" type="date" name="teklif_tarihi" value="<?= e($t['teklif_tarihi'] ?? date('Y-m-d')) ?>" required></div>
            <div class="field"><label>Geçerlilik Tarihi *</label><input class="input" type="date" name="gecerlilik_tarihi" value="<?= e($t['gecerlilik_tarihi'] ?? date('Y-m-d', strtotime('+'.$vars_gun.' days'))) ?>" required></div>
            <div class="field"><label>Genel İskonto (₺)</label><input class="input" type="number" name="iskonto" id="iskonto" step="0.01" min="0" value="<?= e((string)($t['iskonto_tutar'] ?? 0)) ?>"></div>
        </div>

        <h3 style="margin:20px 0 10px"><i class="fas fa-list"></i> Teklif Kalemleri</h3>
        <div class="tbl-wrap">
        <table class="tbl" id="kalemTbl">
            <thead>
                <tr><th style="min-width:240px">Açıklama / Hizmet *</th><th>Miktar</th><th>Birim</th><th>Birim Fiyat (₺)</th><th>İsk %</th><th>KDV %</th><th style="text-align:right">Toplam</th><th></th></tr>
            </thead>
            <tbody id="kalemBody">
                <?php foreach ($kalemler as $k): ?>
                <tr>
                    <td>
                        <input type="hidden" name="kalem_urun_id[]" value="<?= (int)$k['urun_id'] ?>" class="urun_id">
                        <input class="input kalem_aciklama" type="text" name="kalem_aciklama[]" value="<?= e($k['aciklama']) ?>" required list="urunListe" placeholder="Hizmet veya ürün açıklaması">
                    </td>
                    <td><input class="input m" type="number" name="kalem_miktar[]" step="0.001" value="<?= e((string)$k['miktar']) ?>" style="width:90px"></td>
                    <td><input class="input" type="text" name="kalem_birim[]" value="<?= e($k['birim']) ?>" style="width:80px"></td>
                    <td><input class="input bf" type="number" name="kalem_birim_fiyat[]" step="0.01" value="<?= e((string)$k['birim_fiyat']) ?>" style="width:120px"></td>
                    <td><input class="input isk" type="number" name="kalem_iskonto[]" step="0.01" value="<?= e((string)$k['iskonto_yuzde']) ?>" style="width:70px"></td>
                    <td>
                        <select name="kalem_kdv[]" class="kdv" style="width:75px">
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

        <div class="form-row" style="margin-top:14px">
            <div class="field"><label>Notlar (müşteriye görünür)</label><textarea class="input" name="notlar" rows="3" placeholder="Teklifinize özel notlar — örn: kurulum süresi, bonus hediye, vb."><?= e($t['notlar'] ?? '') ?></textarea></div>
        </div>
        <div class="form-row">
            <div class="field"><label>Şartlar / Ödeme Koşulları</label><textarea class="input" name="sartlar" rows="4" placeholder="Ödeme koşulları, geçerlilik süresi, kurulum şartları, vb."><?= e($t['sartlar'] ?? $vars_sartlar) ?></textarea></div>
        </div>

        <div class="form-actions">
            <button class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
            <a href="teklifler.php" class="btn btn-out">İptal</a>
        </div>
    </form>
    <?php if ($duzenle): ?>
    <form method="post" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="sil">
        <input type="hidden" name="id" value="<?= $t['id'] ?>">
        <button class="btn btn-danger btn-sm" data-onay="Teklif silinsin mi? Bu işlem geri alınamaz."><i class="fas fa-trash"></i> Teklifi Sil</button>
    </form>
    <?php endif; ?>
</div>

<?php if ($duzenle && $loglar): ?>
<details class="card" style="margin-top:14px">
    <summary style="cursor:pointer;font-weight:700"><i class="fas fa-clock-rotate-left"></i> Olay Geçmişi (<?= count($loglar) ?>)</summary>
    <table class="tbl" style="margin-top:14px">
        <thead><tr><th>Tarih</th><th>Olay</th><th>Açıklama</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach ($loglar as $lg): ?>
            <tr>
                <td class="num"><?= tarih_tr($lg['olusturma'], true) ?></td>
                <td><span class="badge badge-info"><?= e($lg['olay']) ?></span></td>
                <td><?= e((string)$lg['aciklama']) ?></td>
                <td class="num"><?= e((string)$lg['ip']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</details>
<?php endif; ?>

<script>
(function(){
    const body = document.getElementById('kalemBody');
    const formatTL = n => n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' ₺';
    const VARS_KDV = <?= $vars_kdv ?>;

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
        const iskGenel = parseFloat((document.getElementById('iskonto')?.value || '0').replace(',', '.')) || 0;
        document.getElementById('araTop').textContent = formatTL(ara);
        document.getElementById('iskTop').textContent = formatTL(iskGenel);
        document.getElementById('kdvTop').textContent = formatTL(kdvT);
        document.getElementById('genelTop').textContent = formatTL(ara - iskGenel + kdvT);
    }

    function bagla(tr){
        tr.querySelectorAll('input,select').forEach(el => el.addEventListener('input', hesapla));
        tr.querySelector('.sil').addEventListener('click', () => { tr.remove(); hesapla(); });
        const ad = tr.querySelector('.kalem_aciklama');
        ad.addEventListener('change', () => {
            const opts = document.querySelectorAll('#urunListe option');
            for (const opt of opts) {
                if (opt.value === ad.value) {
                    tr.querySelector('.urun_id').value = opt.dataset.id || 0;
                    if (!tr.querySelector('.bf').value || tr.querySelector('.bf').value == '0') tr.querySelector('.bf').value = opt.dataset.fiyat || 0;
                    tr.querySelector('.kdv').value = opt.dataset.kdv || VARS_KDV;
                    hesapla();
                    break;
                }
            }
        });
    }

    document.getElementById('kalemEkle').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="hidden" name="kalem_urun_id[]" value="0" class="urun_id"><input class="input kalem_aciklama" type="text" name="kalem_aciklama[]" required list="urunListe" placeholder="Hizmet veya ürün açıklaması"></td>
            <td><input class="input m" type="number" name="kalem_miktar[]" step="0.001" value="1" style="width:90px"></td>
            <td><input class="input" type="text" name="kalem_birim[]" value="Adet" style="width:80px"></td>
            <td><input class="input bf" type="number" name="kalem_birim_fiyat[]" step="0.01" value="0" style="width:120px"></td>
            <td><input class="input isk" type="number" name="kalem_iskonto[]" step="0.01" value="0" style="width:70px"></td>
            <td><select name="kalem_kdv[]" class="kdv" style="width:75px"><option value="0">0</option><option value="1">1</option><option value="8">8</option><option value="10">10</option><option value="18">18</option><option value="20" selected>20</option></select></td>
            <td class="num top" style="text-align:right">0,00 ₺</td>
            <td><button type="button" class="btn btn-danger btn-sm sil"><i class="fas fa-times"></i></button></td>
        `;
        body.appendChild(tr);
        bagla(tr);
        tr.querySelector('.kalem_aciklama').focus();
    });

    body.querySelectorAll('tr').forEach(bagla);
    document.getElementById('iskonto').addEventListener('input', hesapla);

    // Cari seçimi → bilgileri otomatik doldur
    const cariSec = document.getElementById('cariSec');
    if (cariSec) {
        cariSec.addEventListener('change', () => {
            const opt = cariSec.selectedOptions[0];
            if (!opt || !opt.value) return;
            if (!document.getElementById('musteriAd').value) document.getElementById('musteriAd').value = opt.dataset.unvan || '';
            if (!document.getElementById('musteriTel').value) document.getElementById('musteriTel').value = opt.dataset.tel || '';
            if (!document.getElementById('musteriEposta').value) document.getElementById('musteriEposta').value = opt.dataset.eposta || '';
            if (!document.getElementById('musteriAdres').value) document.getElementById('musteriAdres').value = opt.dataset.adres || '';
        });
    }

    // İlk yükleme: kalem yoksa bir tane ekle
    if (body.children.length === 0) document.getElementById('kalemEkle').click();
    else hesapla();
})();
</script>

<?php
} else {
    /* ===== LİSTE ===== */
    $arama = trim((string)($_GET['q'] ?? ''));
    $durum = $_GET['durum'] ?? '';
    $bas   = $_GET['bas']   ?? '';
    $bit   = $_GET['bit']   ?? '';
    $sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
    $limit = 20;
    $ofset = ($sayfa - 1) * $limit;

    $where = "1=1"; $params = [];
    if ($arama) {
        $where .= " AND (t.teklif_no LIKE ? OR t.musteri_ad LIKE ? OR t.konu LIKE ?)";
        array_push($params, "%$arama%", "%$arama%", "%$arama%");
    }
    if ($durum) { $where .= " AND t.durum=?"; $params[] = $durum; }
    if ($bas)   { $where .= " AND t.teklif_tarihi>=?"; $params[] = $bas; }
    if ($bit)   { $where .= " AND t.teklif_tarihi<=?"; $params[] = $bit; }

    $tablo_hatasi = null; $toplam = 0; $toplam_sayfa = 1; $rows = []; $ozet = ['toplam_teklif'=>0, 'kabul_tutar'=>0, 'beklemede'=>0];
    try {
        $toplam = (int)db_get("SELECT COUNT(*) c FROM teklifler t WHERE $where", $params)['c'];
        $toplam_sayfa = max(1, (int)ceil($toplam / $limit));
        $rows = db_all("SELECT t.* FROM teklifler t WHERE $where ORDER BY t.teklif_tarihi DESC, t.id DESC LIMIT $limit OFFSET $ofset", $params);
        $ozet = db_get("SELECT
            COALESCE(SUM(t.genel_toplam),0) toplam_teklif,
            COALESCE(SUM(CASE WHEN t.durum IN ('kabul','faturalandi') THEN t.genel_toplam ELSE 0 END),0) kabul_tutar,
            COALESCE(SUM(CASE WHEN t.durum IN ('gonderildi','goruntulendi') THEN t.genel_toplam ELSE 0 END),0) beklemede
        FROM teklifler t WHERE $where", $params);
    } catch (Throwable $e) {
        $tablo_hatasi = $e->getMessage();
    }
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Teklifler</h1>
        <p class="page-sub"><?= $toplam ?> teklif · Toplam: <strong><?= tl((float)$ozet['toplam_teklif']) ?></strong> · Kabul: <strong style="color:var(--c-green)"><?= tl((float)$ozet['kabul_tutar']) ?></strong> · Beklemede: <strong style="color:var(--c-orange)"><?= tl((float)$ozet['beklemede']) ?></strong></p>
    </div>
    <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Teklif</a>
</div>

<?php if ($tablo_hatasi): ?>
<div class="alert alert-err">
    <strong><i class="fas fa-database"></i> Teklif tabloları henüz oluşturulmamış.</strong>
    Sistem migration sistemi üzerinden bu tabloları otomatik oluşturur. Aşağıdaki butona tıkla — tek tık ile uygulanır.
    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
        <form method="post" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="migrasyon_uygula_hizli">
            <button class="btn btn-pri"><i class="fas fa-database"></i> Migrasyonları Şimdi Uygula</button>
        </form>
        <a href="sistem-tani.php" class="btn btn-out"><i class="fas fa-stethoscope"></i> Sistem Tanı</a>
    </div>
    <details style="margin-top:8px"><summary style="cursor:pointer;color:var(--c-muted);font-size:.85rem">Teknik detay</summary><pre style="font-size:.78rem;color:#fca5a5;margin-top:6px;font-family:monospace"><?= e($tablo_hatasi) ?></pre></details>
</div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="filters">
        <input class="input" type="search" name="q" value="<?= e($arama) ?>" placeholder="Teklif no / müşteri / konu…">
        <select name="durum">
            <option value="">Tüm durumlar</option>
            <option value="taslak"        <?= $durum==='taslak'?'selected':'' ?>>Taslak</option>
            <option value="gonderildi"    <?= $durum==='gonderildi'?'selected':'' ?>>Gönderildi</option>
            <option value="goruntulendi"  <?= $durum==='goruntulendi'?'selected':'' ?>>Görüntülendi</option>
            <option value="kabul"         <?= $durum==='kabul'?'selected':'' ?>>Kabul</option>
            <option value="red"           <?= $durum==='red'?'selected':'' ?>>Red</option>
            <option value="iptal"         <?= $durum==='iptal'?'selected':'' ?>>İptal</option>
            <option value="faturalandi"   <?= $durum==='faturalandi'?'selected':'' ?>>Faturalandı</option>
        </select>
        <input class="input" type="date" name="bas" value="<?= e($bas) ?>" style="max-width:150px">
        <input class="input" type="date" name="bit" value="<?= e($bit) ?>" style="max-width:150px">
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i></button>
        <?php if ($arama || $durum || $bas || $bit): ?><a href="teklifler.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
    <thead><tr><th>No</th><th>Müşteri</th><th>Konu</th><th>Tarih</th><th>Geçerlilik</th><th style="text-align:right">Tutar</th><th>Durum</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="8" class="empty"><i class="fas fa-file-circle-question" style="font-size:2rem;display:block;margin-bottom:8px"></i>Teklif bulunamadı.<br><a href="?ekle=1" style="color:var(--c-orange);font-weight:700;margin-top:8px;display:inline-block">İlk teklifi oluştur →</a></td></tr>
    <?php else: foreach ($rows as $r):
        $durum_renk = ['taslak'=>'info','gonderildi'=>'warn','goruntulendi'=>'warn','kabul'=>'ok','red'=>'danger','iptal'=>'danger','faturalandi'=>'ok'];
        $durum_label = ['taslak'=>'Taslak','gonderildi'=>'Gönderildi','goruntulendi'=>'Görüntülendi','kabul'=>'Kabul','red'=>'Red','iptal'=>'İptal','faturalandi'=>'Faturalandı'];
        $kalan_gun = (int)((strtotime($r['gecerlilik_tarihi']) - time()) / 86400);
        $sure_doldu = $kalan_gun < 0 && in_array($r['durum'], ['gonderildi','goruntulendi'], true);
    ?>
        <tr>
            <td><code><?= e($r['teklif_no']) ?></code></td>
            <td><?= e((string)$r['musteri_ad']) ?></td>
            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e((string)$r['konu']) ?></td>
            <td class="num"><?= tarih_tr($r['teklif_tarihi']) ?></td>
            <td class="num">
                <?= tarih_tr($r['gecerlilik_tarihi']) ?>
                <?php if ($sure_doldu): ?><br><small style="color:var(--c-red);font-weight:700">SÜRE DOLDU</small><?php endif; ?>
            </td>
            <td class="num" style="text-align:right;font-weight:600"><?= tl((float)$r['genel_toplam']) ?></td>
            <td><span class="badge badge-<?= $durum_renk[$r['durum']] ?? 'info' ?>"><?= $durum_label[$r['durum']] ?? $r['durum'] ?></span></td>
            <td>
                <a href="?duzenle=<?= $r['id'] ?>" class="btn btn-out btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
                <a href="teklif-yazdir.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-out btn-sm" title="Yazdır"><i class="fas fa-print"></i></a>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/teklifler.php?' . http_build_query(array_filter(['q'=>$arama,'durum'=>$durum,'bas'=>$bas,'bit'=>$bit]));
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

<?php } ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
