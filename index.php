<?php
require_once __DIR__ . '/config.php';

set_meta([
    'baslik'    => 'İzmir Kombi & Doğalgaz Tesisat | Demirdöküm Yetkili — Azra Doğalgaz',
    'aciklama'  => 'İzmir\'de Demirdöküm Ademix kombi, klima ve doğalgaz tesisat hizmetleri. İzmirgaz uyumlu, garantili kurulum, 7/24 teknik destek. 80.000 ₺\'den başlayan paket fiyatlar.',
    'kelimeler' => 'izmir kombi, kombi montaj izmir, demirdöküm ademix izmir, doğalgaz tesisat izmir, klima montaj izmir, izmirgaz uyumlu tesisat, kombi servisi, azra doğalgaz',
    'canonical' => SITE_URL . '/',
    'og_image'  => SITE_URL . '/assets/img/og-default.jpg',
]);

// Aktif kampanya
$aktif_kampanya_id = (int)ayar('aktif_kampanya_id', 0);
$aktif_kampanya = $aktif_kampanya_id
    ? db_get("SELECT * FROM kampanyalar WHERE id=? AND aktif=1", [$aktif_kampanya_id])
    : db_get("SELECT * FROM kampanyalar WHERE aktif=1 ORDER BY id DESC LIMIT 1");

// Hizmet kategorileri
$kategoriler = db_all("SELECT * FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira ASC LIMIT 6");

// Öne çıkan ürünler
$one_cikan = db_all("SELECT u.*, m.ad marka_ad
    FROM urunler u LEFT JOIN markalar m ON m.id=u.marka_id
    WHERE u.aktif=1 AND u.one_cikan=1 ORDER BY u.id DESC LIMIT 6");

// Schema.org Service & FAQPage extra
$ekstra_schema = '';
if ($aktif_kampanya) {
    $ekstra_schema .= schema_org([
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $aktif_kampanya['baslik'],
        'description' => $aktif_kampanya['kisa_aciklama'],
        'brand'    => ['@type' => 'Brand', 'name' => 'Demirdöküm'],
        'offers'   => [
            '@type' => 'Offer',
            'priceCurrency' => 'TRY',
            'price'   => $aktif_kampanya['nakit_fiyat'],
            'availability' => 'https://schema.org/InStock',
            'url'     => SITE_URL . '/kampanya/' . $aktif_kampanya['slug'],
            'seller'  => ['@type' => 'Organization', 'name' => SITE_TITLE],
        ],
    ]);
}
$ekstra_schema .= schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name'  => 'Azra Doğalgaz İzmirgaz uyumlu mu?',
            'acceptedAnswer' => ['@type'=>'Answer','text'=>'Evet, tüm tesisat hizmetlerimiz İzmirgaz onaylı standartlara uygun şekilde yapılır ve gerekli proje çizimi paketimize dahildir.']
        ],
        [
            '@type' => 'Question',
            'name'  => 'Kombi paketi fiyatına neler dahildir?',
            'acceptedAnswer' => ['@type'=>'Answer','text'=>'Demirdöküm Ademix 24 kW tam yoğuşmalı kombi, 5 metre termopan petek, kombi dolabı, 50x100 havlupan, siyah boru ve İzmirgaz uyumlu tesisat dahildir.']
        ],
        [
            '@type' => 'Question',
            'name'  => 'Taksit imkânı var mı?',
            'acceptedAnswer' => ['@type'=>'Answer','text'=>'Evet, kredi kartı ile 6 taksit imkânı sunuyoruz.']
        ],
        [
            '@type' => 'Question',
            'name'  => 'Kurulum sonrası servis veriyor musunuz?',
            'acceptedAnswer' => ['@type'=>'Answer','text'=>'Evet, kurduğumuz tüm sistemler için 7/24 teknik destek ve garantili servis hizmeti sağlıyoruz.']
        ],
    ]
]);
set_meta(['extra_schema' => $ekstra_schema]);

require_once INC_PATH . '/header.php';
?>

