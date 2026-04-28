<?php
require_once __DIR__ . '/_baslat.php';
page_title('Bakım Hatırlatıcıları');

/* ===== AUTO-CREATE TABLE ===== */
try {
    db()->exec("CREATE TABLE IF NOT EXISTS bakim_hatirlaticilari (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        cari_id INT(10) UNSIGNED DEFAULT NULL,
        musteri_ad VARCHAR(220) DEFAULT NULL,
        telefon VARCHAR(40) DEFAULT NULL,
        eposta VARCHAR(160) DEFAULT NULL,
        adres TEXT DEFAULT NULL,
        urun_tipi ENUM('kombi','klima','kazan','sofben','termosifon','diger') DEFAULT 'kombi',
        marka VARCHAR(120) DEFAULT NULL,
        model VARCHAR(160) DEFAULT NULL,
        seri_no VARCHAR(80) DEFAULT NULL,
        kurulum_tarihi DATE DEFAULT NULL,
        son_bakim_tarihi DATE DEFAULT NULL,
        sonraki_bakim_tarihi DATE NOT NULL,
        periyot_ay TINYINT(3) UNSIGNED DEFAULT 12,
        durum ENUM('aktif','pasif','tamamlandi') DEFAULT 'aktif',
        bildirim_gonderildi TINYINT(1) DEFAULT 0,
        son_bildirim_tarihi DATE DEFAULT NULL,
        notlar TEXT DEFAULT NULL,
        olusturan_id INT(10) UNSIGNED DEFAULT NULL,
        olusturma_tarihi TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        guncelleme_tarihi TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_sonraki (sonraki_bakim_tarihi),
        INDEX idx_durum (durum),
        INDEX idx_cari (cari_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) { /* zaten var */ }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $cari_id    = (int)($_POST['cari_id'] ?? 0) ?: null;
        $musteri_ad = clean($_POST['musteri_ad'] ?? '');
        $telefon    = clean($_POST['telefon'] ?? '');
        $eposta     = clean($_POST['eposta'] ?? '');
        $adres      = clean($_POST['adres'] ?? '');
        $urun_tipi  = in_array($_POST['urun_tipi'] ?? '', ['kombi','klima','kazan','sofben','termosifon','diger'], true) ? $_POST['urun_tipi'] : 'kombi';
        $marka      = clean($_POST['marka'] ?? '');
        $model      = clean($_POST['model'] ?? '');
        $seri_no    = clean($_POST['seri_no'] ?? '');
        $kurulum    = $_POST['kurulum_tarihi'] ?: null;
        $son_bakim  = $_POST['son_bakim_tarihi'] ?: null;
        $periyot    = max(1, min(60, (int)($_POST['periyot_ay'] ?? 12)));
        $sonraki    = $_POST['sonraki_bakim_tarihi'] ?? '';
        $durum      = in_array($_POST['durum'] ?? '', ['aktif','pasif','tamamlandi'], true) ? $_POST['durum'] : 'aktif';
        $notlar     = clean($_POST['notlar'] ?? '');

        if (!$sonraki) {
            $temel = $son_bakim ?: ($kurulum ?: date('Y-m-d'));
            $sonraki = date('Y-m-d', strtotime("$temel +$periyot months"));
        }

        if ($cari_id && (!$musteri_ad || !$telefon)) {
            $c = db_get("SELECT unvan, telefon, eposta, adres FROM cariler WHERE id=?", [$cari_id]);
            if ($c) {
                if (!$musteri_ad) $musteri_ad = $c['unvan'];
                if (!$telefon)    $telefon    = $c['telefon'] ?? '';
                if (!$eposta)     $eposta     = $c['eposta'] ?? '';
                if (!$adres)      $adres      = $c['adres'] ?? '';
            }
        }

        if (!$musteri_ad) {
            flash_set('err', 'Müşteri adı zorunludur.');
            redirect($_SERVER['REQUEST_URI']);
        }

        if ($id) {
            db_run("UPDATE bakim_hatirlaticilari SET cari_id=?, musteri_ad=?, telefon=?, eposta=?, adres=?, urun_tipi=?, marka=?, model=?, seri_no=?, kurulum_tarihi=?, son_bakim_tarihi=?, sonraki_bakim_tarihi=?, periyot_ay=?, durum=?, notlar=? WHERE id=?",
                [$cari_id, $musteri_ad, $telefon, $eposta, $adres, $urun_tipi, $marka, $model, $seri_no, $kurulum, $son_bakim, $sonraki, $periyot, $durum, $notlar, $id]);
            log_yaz('bakim_guncelle', "Bakım: $musteri_ad / $urun_tipi (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Hatırlatıcı güncellendi.');
        } else {
            db_run("INSERT INTO bakim_hatirlaticilari (cari_id, musteri_ad, telefon, eposta, adres, urun_tipi, marka, model, seri_no, kurulum_tarihi, son_bakim_tarihi, sonraki_bakim_tarihi, periyot_ay, durum, notlar, olusturan_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$cari_id, $musteri_ad, $telefon, $eposta, $adres, $urun_tipi, $marka, $model, $seri_no, $kurulum, $son_bakim, $sonraki, $periyot, $durum, $notlar, (int)$_kul['id']]);
            $id = (int)db()->lastInsertId();
            log_yaz('bakim_ekle', "Bakım: $musteri_ad / $urun_tipi (#$id)", (int)$_kul['id']);
            flash_set('ok', 'Hatırlatıcı eklendi.');
        }
        redirect(SITE_URL . '/admin/bakim-hatirlaticilari.php?duzenle=' . $id);
    }

    if ($islem === 'sil' && $id) {
        db_run("DELETE FROM bakim_hatirlaticilari WHERE id=?", [$id]);
        log_yaz('bakim_sil', "Bakım silindi (#$id)", (int)$_kul['id']);
        flash_set('ok', 'Hatırlatıcı silindi.');
        redirect(SITE_URL . '/admin/bakim-hatirlaticilari.php');
    }

    if ($islem === 'bakim_yapildi' && $id) {
        $b = db_get("SELECT * FROM bakim_hatirlaticilari WHERE id=?", [$id]);
        if ($b) {
            $bugun = date('Y-m-d');
            $yeni_sonraki = date('Y-m-d', strtotime("$bugun +" . (int)$b['periyot_ay'] . " months"));
            db_run("UPDATE bakim_hatirlaticilari SET son_bakim_tarihi=?, sonraki_bakim_tarihi=?, durum='aktif', bildirim_gonderildi=0 WHERE id=?",
                [$bugun, $yeni_sonraki, $id]);
            log_yaz('bakim_yapildi', "Bakım yapıldı: {$b['musteri_ad']} (#$id) → {$yeni_sonraki}", (int)$_kul['id']);
            flash_set('ok', "Bakım kaydedildi. Sonraki: " . tarih_tr($yeni_sonraki));
        }
        redirect(SITE_URL . '/admin/bakim-hatirlaticilari.php?duzenle=' . $id);
    }

    if ($islem === 'bildirim_isaretle' && $id) {
        db_run("UPDATE bakim_hatirlaticilari SET bildirim_gonderildi=1, son_bildirim_tarihi=? WHERE id=?", [date('Y-m-d'), $id]);
        flash_set('ok', 'Bildirim işaretlendi.');
        redirect(SITE_URL . '/admin/bakim-hatirlaticilari.php');
    }
    redirect($_SERVER['REQUEST_URI']);
}

$ekle    = isset($_GET['ekle']);
$duzenle = (int)($_GET['duzenle'] ?? 0);
$mod     = ($ekle || $duzenle) ? 'form' : 'liste';

require_once __DIR__ . '/_header.php';

/* ===== FORM ===== */
if ($mod === 'form') {
    $b = $duzenle ? db_get("SELECT * FROM bakim_hatirlaticilari WHERE id=?", [$duzenle]) : null;
    if ($duzenle && !$b) { flash_set('err','Hatırlatıcı bulunamadı.'); redirect(SITE_URL.'/admin/bakim-hatirlaticilari.php'); }
    $cariler = db_all("SELECT id, cari_kodu, unvan, telefon, eposta, adres FROM cariler WHERE aktif=1 ORDER BY unvan");
    $markalar = db_all("SELECT ad FROM markalar WHERE aktif=1 ORDER BY ad");
?>
<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $duzenle ? 'Hatırlatıcı Düzenle' : 'Yeni Bakım Hatırlatıcısı' ?></h1>
        <?php if ($duzenle):
            $kalan = (int)floor((strtotime($b['sonraki_bakim_tarihi']) - strtotime(date('Y-m-d'))) / 86400);
            $renk = $kalan < 0 ? 'var(--c-red)' : ($kalan < 30 ? 'var(--c-orange)' : 'var(--c-green)');
        ?>
        <p class="page-sub"><span class="badge badge-info"><?= ucfirst($b['urun_tipi']) ?></span> · Sonraki bakım: <strong style="color:<?= $renk ?>"><?= tarih_tr($b['sonraki_bakim_tarihi']) ?></strong> <small style="color:var(--c-muted)">(<?= $kalan < 0 ? abs($kalan).' gün gecikmiş' : $kalan.' gün kaldı' ?>)</small></p>
        <?php endif; ?>
    </div>
    <a href="bakim-hatirlaticilari.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye</a>
</div>

<?php if ($duzenle): ?>
<div class="card" style="border-left:3px solid var(--c-green)">
    <h3><i class="fas fa-check-circle" style="color:var(--c-green)"></i> Bakım Tamamlandı İşaretle</h3>
    <p style="color:var(--c-muted);font-size:.88rem;margin:8px 0">Bugünü son bakım yapar, sonraki bakımı periyot kadar ileri alır.</p>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="bakim_yapildi">
        <input type="hidden" name="id" value="<?= $b['id'] ?>">
        <button class="btn btn-pri" data-onay="Bugün bakım yapıldı olarak işaretlensin mi?"><i class="fas fa-check-circle"></i> Bakım Yapıldı (Bugün)</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="kaydet">
        <input type="hidden" name="id" value="<?= $b['id'] ?? 0 ?>">

        <div class="form-row cols-2">
            <div class="field"><label>Mevcut Cariden Seç <span class="opt">(opsiyonel)</span></label>
                <select name="cari_id" id="cariSel">
                    <option value="">-- Manuel gir --</option>
                    <?php foreach ($cariler as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            data-ad="<?= e($c['unvan']) ?>"
                            data-tel="<?= e((string)$c['telefon']) ?>"
                            data-mail="<?= e((string)$c['eposta']) ?>"
                            data-adr="<?= e((string)$c['adres']) ?>"
                            <?= ($b['cari_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['cari_kodu']) ?> — <?= e($c['unvan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Müşteri Adı *</label><input class="input" type="text" name="musteri_ad" id="musteriAd" value="<?= e($b['musteri_ad'] ?? '') ?>" required maxlength="220"></div>
        </div>

        <div class="form-row cols-3">
            <div class="field"><label>Telefon</label><input class="input" type="text" name="telefon" id="musteriTel" value="<?= e($b['telefon'] ?? '') ?>" maxlength="40"></div>
            <div class="field"><label>E-posta</label><input class="input" type="email" name="eposta" id="musteriMail" value="<?= e($b['eposta'] ?? '') ?>" maxlength="160"></div>
            <div class="field"><label>Durum</label>
                <select name="durum">
                    <option value="aktif"      <?= ($b['durum'] ?? 'aktif')==='aktif'?'selected':'' ?>>Aktif</option>
                    <option value="pasif"      <?= ($b['durum'] ?? '')==='pasif'?'selected':'' ?>>Pasif</option>
                    <option value="tamamlandi" <?= ($b['durum'] ?? '')==='tamamlandi'?'selected':'' ?>>Tamamlandı</option>
                </select>
            </div>
        </div>

        <div class="form-row"><div class="field"><label>Adres</label><textarea class="input" name="adres" id="musteriAdr" rows="2"><?= e($b['adres'] ?? '') ?></textarea></div></div>

        <hr style="margin:18px 0;border:0;border-top:1px solid var(--c-line)">

        <div class="form-row cols-3">
            <div class="field"><label>Ürün Tipi *</label>
                <select name="urun_tipi">
                    <?php foreach (['kombi'=>'Kombi','klima'=>'Klima','kazan'=>'Kazan','sofben'=>'Şofben','termosifon'=>'Termosifon','diger'=>'Diğer'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($b['urun_tipi'] ?? 'kombi')===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Marka</label>
                <input class="input" type="text" name="marka" value="<?= e($b['marka'] ?? '') ?>" list="markaList" maxlength="120">
                <datalist id="markaList"><?php foreach ($markalar as $m): ?><option value="<?= e($m['ad']) ?>"><?php endforeach; ?></datalist>
            </div>
            <div class="field"><label>Model</label><input class="input" type="text" name="model" value="<?= e($b['model'] ?? '') ?>" maxlength="160"></div>
        </div>

        <div class="form-row cols-3">
            <div class="field"><label>Seri No</label><input class="input" type="text" name="seri_no" value="<?= e($b['seri_no'] ?? '') ?>" maxlength="80"></div>
            <div class="field"><label>Kurulum Tarihi</label><input class="input" type="date" name="kurulum_tarihi" value="<?= e($b['kurulum_tarihi'] ?? '') ?>"></div>
            <div class="field"><label>Son Bakım Tarihi</label><input class="input" type="date" name="son_bakim_tarihi" id="sonBakim" value="<?= e($b['son_bakim_tarihi'] ?? '') ?>"></div>
        </div>

        <div class="form-row cols-2">
            <div class="field"><label>Periyot (Ay) *</label><input class="input" type="number" name="periyot_ay" id="periyot" value="<?= e((string)($b['periyot_ay'] ?? 12)) ?>" min="1" max="60" required></div>
            <div class="field"><label>Sonraki Bakım Tarihi *</label><input class="input" type="date" name="sonraki_bakim_tarihi" id="sonraki" value="<?= e($b['sonraki_bakim_tarihi'] ?? date('Y-m-d', strtotime('+12 months'))) ?>" required></div>
        </div>

        <div class="form-row"><div class="field"><label>Notlar</label><textarea class="input" name="notlar" rows="2"><?= e($b['notlar'] ?? '') ?></textarea></div></div>

        <div class="form-actions">
            <button class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
            <a href="bakim-hatirlaticilari.php" class="btn btn-out">İptal</a>
        </div>
    </form>
    <?php if ($duzenle): ?>
    <form method="post" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="sil">
        <input type="hidden" name="id" value="<?= $b['id'] ?>">
        <button class="btn btn-danger btn-sm" data-onay="Hatırlatıcı silinsin mi?"><i class="fas fa-trash"></i> Sil</button>
    </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('cariSel').addEventListener('change', function(){
    const opt = this.selectedOptions[0];
    if (!opt || !opt.value) return;
    const ad = document.getElementById('musteriAd');
    const tel = document.getElementById('musteriTel');
    const mail = document.getElementById('musteriMail');
    const adr = document.getElementById('musteriAdr');
    if (!ad.value && opt.dataset.ad) ad.value = opt.dataset.ad;
    if (!tel.value && opt.dataset.tel) tel.value = opt.dataset.tel;
    if (!mail.value && opt.dataset.mail) mail.value = opt.dataset.mail;
    if (!adr.value && opt.dataset.adr) adr.value = opt.dataset.adr;
});
function hesapla(){
    const sb = document.getElementById('sonBakim').value;
    const p = parseInt(document.getElementById('periyot').value || '12');
    if (sb) {
        const d = new Date(sb);
        d.setMonth(d.getMonth() + p);
        document.getElementById('sonraki').value = d.toISOString().split('T')[0];
    }
}
document.getElementById('sonBakim').addEventListener('change', hesapla);
document.getElementById('periyot').addEventListener('change', hesapla);
</script>

<?php require_once __DIR__ . '/_footer.php'; return; }

/* ===== LİSTE ===== */
$arama = clean($_GET['q'] ?? '');
$tipf  = $_GET['tip'] ?? '';
$durum = $_GET['durum'] ?? '';
$zaman = $_GET['zaman'] ?? '';
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 25; $ofset = ($sayfa-1)*$limit;

$where = "1=1"; $params = [];
if ($arama) { $where .= " AND (musteri_ad LIKE ? OR telefon LIKE ? OR marka LIKE ? OR model LIKE ? OR seri_no LIKE ?)"; $w="%$arama%"; array_push($params,$w,$w,$w,$w,$w); }
if ($tipf && in_array($tipf, ['kombi','klima','kazan','sofben','termosifon','diger'], true)) { $where .= " AND urun_tipi=?"; $params[] = $tipf; }
if ($durum && in_array($durum, ['aktif','pasif','tamamlandi'], true)) { $where .= " AND durum=?"; $params[] = $durum; }
if ($zaman === 'gecmis')   $where .= " AND sonraki_bakim_tarihi < CURDATE() AND durum='aktif'";
if ($zaman === 'bu_hafta') $where .= " AND sonraki_bakim_tarihi BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND durum='aktif'";
if ($zaman === 'bu_ay')    $where .= " AND sonraki_bakim_tarihi BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND durum='aktif'";

$toplam = (int)db_get("SELECT COUNT(*) c FROM bakim_hatirlaticilari WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));
$rows = db_all("SELECT * FROM bakim_hatirlaticilari WHERE $where ORDER BY sonraki_bakim_tarihi ASC LIMIT $limit OFFSET $ofset", $params);

$ozet = db_get("SELECT
    SUM(CASE WHEN sonraki_bakim_tarihi < CURDATE() AND durum='aktif' THEN 1 ELSE 0 END) gecmis,
    SUM(CASE WHEN sonraki_bakim_tarihi BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY) AND durum='aktif' THEN 1 ELSE 0 END) hafta,
    SUM(CASE WHEN sonraki_bakim_tarihi BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND durum='aktif' THEN 1 ELSE 0 END) ay,
    SUM(CASE WHEN durum='aktif' THEN 1 ELSE 0 END) aktif
FROM bakim_hatirlaticilari");
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Bakım Hatırlatıcıları</h1>
        <p class="page-sub">Toplam <?= (int)$ozet['aktif'] ?> aktif hatırlatıcı</p>
    </div>
    <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Hatırlatıcı</a>
</div>

<div class="stats">
    <a href="?zaman=gecmis" class="stat" style="text-decoration:none">
        <div class="ico r"><i class="fas fa-triangle-exclamation"></i></div>
        <div><strong><?= (int)$ozet['gecmis'] ?></strong><span>Gecikmiş</span></div>
    </a>
    <a href="?zaman=bu_hafta" class="stat" style="text-decoration:none">
        <div class="ico y"><i class="fas fa-clock"></i></div>
        <div><strong><?= (int)$ozet['hafta'] ?></strong><span>Bu Hafta</span></div>
    </a>
    <a href="?zaman=bu_ay" class="stat" style="text-decoration:none">
        <div class="ico b"><i class="fas fa-calendar-day"></i></div>
        <div><strong><?= (int)$ozet['ay'] ?></strong><span>30 Gün İçinde</span></div>
    </a>
    <a href="?durum=aktif" class="stat" style="text-decoration:none">
        <div class="ico g"><i class="fas fa-bell"></i></div>
        <div><strong><?= (int)$ozet['aktif'] ?></strong><span>Toplam Aktif</span></div>
    </a>
</div>

<form method="get" class="toolbar">
    <div class="filters">
        <input class="input" type="search" name="q" value="<?= e($arama) ?>" placeholder="Müşteri / telefon / marka / seri no…">
        <select name="tip">
            <option value="">Tüm tipler</option>
            <?php foreach (['kombi'=>'Kombi','klima'=>'Klima','kazan'=>'Kazan','sofben'=>'Şofben','termosifon'=>'Termosifon','diger'=>'Diğer'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $tipf===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="durum">
            <option value="">Tüm durumlar</option>
            <option value="aktif"      <?= $durum==='aktif'?'selected':'' ?>>Aktif</option>
            <option value="pasif"      <?= $durum==='pasif'?'selected':'' ?>>Pasif</option>
            <option value="tamamlandi" <?= $durum==='tamamlandi'?'selected':'' ?>>Tamamlandı</option>
        </select>
        <select name="zaman">
            <option value="">Tüm zamanlar</option>
            <option value="gecmis"   <?= $zaman==='gecmis'?'selected':'' ?>>Gecikmiş</option>
            <option value="bu_hafta" <?= $zaman==='bu_hafta'?'selected':'' ?>>Bu Hafta</option>
            <option value="bu_ay"    <?= $zaman==='bu_ay'?'selected':'' ?>>30 Gün</option>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i></button>
        <?php if ($arama || $tipf || $durum || $zaman): ?><a href="bakim-hatirlaticilari.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
    <thead><tr><th>Müşteri</th><th>Tip / Marka</th><th>Telefon</th><th>Sonraki Bakım</th><th>Durum</th><th>Bildirim</th><th style="text-align:right">İşlem</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="7" class="empty"><i class="fas fa-bell-slash" style="font-size:2rem;display:block;margin-bottom:8px"></i>Hatırlatıcı bulunamadı.</td></tr>
    <?php else: foreach ($rows as $r):
        $kalan = (int)floor((strtotime($r['sonraki_bakim_tarihi']) - strtotime(date('Y-m-d'))) / 86400);
        $renk = $kalan < 0 ? 'var(--c-red)' : ($kalan < 7 ? 'var(--c-orange)' : ($kalan < 30 ? 'var(--c-blue)' : 'var(--c-green)'));
    ?>
        <tr>
            <td>
                <strong><?= e($r['musteri_ad']) ?></strong>
                <?php if ($r['cari_id']): ?><br><a href="cariler.php?detay=<?= (int)$r['cari_id'] ?>" style="color:var(--c-muted);font-size:.78rem;text-decoration:none"><i class="fas fa-link"></i> Cari</a><?php endif; ?>
            </td>
            <td>
                <span class="badge badge-info"><?= ucfirst($r['urun_tipi']) ?></span>
                <?php if ($r['marka']): ?><br><small style="color:var(--c-muted)"><?= e($r['marka']) ?> <?= e((string)$r['model']) ?></small><?php endif; ?>
            </td>
            <td><?= e((string)$r['telefon']) ?: '-' ?></td>
            <td>
                <strong style="color:<?= $renk ?>" class="num"><?= tarih_tr($r['sonraki_bakim_tarihi']) ?></strong>
                <br><small style="color:<?= $renk ?>"><?= $kalan < 0 ? abs($kalan).' gün gecikmiş' : ($kalan === 0 ? 'BUGÜN' : $kalan.' gün kaldı') ?></small>
            </td>
            <td><span class="badge badge-<?= $r['durum']==='aktif'?'ok':($r['durum']==='pasif'?'warn':'no') ?>"><?= ucfirst($r['durum']) ?></span></td>
            <td>
                <?php if ($r['bildirim_gonderildi']): ?>
                    <span class="badge badge-ok" title="<?= e((string)$r['son_bildirim_tarihi']) ?>"><i class="fas fa-check"></i> OK</span>
                <?php elseif ($kalan < 30 && $r['durum'] === 'aktif'): ?>
                    <form method="post" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="islem" value="bildirim_isaretle">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="btn btn-blue btn-sm" title="Aradım/SMS attım olarak işaretle"><i class="fas fa-bell"></i></button>
                    </form>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <div class="actions">
                    <a href="?duzenle=<?= $r['id'] ?>" class="btn btn-out btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
                    <form method="post" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="islem" value="bakim_yapildi">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="btn btn-pri btn-sm" data-onay="Bakım yapıldı olarak işaretlensin mi?" title="Bakım yapıldı"><i class="fas fa-check-circle"></i></button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/bakim-hatirlaticilari.php?' . http_build_query(array_filter(['q'=>$arama,'tip'=>$tipf,'durum'=>$durum,'zaman'=>$zaman]));
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
