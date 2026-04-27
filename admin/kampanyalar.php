<?php
require_once __DIR__ . '/_baslat.php';
$dosya = basename($_SERVER['SCRIPT_NAME'], '.php');
$basliklar = [
    'hizmetler'           => 'Hizmetler',
    'markalar'            => 'Markalar',
    'urun-kategorileri'   => 'Ürün Kategorileri',
    'urunler'             => 'Ürünler',
    'kampanyalar'         => 'Kampanyalar',
    'blog'                => 'Blog Yönetimi',
    'sayfalar'            => 'KVKK / Gizlilik',
];
page_title($basliklar[$dosya] ?? 'Yakında');
require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <h1 class="page-h1"><?= e($basliklar[$dosya] ?? 'Yakında') ?></h1>
</div>

<div class="card" style="text-align:center;padding:50px 24px">
    <i class="fas fa-screwdriver-wrench" style="font-size:3rem;color:var(--c-orange);margin-bottom:18px;display:block"></i>
    <h3 style="margin-bottom:8px">Bu modül Aşama 3-B'de geliyor</h3>
    <p style="color:var(--c-muted);max-width:480px;margin:0 auto 22px">
        İçerik yönetimi modülleri (hizmetler, ürünler, kampanyalar, blog, dosya yükleme) bir sonraki teslimde aktif olacak.
        Şimdilik içerikleri phpMyAdmin'den de manuel ekleyebilirsin.
    </p>
    <a href="<?= SITE_URL ?>/admin/panel.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Dashboard'a Dön</a>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
