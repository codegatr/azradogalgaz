<?php
require_once __DIR__ . '/config.php';
http_response_code(404);
set_meta([
    'baslik'   => '404 — Sayfa Bulunamadı | ' . SITE_TITLE,
    'aciklama' => 'Aradığınız sayfa bulunamadı.',
    'canonical'=> SITE_URL . '/',
]);
require_once INC_PATH . '/header.php';
?>
<section class="hero" style="text-align:center">
    <div class="container" style="max-width:680px">
        <h1 style="font-size:6rem;margin-bottom:0;background:var(--grad-orange);-webkit-background-clip:text;background-clip:text;color:transparent">404</h1>
        <h2 style="margin-bottom:14px">Sayfa Bulunamadı</h2>
        <p style="color:var(--c-muted);margin-bottom:28px">Aradığınız sayfa kaldırılmış veya hiç var olmamış olabilir. Dilerseniz ana sayfaya dönün ya da bizimle iletişime geçin.</p>
        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap">
            <a href="<?= SITE_URL ?>/" class="btn btn-primary"><i class="fas fa-home"></i> Ana Sayfa</a>
            <a href="<?= SITE_URL ?>/iletisim" class="btn btn-outline"><i class="fas fa-paper-plane"></i> İletişim</a>
        </div>
    </div>
</section>
<?php require_once INC_PATH . '/footer.php'; ?>
