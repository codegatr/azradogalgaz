<?php
if (!defined('AZRA_INTERNAL')) define('AZRA_INTERNAL', true);
$_meta = $GLOBALS['_meta'] ?? [];
$baslik   = get_meta('baslik',  ayar('site_baslik', SITE_TITLE . ' — ' . SITE_SLOGAN));
$aciklama = get_meta('aciklama',ayar('site_aciklama', SITE_DESC));
$kelimeler= get_meta('kelimeler',ayar('site_anahtar_kelime', SITE_KEYWORDS));
$canonical= get_meta('canonical', SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/'));
$og_image = get_meta('og_image', SITE_URL . '/assets/img/og-default.jpg');
$gsc_meta = ayar('google_search_console_meta', '');
$ga_id    = ayar('google_analytics', '');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($baslik) ?></title>
<meta name="description" content="<?= e($aciklama) ?>">
<meta name="keywords" content="<?= e($kelimeler) ?>">
<meta name="author" content="<?= e(SITE_TITLE) ?>">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="theme-color" content="#0d1424">
<meta name="format-detection" content="telephone=yes">
<meta name="geo.region" content="TR-35">
<meta name="geo.placename" content="İzmir">
<link rel="canonical" href="<?= e($canonical) ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="<?= e(SITE_TITLE) ?>">
<meta property="og:title" content="<?= e($baslik) ?>">
<meta property="og:description" content="<?= e($aciklama) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($og_image) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($baslik) ?>">
<meta name="twitter:description" content="<?= e($aciklama) ?>">
<meta name="twitter:image" content="<?= e($og_image) ?>">

<?php if ($gsc_meta): ?><meta name="google-site-verification" content="<?= e($gsc_meta) ?>"><?php endif; ?>

<link rel="icon" href="<?= SITE_URL ?>/assets/img/favicon.ico">
<link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/img/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?: time() ?>">

<?= local_business_schema() ?>
<?= get_meta('extra_schema', '') ?>

<?php if ($ga_id): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga_id) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga_id) ?>');</script>
<?php endif; ?>
</head>
<body>

<!-- ÜST BİLGİ ÇUBUĞU -->
<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-left">
            <span><i class="fas fa-clock"></i> <?= e(ayar('firma_calisma_saatleri','Pzt-Cmt 08:00-20:00')) ?></span>
            <span><i class="fas fa-map-marker-alt"></i> <?= e(ayar('firma_adres', FIRMA_ADRES)) ?></span>
        </div>
        <div class="topbar-right">
            <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1', FIRMA_TEL_1)) ?>"><i class="fas fa-phone-volume"></i> <?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?></a>
            <a href="mailto:<?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?>"><i class="fas fa-envelope"></i> <?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?></a>
        </div>
    </div>
</div>

<!-- ANA NAVBAR -->
<header class="header" id="header">
    <div class="container header-inner">
        <a href="<?= SITE_URL ?>/" class="logo">
            <span class="logo-text">
                <span class="logo-azra">AZRA</span>
                <span class="logo-doga">DOĞALGAZ</span>
            </span>
            <span class="logo-tag">Kombi · Klima · Tesisat</span>
        </a>

        <nav class="nav" id="mainNav">
            <a href="<?= SITE_URL ?>/" <?= (basename($_SERVER['SCRIPT_NAME']) === 'index.php') ? 'class="active"' : '' ?>>Ana Sayfa</a>
            <a href="<?= SITE_URL ?>/hizmetler">Hizmetler</a>
            <a href="<?= SITE_URL ?>/urunler">Ürünler</a>
            <a href="<?= SITE_URL ?>/kampanyalar" class="nav-highlight">Kampanyalar</a>
            <a href="<?= SITE_URL ?>/hakkimizda">Hakkımızda</a>
            <a href="<?= SITE_URL ?>/blog">Blog</a>
            <a href="<?= SITE_URL ?>/iletisim">İletişim</a>
        </nav>

        <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1', FIRMA_TEL_1)) ?>" class="header-cta">
            <i class="fas fa-phone-volume"></i>
            <span>
                <small>Hemen Ara</small>
                <strong><?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?></strong>
            </span>
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Menüyü aç/kapat">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
<main>
