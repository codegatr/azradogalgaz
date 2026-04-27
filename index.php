<?php
require_once __DIR__ . '/config.php';

$aktif_kampanya_id = (int)ayar('aktif_kampanya_id', 0);
$kampanya = $aktif_kampanya_id
    ? db_get("SELECT * FROM kampanyalar WHERE id=? AND aktif=1", [$aktif_kampanya_id])
    : db_get("SELECT * FROM kampanyalar WHERE aktif=1 ORDER BY id DESC LIMIT 1");

$hizmet_kats = db_all("SELECT * FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira ASC, id ASC LIMIT 8");
$one_cikan_urunler = db_all("SELECT u.*, m.ad AS marka_ad FROM urunler u LEFT JOIN markalar m ON u.marka_id=m.id WHERE u.aktif=1 AND u.one_cikan=1 ORDER BY u.id DESC LIMIT 8");
$markalar = db_all("SELECT * FROM markalar WHERE aktif=1 ORDER BY id ASC LIMIT 12");
$son_blog = db_all("SELECT * FROM blog_yazilari WHERE aktif=1 ORDER BY COALESCE(yayin_tarihi,olusturma_tarihi) DESC LIMIT 3");
try { $son_projeler = db_all("SELECT * FROM projeler WHERE aktif=1 ORDER BY id DESC LIMIT 6"); } catch (Throwable $e) { $son_projeler = []; }

$nakit_fiyat = $kampanya['nakit_fiyat'] ?? 80000;
$kart_fiyat  = $kampanya['kart_fiyat']  ?? 87000;
$taksit      = $kampanya['taksit_sayisi'] ?? 6;

