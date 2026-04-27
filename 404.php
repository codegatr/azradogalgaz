<?php
require_once __DIR__ . '/config.php';
http_response_code(404);

$sayfa_baslik   = '404 — Sayfa Bulunamadı | ' . (defined('SITE_TITLE') ? SITE_TITLE : 'Azra Doğalgaz');
$sayfa_aciklama = 'Aradığınız sayfa bulunamadı.';
$kanonik_url    = SITE_URL . '/';

require_once __DIR__ . '/inc/header.php';
?>

<section style="padding:80px 0;background:var(--grad-hero);text-align:center;min-height:60vh;display:flex;align-items:center">
    <div class="container" style="max-width:680px">
        <div style="font-family:var(--font-display);font-size:clamp(5rem,15vw,9rem);font-weight:900;line-height:1;background:var(--grad-primary);-webkit-background-clip:text;background-clip:text;color:transparent;margin-bottom:10px">404</div>
        <h1 style="font-family:var(--font-display);font-size:clamp(1.5rem,3vw,2.2rem);font-weight:800;color:var(--c-text);margin-bottom:14px">Sayfa Bulunamadı</h1>
        <p style="color:var(--c-muted);font-size:1.05rem;margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto">Aradığınız sayfa kaldırılmış, taşınmış veya hiç var olmamış olabilir. Aşağıdaki bağlantılardan devam edebilirsiniz.</p>
        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap">
            <a href="<?= SITE_URL ?>/" class="btn btn-primary btn-lg"><i class="fas fa-house"></i> Ana Sayfa</a>
            <a href="<?= SITE_URL ?>/iletisim" class="btn btn-out btn-lg"><i class="fas fa-paper-plane"></i> İletişim</a>
            <a href="<?= SITE_URL ?>/kesif" class="btn btn-blue btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif</a>
        </div>

        <div style="margin-top:50px;padding-top:30px;border-top:1px solid var(--c-line)">
            <p style="color:var(--c-muted);font-size:.92rem;margin-bottom:14px">Belki şunları arıyordunuz?</p>
            <div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap">
                <a href="<?= SITE_URL ?>/hizmetler" class="btn btn-ghost btn-sm">Hizmetler</a>
                <a href="<?= SITE_URL ?>/urunler" class="btn btn-ghost btn-sm">Ürünler</a>
                <a href="<?= SITE_URL ?>/kampanyalar" class="btn btn-ghost btn-sm">Kampanyalar</a>
                <a href="<?= SITE_URL ?>/kombi-hesaplama" class="btn btn-ghost btn-sm">Kombi Hesaplama</a>
                <a href="<?= SITE_URL ?>/sss" class="btn btn-ghost btn-sm">SSS</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