<!-- ============= HERO ============= -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-text">
            <div class="hero-badges">
                <span class="hero-badge b-orange"><i class="fas fa-fire"></i> Doğalgaz</span>
                <span class="hero-badge b-blue"><i class="fas fa-snowflake"></i> Klima</span>
                <span class="hero-badge b-green"><i class="fas fa-wrench"></i> Tesisat</span>
            </div>
            <h1>
                Konforlu Yaşam,
                <span class="accent">Güvenli Gelecek</span>
            </h1>
            <p class="lead">
                İzmir'de <strong>Demirdöküm yetkili tesisat firması</strong> olarak doğalgaz, kombi ve klima sistemlerinde uçtan uca hizmet veriyoruz. İzmirgaz uyumlu kurulum, garantili ürün, 7/24 teknik destek.
            </p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>/kampanyalar" class="btn btn-primary">
                    <i class="fas fa-tags"></i> Kampanyaları Gör
                </a>
                <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn btn-outline">
                    <i class="fas fa-phone-volume"></i> Hemen Ara
                </a>
            </div>
            <div class="hero-trust">
                <div class="trust-item"><i class="fas fa-shield-halved"></i> İzmirgaz Uyumlu Tesisat</div>
                <div class="trust-item"><i class="fas fa-medal"></i> Demirdöküm Yetkili</div>
                <div class="trust-item"><i class="fas fa-clock"></i> 7/24 Teknik Destek</div>
            </div>
        </div>

        <?php if ($aktif_kampanya): ?>
        <div class="hero-card">
            <span class="card-tag">🔥 Süper Paket</span>
            <h3><?= e($aktif_kampanya['baslik']) ?></h3>
            <ul class="package-list">
                <li><i class="fas fa-check"></i> Demirdöküm Ademix 24 kW Tam Yoğuşmalı Kombi</li>
                <li><i class="fas fa-check"></i> 5 Metre Termopan Petek</li>
                <li><i class="fas fa-check"></i> Kombi Dolabı</li>
                <li><i class="fas fa-check"></i> 50x100 Havlupan</li>
                <li><i class="fas fa-check"></i> Siyah Boru + Proje Dahil</li>
                <li><i class="fas fa-check"></i> İzmirgaz Uyumlu Tesisat</li>
            </ul>
            <div class="price-row">
                <div class="price-box cash">
                    <small>Nakit Fiyat</small>
                    <strong><?= number_format((float)$aktif_kampanya['nakit_fiyat'], 0, ',', '.') ?> ₺</strong>
                </div>
                <div class="price-box card">
                    <small>Kredi Kartı</small>
                    <strong><?= number_format((float)$aktif_kampanya['kart_fiyat'], 0, ',', '.') ?> ₺</strong>
                    <span><?= (int)$aktif_kampanya['taksit_sayisi'] ?> Taksit İmkânı</span>
                </div>
            </div>
            <a href="<?= SITE_URL ?>/kampanya/<?= e($aktif_kampanya['slug']) ?>" class="btn btn-blue" style="width:100%;justify-content:center">
                <i class="fas fa-arrow-right"></i> Detayları Gör
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============= FEATURE BAR ============= -->
<section class="feature-bar">
    <div class="container feature-bar-grid">
        <div class="fb-item">
            <div class="fb-icon o"><i class="fas fa-shield-halved"></i></div>
            <div class="fb-text"><strong>Güvenli Tesisat</strong><small>Standartlara tam uyum</small></div>
        </div>
        <div class="fb-item">
            <div class="fb-icon b"><i class="fas fa-medal"></i></div>
            <div class="fb-text"><strong>Kaliteli Ürünler</strong><small>Yetkili bayi garantisi</small></div>
        </div>
        <div class="fb-item">
            <div class="fb-icon g"><i class="fas fa-user-gear"></i></div>
            <div class="fb-text"><strong>Profesyonel Hizmet</strong><small>Uzman teknik kadro</small></div>
        </div>
        <div class="fb-item">
            <div class="fb-icon y"><i class="fas fa-handshake"></i></div>
            <div class="fb-text"><strong>%100 Memnuniyet</strong><small>Müşteri odaklı yaklaşım</small></div>
        </div>
    </div>
</section>

<!-- ============= HİZMETLER ============= -->
<section class="sec">
    <div class="container">
        <div class="sec-head">
            <span class="sec-tag">Hizmetlerimiz</span>
            <h2>İzmir'in Güvenilir <span class="text-grad-orange">Doğalgaz</span> Çözüm Ortağı</h2>
            <p>Doğalgaz tesisatından kombi montajına, klima sistemlerinden bakım servisine kadar yaşam alanınızı konforla buluşturan tüm hizmetler tek çatı altında.</p>
        </div>

        <div class="services-grid">
            <div class="svc-card s-orange">
                <div class="svc-icon"><i class="fas fa-fire-flame-curved"></i></div>
                <h3>Doğalgaz Tesisatı</h3>
                <p>İzmirgaz uyumlu doğalgaz iç tesisat projelendirme, montaj ve devreye alma hizmetleri. Yetkili teknik kadro ile güvenli kurulum.</p>
                <a href="<?= SITE_URL ?>/kategori/dogalgaz-tesisati" class="svc-link">Detaylı Bilgi <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="svc-card s-blue">
                <div class="svc-icon"><i class="fas fa-snowflake"></i></div>
                <h3>Klima Montajı</h3>
                <p>Demirdöküm, Mitsubishi, Daikin marka split, salon ve VRF klima sistemlerinin profesyonel montajı, bakımı ve tamiri.</p>
                <a href="<?= SITE_URL ?>/kategori/klima-montaji" class="svc-link">Detaylı Bilgi <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="svc-card s-green">
                <div class="svc-icon"><i class="fas fa-wrench"></i></div>
                <h3>Tesisat Hizmetleri</h3>
                <p>Sıhhi tesisat, kalorifer, petek montajı, termopan radyatör, havlupan ve genel tesisat işlemleriyle eviniz size emanet.</p>
                <a href="<?= SITE_URL ?>/kategori/tesisat-hizmetleri" class="svc-link">Detaylı Bilgi <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- ============= NEDEN BİZ ============= -->