$sayfa_baslik   = 'Azra Doğalgaz — İzmir Demirdöküm Yetkili Doğalgaz, Kombi, Klima Tesisatı';
$sayfa_aciklama = 'İzmir\'de Demirdöküm Ademix kombi, klima, doğalgaz tesisat, yerden ısıtma, sıhhi tesisat ve havalandırma. İZMİRGAZ uyumlu, garantili, 7/24 teknik destek.';
$kanonik_url    = SITE_URL . '/';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'url'      => SITE_URL,
        'name'     => 'Azra Doğalgaz',
    ],
    [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => [
            ['@type' => 'Question', 'name' => 'İzmirgaz yetkili doğalgaz firması nasıl seçilir?',
             'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Firmanın geçerli "Sertifikalı İç Tesisat Firması" belgesi olmalı, projeyi İzmirgaz sistemine yükleyebiliyor olmalı, gaz açma sürecinde yetkili teknisyen ile çalışmalıdır. Azra Doğalgaz İzmirgaz onaylı yetkili firmadır.']],
            ['@type' => 'Question', 'name' => 'Kombi seçerken hangi kapasite m² için uygundur?',
             'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Kabaca her 10 m² için 1.5-2 kW kombi gücü gerekir. 100-120 m² için 24 kW yoğuşmalı kombi, 150-180 m² için 30 kW idealdir.']],
            ['@type' => 'Question', 'name' => 'Doğalgaz projesi kaç günde onaylanır?',
             'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'İzmirgaz proje onayı eksiksiz başvurularda 2-5 iş günü içinde tamamlanır. Toplam süreç ortalama 7-10 gün.']],
        ],
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div>
                <span class="hero-badge"><i class="fas fa-certificate"></i> İZMİRGAZ Yetkili · Demirdöküm Bayisi</span>
                <h1>İzmir'in <strong>güvenilir</strong><br>doğalgaz ve ısıtma çözümleri</h1>
                <p class="lead">
                    Konforlu yaşam, güvenli gelecek. Demirdöküm Ademix kombi, klima, doğalgaz tesisatı,
                    yerden ısıtma, sıhhi tesisat ve havalandırma — tek elden, garantili işçilikle.
                </p>
                <div class="hero-actions">
                    <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif İste</a>
                    <a href="https://wa.me/<?= e(ayar('whatsapp_numara', FIRMA_WHATSAPP)) ?>" target="_blank" class="btn btn-out btn-lg"><i class="fab fa-whatsapp"></i> Anında Fiyat Al</a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat"><strong>2.500+</strong><span>Mutlu Müşteri</span></div>
                    <div class="hero-stat"><strong>10+ Yıl</strong><span>Sektör Tecrübesi</span></div>
                    <div class="hero-stat"><strong>7/24</strong><span>Teknik Destek</span></div>
                </div>
            </div>

            <?php if ($kampanya): ?>
            <div class="hero-card">
                <span class="hero-card-tag">🔥 Süper Kampanya</span>
                <h3><?= e($kampanya['baslik']) ?></h3>
                <p class="sub">Demirdöküm Ademix 24 kW · Tam Yoğuşmalı · A Enerji Sınıfı</p>
                <div class="price-block">
                    <div class="price-card">
                        <div class="label">Peşin / Nakit</div>
                        <div class="num"><?= tl((float)$nakit_fiyat) ?></div>
                        <div class="small">Tek ödeme avantajı</div>
                    </div>
                    <div class="price-card alt">
                        <div class="label">Kredi Kartı</div>
                        <div class="num"><?= tl((float)$kart_fiyat) ?></div>
                        <div class="small"><?= (int)$taksit ?> Taksit Seçeneği</div>
                    </div>
                </div>
                <ul class="included">
                    <li>Demirdöküm Ademix 24 kW Tam Yoğuşmalı Kombi</li>
                    <li>5 Metre Termopan Petek</li>
                    <li>Kombi Dolabı + 50x100 Havlupan</li>
                    <li>Siyah Boru + İZMİRGAZ Onaylı Proje</li>
                    <li>Tesisat İşçiliği Dahil</li>
                </ul>
                <a href="<?= SITE_URL ?>/kampanya/<?= e($kampanya['slug']) ?>" class="btn btn-primary btn-block"><i class="fas fa-fire"></i> Kampanya Detayları</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Neden Azra Doğalgaz?</span>
            <h2>Kalite, güvenlik ve müşteri memnuniyeti</h2>
            <p>İZMİRGAZ yetkili firma olarak tüm tesisat ve montaj işlerinizi yasal mevzuata uygun, garantili ve uzman ekibimizle gerçekleştiriyoruz.</p>
        </div>
        <div class="features">
            <div class="feature"><div class="ico"><i class="fas fa-certificate"></i></div><h4>İZMİRGAZ Yetkili</h4><p>Sertifikalı iç tesisat firması olarak proje onayı, montaj ve gaz açma süreçlerinde resmi prosedürlere tam uyum.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-medal"></i></div><h4>Demirdöküm Yetkili</h4><p>Türkiye'nin lider markası Demirdöküm'ün yetkili satıcısı ve montaj firması olarak orijinal ürün, garantili servis.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-clipboard-check"></i></div><h4>Ücretsiz Keşif</h4><p>Adresinize ücretsiz keşif ekibi gönderiyor, ihtiyacınızı yerinde analiz edip en uygun çözümü sunuyoruz.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-handshake"></i></div><h4>Şeffaf Fiyatlandırma</h4><p>Sürpriz maliyet yok. Önceden net teklif veriyor, sözleşme yapıyor ve verdiğimiz fiyata sadık kalıyoruz.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-shield-halved"></i></div><h4>Garantili İşçilik</h4><p>Tüm tesisat ve montaj işlerimizde 2 yıla kadar işçilik garantisi. Gaz kaçak testi, basınç testi standardımız.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-headset"></i></div><h4>7/24 Teknik Destek</h4><p>Tatil günü, gece, sabah... Acil durumda anında ulaşabilirsiniz. Hızlı müdahale, çözüm odaklı yaklaşım.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-bolt"></i></div><h4>Hızlı Teslim</h4><p>Söz verdiğimiz tarihe sadığız. Standart kombi montajı 1 gün, doğalgaz tesisatı ortalama 7-10 günde tamamlanır.</p></div>
            <div class="feature"><div class="ico"><i class="fas fa-leaf"></i></div><h4>Enerji Verimliliği</h4><p>Yoğuşmalı kombi, A++ inverter klima, ısı pompası önerileri ile %20-30 oranında enerji tasarrufu sağlıyoruz.</p></div>
        </div>
    </div>
</section>

