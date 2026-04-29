<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Hakkımızda — Azra Doğalgaz İzmir';
$sayfa_aciklama = 'Azra Doğalgaz, İzmir\'de doğalgaz tesisatı, kombi ve klima montajı alanında uzun yıllara dayanan deneyime sahip Demirdöküm yetkili firmadır.';
$kanonik_url    = SITE_URL . '/hakkimizda';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'AboutPage',
        'name'     => 'Hakkımızda — Azra Doğalgaz',
        'url'      => SITE_URL . '/hakkimizda',
        'mainEntity' => [
            '@type'    => 'HVACBusiness',
            'name'     => 'Azra Doğalgaz',
            'url'      => SITE_URL,
            'telephone'=> ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:''),
            'address'  => ['@type'=>'PostalAddress','addressLocality'=>'İzmir','addressCountry'=>'TR'],
        ],
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Hakkımızda</span>
        </div>
        <h1>Hakkımızda</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">İzmir'in güvenilir doğalgaz, kombi ve klima tesisat firması. 10+ yıllık tecrübe, 2.500+ mutlu müşteri.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <h2>Biz Kimiz?</h2>
            <p><strong>Azra Doğalgaz</strong>, İzmir'de doğalgaz tesisatı, kombi montajı ve servisi, klima sistemleri, yerden ısıtma ve mekanik tesisat alanlarında <strong>10+ yıldır</strong> hizmet veren, sektöründe öncü bir firmadır.</p>
            <p><strong>Demirdöküm yetkili bayisi</strong> olarak; konutlardan ticari mekanlara, küçük dairelerden büyük dubleks villalara kadar her ölçekte projede müşterilerimize uçtan uca profesyonel çözüm sunuyoruz.</p>

            <h2>Misyonumuz</h2>
            <p>Müşterilerimize <strong>güvenilir, garantili ve şeffaf</strong> doğalgaz, kombi, klima ve mekanik tesisat hizmetleri sunmak. Her projede yasal mevzuata uygun, kaliteli malzeme ve uzman işçilikle, sürpriz maliyet çıkarmadan, söz verdiğimiz tarihte tamamlamak.</p>

            <h2>Vizyonumuz</h2>
            <p>İzmir genelinde <strong>en güvenilir ve teknolojiyi en iyi takip eden</strong> tesisat firması olmak. Yenilenebilir enerji, ısı pompası, akıllı ev sistemleri gibi alanlarda öncü uygulamalarla müşterilerimizin yaşam konforunu artırmak.</p>
        </div>

        <!-- Değerlerimiz -->
        <div style="margin-top:60px">
            <h2 style="text-align:center;font-family:var(--font-display);font-size:1.8rem;font-weight:800;margin-bottom:36px">Değerlerimiz</h2>
            <div class="features">
                <div class="feature"><div class="ico"><i class="fas fa-shield-halved"></i></div><h4>Güvenilirlik</h4><p>Söz verdiğimize sadık kalır, sözleşmedeki maddeleri eksiksiz uygularız.</p></div>
                <div class="feature"><div class="ico"><i class="fas fa-handshake"></i></div><h4>Şeffaflık</h4><p>Sürpriz maliyet yok. Önceden net teklif sunar, sözleşme sonrası fiyat değiştirmeyiz.</p></div>
                <div class="feature"><div class="ico"><i class="fas fa-medal"></i></div><h4>Kalite</h4><p>Yetkili bayi olduğumuz markaların orijinal ürünleri, garantili işçilik.</p></div>
                <div class="feature"><div class="ico"><i class="fas fa-rocket"></i></div><h4>Hız</h4><p>Standart kombi montajı 1 gün, doğalgaz tesisatı 7-10 günde.</p></div>
                <div class="feature"><div class="ico"><i class="fas fa-headset"></i></div><h4>7/24 Destek</h4><p>Acil durumlarda gece-gündüz, hafta sonu dahil ulaşılabiliriz.</p></div>
                <div class="feature"><div class="ico"><i class="fas fa-leaf"></i></div><h4>Çevre Bilinci</h4><p>Yoğuşmalı kombi, A++ inverter klima, ısı pompası önerileriyle düşük emisyon.</p></div>
            </div>
        </div>
    </div>
</section>

<section class="stats-band">
    <div class="container">
        <div class="stats-grid">
            <div><div class="num">2500+</div><div class="lbl">Mutlu Müşteri</div></div>
            <div><div class="num">10+</div><div class="lbl">Yıl Tecrübe</div></div>
            <div><div class="num">15+</div><div class="lbl">Yetkili Marka</div></div>
            <div><div class="num">7/24</div><div class="lbl">Teknik Destek</div></div>
        </div>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <h2>Yetkilerimiz ve Belgelerimiz</h2>
            <ul>
                
                <li><strong>Demirdöküm Yetkili Bayi</strong> — Türkiye'nin lider markası</li>
                <li><strong>Bosch / Vaillant / ECA / Buderus</strong> — Çoklu marka satış ve montaj yetkisi</li>
                <li><strong>Klima yetkili servisleri</strong> — Daikin, Mitsubishi, LG, Samsung, Vestel</li>
                <li><strong>Yapı denetim sigortası</strong> — Yapılan tüm tesisatlar sigortalı</li>
                <li><strong>İSG (İş Sağlığı ve Güvenliği) sertifikaları</strong> — Tüm teknik personelimiz</li>
            </ul>

            <h2>Hizmet Alanlarımız</h2>
            <p>İzmir merkez ve tüm ilçelerinde hizmet veriyoruz: Bornova, Karşıyaka, Konak, Buca, Çiğli, Gaziemir, Bayraklı, Karabağlar, Balçova, Narlıdere, Güzelbahçe, Urla, Çeşme, Menemen, Aliağa, Foça, Selçuk, Torbalı, Menderes ve daha fazlası.</p>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Tanışmak ister misiniz?</h3>
            <p>Adresinize ücretsiz keşfe gelelim, ihtiyacınızı yerinde analiz edelim, en uygun çözümü sunalım.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
