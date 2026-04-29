<?php
require_once __DIR__ . '/config.php';

$kampanyalar = db_all("SELECT * FROM kampanyalar WHERE aktif=1 ORDER BY id DESC");

$sayfa_baslik   = 'Kampanyalar — Kombi & Klima Paket Fiyatları | Azra Doğalgaz';
$sayfa_aciklama = 'İzmir\'de Demirdöküm kombi paketi 80.000 ₺\'den başlayan fiyatlarla. 6 taksit, ücretsiz keşif, Mevzuata uygun kurulum.';
$kanonik_url    = SITE_URL . '/kampanyalar';

$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Kampanyalar','item'=>SITE_URL.'/kampanyalar'],
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Kampanyalar</span>
        </div>
        <h1>Kampanyalar</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Kombi, klima ve tesisat paketlerinde fırsat fiyatları. Sınırlı süreli kampanyaları kaçırmayın!</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <?php if ($kampanyalar): ?>
        <div class="services">
            <?php foreach ($kampanyalar as $k):
                $nakit = (float)$k['nakit_fiyat'];
                $kart  = (float)$k['kart_fiyat'];
                $taksit = (int)($k['taksit_sayisi'] ?? 0);
            ?>
            <article class="service-card">
                <div class="service-image" style="position:relative">
                    <?php if (!empty($k['gorsel'])): ?>
                        <img src="<?= e(gorsel_url($k['gorsel'])) ?>" alt="<?= e($k['baslik']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
                    <?php else: ?>
                        <i class="fas fa-fire-flame-curved"></i>
                    <?php endif; ?>
                    <span style="position:absolute;top:12px;left:12px;background:var(--c-red);color:#fff;padding:5px 12px;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;z-index:2">🔥 Kampanya</span>
                </div>
                <div class="service-body">
                    <h3><?= e($k['baslik']) ?></h3>
                    <?php if (!empty($k['kisa_aciklama'])): ?>
                    <p><?= e(mb_strimwidth($k['kisa_aciklama'], 0, 130, '…', 'UTF-8')) ?></p>
                    <?php endif; ?>

                    <?php if ($nakit > 0): ?>
                    <div style="background:var(--c-primary-l);border-radius:10px;padding:14px;margin:12px 0">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                            <div>
                                <div style="font-size:.7rem;color:var(--c-muted);text-transform:uppercase;letter-spacing:.5px">Peşin</div>
                                <div style="font-family:var(--font-display);font-weight:800;color:var(--c-primary-d);font-size:1.05rem"><?= tl($nakit) ?></div>
                            </div>
                            <?php if ($kart > 0): ?>
                            <div>
                                <div style="font-size:.7rem;color:var(--c-muted);text-transform:uppercase;letter-spacing:.5px">Kart<?= $taksit ? ' / '.$taksit.' Taksit' : '' ?></div>
                                <div style="font-family:var(--font-display);font-weight:800;color:var(--c-blue-d);font-size:1.05rem"><?= tl($kart) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <a href="<?= SITE_URL ?>/kampanya/<?= e($k['slug']) ?>" class="btn btn-primary btn-block"><i class="fas fa-fire"></i> Kampanya Detayları</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info" style="max-width:680px;margin:0 auto">
            <i class="fas fa-circle-info"></i>
            <div><strong>Şu an aktif kampanya yok.</strong> Yeni kampanyalardan haberdar olmak için bizi takip edebilir veya iletişime geçebilirsiniz.</div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Size özel teklif hazırlayalım</h3>
            <p>Adresinize keşif ekibi gönderelim, ihtiyacınıza özel kampanya teklifi sunalım.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif</a>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
