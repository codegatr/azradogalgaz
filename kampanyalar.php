<?php
require_once __DIR__ . '/config.php';

$kampanyalar = db_all("SELECT * FROM kampanyalar WHERE aktif=1 ORDER BY id DESC");

set_meta([
    'baslik'    => 'Kampanyalar — Kombi & Klima Paket Fiyatları | ' . SITE_TITLE,
    'aciklama'  => 'İzmir\'de Demirdöküm kombi paketi 80.000 ₺\'den başlayan fiyatlarla. 6 taksit, ücretsiz keşif, İzmirgaz uyumlu kurulum.',
    'kelimeler' => 'izmir kombi kampanya, kombi paket fiyat, demirdöküm kampanya, izmir doğalgaz indirim',
    'canonical' => SITE_URL . '/kampanyalar',
]);

$ekstra = schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Kampanyalar','item'=>SITE_URL.'/kampanyalar'],
    ],
]);
set_meta(['extra_schema' => $ekstra]);

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>Kampanyalar</span>
        </nav>
        <h1>Kampanyalar</h1>
        <p>Kombi, klima ve tesisat paketlerinde fırsat fiyatları. Sınırlı süreli kampanyaları kaçırmayın!</p>
    </div>
</section>

<section class="sec">
    <div class="container">
        <?php if ($kampanyalar): ?>
            <div class="campaign-grid">
                <?php foreach ($kampanyalar as $k): ?>
                    <article class="campaign-card">
                        <div class="thumb">
                            <?php if ($k['gorsel']): ?>
                                <img src="<?= UPLOAD_URL . '/' . e($k['gorsel']) ?>" alt="<?= e($k['baslik']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="thumb-placeholder">
                                    <i class="fas fa-fire-flame-curved"></i>
                                </div>
                            <?php endif; ?>
                            <span class="campaign-tag">🔥 Kampanya</span>
                        </div>
                        <div class="body">
                            <h3><?= e($k['baslik']) ?></h3>
                            <p><?= e(mb_substr((string)$k['kisa_aciklama'], 0, 160)) ?></p>
                            <div class="price-row" style="margin-top:14px">
                                <?php if ((float)$k['nakit_fiyat'] > 0): ?>
                                    <div class="price-box cash">
                                        <small>Nakit</small>
                                        <strong><?= number_format((float)$k['nakit_fiyat'], 0, ',', '.') ?> ₺</strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ((float)$k['kart_fiyat'] > 0): ?>
                                    <div class="price-box card">
                                        <small>Kart</small>
                                        <strong><?= number_format((float)$k['kart_fiyat'], 0, ',', '.') ?> ₺</strong>
                                        <?php if ((int)$k['taksit_sayisi'] > 0): ?>
                                            <span><?= (int)$k['taksit_sayisi'] ?> Taksit</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="<?= SITE_URL ?>/kampanya/<?= e($k['slug']) ?>" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px">
                                <i class="fas fa-arrow-right"></i> Detayları Gör
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <p>Şu anda aktif kampanya bulunmamaktadır. Yakında yeni fırsatlar için takipte kalın!</p>
                <a href="<?= SITE_URL ?>/iletisim" class="btn btn-primary">Bize Ulaşın</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
