<?php
require_once __DIR__ . '/config.php';

set_meta([
    'baslik'    => 'Hakkımızda — İzmir\'in Güvenilir Doğalgaz Firması | ' . SITE_TITLE,
    'aciklama'  => 'Azra Doğalgaz, İzmir\'de doğalgaz tesisatı, kombi ve klima montajı alanında uzun yıllara dayanan deneyimi olan bir firmadır. Demirdöküm yetkili bayi.',
    'canonical' => SITE_URL . '/hakkimizda',
]);

$ekstra = schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'BreadcrumbList',
    'itemListElement'=>[
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Hakkımızda','item'=>SITE_URL.'/hakkimizda'],
    ],
]) . schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'AboutPage',
    'name'=>'Hakkımızda — '.SITE_TITLE,
    'mainEntity'=>['@type'=>'HVACBusiness','name'=>SITE_TITLE,'url'=>SITE_URL],
]);
set_meta(['extra_schema' => $ekstra]);

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>Hakkımızda</span>
        </nav>
        <h1>Hakkımızda</h1>
        <p>Konforlu yaşam, güvenli gelecek için yıllardır İzmir'in yanında.</p>
    </div>
</section>

<section class="sec">
    <div class="container about-grid">
        <div>
            <span class="sec-tag">Kim Olduğumuz</span>
            <h2 style="margin:14px 0 22px"><span class="text-grad-orange">Azra Doğalgaz</span> ile Tanışın</h2>
            <p style="color:var(--c-muted);margin-bottom:14px">
                Azra Doğalgaz olarak İzmir'de <strong>doğalgaz tesisatı, kombi montajı, klima sistemleri ve sıhhi tesisat</strong> alanlarında profesyonel hizmet veriyoruz. Deneyimli teknik kadromuz, İzmirgaz uyumlu projelendirme ve garantili işçiliği bir araya getirerek yüzlerce haneye konfor taşıdı.
            </p>
            <p style="color:var(--c-muted);margin-bottom:14px">
                <strong>Demirdöküm</strong> başta olmak üzere Bosch, Vaillant, Baymak, Buderus, Mitsubishi ve Daikin gibi sektörün önde gelen markalarının yetkili tedarikçisiyiz. Sadece ürün satmıyor; projeyi çiziyor, tesisatı kuruyor, kullanıma açıyor ve sonrasında <strong>7/24 teknik destek</strong> sağlıyoruz.
            </p>
            <p style="color:var(--c-muted);margin-bottom:24px">
                Bizim için her ev, sadece bir iş değil <em>uzun ömürlü bir teslimattır</em>. Bu yüzden malzeme seçiminden işçilik kalitesine, satış sonrası servisten müşteri ilişkilerine kadar her aşamada aynı titizliği gösteririz.
            </p>

            <div class="hero-actions">
                <a href="<?= SITE_URL ?>/hizmetler" class="btn btn-primary">
                    <i class="fas fa-tools"></i> Hizmetlerimiz
                </a>
                <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn btn-outline">
                    <i class="fas fa-phone-volume"></i> İletişime Geç
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box"><strong>1500+</strong><span>Tamamlanan Proje</span></div>
            <div class="stat-box"><strong>%100</strong><span>Memnuniyet Oranı</span></div>
            <div class="stat-box"><strong>10+</strong><span>Yıllık Tecrübe</span></div>
            <div class="stat-box"><strong>7/24</strong><span>Teknik Destek</span></div>
        </div>
    </div>
</section>

<section class="sec why-section">
    <div class="container">
        <div class="sec-head">
            <span class="sec-tag">Değerlerimiz</span>
            <h2>Bizi Farklı Kılan <span class="text-grad-orange">İlkelerimiz</span></h2>
        </div>
        <div class="services-grid">
            <div class="svc-card s-orange">
                <div class="svc-icon"><i class="fas fa-handshake"></i></div>
                <h3>Güven</h3>
                <p>Her projede şeffaf fiyatlandırma, yazılı sözleşme ve verdiğimiz sözden taviz vermeyen bir yaklaşım.</p>
            </div>
            <div class="svc-card s-blue">
                <div class="svc-icon"><i class="fas fa-medal"></i></div>
                <h3>Kalite</h3>
                <p>Yetkili bayi olduğumuz markalardan sıfır ürün, sertifikalı işçilik ve kalıcı çözümler.</p>
            </div>
            <div class="svc-card s-green">
                <div class="svc-icon"><i class="fas fa-clock"></i></div>
                <h3>Süreklilik</h3>
                <p>Kurulum sonrası bakım, arıza çözümü ve danışmanlıkla yanınızdayız. Bir projeyi bitirince ilişki bitmez.</p>
            </div>
        </div>
    </div>
</section>

<section class="container">
    <div class="cta-band">
        <div>
            <h3>Tanışmak ister misiniz?</h3>
            <p>Bir telefon veya WhatsApp mesajı kadar yakınız. Ücretsiz keşif talebinizi alalım.</p>
        </div>
        <a href="<?= SITE_URL ?>/iletisim" class="btn">
            <i class="fas fa-paper-plane"></i> İletişime Geç
        </a>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
