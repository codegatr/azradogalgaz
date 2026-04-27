<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Sık Sorulan Sorular — Azra Doğalgaz İzmir';
$sayfa_aciklama = 'Doğalgaz tesisatı, kombi, klima, yerden ısıtma ve mekanik tesisat hakkında en sık sorulan soruların yanıtları.';
$kanonik_url    = SITE_URL . '/sss';

// FAQ veri yapısı
$faq_kategoriler = [
    'dogalgaz' => [
        'baslik' => 'Doğalgaz Tesisatı',
        'ikon'   => 'fa-fire-flame-curved',
        'sorular' => [
            [
                'İzmirgaz yetkili doğalgaz firması nasıl seçilir?',
                'Bir doğalgaz firmasının ilk yetkinlik kanıtı, İzmirgaz tarafından verilmiş geçerli "Sertifikalı İç Tesisat Firması" belgesidir. Yalnızca yetkili firmaların projeleri İzmirgaz sistemine yüklenebilir ve onaylanabilir. <strong>Azra Doğalgaz İzmirgaz onaylı yetkili firmadır</strong> — yetki numaramızı talep ederek iletişim sayfasından doğrulayabilirsiniz.',
            ],
            [
                'Doğalgaz projesi kaç günde onaylanır?',
                'İzmirgaz proje onayı eksiksiz başvurularda <strong>2–5 iş günü</strong> içinde tamamlanır. Proje onayı sonrası firmanın onay bedeli ödemesi yapılır, ardından sigorta poliçesi ve sözleşme yüklenir. Eksik proje düzeltilmek üzere geri gönderilir.',
            ],
            [
                'Yeni doğalgaz tesisatı için tüm süreç ne kadar sürer?',
                'Standart bir daire için: <ul><li>Keşif: 1 gün</li><li>Proje çizimi + İzmirgaz onayı: 2–5 iş günü</li><li>Tesisat döşeme: 1–2 gün</li><li>Sızdırmazlık testi + gaz açma randevusu: 2–5 iş günü</li><li>İzmirgaz kontrol + gaz arzı: 1 gün</li></ul>Toplam: <strong>ortalama 7–10 iş günü</strong>.',
            ],
            [
                'Tesisat için evde bulunmak gerekiyor mu?',
                'Tesisat döşeme aşamasında giriş izni vermeniz yeterlidir. <strong>Gaz açma günü</strong> ise sözleşme sahibinin (kendiniz) mutlaka adreste bulunması gereklidir. Tesisat kontrolüne gelinmeden önce ocak, kombi gibi cihazların bağlı olması beklenir.',
            ],
            [
                'Doğalgaz aboneliği için hangi belgeler gerekiyor?',
                'Mülk sahibi için: kimlik fotokopisi, tapu, DASK poliçesi. Kiracı için: kimlik fotokopisi, kira sözleşmesi (DASK kiracıdan istenmez). Güvence bedeli abonelik tipine göre değişir, sözleşme bitiminde fatura borcu düşülerek iade edilir.',
            ],
            [
                'Boş dairenin doğalgazı açılır mı?',
                'Evet. Adreste kullanılacak yakıcı cihazların (kombi, ocak vb.) bağlı olması ve sözleşme sahibinin gaz açım sırasında orada bulunması koşulu ile boş dairenin gazı açılır. Cihaz bağlı değilse <strong>yetkili tesisat firması</strong>\'ndan destek alınmalıdır.',
            ],
        ],
    ],
    'kombi' => [
        'baslik' => 'Kombi Seçimi ve Servisi',
        'ikon'   => 'fa-screwdriver-wrench',
        'sorular' => [
            [
                'Kaç m² eve kaç kW kombi gerekir?',
                'Genel kural: her 10 m² için 1.5–2 kW kombi gücü gerekir. Yaklaşık tablo:<ul><li><strong>80–100 m²:</strong> 18–20 kW</li><li><strong>100–120 m²:</strong> 24 kW (en yaygın)</li><li><strong>120–150 m²:</strong> 24–28 kW</li><li><strong>150–180 m²:</strong> 30 kW</li><li><strong>180+ m² / dubleks:</strong> 35 kW veya kazan sistemi</li></ul>İzolasyon, cephe, kat tipi gibi faktörler hesabı değiştirir. <a href="' . SITE_URL . '/kombi-hesaplama" style="color:var(--c-primary);font-weight:700">Online hesaplama aracımızı</a> kullanın.',
            ],
            [
                'Yoğuşmalı kombi normal kombiden daha mı tasarrufludur?',
                'Evet, yoğuşmalı kombiler <strong>%20–25 oranında doğalgaz tasarrufu</strong> sağlar. Yasa gereği Türkiye\'de satılan tüm kombiler artık yoğuşmalı teknolojiye sahip olmak zorundadır. Tam yoğuşma için tesisatın 55°C–35°C çalışacak şekilde tasarlanması (yeterli petek uzunluğu — en az 8 metre) önemlidir.',
            ],
            [
                'Hangi kombi markası en iyisidir?',
                'Türkiye\'de sevilen markalar: <strong>Demirdöküm</strong> (Türk üretimi, geniş servis ağı), <strong>Bosch / Buderus</strong> (Alman, premium), <strong>Vaillant</strong> (Alman, yüksek modülasyon), <strong>ECA</strong> (Türk, Demirdöküm grubu), <strong>Baymak</strong>, <strong>Ariston</strong>. Bölgenizde yetkili servis ağı, garanti süresi ve yedek parça erişimi en az model kadar önemlidir.',
            ],
            [
                'Kombi bakımı ne sıklıkta yapılmalı?',
                'Yıllık bakım <strong>her 12 ayda bir</strong> yapılmalıdır. Yetkili servis tarafından eşanjör temizliği, brülör kontrolü, baca gazı analizi ve filtre temizliği yapılır. İhmal edilen kombilerde verim %15-20 düşer, arıza ihtimali artar, garantisi geçersiz olabilir.',
            ],
            [
                'Kombim sürekli arıza veriyor, ne yapmalıyım?',
                'Önce hata kodunu not edin (ekrandaki E1, F37 gibi). Sık karşılaşılan sebepler: tesisat suyunda hava, basınç düşüklüğü, baca tıkanıklığı, eşanjör kireçlenmesi. Yetkili servis çağrısı yapın — kendi başınıza müdahale, garantiyi düşürür ve gaz kaçağı riski yaratır.',
            ],
        ],
    ],
    'klima' => [
        'baslik' => 'Klima Seçimi ve Montajı',
        'ikon'   => 'fa-snowflake',
        'sorular' => [
            [
                'Klima BTU değeri nasıl hesaplanır?',
                'Ege bölgesi için temel formül: <strong>BTU = oda m² × 425</strong>. Buna <strong>+ (kişi sayısı – 1) × 600 BTU</strong> eklenir. 500W üzeri aydınlatma varsa her watt için ×3.4 BTU eklenir. Pratik tablo:<ul><li>9.000 BTU: 12–18 m²</li><li>12.000 BTU: 18–22 m²</li><li>18.000 BTU: 28–35 m²</li><li>24.000 BTU: 40–50 m²</li></ul><a href="' . SITE_URL . '/klima-hesaplama" style="color:var(--c-primary);font-weight:700">Online hesaplama aracımızı</a> kullanın.',
            ],
            [
                'Inverter klima ile normal klima arasında ne fark var?',
                '<strong>Normal klima</strong> hedef sıcaklığa ulaşınca tamamen kapanır, sıcaklık tekrar yükselince yeniden tam güçle açılır — bu sürekli "dur-kalk" hem konfor azaltır hem enerji harcar. <strong>Inverter klima</strong> ise hedef sıcaklığa yaklaşınca kompresör hızını düşürerek düşük güçte sürekli çalışır. Sonuç: %30 daha az elektrik tüketimi, sabit konfor, daha sessiz çalışma.',
            ],
            [
                '12.000 BTU klima saatte ne kadar elektrik tüketir?',
                'A++ enerji sınıfı modern bir 12.000 BTU inverter klima soğutmada <strong>saatte ortalama 0.9–1.2 kWh</strong> tüketir. Günde 6 saat çalışma: ~6.6 kWh. Aylık ortalama: ~200 kWh. 2026 birim fiyatı 3 TL/kWh ile aylık ~600 TL. Eski A sınıfı klimalar bu değerin %40-60 fazlasını harcar.',
            ],
            [
                'Klima kaç metrede bir oda soğutur?',
                'Tek klima sadece monte edildiği oda soğutur. Kapı kapalıysa diğer odalara etkisi olmaz, açıksa verim büyük ölçüde düşer. Geniş açık plan ev için multi-split sistem (1 dış ünite + 2-5 iç ünite) ideal çözümdür.',
            ],
            [
                'Klima R32 gazı nedir, R410\'dan farkı?',
                'R32, R410A\'nın yerini alan yeni nesil çevre dostu soğutucu gazdır. <strong>Küresel ısınma potansiyeli (GWP) %68 daha düşük</strong>, soğutma verimi %5 daha yüksek, gaz miktarı %30 daha az. AB ve Türkiye yeni mevzuatları R32 lehine düzenliyor — yeni klima alıyorsanız mutlaka R32 modeli tercih edin.',
            ],
        ],
    ],
    'tesisat' => [
        'baslik' => 'Yerden Isıtma & Diğer Tesisatlar',
        'ikon'   => 'fa-toolbox',
        'sorular' => [
            [
                'Yerden ısıtma vs radyatör — hangisi daha iyi?',
                '<strong>Yerden ısıtma:</strong> homojen sıcaklık dağılımı, radyatör görünmüyor (estetik), %15–25 daha az gaz tüketimi (yoğuşmalı kombi ile birlikte), ayak konforu. Dezavantajı: ilk yatırım yüksek (~%40-60), sıcaklık değişimi yavaş.<br><strong>Radyatör:</strong> hızlı ısınır, ucuz, mevcut binada kolay kurulur. Dezavantajı: oda ısısı dengesiz dağılır, panel görünür.<br>Yeni inşaat veya ciddi tadilat varsa <strong>yerden ısıtma tavsiye edilir</strong>.',
            ],
            [
                'Isı pompası nedir, kombi yerine kullanılabilir mi?',
                'Isı pompası, dış havadaki ısıyı içeri pompalayan elektrikli sistemdir. <strong>Bir birim elektrikle 3-4 birim ısı üretir</strong> (COP 3-4) — dolayısıyla doğalgazdan bile ucuza gelebilir. Hem ısıtma hem soğutma yapar. Dış sıcaklık -10°C\'nin altına düşmeyen bölgelerde (Ege, Akdeniz) <strong>kombi yerine geçebilir</strong>. İlk yatırım yüksek (75-200 bin TL) ama uzun vadede tasarrufludur.',
            ],
            [
                'Sıhhi tesisat değişimi ne kadar sürer?',
                'Standart bir 100 m² dairede komple sıhhi tesisat yenileme: <strong>3–5 iş günü</strong>. Plastik (PPRC) boru sistemi tercih edilir, paslanmaz, uzun ömürlü. Banyo + WC + mutfak tesisatı dahil ortalama 50–80 bin TL malzeme + işçilik. Su sızıntısı testleri mutlaka yapılmalıdır.',
            ],
            [
                'Kombi yerine merkezi sistemden bireysel sisteme geçiş yapabilir miyim?',
                'Evet, <strong>kat kaloriferi</strong>nden bireysel doğalgaz kombiye geçiş İzmirgaz onayı ile mümkündür. Apartman yöneticisi onayı ve ortak gider düzenlemesi gerekir. Süreç: keşif → proje → İzmirgaz onayı → tesisat döşeme → kombi montajı → gaz açma. Ortalama 10-15 iş günü, 80-150 bin TL maliyet (kombi dahil).',
            ],
        ],
    ],
    'genel' => [
        'baslik' => 'Genel Sorular',
        'ikon'   => 'fa-circle-question',
        'sorular' => [
            [
                'Keşif gerçekten ücretsiz mi?',
                'Evet, <strong>kesinlikle ücretsizdir</strong>. Keşif ekibimiz adresinize gelir, ölçüm yapar, fotoğraf çeker, ihtiyaç tespit eder. Yazılı teklif sunarız. Teklifi kabul etmek zorunda değilsiniz, hiçbir yükümlülük doğmaz.',
            ],
            [
                'Verdiğiniz fiyat sonradan değişir mi?',
                'Hayır. Yazılı teklif <strong>15 gün geçerlidir</strong> ve sözleşmede yazılı bedele sadık kalırız. Müşteriden kaynaklanan ek istekler (model değişikliği, ek nokta, ekstra petek) varsa bunlar açıkça fiyatlandırılarak onayınız alınır. Sürpriz maliyet politikamızda yoktur.',
            ],
            [
                'Hangi ödeme yöntemlerini kabul ediyorsunuz?',
                'Nakit, banka havalesi/EFT, kredi kartı (taksit imkanı), ve uygun durumlarda senet. Demirdöküm Ademix kombi paketi gibi ürünlerimizde kart ile <strong>3, 6, 9 taksit</strong> seçenekleri mevcut. Kampanya sayfalarımızda güncel taksit imkanları gösterilir.',
            ],
            [
                'Garanti süreniz nedir?',
                '<strong>İşçilik garantisi</strong>: 2 yıl (sızdırmazlık, montaj kalitesi). <strong>Cihaz garantisi</strong>: üretici firma tarafından (genelde 2 yıl kombi, 5+ yıl eşanjör, 1-3 yıl klima). Yetkili bayi olduğumuz için garanti şartları üreticinin tam koşullarında geçerlidir.',
            ],
            [
                'Acil durumda 7/24 ulaşılabilir misiniz?',
                'Gaz kaçağı şüphesi gibi <strong>acil durumlarda öncelikle 187 İzmirgaz Acil Müdahale</strong> hattını arayın. Ardından bizi de bilgilendirin — hafta sonu/gece dahil teknik ekibimiz ulaşılabilir. WhatsApp\'tan da hızlı dönüş alabilirsiniz.',
            ],
        ],
    ],
];