<section class="sec why-section">
    <div class="container why-grid">
        <div>
            <span class="sec-tag">Neden Azra Doğalgaz?</span>
            <h2 style="margin:14px 0 18px">Eviniz Bizimle <span class="text-grad-orange">Güvende</span></h2>
            <p style="color:var(--c-muted);margin-bottom:30px">Yıllarca süren konforun anahtarı doğru tesisattan geçer. Azra Doğalgaz, İzmir'de yüzlerce haneye kazandırdığı güveni size de sunar.</p>

            <div class="why-list">
                <div class="why-item">
                    <i class="fas fa-certificate"></i>
                    <div>
                        <strong>İzmirgaz Onaylı Standartlar</strong>
                        <p>Tüm projelerimiz İzmirgaz teknik şartnamesine uygun, eksiksiz çizilir ve onaylatılır.</p>
                    </div>
                </div>
                <div class="why-item">
                    <i class="fas fa-truck-fast"></i>
                    <div>
                        <strong>Hızlı Keşif & Montaj</strong>
                        <p>Aynı gün ücretsiz keşif, anlaşma sonrasında planlanan günde noktasında çalışma.</p>
                    </div>
                </div>
                <div class="why-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <strong>7/24 Teknik Destek</strong>
                        <p>Kurulum sonrasında da yanınızdayız. Acil durumlarda saat fark etmeksizin ulaşın.</p>
                    </div>
                </div>
                <div class="why-item">
                    <i class="fas fa-credit-card"></i>
                    <div>
                        <strong>Esnek Ödeme Seçenekleri</strong>
                        <p>Nakit avantajı veya 6 taksit kredi kartı ile bütçenize uygun çözümler.</p>
                    </div>
                </div>
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

<!-- ============= ÖNE ÇIKAN ÜRÜNLER ============= -->
<?php if ($one_cikan): ?>
<section class="sec">
    <div class="container">
        <div class="sec-head">
            <span class="sec-tag">Öne Çıkan Ürünler</span>
            <h2>Markalı Ürünler, <span class="text-grad-orange">Garantili Servis</span></h2>
        </div>
        <div class="cards-grid">
            <?php foreach ($one_cikan as $u): ?>
                <article class="product-card">
                    <div class="thumb">
                        <?php if ($u['gorsel']): ?>
                            <img src="<?= UPLOAD_URL . '/' . e($u['gorsel']) ?>" alt="<?= e($u['ad']) ?>" loading="lazy">
                        <?php else: ?>
                            <i class="fas fa-fire-flame-curved"></i>
                        <?php endif; ?>
                    </div>
                    <div class="body">
                        <span class="brand"><?= e($u['marka_ad'] ?? 'Marka') ?></span>
                        <h4><?= e($u['ad']) ?></h4>
                        <p class="desc"><?= e(mb_substr((string)$u['kisa_aciklama'], 0, 120)) ?></p>
                        <?php if ((float)$u['fiyat'] > 0): ?>
                            <div class="price"><?= number_format((float)($u['indirimli_fiyat'] ?: $u['fiyat']), 0, ',', '.') ?> ₺</div>
                        <?php endif; ?>
                        <a href="<?= SITE_URL ?>/urun/<?= e($u['slug']) ?>" class="btn btn-primary">İncele <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============= CTA BANT ============= -->
<section class="container">
    <div class="cta-band">
        <div>
            <h3>Ücretsiz keşif ve fiyat teklifi alın</h3>
            <p>Tek bir telefonla evinize geliyor, ihtiyacınızı yerinde inceleyip en uygun çözümü sunuyoruz.</p>
        </div>
        <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn">
            <i class="fas fa-phone-volume"></i> <?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?>
        </a>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
