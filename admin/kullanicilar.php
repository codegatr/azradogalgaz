<?php
require_once __DIR__ . '/_baslat.php';
page_title('Kullanıcılar');

// Sadece admin rolü erişebilir
if (($_kul['rol'] ?? '') !== 'admin') {
    flash_set('err', 'Bu sayfaya yalnızca admin yetkili kullanıcılar erişebilir.');
    redirect(SITE_URL . '/admin/panel.php');
}

$mod      = $_GET['mod'] ?? 'liste';
$kayit_id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect(SITE_URL . '/admin/kullanicilar.php');
    }
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'sil') {
        $sid = (int)($_POST['id'] ?? 0);
        if ($sid === (int)$_kul['id']) {
            flash_set('err', 'Kendi hesabınızı silemezsiniz.');
        } elseif ($sid > 0) {
            db_run("DELETE FROM kullanicilar WHERE id=?", [$sid]);
            log_yaz('kullanici_sil', "Kullanıcı silindi: ID $sid", (int)$_kul['id']);
            flash_set('ok', 'Kullanıcı silindi.');
        }
        redirect(SITE_URL . '/admin/kullanicilar.php');
    }

    if ($islem === 'kaydet') {
        $id     = (int)($_POST['id'] ?? 0);
        $ad     = trim((string)($_POST['ad'] ?? ''));
        $eposta = trim((string)($_POST['eposta'] ?? ''));
        $rol    = ($_POST['rol'] ?? 'editor') === 'admin' ? 'admin' : 'editor';
        $aktif  = !empty($_POST['aktif']) ? 1 : 0;
        $sifre  = (string)($_POST['sifre'] ?? '');

        if (!$ad || !$eposta) {
            flash_set('err', 'Ad ve e-posta zorunlu.');
            redirect($_SERVER['REQUEST_URI']);
        }
        if (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
            flash_set('err', 'Geçerli bir e-posta girin.');
            redirect($_SERVER['REQUEST_URI']);
        }

        try {
            if ($id > 0) {
                // Güncelleme
                if (strlen($sifre) >= 6) {
                    $hash = password_hash($sifre, PASSWORD_DEFAULT);
                    db_run("UPDATE kullanicilar SET ad=?, eposta=?, sifre=?, rol=?, aktif=? WHERE id=?",
                        [$ad, $eposta, $hash, $rol, $aktif, $id]);
                } else {
                    db_run("UPDATE kullanicilar SET ad=?, eposta=?, rol=?, aktif=? WHERE id=?",
                        [$ad, $eposta, $rol, $aktif, $id]);
                }
                log_yaz('kullanici_guncelle', "Kullanıcı güncellendi: $eposta", (int)$_kul['id']);
                flash_set('ok', 'Kullanıcı güncellendi.');
            } else {
                if (strlen($sifre) < 6) {
                    flash_set('err', 'Yeni kullanıcıya en az 6 karakter şifre girilmeli.');
                    redirect($_SERVER['REQUEST_URI']);
                }
                $hash = password_hash($sifre, PASSWORD_DEFAULT);
                db_run("INSERT INTO kullanicilar (ad, eposta, sifre, rol, aktif) VALUES (?, ?, ?, ?, ?)",
                    [$ad, $eposta, $hash, $rol, $aktif]);
                log_yaz('kullanici_ekle', "Yeni kullanıcı: $eposta", (int)$_kul['id']);
                flash_set('ok', 'Kullanıcı eklendi.');
            }
            redirect(SITE_URL . '/admin/kullanicilar.php');
        } catch (PDOException $e) {
            $msg = (str_contains($e->getMessage(), 'Duplicate') ? 'Bu e-posta zaten kullanılıyor.' : 'Hata: ' . $e->getMessage());
            flash_set('err', $msg);
            redirect($_SERVER['REQUEST_URI']);
        }
    }
}

$kayit = null;
if ($mod === 'duzenle' && $kayit_id) {
    $kayit = db_get("SELECT * FROM kullanicilar WHERE id=?", [$kayit_id]);
    if (!$kayit) { flash_set('err', 'Kullanıcı bulunamadı.'); redirect(SITE_URL . '/admin/kullanicilar.php'); }
}

$kullanicilar = db_all("SELECT id, ad, eposta, rol, aktif, son_giris, olusturma_tarihi FROM kullanicilar ORDER BY id ASC");

require_once __DIR__ . '/_header.php';
?>

