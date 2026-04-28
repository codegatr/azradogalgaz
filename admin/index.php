<?php
require_once __DIR__ . '/_baslat.php';

if (admin_giris_var()) {
    redirect(SITE_URL . '/admin/panel.php');
}

$hata = $_GET['hata'] ?? '';
$bilgi = $_GET['bilgi'] ?? '';
$eposta_son = $_GET['e'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giriş Yap · Azra Doğalgaz Yönetim</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?: time() ?>">
</head>
<body>

<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <span class="azra">AZRA</span>
            <span class="doga">DOĞALGAZ</span>
            <small>Yönetim Paneli</small>
        </div>
        <h2>Hoş Geldiniz</h2>
        <p class="sub">Devam etmek için giriş yapın.</p>

        <?php if ($hata): ?>
            <div class="alert alert-err"><i class="fas fa-circle-xmark"></i> <?= e($hata) ?></div>
        <?php endif; ?>
        <?php if ($bilgi): ?>
            <div class="alert alert-info"><i class="fas fa-circle-info"></i> <?= e($bilgi) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= SITE_URL ?>/admin/giris-yap.php" autocomplete="on">
            <?= csrf_field() ?>
            <div class="form-row" style="margin-bottom:14px">
                <div class="field">
                    <label>Kullanıcı Adı veya E-posta</label>
                    <input type="text" name="kimlik" value="<?= e($eposta_son) ?>" class="input" required autofocus autocomplete="username" placeholder="kullanici_adi veya kullanici@domain.com">
                </div>
            </div>
            <div class="form-row" style="margin-bottom:14px">
                <div class="field">
                    <label>Şifre</label>
                    <input type="password" name="sifre" class="input" required>
                </div>
            </div>
            <div class="form-row" style="margin-bottom:18px">
                <label class="check">
                    <input type="checkbox" name="hatirla" value="1">
                    <span>Beni hatırla (30 gün)</span>
                </label>
            </div>
            <button type="submit" class="btn btn-pri" style="width:100%;font-size:.95rem;padding:12px">
                <i class="fas fa-right-to-bracket"></i> Giriş Yap
            </button>
        </form>

        <p style="text-align:center;color:var(--c-muted);font-size:.78rem;margin-top:22px">
            <a href="<?= SITE_URL ?>/" style="color:var(--c-muted)"><i class="fas fa-arrow-left"></i> Siteye dön</a>
        </p>
    </div>
</div>

</body>
</html>
