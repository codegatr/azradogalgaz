<?php
/**
 * Frontend ortak header — v1.4.0 açık tema
 */
$tit = $sayfa_baslik ?? ayar('site_baslik', defined('SITE_TITLE')?SITE_TITLE:'Azra Doğalgaz');
$des = $sayfa_aciklama ?? ayar('site_aciklama', defined('SITE_DESC')?SITE_DESC:'');
$can = $kanonik_url ?? (SITE_URL . $_SERVER['REQUEST_URI']);
$og  = $og_resim ?? (SITE_URL . '/assets/img/og-default.jpg');
$key = $sayfa_anahtar ?? ayar('site_anahtar_kelime', defined('SITE_KEYWORDS')?SITE_KEYWORDS:'');

$tel1 = ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:'');
$tel2 = ayar('firma_telefon_2', defined('FIRMA_TEL_2')?FIRMA_TEL_2:'');
$wa   = ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'');
$mail = ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'');
$saat = ayar('firma_calisma_saatleri', 'Pzt-Cmt 08:00-20:00');

$ga = trim((string)ayar('google_analytics', ''));
$gsc = trim((string)ayar('google_search_console_meta', ''));
$aktif = basename((string)$_SERVER['SCRIPT_NAME'], '.php');

try {
    $hizmet_menu = db_all("SELECT slug, ad FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira ASC, id ASC");
} catch (Throwable $e) { $hizmet_menu = []; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($tit) ?></title>
<meta name="description" content="<?= e($des) ?>">
<meta name="keywords" content="<?= e($key) ?>">
<meta name="robots" content="index, follow">
<meta name="author" content="Azra Doğalgaz">
<meta name="geo.region" content="TR-35">
<meta name="geo.placename" content="İzmir">
<link rel="canonical" href="<?= e($can) ?>">

<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($tit) ?>">
<meta property="og:description" content="<?= e($des) ?>">
<meta property="og:url" content="<?= e($can) ?>">
<meta property="og:image" content="<?= e($og) ?>">
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="Azra Doğalgaz">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($tit) ?>">
<meta name="twitter:description" content="<?= e($des) ?>">
<meta name="twitter:image" content="<?= e($og) ?>">

<?php if ($gsc): ?><meta name="google-site-verification" content="<?= e($gsc) ?>"><?php endif; ?>

<link rel="icon" href="<?= SITE_URL ?>/assets/img/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?: time() ?>">

<?php
$base_schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'HVACBusiness',
    'name'     => 'Azra Doğalgaz Tesisat',
    'image'    => SITE_URL . '/assets/img/logo.png',
    'url'      => SITE_URL,
    'telephone'=> $tel1,
    'email'    => $mail,
    'priceRange'=> '₺₺',
    'address'  => ['@type' => 'PostalAddress', 'addressLocality' => 'İzmir', 'addressRegion' => 'İzmir', 'addressCountry' => 'TR'],
    'areaServed' => ['@type' => 'City', 'name' => 'İzmir'],
    'openingHoursSpecification' => ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'], 'opens' => '08:00', 'closes' => '20:00'],
];
echo schema_org($base_schema);

if (!empty($schema_jsonld)) {
    if (isset($schema_jsonld['@context'])) echo schema_org($schema_jsonld);
    else foreach ($schema_jsonld as $s) echo schema_org($s);
}
?>

<?php if ($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?= e($ga) ?>');
</script>
<?php endif; ?>
</head>
<body>

<div class="top-bar">
    <div class="container">
        <div class="left">
            <span><i class="fas fa-clock"></i><?= e($saat) ?></span>
            <a href="mailto:<?= e($mail) ?>"><i class="fas fa-envelope"></i><?= e($mail) ?></a>
        </div>
        <div class="right">
            <span class="tag" style="background:rgba(255,107,0,.2);color:#ffaa44">İZMİRGAZ Yetkili Tesisat Firması</span>
            <a href="tel:<?= e(preg_replace('/\s/','',$tel1)) ?>"><i class="fas fa-phone"></i><?= e($tel1) ?></a>
        </div>
    </div>
</div>

<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">
            <a href="<?= SITE_URL ?>/" class="brand" aria-label="Azra Doğalgaz Ana Sayfa">
                <div class="brand-text">
                    <span class="azra">AZRA</span>
                    <span class="doga">DOĞALGAZ</span>
                    <small>İzmir · Demirdöküm Yetkili</small>
                </div>
            </a>

            <nav class="main-nav" id="mainNav" aria-label="Ana Menü">
                <a href="<?= SITE_URL ?>/" class="<?= $aktif==='index'?'active':'' ?>">Ana Sayfa</a>
                <div class="has-dropdown">
                    <a href="<?= SITE_URL ?>/hizmetler" class="<?= in_array($aktif,['hizmetler','hizmet','kategori'])?'active':'' ?>">Hizmetler</a>
                    <div class="dropdown">
                        <?php foreach (array_slice($hizmet_menu, 0, 8) as $h): ?>
                            <a href="<?= SITE_URL ?>/kategori/<?= e($h['slug']) ?>"><i class="fas fa-fire"></i><?= e($h['ad']) ?></a>
                        <?php endforeach; ?>
                        <a href="<?= SITE_URL ?>/hizmetler" style="border-top:1px solid var(--c-line);margin-top:6px;padding-top:14px;font-weight:700"><i class="fas fa-arrow-right"></i>Tüm Hizmetler</a>
                    </div>
                </div>
                <a href="<?= SITE_URL ?>/urunler" class="<?= in_array($aktif,['urunler','urun'])?'active':'' ?>">Ürünler</a>
                <a href="<?= SITE_URL ?>/projeler" class="<?= $aktif==='projeler'?'active':'' ?>">Projelerimiz</a>
                <a href="<?= SITE_URL ?>/kampanyalar" class="<?= in_array($aktif,['kampanyalar','kampanya'])?'active':'' ?>">Kampanyalar</a>
                <div class="has-dropdown">
                    <a href="<?= SITE_URL ?>/bilgi-bankasi" class="<?= in_array($aktif,['bilgi-bankasi','blog','sss','kombi-hesaplama','klima-hesaplama'])?'active':'' ?>">Bilgi</a>
                    <div class="dropdown">
                        <a href="<?= SITE_URL ?>/blog"><i class="fas fa-newspaper"></i>Blog</a>
                        <a href="<?= SITE_URL ?>/sss"><i class="fas fa-circle-question"></i>Sık Sorulan Sorular</a>
                        <a href="<?= SITE_URL ?>/kombi-hesaplama"><i class="fas fa-calculator"></i>Kombi Kapasite Hesaplama</a>
                        <a href="<?= SITE_URL ?>/klima-hesaplama"><i class="fas fa-snowflake"></i>Klima BTU Hesaplama</a>
                        <a href="<?= SITE_URL ?>/bilgi-bankasi"><i class="fas fa-book"></i>Doğalgaz Bilgi Bankası</a>
                    </div>
                </div>
                <a href="<?= SITE_URL ?>/hakkimizda" class="<?= $aktif==='hakkimizda'?'active':'' ?>">Hakkımızda</a>
                <a href="<?= SITE_URL ?>/iletisim" class="<?= $aktif==='iletisim'?'active':'' ?>">İletişim</a>
            </nav>

            <div class="header-cta">
                <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary btn-sm" style="white-space:nowrap"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif</a>
                <button class="menu-toggle" id="menuToggle" aria-label="Menü"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </div>
</header>