<?php if ($mod === 'liste'): ?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Kullanıcılar</h1>
        <p class="page-sub">Admin paneline erişim yetkisi olan kullanıcıları yönetin. <strong><?= count($kullanicilar) ?></strong> kullanıcı.</p>
    </div>
    <a href="?mod=yeni" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Kullanıcı</a>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<div class="tbl">
<table>
    <thead>
        <tr>
            <th>Ad Soyad</th>
            <th>E-posta</th>
            <th>Rol</th>
            <th>Son Giriş</th>
            <th>Durum</th>
            <th style="width:140px;text-align:right">İşlem</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($kullanicilar as $k): ?>
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <span class="av"><?= mb_strtoupper(mb_substr($k['ad'], 0, 1, 'UTF-8'), 'UTF-8') ?></span>
                    <strong><?= e($k['ad']) ?></strong>
                    <?php if ($k['id'] === $_kul['id']): ?><span class="badge badge-info" style="font-size:.65rem">Siz</span><?php endif; ?>
                </div>
            </td>
            <td><?= e($k['eposta']) ?></td>
            <td>
                <?php if ($k['rol'] === 'admin'): ?>
                <span class="badge badge-warn"><i class="fas fa-crown"></i> Admin</span>
                <?php else: ?>
                <span class="badge badge-info">Editor</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($k['son_giris']): ?>
                <span style="font-size:.85rem"><?= tarih_tr($k['son_giris'], true) ?></span>
                <?php else: ?>
                <span style="color:var(--c-muted)">—</span>
                <?php endif; ?>
            </td>
            <td><?php if ($k['aktif']): ?><span class="badge badge-ok">Aktif</span><?php else: ?><span class="badge badge-no">Pasif</span><?php endif; ?></td>
            <td style="text-align:right">
                <a href="?mod=duzenle&id=<?= (int)$k['id'] ?>" class="btn btn-out btn-sm"><i class="fas fa-pen"></i></a>
                <?php if ($k['id'] !== $_kul['id']): ?>
                <form method="post" style="display:inline" onsubmit="return confirm('<?= e($k['ad']) ?> kullanıcısı silinsin mi?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="islem" value="sil">
                    <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php else: // FORM ?>

<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $kayit ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı' ?></h1>
        <p class="page-sub"><?= $kayit ? e($kayit['ad']) : 'Admin paneline erişim yetkisi olan yeni kullanıcı oluştur' ?></p>
    </div>
    <a href="kullanicilar.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye Dön</a>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<form method="post" style="max-width:680px">
    <?= csrf_field() ?>
    <input type="hidden" name="islem" value="kaydet">
    <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

    <div class="card">
        <div class="field">
            <label>Ad Soyad <span class="req">*</span></label>
            <input class="input" name="ad" value="<?= e($kayit['ad'] ?? '') ?>" required maxlength="100">
        </div>
        <div class="field">
            <label>E-posta <span class="req">*</span></label>
            <input type="email" class="input" name="eposta" value="<?= e($kayit['eposta'] ?? '') ?>" required maxlength="160">
        </div>
        <div class="field">
            <label>
                <?= $kayit ? 'Yeni Şifre (boş bırakırsa değişmez)' : 'Şifre' ?>
                <?php if (!$kayit): ?><span class="req">*</span><?php endif; ?>
            </label>
            <input type="password" class="input" name="sifre" minlength="6" autocomplete="new-password" placeholder="<?= $kayit ? 'Değiştirmek için yeni şifre yazın' : 'En az 6 karakter' ?>">
        </div>
        <div class="form-row cols-2">
            <div class="field">
                <label>Rol</label>
                <select name="rol" class="input">
                    <option value="editor" <?= ($kayit['rol'] ?? '')==='editor'?'selected':'' ?>>Editor (içerik yönetimi)</option>
                    <option value="admin" <?= ($kayit['rol'] ?? 'editor')==='admin'?'selected':'' ?>>Admin (tüm yetkiler)</option>
                </select>
            </div>
            <div class="field">
                <label>Durum</label>
                <label class="check" style="margin-top:10px">
                    <input type="checkbox" name="aktif" value="1" <?= !isset($kayit['aktif']) || $kayit['aktif'] ? 'checked' : '' ?>>
                    <span>Hesap aktif</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
        <a href="kullanicilar.php" class="btn btn-out">İptal</a>
    </div>
</form>

<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