<?php if ($hizmet_kats): ?>
<section class="s" style="background:var(--c-bg-alt)">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Hizmetlerimiz</span>
            <h2>Tek elden tüm tesisat çözümleri</h2>
            <p>Doğalgaz tesisatından klimaya, yerden ısıtmadan yangın tesisatına kadar mekanik tesisatın her alanında uzman hizmet.</p>
        </div>
        <div class="services">
            <?php
            $hizmet_ikon_map = [
                'dogalgaz-tesisati' => ['fa-fire-flame-curved', 'Doğalgaz tesisat projesi, döşeme, gaz açma. İZMİRGAZ onaylı.'],
                'kombi-servisi'     => ['fa-screwdriver-wrench', 'Yoğuşmalı kombi satış, montaj, bakım, arıza. Tüm markalar.'],
                'klima-montaji'     => ['fa-snowflake', 'Inverter split klima, multi-split sistem montaj ve bakım.'],
                'yerden-isitma'     => ['fa-temperature-arrow-up', 'PE-X borulu yerden ısıtma sistem tasarımı ve montajı.'],
                'havalandirma'      => ['fa-wind', 'Mekanik havalandırma, davlumbaz, ısı geri kazanım sistemleri.'],
                'sihhi-tesisat'     => ['fa-faucet', 'Su tesisatı, banyo - WC tesisatı, drenaj, atık su sistemleri.'],
                'tesisat-hizmetleri'=> ['fa-toolbox', 'Genel mekanik tesisat onarım ve revizyon hizmetleri.'],
                'yangin-tesisati'   => ['fa-fire-extinguisher', 'Yangın algılama, sprinkler, hidrant ve duman tahliye sistemleri.'],
                'isi-pompasi'       => ['fa-arrows-rotate', 'Hava kaynaklı ısı pompası satış, montaj ve bakım hizmetleri.'],
            ];
            foreach ($hizmet_kats as $h):
                $ikon = $hizmet_ikon_map[$h['slug']][0] ?? 'fa-tools';
                $aciklama = $hizmet_ikon_map[$h['slug']][1] ?? 'Profesyonel mekanik tesisat hizmetleri.';
            ?>
            <div class="service-card">
                <div class="service-image"><i class="fas <?= e($ikon) ?>"></i></div>
                <div class="service-body">
                    <h3><?= e($h['ad']) ?></h3>
                    <p><?= e($aciklama) ?></p>
                    <a href="<?= SITE_URL ?>/kategori/<?= e($h['slug']) ?>" class="service-link">İncele <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="s">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Akıllı Araçlar</span>
            <h2>Doğru kapasiteyi saniyeler içinde bulun</h2>
            <p>Kombi ve klima seçimi tahminden uzaktır. Aşağıdaki ücretsiz hesaplama araçlarımızla evinize en uygun kapasiteyi öğrenin.</p>
        </div>
        <div class="services" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">
            <div class="service-card">
                <div class="service-image" style="background:var(--c-primary-l)">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="service-body">
                    <h3>Kombi Kapasite Hesaplama</h3>
                    <p>Evinizin metrekaresi, izolasyonu, cephesi ve petek uzunluğuna göre kaç kW kombi alacağınızı TSE-825 mantığıyla hesaplayın.</p>
                    <a href="<?= SITE_URL ?>/kombi-hesaplama" class="btn btn-primary"><i class="fas fa-calculator"></i> Hesapla</a>
                </div>
            </div>
            <div class="service-card">
                <div class="service-image" style="background:var(--c-blue-l)">
                    <i class="fas fa-snowflake" style="background:var(--grad-blue);-webkit-background-clip:text;background-clip:text;color:transparent"></i>
                </div>
                <div class="service-body">
                    <h3>Klima BTU Hesaplama</h3>
                    <p>Oda büyüklüğünüze, bölgenize ve kişi sayısına göre 9.000–24.000 BTU arası uygun klima kapasitesini saniyede bulun.</p>
                    <a href="<?= SITE_URL ?>/klima-hesaplama" class="btn btn-blue"><i class="fas fa-snowflake"></i> Hesapla</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($one_cikan_urunler): ?>
<section class="s" style="background:var(--c-bg-alt)">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Ürünlerimiz</span>
            <h2>Öne çıkan ürünler</h2>
            <p>Demirdöküm, Bosch, Vaillant, Daikin, Mitsubishi gibi öncü markaların kombi, klima ve ısıtma cihazları.</p>
        </div>
        <div class="products">
            <?php foreach ($one_cikan_urunler as $u):
                $fiyat = (float)($u['indirimli_fiyat'] ?? 0) > 0 ? $u['indirimli_fiyat'] : $u['fiyat']; ?>
            <a href="<?= SITE_URL ?>/urun/<?= e($u['slug']) ?>" class="product-card" style="text-decoration:none;color:inherit">
                <div class="product-image">
                    <?php if ($u['gorsel']): ?><img src="<?= e($u['gorsel']) ?>" alt="<?= e($u['ad']) ?>" loading="lazy">
                    <?php else: ?><i class="fas fa-fire-flame-curved" style="font-size:3rem;color:var(--c-primary);opacity:.4"></i><?php endif; ?>
                    <?php if ($u['one_cikan']): ?><span class="badge">Öne Çıkan</span><?php endif; ?>
                </div>
                <div class="product-body">
                    <?php if ($u['marka_ad']): ?><span class="product-brand"><?= e($u['marka_ad']) ?></span><?php endif; ?>
                    <h4><?= e($u['ad']) ?></h4>
                    <?php if ((float)$fiyat > 0): ?><div class="product-price"><?= tl((float)$fiyat) ?></div><?php endif; ?>
                    <span class="btn btn-out btn-sm btn-block">Detaylar <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:36px">
            <a href="<?= SITE_URL ?>/urunler" class="btn btn-primary">Tüm Ürünler <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($markalar): ?>
