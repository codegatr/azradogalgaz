<?php
require_once __DIR__ . '/_baslat.php';
page_title('Fişler');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $cari_id  = (int)($_POST['cari_id'] ?? 0) ?: null;
        $fis_no   = clean($_POST['fis_no'] ?? '');
        $tip      = in_array($_POST['tip'] ?? '', ['satis','tahsilat','odeme','gider','gelir'], true) ? $_POST['tip'] : 'satis';
        $tarih    = $_POST['tarih'] ?? date('Y-m-d');
        $aciklama = clean($_POST['aciklama'] ?? '');
        $yontem   = in_array($_POST['odeme_yontemi'] ?? '', ['nakit','kart','havale','cek','senet'], true) ? $_POST['odeme_yontemi'] : 'nakit';
        $notlar   = clean($_POST['notlar'] ?? '');

        if (!$fis_no) { flash_set('err', 'Fiş no zorunludur.'); redirect($_SERVER['REQUEST_URI']); }

        $kalemler = [];
        $adlar = $_POST['kalem_ad']      ?? [];
        $miks  = $_POST['kalem_miktar']  ?? [];
        $bims  = $_POST['kalem_birim']   ?? [];
        $bfs   = $_POST['kalem_birim_fiyat'] ?? [];
        $kdvs  = $_POST['kalem_kdv']     ?? [];
        $upids = $_POST['kalem_urun_id'] ?? [];
        $ara_top = 0; $kdv_top = 0;
        foreach ($adlar as $i => $ad) {
            $ad = clean((string)$ad);
            if (!$ad) continue;
            $miktar = (float)str_replace(',', '.', (string)($miks[$i] ?? 1));
            $birim  = clean((string)($bims[$i] ?? 'Adet'));
            $bf     = (float)str_replace(',', '.', (string)($bfs[$i] ?? 0));
            $kdv    = (int)($kdvs[$i] ?? 20);
            $up     = (int)($upids[$i] ?? 0) ?: null;
            $netto  = $miktar * $bf;
            $kdv_t  = $netto * ($kdv/100);
            $top    = $netto + $kdv_t;
            $kalemler[] = compact('ad','miktar','birim','bf','kdv','up','top');
            $ara_top += $netto;
            $kdv_top += $kdv_t;
        }
        if (in_array($tip, ['tahsilat','odeme','gider','gelir'], true) && empty($kalemler)) {
            $tek_tutar = (float)str_replace(',', '.', (string)($_POST['tek_tutar'] ?? 0));
            if ($tek_tutar > 0) { $ara_top = $tek_tutar; $kdv_top = 0; }
        }
        $genel = $ara_top + $kdv_top;
        if ($genel <= 0) { flash_set('err', 'Tutar 0\'dan büyük olmalı.'); redirect($_SERVER['REQUEST_URI']); }

        if ($id) {
            $eski = db_get("SELECT * FROM fisler WHERE id=?", [$id]);
            db_run("UPDATE fisler SET cari_id=?, fis_no=?, tip=?, tarih=?, aciklama=?, ara_toplam=?, kdv_toplam=?, genel_toplam=?, odeme_yontemi=?, notlar=? WHERE id=?",
                [$cari_id, $fis_no, $tip, $tarih, $aciklama, $ara_top, $kdv_top, $genel, $yontem, $notlar, $id]);
            db_run("DELETE FROM fis_kalemleri WHERE fis_id=?", [$id]);
            if ($eski && $eski['cari_id'] && in_array($eski['tip'], ['satis','tahsilat','odeme'], true)) {
                $eski_delta = match($eski['tip']) {
                    'satis'    => -$eski['genel_toplam'],
                    'tahsilat' =>  $eski['genel_toplam'],
                    'odeme'    => -$eski['genel_toplam'],
                    default    => 0,
                };
                db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$eski_delta, $eski['cari_id']]);
            }
            db_run("DELETE FROM cari_hareketler WHERE belge_tip='fis' AND belge_id=?", [$id]);
            log_yaz('fis_guncelle', "Fiş: $fis_no (#$id)", (int)$_kul['id']);
        } else {
            db_run("INSERT INTO fisler (cari_id, fis_no, tip, tarih, aciklama, ara_toplam, kdv_toplam, genel_toplam, odeme_yontemi, notlar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                [$cari_id, $fis_no, $tip, $tarih, $aciklama, $ara_top, $kdv_top, $genel, $yontem, $notlar, (int)$_kul['id']]);
            $id = (int)db()->lastInsertId();
            log_yaz('fis_ekle', "Fiş: $fis_no (#$id)", (int)$_kul['id']);
        }

        if ($kalemler) {
            $stmt = db()->prepare("INSERT INTO fis_kalemleri (fis_id, urun_id, ad, miktar, birim, birim_fiyat, kdv_orani, toplam) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($kalemler as $k) $stmt->execute([$id, $k['up'], $k['ad'], $k['miktar'], $k['birim'], $k['bf'], $k['kdv'], $k['top']]);
        }

        if ($cari_id && in_array($tip, ['satis','tahsilat','odeme'], true)) {
            $hareket_tip = match($tip) {
                'satis'    => 'borc',
                'tahsilat' => 'tahsilat',
                'odeme'    => 'odeme',
            };
            db_run("INSERT INTO cari_hareketler (cari_id, tarih, tip, belge_tip, belge_id, belge_no, aciklama, tutar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?)",
                [$cari_id, $tarih, $hareket_tip, 'fis', $id, $fis_no, $aciklama ?: "Fiş: $fis_no", $genel, (int)$_kul['id']]);
            $delta = match($tip) {
                'satis'    =>  $genel,
                'tahsilat' => -$genel,
                'odeme'    =>  $genel,
            };
            db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$delta, $cari_id]);
        }

        flash_set('ok', 'Fiş kaydedildi.');
        redirect(SITE_URL . '/admin/fisler.php?duzenle=' . $id);
    }

    if ($islem === 'sil' && $id) {
        $f = db_get("SELECT * FROM fisler WHERE id=?", [$id]);
        if ($f) {
            if ($f['cari_id'] && in_array($f['tip'], ['satis','tahsilat','odeme'], true)) {
                $delta = match($f['tip']) {
                    'satis'    => -$f['genel_toplam'],
                    'tahsilat' =>  $f['genel_toplam'],
                    'odeme'    => -$f['genel_toplam'],
                };
                db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$delta, $f['cari_id']]);
            }
            db_run("DELETE FROM cari_hareketler WHERE belge_tip='fis' AND belge_id=?", [$id]);
            db_run("DELETE FROM fis_kalemleri WHERE fis_id=?", [$id]);
            db_run("DELETE FROM fisler WHERE id=?", [$id]);
            log_yaz('fis_sil', "Fiş silindi: {$f['fis_no']} (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Fiş silindi.');
        }
        redirect(SITE_URL . '/admin/fisler.php');
    }
    redirect($_SERVER['REQUEST_URI']);
}

