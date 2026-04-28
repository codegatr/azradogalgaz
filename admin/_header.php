<?php
if (!isset($_kul)) { redirect(SITE_URL . '/admin/'); }
$page_title = $GLOBALS['_admin_page_title'] ?? 'Yönetim Paneli';

// Yeni mesaj sayısı (sidebar rozeti)
$_yeni_mesaj = (int)(db_get("SELECT COUNT(*) c FROM iletisim_mesajlari WHERE durum='yeni'") ?? ['c' => 0])['c'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> · Azra Doğalgaz Yönetim</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?: time() ?>">
</head>
<body>
<div class="adm-body">

<aside class="adm-sidebar" id="admSidebar">
    <div class="adm-brand">
        <span class="azra">AZRA</span>
        <span class="doga">DOĞALGAZ</span>
        <small>Yönetim Paneli</small>
    </div>

    <nav class="adm-nav">
        <div class="group">Genel</div>
        <a href="<?= SITE_URL ?>/admin/panel.php" class="<?= nav_active('panel.php') ?>">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/admin/iletisim-mesajlari.php" class="<?= nav_active('iletisim-mesajlari.php') ?>">
            <i class="fas fa-envelope"></i> İletişim Mesajları
            <?php if ($_yeni_mesaj > 0): ?><span class="badge"><?= $_yeni_mesaj ?></span><?php endif; ?>
        </a>

        <div class="group">İçerik</div>
        <a href="<?= SITE_URL ?>/admin/hizmet-kategorileri.php" class="<?= nav_active('hizmet-kategorileri.php') ?>">
            <i class="fas fa-folder-tree"></i> Hizmet Kategorileri
        </a>
        <a href="<?= SITE_URL ?>/admin/hizmetler.php" class="<?= nav_active('hizmetler.php') ?>">
            <i class="fas fa-tools"></i> Hizmetler
        </a>
        <a href="<?= SITE_URL ?>/admin/markalar.php" class="<?= nav_active('markalar.php') ?>">
            <i class="fas fa-trademark"></i> Markalar
        </a>
        <a href="<?= SITE_URL ?>/admin/urun-kategorileri.php" class="<?= nav_active('urun-kategorileri.php') ?>">
            <i class="fas fa-tags"></i> Ürün Kategorileri
        </a>
        <a href="<?= SITE_URL ?>/admin/urunler.php" class="<?= nav_active('urunler.php') ?>">
            <i class="fas fa-fire-flame-curved"></i> Ürünler
        </a>
        <a href="<?= SITE_URL ?>/admin/kampanyalar.php" class="<?= nav_active('kampanyalar.php') ?>">
            <i class="fas fa-bullhorn"></i> Kampanyalar
        </a>
        <a href="<?= SITE_URL ?>/admin/blog.php" class="<?= nav_active('blog.php') ?>">
            <i class="fas fa-newspaper"></i> Blog
        </a>
        <a href="<?= SITE_URL ?>/admin/projeler.php" class="<?= nav_active('projeler.php') ?>">
            <i class="fas fa-building"></i> Projeler / Referanslar
        </a>
        <a href="<?= SITE_URL ?>/admin/sayfalar.php" class="<?= nav_active('sayfalar.php') ?>">
            <i class="fas fa-file-lines"></i> KVKK / Gizlilik
        </a>

        <?php if (($_kul['rol'] ?? '') === 'admin'): ?>
        <div class="group">Sistem</div>
        <a href="<?= SITE_URL ?>/admin/ayarlar.php" class="<?= nav_active('ayarlar.php') ?>">
            <i class="fas fa-gear"></i> Ayarlar
        </a>
        <a href="<?= SITE_URL ?>/admin/kullanicilar.php" class="<?= nav_active('kullanicilar.php') ?>">
            <i class="fas fa-users-gear"></i> Kullanıcılar
        </a>
        <a href="<?= SITE_URL ?>/admin/profil.php" class="<?= nav_active('profil.php') ?>">
            <i class="fas fa-user-shield"></i> Profil & Şifre
        </a>
        <a href="<?= SITE_URL ?>/admin/loglar.php" class="<?= nav_active('loglar.php') ?>">
            <i class="fas fa-clipboard-list"></i> Sistem Logları
        </a>
        <a href="<?= SITE_URL ?>/admin/guncelleme.php" class="<?= nav_active('guncelleme.php') ?>">
            <i class="fas fa-cloud-arrow-down"></i> Güncelleme
        </a>
        <a href="<?= SITE_URL ?>/admin/cikis.php">
            <i class="fas fa-right-from-bracket"></i> Çıkış Yap
        </a>
        <?php endif; ?>
    </nav>

    <div class="adm-side-foot">
        <span>v<?= e(json_decode(file_get_contents(__DIR__.'/../manifest.json'),true)['version'] ?? '1.0') ?></span>
        <a href="<?= SITE_URL ?>/" target="_blank">Siteyi Gör <i class="fas fa-external-link-alt"></i></a>
    </div>
</aside>

<main class="adm-main">
    <header class="adm-topbar">
        <button class="menu-btn" id="admMenuBtn" aria-label="Menü"><i class="fas fa-bars"></i></button>
        <h1 class="page-title"><?= e($page_title) ?></h1>
        <div class="adm-user">
            <span class="av"><?= mb_strtoupper(mb_substr($_kul['ad'] ?? 'A', 0, 1, 'UTF-8'), 'UTF-8') ?></span>
            <span><?= e(mb_strimwidth($_kul['ad'] ?? 'Yönetici', 0, 26, '…', 'UTF-8')) ?></span>
        </div>
    </header>

    <section class="adm-content">
        <?php foreach (flash_pop() as $f): ?>
            <div class="alert alert-<?= e($f['tip']) ?>"><?= $f['msg'] ?></div>
        <?php endforeach; ?>