<section class="brands">
    <div class="container">
        <p style="text-align:center;color:var(--c-muted);font-size:.85rem;text-transform:uppercase;letter-spacing:2px;margin-bottom:30px;font-weight:700">Yetkili Bayisi Olduğumuz Markalar</p>
        <div class="brand-strip">
            <?php foreach ($markalar as $m): ?><div class="b"><?= e($m['ad']) ?></div><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($son_projeler): ?>
<section class="s">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Projelerimiz</span>
            <h2>Tamamladığımız son işler</h2>
            <p>Konut, iş yeri ve ticari projeler. Doğalgaz tesisatından komple HVAC sistemlerine kadar.</p>
        </div>
        <div class="gallery">
            <?php foreach ($son_projeler as $p): ?>
            <div class="gallery-item <?= $p['gorsel'] ? '' : 'placeholder' ?>">
                <?php if ($p['gorsel']): ?><img src="<?= e($p['gorsel']) ?>" alt="<?= e($p['baslik']) ?>" loading="lazy">
                <?php else: ?><i class="fas fa-hammer"></i><?php endif; ?>
                <div class="info"><?= e($p['baslik']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:36px">
            <a href="<?= SITE_URL ?>/projeler" class="btn btn-out">Tüm Projeler <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

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
        <div class="s-head">
            <span class="s-tag">Müşteri Yorumları</span>
            <h2>İzmir genelinde tercih edilen tesisat firması</h2>
        </div>
        <div class="testimonials">
            <div class="testi">
                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>Demirdöküm Ademix kombi montajı için aradığımızda ertesi gün geldiler. Proje onayı 3 gün, gaz açma 5 gün... Hızlı ve titiz bir ekip. İzmir'de tavsiye ederim.</p>
                <div class="testi-author"><div class="testi-avatar">M</div><div><div class="testi-name">Mehmet Yılmaz</div><div class="testi-role">Bornova / İzmir</div></div></div>
            </div>
            <div class="testi">
                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>İki katlı dubleks evimize yerden ısıtma yaptırdık. Keşif geldiklerinde her detayı açıkladılar, malzeme kalitesinden hiç şaşmadılar. Kış konforumuz başka.</p>
                <div class="testi-author"><div class="testi-avatar">A</div><div><div class="testi-name">Ayşe Demir</div><div class="testi-role">Karşıyaka / İzmir</div></div></div>
            </div>
            <div class="testi">
                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>Eski kombim arızalandı, hafta sonu aradım anında geldiler. Yoğuşmalı yeni kombi taktılar, eskisinden çok daha az gaz tüketiyor. Verdiği teklif aynen tutturuldu.</p>
                <div class="testi-author"><div class="testi-avatar">E</div><div><div class="testi-name">Emre Kaya</div><div class="testi-role">Karşıyaka / İzmir</div></div></div>
            </div>
        </div>
    </div>
</section>

<?php if ($son_blog): ?>
<section class="s" style="background:var(--c-bg-alt)">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Blog</span>
            <h2>Doğalgaz, kombi ve klima rehberleri</h2>
            <p>Doğru kararlar vermek için bilgi şart. Sektörel rehberlerimizi okuyun.</p>
        </div>
        <div class="services">
            <?php foreach ($son_blog as $b): ?>
            <article class="service-card">
                <div class="service-image" style="background:var(--c-blue-l)"><i class="fas fa-newspaper" style="background:var(--grad-blue);-webkit-background-clip:text;background-clip:text;color:transparent"></i></div>
                <div class="service-body">
                    <h3><?= e($b['baslik']) ?></h3>
                    <?php if ($b['ozet']): ?><p><?= e(mb_strimwidth($b['ozet'], 0, 140, '…', 'UTF-8')) ?></p><?php endif; ?>
                    <a href="<?= SITE_URL ?>/blog/<?= e($b['slug']) ?>" class="service-link">Devamını Oku <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:36px">
            <a href="<?= SITE_URL ?>/blog" class="btn btn-out">Tüm Yazılar <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="s">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Sık Sorulan Sorular</span>
            <h2>Aklınızdaki sorular</h2>
        </div>
        <div class="faq-list">
            <details class="faq" open>
                <summary>İzmirgaz yetkili doğalgaz firması nasıl seçilir?</summary>
                <div class="faq-body">
                    <p>Bir doğalgaz firmasının ilk yetkinlik kanıtı, İzmirgaz tarafından verilmiş geçerli "Sertifikalı İç Tesisat Firması" belgesidir. Yalnızca yetkili firmaların hazırladığı projeler İzmirgaz sistemine yüklenebilir ve onaylanır. <strong>Azra Doğalgaz İzmirgaz onaylı yetkili firmadır</strong> — yetki numaramızı talep edebilirsiniz.</p>
                </div>
            </details>
            <details class="faq">
                <summary>Kombi seçerken hangi kapasite m² için uygundur?</summary>
                <div class="faq-body">
                    <p>Genel kural: her 10 m² için 1.5–2 kW kombi gücü gerekir. Ancak yalıtım, cephe yönü, kat yüksekliği ve petek uzunluğu da hesaba katılmalıdır.</p>
                    <ul>
                        <li><strong>80–100 m²:</strong> 18–20 kW yoğuşmalı kombi</li>
                        <li><strong>100–120 m²:</strong> 24 kW yoğuşmalı kombi (en yaygın)</li>
                        <li><strong>120–150 m²:</strong> 24–28 kW</li>
                        <li><strong>150–180 m²:</strong> 30 kW</li>
                        <li><strong>180+ m² / dubleks:</strong> 35 kW veya kazan sistemi</li>
                    </ul>
                    <p><a href="<?= SITE_URL ?>/kombi-hesaplama" style="color:var(--c-primary);font-weight:700">→ Online kombi kapasite hesaplama aracımızı kullanın</a></p>
                </div>
            </details>
            <details class="faq">
                <summary>Doğalgaz projesi kaç günde onaylanır? Tüm süreç ne kadar sürer?</summary>
                <div class="faq-body">
                    <p>İzmirgaz onay süreci eksiksiz başvurularda <strong>2–5 iş günüdür</strong>. Tesisat tamamlandıktan sonra gaz açma için randevu alınır, kontrol 2–5 iş günü içinde yapılır. <strong>Toplam süreç ortalama 7–10 iş günü</strong>'dür.</p>
                </div>
            </details>
            <details class="faq">
                <summary>Yoğuşmalı kombi normal kombiden daha mı tasarrufludur?</summary>
                <div class="faq-body">
                    <p>Evet, yoğuşmalı kombiler %20–25 oranında doğalgaz tasarrufu sağlar. Yasa gereği <strong>Türkiye'de satılan tüm kombiler artık yoğuşmalı teknolojiye sahip olmak zorundadır</strong>.</p>
                </div>
            </details>
            <details class="faq">
                <summary>Klima BTU değeri nasıl hesaplanır?</summary>
                <div class="faq-body">
                    <p>Ege bölgesi için: <strong>BTU = oda m² × 425</strong>. 25 m² oda için yaklaşık 10.625 BTU + (kişi sayısı – 1) × 600 BTU eklenir.</p>
                    <ul>
                        <li><strong>9.000 BTU:</strong> 12–18 m² oda</li>
                        <li><strong>12.000 BTU:</strong> 18–22 m² oda</li>
                        <li><strong>18.000 BTU:</strong> 28–35 m² salon</li>
                        <li><strong>24.000 BTU:</strong> 40–50 m² büyük salon</li>
                    </ul>
                </div>
            </details>
        </div>
        <div style="text-align:center;margin-top:36px">
            <a href="<?= SITE_URL ?>/sss" class="btn btn-out">Tüm SSS <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Ücretsiz keşif için bizi arayın</h3>
            <p>Adresinize gelelim, ihtiyacınızı yerinde analiz edelim, en uygun çözümü sunalım. Hiçbir ücret ödemenize gerek yok.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="tel:<?= e(preg_replace('/\s/','',ayar('firma_telefon_1', FIRMA_TEL_1))) ?>" class="btn btn-lg"><i class="fas fa-phone"></i> Hemen Ara</a>
            <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg btn-out" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.4)"><i class="fas fa-clipboard-check"></i> Keşif Formu</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