$ekle    = isset($_GET['ekle']);
$duzenle = (int)($_GET['duzenle'] ?? 0);
$mod     = ($ekle || $duzenle) ? 'form' : 'liste';

require_once __DIR__ . '/_header.php';

/* ===== FORM ===== */
if ($mod === 'form') {
    $f = $duzenle ? db_get("SELECT * FROM fisler WHERE id=?", [$duzenle]) : null;
    if ($duzenle && !$f) { flash_set('err','Fiş bulunamadı.'); redirect(SITE_URL.'/admin/fisler.php'); }
    $kalemler = $duzenle ? db_all("SELECT * FROM fis_kalemleri WHERE fis_id=? ORDER BY id", [$duzenle]) : [];
    $cariler = db_all("SELECT id, cari_kodu, unvan FROM cariler WHERE aktif=1 ORDER BY unvan");
    $urunler = db_all("SELECT id, ad, fiyat, kdv_orani FROM urunler WHERE aktif=1 ORDER BY ad");
    $sonraki_no = 'FS-' . date('Ymd') . '-' . str_pad((string)((int)(db_get("SELECT COUNT(*) c FROM fisler WHERE DATE(olusturma_tarihi)=CURDATE()")['c'] ?? 0) + 1), 3, '0', STR_PAD_LEFT);
?>
<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $duzenle ? 'Fiş: ' . e($f['fis_no']) : 'Yeni Fiş' ?></h1>
        <?php if ($duzenle): ?><p class="page-sub"><span class="badge badge-info"><?= ucfirst($f['tip']) ?></span> · <?= ucfirst($f['odeme_yontemi']) ?> · <strong><?= tl((float)$f['genel_toplam']) ?></strong></p><?php endif; ?>
    </div>
    <a href="fisler.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye</a>
</div>

<div class="card">
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="kaydet">
        <input type="hidden" name="id" value="<?= $f['id'] ?? 0 ?>">

        <div class="form-row cols-3">
            <div class="field"><label>Fiş No *</label><input class="input" type="text" name="fis_no" value="<?= e($f['fis_no'] ?? $sonraki_no) ?>" required maxlength="40"></div>
            <div class="field"><label>Tip</label>
                <select name="tip" id="tipSel">
                    <option value="satis"    <?= ($f['tip'] ?? 'satis')==='satis'?'selected':'' ?>>Peşin Satış</option>
                    <option value="tahsilat" <?= ($f['tip'] ?? '')==='tahsilat'?'selected':'' ?>>Tahsilat</option>
                    <option value="odeme"    <?= ($f['tip'] ?? '')==='odeme'?'selected':'' ?>>Ödeme</option>
                    <option value="gider"    <?= ($f['tip'] ?? '')==='gider'?'selected':'' ?>>Gider</option>
                    <option value="gelir"    <?= ($f['tip'] ?? '')==='gelir'?'selected':'' ?>>Gelir</option>
                </select>
            </div>
            <div class="field"><label>Tarih *</label><input class="input" type="date" name="tarih" value="<?= e($f['tarih'] ?? date('Y-m-d')) ?>" required></div>
        </div>

        <div class="form-row cols-2">
            <div class="field"><label>Cari (opsiyonel)</label>
                <select name="cari_id">
                    <option value="">-- Cari yok --</option>
                    <?php foreach ($cariler as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($f['cari_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['cari_kodu']) ?> — <?= e($c['unvan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Ödeme Yöntemi</label>
                <select name="odeme_yontemi">
                    <?php foreach (['nakit'=>'Nakit','kart'=>'Kart','havale'=>'Havale/EFT','cek'=>'Çek','senet'=>'Senet'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($f['odeme_yontemi'] ?? 'nakit')===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row"><div class="field"><label>Açıklama</label><input class="input" type="text" name="aciklama" value="<?= e($f['aciklama'] ?? '') ?>" maxlength="300"></div></div>

        <div class="form-row" id="tekTutarKutu" style="display:none">
            <div class="field"><label>Tutar (Kalem detayı yoksa)</label><input class="input" type="number" name="tek_tutar" step="0.01" min="0" value="<?= $duzenle && !$kalemler ? e((string)$f['genel_toplam']) : '' ?>" placeholder="Sadece tek tutarlık fiş için"></div>
        </div>

        <h3 style="margin:20px 0 10px">Kalemler <small style="font-weight:400;color:var(--c-muted)">(Satış için zorunlu, diğerleri için opsiyonel)</small></h3>
        <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr><th style="min-width:200px">Ürün/Hizmet</th><th>Miktar</th><th>Birim</th><th>Birim Fiyat</th><th>KDV %</th><th style="text-align:right">Toplam</th><th></th></tr></thead>
            <tbody id="kalemBody">
                <?php foreach ($kalemler as $k): ?>
                <tr>
                    <td><input type="hidden" name="kalem_urun_id[]" value="<?= (int)$k['urun_id'] ?>" class="urun_id"><input class="input kalem_ad" type="text" name="kalem_ad[]" value="<?= e($k['ad']) ?>" list="urunListe"></td>
                    <td><input class="input m" type="number" name="kalem_miktar[]" step="0.001" value="<?= e((string)$k['miktar']) ?>" style="width:90px"></td>
                    <td><input class="input" type="text" name="kalem_birim[]" value="<?= e($k['birim']) ?>" style="width:80px"></td>
                    <td><input class="input bf" type="number" name="kalem_birim_fiyat[]" step="0.01" value="<?= e((string)$k['birim_fiyat']) ?>" style="width:110px"></td>
                    <td><select name="kalem_kdv[]" class="kdv" style="width:70px"><?php foreach ([0,1,8,10,18,20] as $v): ?><option value="<?= $v ?>" <?= (int)$k['kdv_orani']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></td>
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
            <div style="display:flex;justify-content:space-between;font-size:.95rem;margin-bottom:6px"><span style="color:var(--c-muted)">KDV Toplam</span><strong id="kdvTop" class="num">0,00 ₺</strong></div>
            <hr style="margin:10px 0;border:0;border-top:1px solid var(--c-line)">
            <div style="display:flex;justify-content:space-between;font-size:1.2rem"><span>GENEL TOPLAM</span><strong id="genelTop" class="num" style="color:var(--c-orange)">0,00 ₺</strong></div>
        </div>

        <div class="form-row" style="margin-top:14px"><div class="field"><label>Notlar</label><textarea class="input" name="notlar" rows="2"><?= e($f['notlar'] ?? '') ?></textarea></div></div>

        <div class="form-actions">
            <button class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
            <a href="fisler.php" class="btn btn-out">İptal</a>
        </div>
    </form>
    <?php if ($duzenle): ?>
    <form method="post" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="sil">
        <input type="hidden" name="id" value="<?= $f['id'] ?>">
        <button class="btn btn-danger btn-sm" data-onay="Fiş silinsin mi?"><i class="fas fa-trash"></i> Fişi Sil</button>
    </form>
    <?php endif; ?>
</div>

<script>
(function(){
    const body = document.getElementById('kalemBody');
    const tipSel = document.getElementById('tipSel');
    const tekKutu = document.getElementById('tekTutarKutu');
    const formatTL = n => n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' ₺';

    function tipDegis(){ tekKutu.style.display = (tipSel.value === 'satis') ? 'none' : 'flex'; }
    tipSel.addEventListener('change', tipDegis); tipDegis();

    function hesapla(){
        let ara = 0, kdvT = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const m = parseFloat((tr.querySelector('.m')?.value || '0').replace(',', '.')) || 0;
            const bf = parseFloat((tr.querySelector('.bf')?.value || '0').replace(',', '.')) || 0;
            const kdv = parseFloat(tr.querySelector('.kdv')?.value || '0') || 0;
            const netto = m * bf;
            const k = netto * (kdv/100);
            tr.querySelector('.top').textContent = formatTL(netto + k);
            ara += netto; kdvT += k;
        });
        document.getElementById('araTop').textContent = formatTL(ara);
        document.getElementById('kdvTop').textContent = formatTL(kdvT);
        document.getElementById('genelTop').textContent = formatTL(ara + kdvT);
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
            <td><input type="hidden" name="kalem_urun_id[]" value="0" class="urun_id"><input class="input kalem_ad" type="text" name="kalem_ad[]" list="urunListe"></td>
            <td><input class="input m" type="number" name="kalem_miktar[]" step="0.001" value="1" style="width:90px"></td>
            <td><input class="input" type="text" name="kalem_birim[]" value="Adet" style="width:80px"></td>
            <td><input class="input bf" type="number" name="kalem_birim_fiyat[]" step="0.01" value="0" style="width:110px"></td>
            <td><select name="kalem_kdv[]" class="kdv" style="width:70px"><option>0</option><option>1</option><option>8</option><option>10</option><option>18</option><option value="20" selected>20</option></select></td>
            <td class="num top" style="text-align:right">0,00 ₺</td>
            <td><button type="button" class="btn btn-danger btn-sm sil"><i class="fas fa-times"></i></button></td>`;
        body.appendChild(tr); bagla(tr); tr.querySelector('.kalem_ad').focus();
    });

    body.querySelectorAll('tr').forEach(bagla);
    hesapla();
})();
</script>

<?php require_once __DIR__ . '/_footer.php'; return; }

/* ===== LİSTE ===== */
$arama = clean($_GET['q'] ?? '');
$tip   = $_GET['tip'] ?? '';
$bas   = $_GET['bas'] ?? '';
$bit   = $_GET['bit'] ?? '';
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 25; $ofset = ($sayfa-1)*$limit;

$where = "1=1"; $params = [];
if ($arama) { $where .= " AND (f.fis_no LIKE ? OR f.aciklama LIKE ? OR c.unvan LIKE ?)"; $w="%$arama%"; array_push($params,$w,$w,$w); }
if ($tip && in_array($tip, ['satis','tahsilat','odeme','gider','gelir'], true)) { $where .= " AND f.tip=?"; $params[] = $tip; }
if ($bas) { $where .= " AND f.tarih>=?"; $params[] = $bas; }
if ($bit) { $where .= " AND f.tarih<=?"; $params[] = $bit; }

$toplam = (int)db_get("SELECT COUNT(*) c FROM fisler f LEFT JOIN cariler c ON c.id=f.cari_id WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));
$rows = db_all("SELECT f.*, c.unvan, c.cari_kodu FROM fisler f LEFT JOIN cariler c ON c.id=f.cari_id WHERE $where ORDER BY f.tarih DESC, f.id DESC LIMIT $limit OFFSET $ofset", $params);
$ozet = db_get("SELECT
    COALESCE(SUM(CASE WHEN tip IN ('satis','gelir','tahsilat') THEN genel_toplam ELSE 0 END),0) gelir,
    COALESCE(SUM(CASE WHEN tip IN ('odeme','gider') THEN genel_toplam ELSE 0 END),0) gider
FROM fisler f LEFT JOIN cariler c ON c.id=f.cari_id WHERE $where", $params);
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Fişler</h1>
        <p class="page-sub"><?= $toplam ?> kayıt · Gelir: <strong style="color:var(--c-green)"><?= tl((float)$ozet['gelir']) ?></strong> · Gider: <strong style="color:var(--c-red)"><?= tl((float)$ozet['gider']) ?></strong></p>
    </div>
    <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Fiş</a>
</div>

<form method="get" class="toolbar">
    <div class="filters">
        <input class="input" type="search" name="q" value="<?= e($arama) ?>" placeholder="Fiş no / cari / açıklama…">
        <select name="tip">
            <option value="">Tüm tipler</option>
            <option value="satis"    <?= $tip==='satis'?'selected':'' ?>>Peşin Satış</option>
            <option value="tahsilat" <?= $tip==='tahsilat'?'selected':'' ?>>Tahsilat</option>
            <option value="odeme"    <?= $tip==='odeme'?'selected':'' ?>>Ödeme</option>
            <option value="gider"    <?= $tip==='gider'?'selected':'' ?>>Gider</option>
            <option value="gelir"    <?= $tip==='gelir'?'selected':'' ?>>Gelir</option>
        </select>
        <input class="input" type="date" name="bas" value="<?= e($bas) ?>" style="max-width:150px">
        <input class="input" type="date" name="bit" value="<?= e($bit) ?>" style="max-width:150px">
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i></button>
        <?php if ($arama || $tip || $bas || $bit): ?><a href="fisler.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
    <thead><tr><th>No</th><th>Tarih</th><th>Tip</th><th>Cari</th><th>Açıklama</th><th>Yöntem</th><th style="text-align:right">Tutar</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="8" class="empty"><i class="fas fa-receipt" style="font-size:2rem;display:block;margin-bottom:8px"></i>Fiş bulunamadı.</td></tr>
    <?php else: foreach ($rows as $r): ?>
        <tr>
            <td><code><?= e($r['fis_no']) ?></code></td>
            <td class="num"><?= tarih_tr($r['tarih']) ?></td>
            <td><span class="badge badge-info"><?= ucfirst($r['tip']) ?></span></td>
            <td><?php if ($r['cari_id']): ?><a href="cariler.php?detay=<?= (int)$r['cari_id'] ?>" style="color:var(--c-orange);text-decoration:none"><?= e((string)$r['unvan']) ?></a><?php else: ?>-<?php endif; ?></td>
            <td><?= e((string)$r['aciklama']) ?></td>
            <td><?= ucfirst($r['odeme_yontemi']) ?></td>
            <td class="num" style="text-align:right;font-weight:600"><?= tl((float)$r['genel_toplam']) ?></td>
            <td><a href="?duzenle=<?= $r['id'] ?>" class="btn btn-out btn-sm"><i class="fas fa-pen"></i></a></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/fisler.php?' . http_build_query(array_filter(['q'=>$arama,'tip'=>$tip,'bas'=>$bas,'bit'=>$bit]));
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
