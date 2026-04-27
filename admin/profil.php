<?php
require_once __DIR__ . '/_baslat.php';
page_title('Profil & Şifre');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu, sayfayı yenileyin.');
        redirect($_SERVER['REQUEST_URI']);
    }

    $islem = $_POST['islem'] ?? '';

    if ($islem === 'profil') {
        $ad = clean($_POST['ad'] ?? '');
        $eposta = clean($_POST['eposta'] ?? '');
        if ($ad === '' || !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
            flash_set('err', 'Ad ve geçerli bir e-posta adresi gerekli.');
        } else {
            // E-posta başkasında var mı?
            $cak = db_get("SELECT id FROM kullanicilar WHERE eposta=? AND id<>?", [$eposta, (int)$_kul['id']]);
            if ($cak) {
                flash_set('err', 'Bu e-posta başka bir hesapta kayıtlı.');
            } else {
                db_run("UPDATE kullanicilar SET ad=?, eposta=? WHERE id=?", [$ad, $eposta, (int)$_kul['id']]);
                $_SESSION['admin_ad'] = $ad;
                log_yaz('profil_guncelle', 'Profil güncellendi.', (int)$_kul['id']);
                flash_set('ok', 'Profil bilgilerin güncellendi.');
            }
        }
    } elseif ($islem === 'sifre') {
        $eski = (string)($_POST['eski_sifre'] ?? '');
        $yeni = (string)($_POST['yeni_sifre'] ?? '');
        $tek  = (string)($_POST['yeni_sifre_tekrar'] ?? '');

        if (mb_strlen($yeni) < 8) {
            flash_set('err', 'Yeni şifre en az 8 karakter olmalı.');
        } elseif ($yeni !== $tek) {
            flash_set('err', 'Yeni şifre tekrarı uyuşmuyor.');
        } else {
            $sifre_db = db_get("SELECT sifre FROM kullanicilar WHERE id=?", [(int)$_kul['id']])['sifre'] ?? '';
            if (!password_verify($eski, $sifre_db)) {
                log_yaz('sifre_degistir_fail', 'Yanlış mevcut şifre denemesi.', (int)$_kul['id']);
                flash_set('err', 'Mevcut şifre hatalı.');
            } else {
                db_run("UPDATE kullanicilar SET sifre=? WHERE id=?", [password_hash($yeni, PASSWORD_DEFAULT), (int)$_kul['id']]);
                log_yaz('sifre_degistir', 'Şifre başarıyla değiştirildi.', (int)$_kul['id']);
                flash_set('ok', 'Şifren güncellendi.');
            }
        }
    }
    redirect($_SERVER['REQUEST_URI']);
}

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Profil & Şifre</h1>
        <p class="page-sub">Hesap bilgilerini ve şifreni buradan güncelleyebilirsin.</p>
    </div>
</div>

<div class="form-row cols-2" style="margin-bottom:0">

    <!-- Profil -->
    <div class="card">
        <h3>Profil Bilgileri</h3>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="profil">
            <div class="form-row">
                <div class="field">
                    <label>Ad Soyad</label>
                    <input class="input" name="ad" value="<?= e($_kul['ad']) ?>" required maxlength="80">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>E-posta</label>
                    <input class="input" type="email" name="eposta" value="<?= e($_kul['eposta']) ?>" required maxlength="160">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Rol</label>
                    <input class="input" value="<?= e($_kul['rol']) ?>" disabled>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Profili Kaydet</button>
            </div>
        </form>
    </div>

    <!-- Şifre -->
    <div class="card">
        <h3>Şifre Değiştir</h3>
        <form method="post" autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="sifre">
            <div class="form-row">
                <div class="field">
                    <label>Mevcut Şifre</label>
                    <input class="input" type="password" name="eski_sifre" required autocomplete="current-password">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Yeni Şifre <span class="opt">(en az 8 karakter)</span></label>
                    <input class="input" type="password" name="yeni_sifre" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Yeni Şifre (Tekrar)</label>
                    <input class="input" type="password" name="yeni_sifre_tekrar" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-key"></i> Şifreyi Değiştir</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
