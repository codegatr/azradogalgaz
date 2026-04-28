<?php
require_once __DIR__ . '/_baslat.php';
page_title('Cariler');

/* ========== AKSİYONLAR ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $cari_kodu = clean($_POST['cari_kodu'] ?? '');
        $unvan     = clean($_POST['unvan'] ?? '');
        $tip       = ($_POST['tip'] ?? 'bireysel') === 'kurumsal' ? 'kurumsal' : 'bireysel';
        $tckn_vkn  = clean($_POST['tckn_vkn'] ?? '');
        $vd        = clean($_POST['vergi_dairesi'] ?? '');
        $tel       = clean($_POST['telefon'] ?? '');
        $tel2      = clean($_POST['telefon_2'] ?? '');
        $eposta    = clean($_POST['eposta'] ?? '');
        $il        = clean($_POST['il'] ?? '');
        $ilce      = clean($_POST['ilce'] ?? '');
        $adres     = clean($_POST['adres'] ?? '');
        $notlar    = clean($_POST['notlar'] ?? '');
        $aktif     = isset($_POST['aktif']) ? 1 : 0;

        if (!$unvan) {
            flash_set('err', 'Unvan / Ad-Soyad zorunludur.');
            redirect(SITE_URL . '/admin/cariler.php' . ($id ? '?duzenle=' . $id : '?ekle=1'));
        }

        if (!$cari_kodu) {
            $son = (int)(db_get("SELECT MAX(CAST(SUBSTRING(cari_kodu, 3) AS UNSIGNED)) m FROM cariler WHERE cari_kodu LIKE 'CR%'")['m'] ?? 0);
            $cari_kodu = 'CR' . str_pad((string)($son + 1), 4, '0', STR_PAD_LEFT);
        }

        if ($id) {
            db_run("UPDATE cariler SET cari_kodu=?, unvan=?, tip=?, tckn_vkn=?, vergi_dairesi=?, telefon=?, telefon_2=?, eposta=?, il=?, ilce=?, adres=?, notlar=?, aktif=? WHERE id=?",
                [$cari_kodu, $unvan, $tip, $tckn_vkn, $vd, $tel, $tel2, $eposta, $il, $ilce, $adres, $notlar, $aktif, $id]);
            log_yaz('cari_guncelle', "Cari güncellendi: $unvan (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Cari güncellendi.');
        } else {
            db_run("INSERT INTO cariler (cari_kodu, unvan, tip, tckn_vkn, vergi_dairesi, telefon, telefon_2, eposta, il, ilce, adres, notlar, aktif) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$cari_kodu, $unvan, $tip, $tckn_vkn, $vd, $tel, $tel2, $eposta, $il, $ilce, $adres, $notlar, $aktif]);
            $id = (int)db()->lastInsertId();
            log_yaz('cari_ekle', "Cari eklendi: $unvan (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Cari eklendi.');
        }
        redirect(SITE_URL . '/admin/cariler.php?detay=' . $id);
    }

    if ($islem === 'sil' && $id) {
        $f = (int)db_get("SELECT COUNT(*) c FROM faturalar WHERE cari_id=?", [$id])['c'];
        $fs = (int)db_get("SELECT COUNT(*) c FROM fisler WHERE cari_id=?", [$id])['c'];
        $h = (int)db_get("SELECT COUNT(*) c FROM cari_hareketler WHERE cari_id=?", [$id])['c'];
        if ($f + $fs + $h > 0) {
            flash_set('err', "Bu cariye bağlı $f fatura, $fs fiş, $h hareket var. Silinemez.");
        } else {
            db_run("DELETE FROM cariler WHERE id=?", [$id]);
            log_yaz('cari_sil', "Cari silindi (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Cari silindi.');
        }
        redirect(SITE_URL . '/admin/cariler.php');
    }

    if ($islem === 'hareket_ekle' && $id) {
        $tarih    = $_POST['tarih'] ?? date('Y-m-d');
        $tip      = in_array($_POST['tip'] ?? '', ['borc','alacak','tahsilat','odeme'], true) ? $_POST['tip'] : 'borc';
        $aciklama = clean($_POST['aciklama'] ?? '');
        $tutar    = (float)str_replace(',', '.', (string)($_POST['tutar'] ?? 0));
        if ($tutar <= 0) {
            flash_set('err', 'Tutar 0\'dan büyük olmalı.');
        } else {
            db_run("INSERT INTO cari_hareketler (cari_id, tarih, tip, belge_tip, aciklama, tutar, olusturan_id) VALUES (?,?,?,?,?,?,?)",
                [$id, $tarih, $tip, 'manuel', $aciklama ?: null, $tutar, (int)$_kul['id']]);
            $delta = match($tip) { 'borc'=>$tutar, 'alacak'=>-$tutar, 'tahsilat'=>-$tutar, 'odeme'=>$tutar };
            db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$delta, $id]);
            log_yaz('cari_hareket', "Cari #$id $tip: " . tl($tutar), (int)$_kul['id']);
            flash_set('ok', 'Hareket eklendi.');
        }
        redirect(SITE_URL . '/admin/cariler.php?detay=' . $id);
    }

    if ($islem === 'hareket_sil') {
        $hid = (int)($_POST['hareket_id'] ?? 0);
        $h = db_get("SELECT * FROM cari_hareketler WHERE id=?", [$hid]);
        if ($h && $h['belge_tip'] === 'manuel') {
            $delta = match($h['tip']) { 'borc'=>-$h['tutar'], 'alacak'=>$h['tutar'], 'tahsilat'=>$h['tutar'], 'odeme'=>-$h['tutar'] };
            db_run("UPDATE cariler SET bakiye = bakiye + ? WHERE id=?", [$delta, $h['cari_id']]);
            db_run("DELETE FROM cari_hareketler WHERE id=?", [$hid]);
            flash_set('ok', 'Hareket silindi.');
            redirect(SITE_URL . '/admin/cariler.php?detay=' . $h['cari_id']);
        }
        flash_set('err', 'Sadece manuel hareketler silinebilir.');
        redirect($_SERVER['REQUEST_URI']);
    }
    redirect($_SERVER['REQUEST_URI']);
}

$detay_id = (int)($_GET['detay'] ?? 0);
$ekle     = isset($_GET['ekle']);
$duzenle  = (int)($_GET['duzenle'] ?? 0);
$mod      = $detay_id ? 'detay' : (($ekle || $duzenle) ? 'form' : 'liste');

require_once __DIR__ . '/_header.php';

/* ===== DETAY ===== */
if ($mod === 'detay') {
    $cari = db_get("SELECT * FROM cariler WHERE id=?", [$detay_id]);
    if (!$cari) { flash_set('err','Cari bulunamadı.'); redirect(SITE_URL.'/admin/cariler.php'); }
    $hareketler = db_all("SELECT * FROM cari_hareketler WHERE cari_id=? ORDER BY tarih DESC, id DESC LIMIT 200", [$detay_id]);
    $faturalar = db_all("SELECT id, fatura_no, tarih, genel_toplam, odeme_durumu FROM faturalar WHERE cari_id=? ORDER BY tarih DESC, id DESC LIMIT 50", [$detay_id]);
    $fisler = db_all("SELECT id, fis_no, tarih, tip, genel_toplam FROM fisler WHERE cari_id=? ORDER BY tarih DESC, id DESC LIMIT 50", [$detay_id]);
    $bk = (float)$cari['bakiye'];
    $bk_renk = $bk > 0 ? 'var(--c-red)' : ($bk < 0 ? 'var(--c-green)' : 'var(--c-muted)');
?>
<div class="page-head">
    <div>
        <h1 class="page-h1"><?= e($cari['unvan']) ?></h1>
        <p class="page-sub"><code><?= e($cari['cari_kodu']) ?></code> · <?= $cari['tip']==='kurumsal'?'Kurumsal':'Bireysel' ?> · Bakiye: <strong style="color:<?= $bk_renk ?>"><?= tl($bk) ?></strong> <?php if ($bk!=0): ?><small style="color:var(--c-muted)"><?= $bk>0?'(borçlu)':'(alacaklı)' ?></small><?php endif; ?></p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="?duzenle=<?= $cari['id'] ?>" class="btn btn-out"><i class="fas fa-pen"></i> Düzenle</a>
        <a href="cariler.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye</a>
    </div>
</div>

<div class="form-row cols-2">
    <div class="card">
        <h3>İletişim & Adres</h3>
        <div class="tbl-wrap">
        <table class="tbl">
            <tr><th style="width:130px">Telefon</th><td><?= e($cari['telefon']) ?: '-' ?><?php if ($cari['telefon_2']): ?> <small style="color:var(--c-muted)">/ <?= e($cari['telefon_2']) ?></small><?php endif; ?></td></tr>
            <tr><th>E-posta</th><td><?= e($cari['eposta']) ?: '-' ?></td></tr>
            <tr><th>TCKN/VKN</th><td><?= e($cari['tckn_vkn']) ?: '-' ?></td></tr>
            <tr><th>V.D.</th><td><?= e($cari['vergi_dairesi']) ?: '-' ?></td></tr>
            <tr><th>İl/İlçe</th><td><?= e(trim($cari['il'].' / '.$cari['ilce'], ' /')) ?: '-' ?></td></tr>
            <tr><th>Adres</th><td><?= nl2br(e((string)$cari['adres'])) ?: '-' ?></td></tr>
            <?php if ($cari['notlar']): ?><tr><th>Notlar</th><td><?= nl2br(e($cari['notlar'])) ?></td></tr><?php endif; ?>
        </table>
        </div>
    </div>

    <div class="card">
        <h3>Manuel Hareket Ekle</h3>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="hareket_ekle">
            <input type="hidden" name="id" value="<?= $cari['id'] ?>">
            <div class="form-row cols-2">
                <div class="field"><label>Tarih</label><input class="input" type="date" name="tarih" value="<?= date('Y-m-d') ?>" required></div>
                <div class="field"><label>Tip</label>
                    <select name="tip">
                        <option value="borc">Borç (+)</option>
                        <option value="alacak">Alacak (−)</option>
                        <option value="tahsilat">Tahsilat (−)</option>
                        <option value="odeme">Ödeme (+)</option>
                    </select>
                </div>
            </div>
            <div class="form-row"><div class="field"><label>Tutar (₺) *</label><input class="input" type="number" name="tutar" step="0.01" min="0.01" required></div></div>
            <div class="form-row"><div class="field"><label>Açıklama</label><input class="input" type="text" name="aciklama" maxlength="300"></div></div>
            <div class="form-actions"><button class="btn btn-pri"><i class="fas fa-plus"></i> Hareket Ekle</button></div>
        </form>
    </div>
</div>

<div class="card">
    <h3>Cari Hareketler (<?= count($hareketler) ?>)</h3>
    <?php if (!$hareketler): ?>
        <div class="tbl-wrap"><div class="empty">Henüz hareket yok.</div></div>
    <?php else: ?>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th>Tarih</th><th>Tip</th><th>Belge</th><th>Açıklama</th><th style="text-align:right">Tutar</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($hareketler as $h):
            $renk = in_array($h['tip'], ['borc','odeme'], true) ? 'var(--c-red)' : 'var(--c-green)';
            $isaret = in_array($h['tip'], ['borc','odeme'], true) ? '+' : '−';
        ?>
            <tr>
                <td class="num"><?= tarih_tr($h['tarih']) ?></td>
                <td><span class="badge badge-info"><?= ucfirst($h['tip']) ?></span></td>
                <td><?= e($h['belge_tip']) ?><?php if ($h['belge_no']): ?> <small style="color:var(--c-muted)"><?= e($h['belge_no']) ?></small><?php endif; ?></td>
                <td><?= e((string)$h['aciklama']) ?></td>
                <td class="num" style="text-align:right;color:<?= $renk ?>;font-weight:600"><?= $isaret ?> <?= tl((float)$h['tutar']) ?></td>
                <td>
                    <?php if ($h['belge_tip']==='manuel'): ?>
                    <form method="post" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="islem" value="hareket_sil">
                        <input type="hidden" name="hareket_id" value="<?= $h['id'] ?>">
                        <button class="btn btn-danger btn-sm" data-onay="Hareket silinsin mi?"><i class="fas fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($faturalar): ?>
<div class="card">
    <h3>Faturalar (<?= count($faturalar) ?>)</h3>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th>No</th><th>Tarih</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($faturalar as $f): ?>
            <tr>
                <td><code><?= e($f['fatura_no']) ?></code></td>
                <td class="num"><?= tarih_tr($f['tarih']) ?></td>
                <td class="num"><?= tl((float)$f['genel_toplam']) ?></td>
                <td><span class="badge badge-<?= $f['odeme_durumu']==='odendi'?'ok':($f['odeme_durumu']==='kismi'?'warn':'danger') ?>"><?= ucfirst(str_replace('_',' ', $f['odeme_durumu'])) ?></span></td>
                <td><a href="faturalar.php?duzenle=<?= $f['id'] ?>" class="btn btn-out btn-sm">Aç</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php if ($fisler): ?>
<div class="card">
    <h3>Fişler (<?= count($fisler) ?>)</h3>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th>No</th><th>Tarih</th><th>Tip</th><th>Tutar</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($fisler as $f): ?>
            <tr>
                <td><code><?= e($f['fis_no']) ?></code></td>
                <td class="num"><?= tarih_tr($f['tarih']) ?></td>
                <td><span class="badge badge-info"><?= ucfirst($f['tip']) ?></span></td>
                <td class="num"><?= tl((float)$f['genel_toplam']) ?></td>
                <td><a href="fisler.php?duzenle=<?= $f['id'] ?>" class="btn btn-out btn-sm">Aç</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; return; }

