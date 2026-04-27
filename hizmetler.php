<?php
require_once __DIR__ . '/config.php';

set_meta([
    'baslik'    => 'Hizmetlerimiz — Doğalgaz, Kombi, Klima, Tesisat | ' . SITE_TITLE,
    'aciklama'  => 'İzmir\'de doğalgaz tesisatı, kombi montajı, klima montajı, tesisat hizmetleri ve kombi servisi. İzmirgaz uyumlu projelendirme ve garantili kurulum.',
    'kelimeler' => 'izmir doğalgaz tesisat, kombi montaj izmir, klima montaj izmir, kombi servisi izmir, tesisat firması izmir',
    'canonical' => SITE_URL . '/hizmetler',
]);

$kategoriler = db_all("SELECT * FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira ASC");

$ekstra = schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Hizmetler','item'=>SITE_URL.'/hizmetler'],
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
            <span>Hizmetlerimiz</span>
        </nav>
        <h1>Hizmetlerimiz</h1>
        <p>İzmir'de doğalgaz, kombi, klima ve tesisat alanlarında uçtan uca profesyonel hizmet.</p>
    </div>
</section>

<section class="sec">
    <div class="container">
        <div class="services-grid">
            <?php
            $renkler = ['s-orange','s-blue','s-green','s-orange','s-blue','s-green'];
            $iconlar = [
                'flame'    => 'fa-fire-flame-curved',
                'snowflake'=> 'fa-snowflake',
                'wrench'   => 'fa-wrench',
                'tools'    => 'fa-screwdriver-wrench',
            ];
            foreach ($kategoriler as $i => $k):
                $klas = $renkler[$i % count($renkler)];
                $ic   = $iconlar[$k['ikon']] ?? 'fa-fire';
            ?>
                <a href="<?= SITE_URL ?>/kategori/<?= e($k['slug']) ?>" class="svc-card <?= $klas ?>" style="text-decoration:none">
                    <div class="svc-icon"><i class="fas <?= $ic ?>"></i></div>
                    <h3><?= e($k['ad']) ?></h3>
                    <p>İzmirgaz onaylı standartlarda profesyonel <?= e(mb_strtolower($k['ad'], 'UTF-8')) ?> hizmetleri. Detaylar için tıklayın.</p>
                    <span class="svc-link">Hizmeti İncele <i class="fas fa-arrow-right"></i></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$kategoriler): ?>
            <div class="empty-state">
                <i class="fas fa-tools"></i>
                <p>Şu anda gösterilecek hizmet bulunmamaktadır.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container">
    <div class="cta-band">
        <div>
            <h3>Hangi hizmete ihtiyacınız var?</h3>
            <p>Telefonla detayları konuşalım, ücretsiz keşif planlayalım.</p>
        </div>
        <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn">
            <i class="fas fa-phone-volume"></i> <?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?>
        </a>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