// Schema FAQ
$faq_items = [];
foreach ($faq_kategoriler as $cat) {
    foreach ($cat['sorular'] as $q) {
        $faq_items[] = [
            '@type' => 'Question',
            'name' => $q[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($q[1])],
        ];
    }
}
$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => $faq_items,
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Sık Sorulan Sorular</span>
        </div>
        <h1>Sık Sorulan Sorular</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Doğalgaz, kombi, klima ve mekanik tesisat hakkında müşterilerimizin en sık sorduğu sorulara verdiğimiz detaylı yanıtlar.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div style="max-width:880px;margin:0 auto">

            <!-- Kategori Navigasyonu -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:36px;justify-content:center">
                <?php foreach ($faq_kategoriler as $key => $cat): ?>
                <a href="#<?= e($key) ?>" class="btn btn-out btn-sm">
                    <i class="fas <?= e($cat['ikon']) ?>"></i> <?= e($cat['baslik']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <?php foreach ($faq_kategoriler as $key => $cat): ?>
            <div id="<?= e($key) ?>" style="margin-bottom:50px;scroll-margin-top:120px">
                <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;margin-bottom:20px;color:var(--c-text);display:flex;align-items:center;gap:12px">
                    <span style="width:42px;height:42px;border-radius:50%;background:var(--c-primary-l);color:var(--c-primary);display:flex;align-items:center;justify-content:center"><i class="fas <?= e($cat['ikon']) ?>"></i></span>
                    <?= e($cat['baslik']) ?>
                </h2>
                <div class="faq-list" style="margin:0">
                    <?php foreach ($cat['sorular'] as $i => $q): ?>
                    <details class="faq" <?= ($key === 'dogalgaz' && $i === 0) ? 'open' : '' ?>>
                        <summary><?= e($q[0]) ?></summary>
                        <div class="faq-body">
                            <p><?= $q[1] ?></p>
                        </div>
                    </details>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Sormak istediğiniz başka bir şey -->
            <div class="card" style="background:var(--c-primary-l);border:1px solid #fed7aa;text-align:center;padding:36px">
                <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:10px">Aradığınız soru burada yok mu?</h3>
                <p style="color:var(--c-text-2);margin-bottom:20px">İletişim formundan veya WhatsApp'tan bize yazın, en kısa sürede yanıtlayalım.</p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
                    <a href="<?= SITE_URL ?>/iletisim" class="btn btn-primary"><i class="fas fa-envelope"></i> İletişim</a>
                    <a href="https://wa.me/<?= e(ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'')) ?>" class="btn btn-green" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