/* ===== FORM ===== */
if ($mod === 'form') {
    $cari = $duzenle ? db_get("SELECT * FROM cariler WHERE id=?", [$duzenle]) : null;
    if ($duzenle && !$cari) { flash_set('err','Cari bulunamadı.'); redirect(SITE_URL.'/admin/cariler.php'); }
?>
<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $duzenle ? 'Cari Düzenle' : 'Yeni Cari' ?></h1>
        <p class="page-sub">Müşteri / tedarikçi bilgileri.</p>
    </div>
    <a href="cariler.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye</a>
</div>

<div class="card">
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="kaydet">
        <input type="hidden" name="id" value="<?= $cari['id'] ?? 0 ?>">

        <div class="form-row cols-3">
            <div class="field"><label>Cari Kodu <span class="opt">(boş = otomatik CR0001…)</span></label><input class="input" type="text" name="cari_kodu" value="<?= e($cari['cari_kodu'] ?? '') ?>"></div>
            <div class="field"><label>Tip</label>
                <select name="tip">
                    <option value="bireysel" <?= ($cari['tip'] ?? 'bireysel')==='bireysel'?'selected':'' ?>>Bireysel</option>
                    <option value="kurumsal" <?= ($cari['tip'] ?? '')==='kurumsal'?'selected':'' ?>>Kurumsal</option>
                </select>
            </div>
            <div class="field" style="display:flex;align-items:flex-end;padding-bottom:8px"><label class="check"><input type="checkbox" name="aktif" <?= ($cari['aktif'] ?? 1) ? 'checked' : '' ?>> <span>Aktif</span></label></div>
        </div>

        <div class="form-row"><div class="field"><label>Unvan / Ad-Soyad *</label><input class="input" type="text" name="unvan" value="<?= e($cari['unvan'] ?? '') ?>" required maxlength="220"></div></div>

        <div class="form-row cols-2">
            <div class="field"><label>TCKN / VKN</label><input class="input" type="text" name="tckn_vkn" value="<?= e($cari['tckn_vkn'] ?? '') ?>" maxlength="20"></div>
            <div class="field"><label>Vergi Dairesi</label><input class="input" type="text" name="vergi_dairesi" value="<?= e($cari['vergi_dairesi'] ?? '') ?>" maxlength="120"></div>
        </div>

        <div class="form-row cols-3">
            <div class="field"><label>Telefon</label><input class="input" type="text" name="telefon" value="<?= e($cari['telefon'] ?? '') ?>" maxlength="40"></div>
            <div class="field"><label>Telefon 2</label><input class="input" type="text" name="telefon_2" value="<?= e($cari['telefon_2'] ?? '') ?>" maxlength="40"></div>
            <div class="field"><label>E-posta</label><input class="input" type="email" name="eposta" value="<?= e($cari['eposta'] ?? '') ?>" maxlength="160"></div>
        </div>

        <div class="form-row cols-2">
            <div class="field"><label>İl</label><input class="input" type="text" name="il" value="<?= e($cari['il'] ?? 'İzmir') ?>" maxlength="60"></div>
            <div class="field"><label>İlçe</label><input class="input" type="text" name="ilce" value="<?= e($cari['ilce'] ?? '') ?>" maxlength="80"></div>
        </div>

        <div class="form-row"><div class="field"><label>Adres</label><textarea class="input" name="adres" rows="2"><?= e($cari['adres'] ?? '') ?></textarea></div></div>
        <div class="form-row"><div class="field"><label>Notlar</label><textarea class="input" name="notlar" rows="2"><?= e($cari['notlar'] ?? '') ?></textarea></div></div>

        <div class="form-actions">
            <button class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
            <a href="cariler.php" class="btn btn-out">İptal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/_footer.php'; return; }

/* ===== LİSTE ===== */
$arama = clean($_GET['q'] ?? '');
$tip   = $_GET['tip'] ?? '';
$durum = $_GET['durum'] ?? '';
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 25; $ofset = ($sayfa-1)*$limit;

$where = "1=1"; $params = [];
if ($arama) {
    $where .= " AND (unvan LIKE ? OR cari_kodu LIKE ? OR telefon LIKE ? OR tckn_vkn LIKE ?)";
    $w = "%$arama%"; array_push($params, $w, $w, $w, $w);
}
if ($tip && in_array($tip, ['bireysel','kurumsal'], true)) { $where .= " AND tip=?"; $params[] = $tip; }
if ($durum === 'aktif')    $where .= " AND aktif=1";
if ($durum === 'pasif')    $where .= " AND aktif=0";
if ($durum === 'borclu')   $where .= " AND bakiye>0";
if ($durum === 'alacakli') $where .= " AND bakiye<0";

$toplam = (int)db_get("SELECT COUNT(*) c FROM cariler WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));
$cariler = db_all("SELECT * FROM cariler WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $ofset", $params);
$genel_borc   = (float)(db_get("SELECT COALESCE(SUM(bakiye),0) t FROM cariler WHERE bakiye>0")['t'] ?? 0);
$genel_alacak = (float)(db_get("SELECT COALESCE(ABS(SUM(bakiye)),0) t FROM cariler WHERE bakiye<0")['t'] ?? 0);
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Cariler</h1>
        <p class="page-sub">Toplam <?= $toplam ?> cari · Borç: <strong style="color:var(--c-red)"><?= tl($genel_borc) ?></strong> · Alacak: <strong style="color:var(--c-green)"><?= tl($genel_alacak) ?></strong></p>
    </div>
    <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Cari</a>
</div>

<form method="get" class="toolbar">
    <div class="filters">
        <input class="input" type="search" name="q" value="<?= e($arama) ?>" placeholder="Unvan / kod / telefon / VKN…">
        <select name="tip">
            <option value="">Tüm tipler</option>
            <option value="bireysel" <?= $tip==='bireysel'?'selected':'' ?>>Bireysel</option>
            <option value="kurumsal" <?= $tip==='kurumsal'?'selected':'' ?>>Kurumsal</option>
        </select>
        <select name="durum">
            <option value="">Tüm durumlar</option>
            <option value="aktif"    <?= $durum==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="pasif"    <?= $durum==='pasif'?'selected':'' ?>>Pasif</option>
            <option value="borclu"   <?= $durum==='borclu'?'selected':'' ?>>Borçlular</option>
            <option value="alacakli" <?= $durum==='alacakli'?'selected':'' ?>>Alacaklılar</option>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i> Filtrele</button>
        <?php if ($arama || $tip || $durum): ?><a href="cariler.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
    <thead>
        <tr><th>Kod</th><th>Unvan</th><th>Tip</th><th>Telefon</th><th>İlçe</th><th style="text-align:right">Bakiye</th><th>Durum</th><th style="text-align:right">İşlem</th></tr>
    </thead>
    <tbody>
    <?php if (!$cariler): ?>
        <tr><td colspan="8" class="empty"><i class="fas fa-users" style="font-size:2rem;display:block;margin-bottom:8px"></i>Cari bulunamadı.</td></tr>
    <?php else: foreach ($cariler as $c):
        $bk = (float)$c['bakiye'];
        $renk = $bk > 0 ? 'var(--c-red)' : ($bk < 0 ? 'var(--c-green)' : 'var(--c-muted)');
    ?>
        <tr>
            <td><code><?= e($c['cari_kodu']) ?></code></td>
            <td><a href="?detay=<?= $c['id'] ?>" style="color:var(--c-orange);font-weight:600;text-decoration:none"><?= e($c['unvan']) ?></a></td>
            <td><span class="badge badge-info"><?= $c['tip']==='kurumsal'?'Kurumsal':'Bireysel' ?></span></td>
            <td><?= e((string)$c['telefon']) ?: '-' ?></td>
            <td><?= e((string)$c['ilce']) ?: '-' ?></td>
            <td class="num" style="text-align:right;color:<?= $renk ?>;font-weight:600"><?= tl(abs($bk)) ?> <?= $bk>0?'B':($bk<0?'A':'') ?></td>
            <td><?= $c['aktif'] ? '<span class="badge badge-ok">Aktif</span>' : '<span class="badge badge-warn">Pasif</span>' ?></td>
            <td>
                <div class="actions">
                    <a href="?detay=<?= $c['id'] ?>" class="btn btn-blue btn-sm" title="Detay"><i class="fas fa-eye"></i></a>
                    <a href="?duzenle=<?= $c['id'] ?>" class="btn btn-out btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
                    <form method="post" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="islem" value="sil">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button class="btn btn-danger btn-sm" data-onay="Cari silinsin mi?" title="Sil"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/cariler.php?' . http_build_query(array_filter(['q'=>$arama,'tip'=>$tip,'durum'=>$durum]));
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
