<?php
/**
 * Sektörel İçerik Seed Aracı — v1.4.4
 *
 * Yunus'un talimatı: "siteye ayrıca içerikleri araştırarak eksik bir bilgi olmamasını istemiştim"
 *
 * Bu araç sektörel araştırma sonucu hazırlanmış GERÇEK ve GÜNCEL içerikleri
 * (markalar, ürün kategorileri, hizmetler, ürünler, kampanyalar, blog yazıları, projeler)
 * mevcut DB'ye ekler. Var olan kayıtları KORUR, sadece eksik olanları ekler (UPSERT mantığı).
 *
 * Kullanım: /seed.php (admin oturumu zorunlu)
 */
require_once __DIR__ . '/config.php';

if (!admin_giris_var()) {
    header('Location: ' . SITE_URL . '/admin/?bilgi=' . urlencode('Önce admin girişi yapın') . '&donus=' . urlencode('/seed.php'));
    exit;
}

$kilit = __DIR__ . '/.seed-1.4.4.lock';
$mesajlar = [];
$hatalar  = [];
$sayilar  = ['markalar'=>0,'urun_kat'=>0,'hizmetler'=>0,'urunler'=>0,'kampanyalar'=>0,'blog'=>0,'projeler'=>0,'ayarlar'=>0];

if (!empty($_POST['calistir'])) {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $hatalar[] = '❌ CSRF doğrulama hatası, sayfayı yenileyip tekrar deneyin.';
    } else {

    // =========================================================================
    // 1) MARKALAR
    // =========================================================================
    $markalar = [
        ['Demirdöküm','demirdokum'],
        ['Bosch','bosch'],
        ['Vaillant','vaillant'],
        ['Buderus','buderus'],
        ['ECA','eca'],
        ['Baymak','baymak'],
        ['Ariston','ariston'],
        ['Daikin','daikin'],
        ['Mitsubishi Electric','mitsubishi-electric'],
        ['Mitsubishi Heavy','mitsubishi-heavy'],
        ['LG','lg'],
        ['Samsung','samsung'],
        ['Vestel','vestel'],
        ['Arçelik','arcelik'],
        ['Beko','beko'],
    ];
    foreach ($markalar as [$ad, $slug]) {
        try {
            $exists = db_get("SELECT id FROM markalar WHERE slug=?", [$slug]);
            if (!$exists) {
                db_run("INSERT INTO markalar (ad, slug, aktif) VALUES (?, ?, 1)", [$ad, $slug]);
                $sayilar['markalar']++;
            }
        } catch (Throwable $e) { $hatalar[] = "Marka $ad: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['markalar']}</strong> yeni marka eklendi (toplam ".count($markalar).' kontrol edildi).';

    // Marka ID'lerini al
    $marka_id = [];
    foreach (db_all("SELECT id, slug FROM markalar") as $m) $marka_id[$m['slug']] = (int)$m['id'];

    // =========================================================================
    // 2) ÜRÜN KATEGORİLERİ
    // =========================================================================
    $urun_kategorileri = [
        ['Yoğuşmalı Kombi','yogusmali-kombi',10],
        ['Hermetik Kombi','hermetik-kombi',20],
        ['Inverter Klima','inverter-klima',30],
        ['Salon Tipi Klima','salon-tipi-klima',40],
        ['Multi Split Klima','multi-split-klima',50],
        ['Isı Pompası','isi-pompasi-urun',60],
        ['Yerden Isıtma Sistemi','yerden-isitma-sistemi',70],
        ['Radyatör','radyator',80],
        ['Termosifon & Şofben','termosifon-sofben',90],
        ['Tesisat Malzemesi','tesisat-malzemesi',100],
    ];
    foreach ($urun_kategorileri as [$ad, $slug, $sira]) {
        try {
            $exists = db_get("SELECT id FROM urun_kategorileri WHERE slug=?", [$slug]);
            if (!$exists) {
                db_run("INSERT INTO urun_kategorileri (ad, slug, sira, aktif) VALUES (?, ?, ?, 1)", [$ad, $slug, $sira]);
                $sayilar['urun_kat']++;
            }
        } catch (Throwable $e) { $hatalar[] = "Ürün kategorisi $ad: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['urun_kat']}</strong> yeni ürün kategorisi eklendi.";

    $kat_id = [];
    foreach (db_all("SELECT id, slug FROM urun_kategorileri") as $k) $kat_id[$k['slug']] = (int)$k['id'];

    // =========================================================================
    // 3) HİZMETLER (kategori_id ile bağlı)
    // =========================================================================
    $hizmet_kat_id = [];
    foreach (db_all("SELECT id, slug FROM hizmet_kategorileri") as $k) $hizmet_kat_id[$k['slug']] = (int)$k['id'];

    $hizmetler = [
        // Doğalgaz Tesisatı altında
        ['dogalgaz-tesisati','İzmirgaz Onaylı Doğalgaz Tesisatı','izmirgaz-onayli-dogalgaz-tesisati',
            'İzmirgaz sertifikalı uzman ekiple, projelendirme + tesisat döşeme + gaz açma uçtan uca hizmet.',
            '<h2>İzmirgaz Onaylı Doğalgaz Tesisatı Süreci</h2>
            <p><strong>Azra Doğalgaz</strong>, İzmirgaz tarafından sertifikalı yetkili iç tesisat firmasıdır. Yeni doğalgaz aboneliği, kombi dönüşümü veya mevcut tesisat yenileme işlemlerini İzmirgaz mevzuatına %100 uygun şekilde gerçekleştiririz.</p>
            <h3>Süreç Adımları</h3>
            <ol>
                <li><strong>Ücretsiz keşif:</strong> Adresinize uzman ekip gönderir, sayaç yeri, kombi yeri, baca durumu, petek sayısı/uzunluğu ölçülür</li>
                <li><strong>Yazılı teklif:</strong> Detaylı malzeme + işçilik dökümü, sürpriz maliyet yok</li>
                <li><strong>Proje çizimi:</strong> Yetkili makine mühendisi tarafından proje çizilir (1-2 iş günü)</li>
                <li><strong>İzmirgaz onayı:</strong> Online sisteme yüklenir, 2-5 iş günü içinde onaylanır</li>
                <li><strong>Tesisat döşeme:</strong> Standart bir daire için 1-2 iş günü</li>
                <li><strong>Sızdırmazlık testi:</strong> Basınç testi ile tüm bağlantılar kontrol edilir</li>
                <li><strong>Gaz açma randevusu:</strong> Online sistem üzerinden alınır, 2-5 iş günü</li>
                <li><strong>İzmirgaz kontrol + gaz arzı:</strong> Doğal Gaz Uygunluk Belgesi verilir</li>
            </ol>
            <p><strong>Toplam süre:</strong> Standart daire için 7-10 iş günü.</p>
            <h3>Kullanılan Malzemeler</h3>
            <ul>
                <li>TS EN 15266 standartlarına uygun çelik veya gasfil paslanmaz boru</li>
                <li>İzmirgaz onaylı küresel vana, sayaç odası fitingleri</li>
                <li>Gaz dedektörü (talep halinde, mutfak için tavsiye edilir)</li>
                <li>2 yıl işçilik garantisi, sigortalı uygulama</li>
            </ul>',
            10],

        ['dogalgaz-tesisati','Mevcut Tesisatı Doğalgaza Dönüştürme','mevcut-tesisati-dogalgaza-donusturme',
            'Soba, kalorifer, LPG sisteminden doğalgaza geçiş. Mevcut tesisat değişimi ve dönüşüm projesi.',
            '<h2>Doğalgaz Dönüşümü</h2>
            <p>Mevcut sistemleriniz (kömür sobası, kat kaloriferi, LPG\'li tüplü sistem, fuel-oil kazan) doğalgaza dönüştürülebilir. Bu işlem hem maliyetli ısınma sistemlerinden kurtulmanızı hem de yıllık %30-50 yakıt tasarrufu sağlamanızı mümkün kılar.</p>
            <h3>Dönüşüm Avantajları</h3>
            <ul>
                <li>Yıllık ortalama %35 yakıt tasarrufu</li>
                <li>Daha temiz, kül-duman olmayan ısınma</li>
                <li>Kombiyle istediğiniz odayı bireysel ısıtma imkanı</li>
                <li>24 saat sıcak su konforu</li>
                <li>Otomatik termostat kontrolü ile akıllı ısıtma</li>
            </ul>
            <h3>Dönüşüm Maliyeti (2026)</h3>
            <p>100 m² standart daire için tipik maliyetler:</p>
            <ul>
                <li>Doğalgaz tesisatı (proje + döşeme + gaz açma): 25.000 - 45.000 ₺</li>
                <li>Yoğuşmalı kombi (Demirdöküm Ademix 24kW): 27.000 - 35.000 ₺</li>
                <li>Petek sistemi (mevcut radyatörler kullanılırsa daha az)</li>
                <li>Toplam dönüşüm: 70.000 - 120.000 ₺</li>
            </ul>',
            20],

        ['dogalgaz-tesisati','Apartman Doğalgaz Tesisat Yenileme','apartman-dogalgaz-tesisat-yenileme',
            'Çok daireli binalarda toplu doğalgaz yenileme. Kolon hatları, dış ünite, her dairenin bağımsız kombilenmesi.',
            '<h2>Apartman Doğalgaz Yenileme</h2>
            <p>Apartmanınızda kat kaloriferi (merkezi sistem) varsa veya mevcut tesisat eskiyse, tüm bina için toplu doğalgaz yenileme projesi hazırlıyoruz. Her dairenin bireysel kombi sistemine geçişi de mümkündür.</p>
            <h3>Apartman Yöneticisi İçin</h3>
            <ul>
                <li>Karar defteri için karar metni hazırlama desteği</li>
                <li>Tüm daireler için tek merkezli koordinasyon</li>
                <li>Toplu fiyat avantajı</li>
                <li>Hatalı eski tesisatın güvenli sökülmesi</li>
                <li>Ortak gider düzenlemesi (merkezi sistemden bireysele geçişte)</li>
            </ul>
            <h3>Süreç</h3>
            <p>Apartman ölçeğinde tipik süreç 15-25 iş günü arasındadır. Önce yönetim ile sözleşme, sonra her daire için ayrı proje, ardından İzmirgaz onayı, kolon hatlarının döşenmesi, tek tek dairelerin tesisatı, son olarak tüm sayaç ve kombiyle birlikte gaz açma.</p>',
            30],

        // Kombi Servisi altında
        ['kombi-servisi','Demirdöküm Yetkili Kombi Montajı','demirdokum-yetkili-kombi-montaji',
            'Demirdöküm Ademix, Atron, Atromix, Vintomix, Nitromix yetkili bayi montajı. 2 yıl işçilik + üretici garantisi.',
            '<h2>Demirdöküm Kombi Montajı</h2>
            <p>Azra Doğalgaz, Demirdöküm yetkili bayisidir. Yetkili bayi olarak yapılan montaj, üretici garantisinin geçerliliği için zorunludur. Yetkisiz firma montajı durumunda Demirdöküm garantisini iptal edebilir.</p>
            <h3>Montaj Hizmetimiz Şunları Kapsar</h3>
            <ul>
                <li>Eski kombi sökümü ve nakliye</li>
                <li>Kombi konumu seçim danışmanlığı</li>
                <li>Doğalgaz, su giriş-çıkış, baca bağlantıları</li>
                <li>İlk devreye alma ve test</li>
                <li>Kullanım eğitimi</li>
                <li>2 yıl işçilik garantisi</li>
                <li>Yetkili servis garanti belgesi tanzimi</li>
            </ul>
            <h3>Demirdöküm Kombi Modelleri</h3>
            <p>Stoğumuzda yer alan ve hızlı temin edebildiğimiz Demirdöküm modeller:</p>
            <ul>
                <li><strong>Ademix P 18/24 kW</strong> — 80-100 m² için ideal, ~30.000 ₺</li>
                <li><strong>Ademix P 24/24 kW</strong> — Sıcak su konforlu 100 m², ~31.500 ₺</li>
                <li><strong>Ademix P 24/28 kW</strong> — En yaygın model, 100-130 m², ~32.000 ₺</li>
                <li><strong>Atron Condense P24</strong> — Ekonomik tam yoğuşmalı, ~27.000 ₺</li>
                <li><strong>Atromix P24/P28</strong> — Orta segment, ~31.000-32.000 ₺</li>
                <li><strong>Nitromix P24/P28/P35</strong> — Premium (Vaillant teknolojisi), 38.000-46.000 ₺</li>
                <li><strong>Vintomix 18/24, 24/28</strong> — Kompakt seçenek, ~29.000-33.000 ₺</li>
            </ul>
            <p><em>Fiyatlar 2026 Q1 piyasa ortalamasıdır, montaj ve nakliye hariç tutulmuştur.</em></p>',
            10],

        ['kombi-servisi','Yıllık Kombi Bakımı','yillik-kombi-bakimi',
            'Yetkili servis kalitesinde kombi bakım. Eşanjör temizliği, brülör kontrolü, baca gazı analizi.',
            '<h2>Yıllık Kombi Bakımı</h2>
            <p>Yıllık bakım kombinizin verimini, ömrünü ve güvenliğini doğrudan etkiler. İhmal edilen kombilerde verim %15-20 düşer, arıza ihtimali artar, üretici garantisi geçersiz olabilir.</p>
            <h3>Bakım Kapsamı (Yetkili Servis Standardı)</h3>
            <ul>
                <li>Brülör temizliği ve ayarı</li>
                <li>Eşanjör temizliği (kireç ve kurum giderme)</li>
                <li>Baca gazı analizi (CO, CO₂, O₂ ölçümü)</li>
                <li>Hava-gaz karışım kontrolü</li>
                <li>Genleşme tankı basınç kontrolü</li>
                <li>Filtre temizliği (sıhhi tesisat ve gaz hattı)</li>
                <li>Su tesisatı sızdırmazlık kontrolü</li>
                <li>Pompa testi</li>
                <li>Emniyet ventili ve termostat testi</li>
                <li>Yazılı bakım raporu ve sticker</li>
            </ul>
            <h3>Bakım Maliyeti</h3>
            <p>Standart kombi bakımı ortalama 1.200-1.800 ₺ aralığındadır. Yetkili servis garanti belgesinin geçerliliği için her yıl bakım yapılması zorunludur.</p>',
            20],

        ['kombi-servisi','Kombi Arıza Servisi','kombi-ariza-servisi',
            'Kombi çalışmıyor, hata kodu veriyor, sıcak su gelmiyor — uzman teknisyen aynı gün arıza müdahalesi.',
            '<h2>Kombi Arıza Servisi</h2>
            <p>Kombi arızalarında zaman kritik — özellikle kış aylarında. Aynı gün servis politikamızla, çağrı saatinden itibaren ortalama 2-4 saat içinde teknisyenimiz adresinizde olur.</p>
            <h3>Sık Karşılaşılan Arızalar ve Çözümleri</h3>
            <ul>
                <li><strong>E1, E2, E3 hata kodu (basınç düşüklüğü):</strong> Tesisat su basıncını 1-2 bara çıkarın</li>
                <li><strong>F37 hata kodu:</strong> Kombi suyu, doldurma musluğundan ekleme yapın</li>
                <li><strong>Sıcak su gelmiyor:</strong> Plakalı eşanjör tıkanmış olabilir, yetkili servis çağırın</li>
                <li><strong>Kombi sürekli açıp kapanıyor:</strong> Modülasyon arızası veya gaz kaçağı şüphesi</li>
                <li><strong>Petek soğuk:</strong> Hava kalmış olabilir, peteklerin havasını alın</li>
                <li><strong>Anormal ses:</strong> Pompa veya kireç sorunudur, yetkili müdahale gerekli</li>
            </ul>
            <p><strong>UYARI:</strong> Garaza dokunmayın, gaz kokusunda <strong>187 İzmirgaz Acil</strong>\'ı arayın.</p>',
            30],

        // Klima Montajı altında
        ['klima-montaji','Inverter Klima Satış ve Montajı','inverter-klima-satis-ve-montaji',
            'Daikin, Mitsubishi Electric, Bosch, LG, Samsung, Vestel, Arçelik inverter klima satış + profesyonel montaj.',
            '<h2>Inverter Klima Montajı</h2>
            <p>Çoklu marka klima satışı yapıyoruz, montaj ve garanti dahil. <strong>Profesyonel teknisyen montajı</strong>, üretici garantisinin geçerli olması için zorunludur.</p>
            <h3>Montaj Hizmetimizin Kapsamı</h3>
            <ul>
                <li>Adres keşfi (klima konumu, kablo güzergahı, dış ünite yeri)</li>
                <li>İç ve dış ünite duvar montajı (delme, cıvata, kablo)</li>
                <li>Bakır boru tesisatı (max 5 m standart, üzeri ek ücret)</li>
                <li>Drenaj tesisatı</li>
                <li>Vakum çekme + gaz dolumu (R32 / R410A)</li>
                <li>Test ve devreye alma</li>
                <li>2 yıl işçilik garantisi</li>
                <li>Üretici garantisi (üretici firma şartları geçerli)</li>
            </ul>
            <h3>Önerdiğimiz Modeller (2026)</h3>
            <ul>
                <li><strong>Daikin Sensira ATXF35F 12000 BTU</strong> — A++ inverter, R32, ~33.500 ₺</li>
                <li><strong>Mitsubishi Electric SRK35ZSP 12000 BTU</strong> — Premium Japon teknolojisi, ~43.700 ₺</li>
                <li><strong>Bosch Climate 5000 12000 BTU</strong> — Avrupa standardı, ~35.000 ₺</li>
                <li><strong>LG Dualcool Plus 18000 BTU</strong> — Geniş salon için, ~40.000 ₺</li>
                <li><strong>Samsung WindFree 18000 BTU</strong> — Direkt üfleme yok, ~38.000 ₺</li>
                <li><strong>Vestel inverter 12000 BTU</strong> — Ekonomik yerli üretim, ~22.000 ₺</li>
                <li><strong>ECA Spylos 9000 BTU</strong> — Demirdöküm grubu, ~21.000 ₺</li>
            </ul>',
            10],

        ['klima-montaji','Multi Split Klima Sistemi','multi-split-klima-sistemi',
            'Tek dış ünite + 2-5 iç ünite. Daire, villa, ofisler için sessiz ve estetik çözüm.',
            '<h2>Multi Split Klima Sistemi</h2>
            <p>Birden fazla odanız varsa multi split sistem hem ekonomik hem estetik bir çözümdür. Tek bir dış ünite, balkonda veya çatıda tek nokta görüntü kirliliği yaratırken, iç ünite sayısı ihtiyaca göre 2-5 arası olabilir.</p>
            <h3>Avantajları</h3>
            <ul>
                <li>Tek dış ünite — daha az gürültü, daha az balkon işgali</li>
                <li>Her oda bağımsız sıcaklık ayarı</li>
                <li>%15-20 elektrik tasarrufu (tek tek klimalara göre)</li>
                <li>Daha estetik, az kablolama</li>
                <li>İlk yatırım yüksek ama uzun vadede karlı</li>
            </ul>
            <h3>Tipik Sistem Maliyeti</h3>
            <ul>
                <li>2 iç + 1 dış (3 odalı daire): 60.000 - 90.000 ₺</li>
                <li>3 iç + 1 dış (4-5 odalı): 90.000 - 140.000 ₺</li>
                <li>5 iç + 1 dış (villa, ofis): 150.000 - 250.000 ₺</li>
            </ul>
            <p>Markalar: Daikin, Mitsubishi Electric, LG, Samsung, Bosch.</p>',
            20],

        // Yerden Isıtma altında
        ['yerden-isitma','Yerden Isıtma Tesisatı','yerden-isitma-tesisati',
            'Yeni yapı veya tadilat — şap altı yerden ısıtma sistemi tasarımı ve montajı.',
            '<h2>Yerden Isıtma Sistemi</h2>
            <p>Yerden ısıtma, ısı kaynağının zemine yerleştirildiği modern ısıtma sistemidir. Homojen sıcaklık dağılımı, ayak konforu, estetik ve %15-25 daha az enerji tüketimi sağlar.</p>
            <h3>Sistem Bileşenleri</h3>
            <ul>
                <li>PEX boru (16 mm × 2 mm, oksijen bariyerli, 50 yıl ömür)</li>
                <li>EPS şap altı izolasyon plakası (görsel ve termik)</li>
                <li>Folyo kaplı yansıtıcı tabaka</li>
                <li>Kollektör (her oda için ayrı vanalı)</li>
                <li>Oda termostatı (kablolu veya kablosuz)</li>
                <li>Tam yoğuşmalı kombi veya ısı pompası</li>
            </ul>
            <h3>m² Başına Yatırım Maliyeti (2026)</h3>
            <ul>
                <li>Sadece yerden ısıtma sistemi: 600-900 ₺/m²</li>
                <li>Şap dahil paket: 850-1.200 ₺/m²</li>
                <li>Kombi + tesisat dahil komple: 1.500-2.200 ₺/m²</li>
            </ul>
            <p>100 m² bir daire için tipik komple yatırım: 150.000-220.000 ₺. Yıllık tasarruf 8.000-15.000 ₺ aralığındadır, geri dönüş süresi 8-12 yıl.</p>',
            10],

        // Isı Pompası altında
        ['isi-pompasi','Hava Kaynaklı Isı Pompası','hava-kaynakli-isi-pompasi',
            'Bosch, Daikin Altherma, ECA, Mitsubishi Ecodan ısı pompası satış ve kurulum.',
            '<h2>Hava Kaynaklı Isı Pompası</h2>
            <p>Isı pompası, dış havadan ısı çekerek içeri pompalayan sistemdir. <strong>1 kWh elektrikle 3-4 kWh ısı üretir</strong> (COP 3-4). Kombiden bile ucuza gelebilir, hem ısıtma hem soğutma yapar.</p>
            <h3>Kimler İçin Uygun?</h3>
            <ul>
                <li>Doğalgaz altyapısı olmayan bölgeler (yazlık, kırsal)</li>
                <li>Ege/Akdeniz bölgesi (dış sıcaklık -10°C altına düşmüyor)</li>
                <li>Yenilenebilir enerji odaklı yapılar (güneş paneli ile sıfır enerji ev)</li>
                <li>Hem ısıtma hem soğutma isteyenler</li>
                <li>Yerden ısıtma sistemi olan veya yapacak olanlar</li>
            </ul>
            <h3>Sistem Tipleri</h3>
            <ul>
                <li><strong>Hava-Hava (Split klima türü):</strong> En basit, klima gibi çalışır</li>
                <li><strong>Hava-Su Monoblok:</strong> Yerden ısıtma + sıcak su, kombi yerine</li>
                <li><strong>Hava-Su Split:</strong> İç ve dış ünite ayrı, daha sessiz</li>
            </ul>
            <h3>Önerilen Modeller (2026)</h3>
            <ul>
                <li><strong>Bosch CS3400i AWS Split</strong> — 8/10/14 kW R32 monofaze, ~150-180.000 ₺</li>
                <li><strong>ECA Monoblok Isı Pompası</strong> — 8/11/16 kW kontrol paneli dahil, ~110-160.000 ₺</li>
                <li><strong>Daikin Altherma 3</strong> — Premium R32 split, ~180-250.000 ₺</li>
                <li><strong>Mitsubishi Ecodan</strong> — Yüksek verim, sessiz, ~190-240.000 ₺</li>
            </ul>',
            10],

        // Sıhhi Tesisat altında
        ['sihhi-tesisat','Sıhhi Tesisat Yenileme','sihhi-tesisat-yenileme',
            'PPRC plastik boru ile komple banyo, mutfak, WC tesisat yenileme. Sızıntı garantili.',
            '<h2>Sıhhi Tesisat</h2>
            <p>Eski galvaniz veya bakır tesisatların yerine, modern PPRC (polipropilen kopolimer) plastik boru sistemi öneriyoruz. Paslanmaz, kireç tutmaz, 50+ yıl ömürlü.</p>
            <h3>Hizmet Kapsamı</h3>
            <ul>
                <li>Komple sıhhi tesisat değişimi</li>
                <li>Banyo / WC tesisatı</li>
                <li>Mutfak tesisatı</li>
                <li>Sıcak / soğuk su hatları</li>
                <li>Drenaj ve gider sistemleri</li>
                <li>Su sayacı ve manifold sistemleri</li>
                <li>Hidrofor / pompa montajı</li>
                <li>Su sızıntı tespiti (termal kamera ile)</li>
            </ul>
            <h3>Tipik Maliyet (2026)</h3>
            <ul>
                <li>Banyo + WC sıhhi tesisat: 30.000-50.000 ₺</li>
                <li>Komple 100 m² daire: 50.000-80.000 ₺</li>
            </ul>',
            10],
    ];

    foreach ($hizmetler as [$kat_slug, $baslik, $slug, $kisa, $icerik, $sira]) {
        try {
            $exists = db_get("SELECT id FROM hizmetler WHERE slug=?", [$slug]);
            if ($exists) continue;
            $kid = $hizmet_kat_id[$kat_slug] ?? null;
            db_run("INSERT INTO hizmetler (kategori_id, baslik, slug, kisa_aciklama, icerik, sira, aktif) VALUES (?, ?, ?, ?, ?, ?, 1)",
                [$kid, $baslik, $slug, $kisa, $icerik, $sira]);
            $sayilar['hizmetler']++;
        } catch (Throwable $e) { $hatalar[] = "Hizmet $baslik: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['hizmetler']}</strong> yeni hizmet eklendi.";

    // =========================================================================
    // 4) ÜRÜNLER
    // =========================================================================
    $urunler = [
        // ----- KOMBİLER -----
        ['yogusmali-kombi','demirdokum',
            'Demirdöküm Ademix P 18/24 kW Tam Yoğuşmalı Kombi','demirdokum-ademix-p-18-24-kw',
            'DEMIRDOKUM-ADEMIX-1824P',
            'Türk üretimi, Good Design ödüllü tam yoğuşmalı kombi. 80-100 m² için ideal.',
            '<p>Demirdöküm Ademix P 18/24 kW, A enerji sınıfı, paslanmaz çelik premix eşanjör, 53 dB sessiz çalışma. ErP yönetmeliğine uygun XL akış profili A sınıfı sıcak su.</p>
            <p><strong>Boyutlar:</strong> 626 × 400 × 270 mm, 25.6 kg<br><strong>Garanti:</strong> 2 yıl genel + 5 yıl eşanjör</p>
            <p>15 yıl süreye karşılık gelen zorlayıcı koşullarda test edilmiştir: 50.000 çevrim ömür testi, 25.000 ateşleme çevrimi.</p>',
            27322, 30260, 1, 1],

        ['yogusmali-kombi','demirdokum',
            'Demirdöküm Ademix P 24/24 kW Tam Yoğuşmalı Kombi','demirdokum-ademix-p-24-24-kw',
            'DEMIRDOKUM-ADEMIX-2424P',
            'Tam yoğuşmalı, 100-120 m² ev için en yaygın model. LCD ekran, donma emniyeti.',
            '<p>Yeni nesil Ademix 24 kW kapasiteli tam yoğuşmalı kombi. 55°C/35°C maksimum verimle çalışır, yerden ısıtma sistemleriyle uyumludur.</p>
            <p><strong>Voltaj:</strong> 230V monofaze<br><strong>Su sıcaklığı:</strong> 35-55°C<br><strong>Sıcak su debisi:</strong> 13.4 l/dk</p>',
            30243, 33180, 1, 1],

        ['yogusmali-kombi','demirdokum',
            'Demirdöküm Ademix P 24/28 kW Tam Yoğuşmalı Kombi','demirdokum-ademix-p-24-28-kw',
            'DEMIRDOKUM-ADEMIX-2428P',
            'Türkiye\'nin en çok satan kombi modeli. 24 kW ısıtma + 28 kW sıcak su gücü. 100-130 m² ideal.',
            '<p>Türkiye\'nin en sevilen yoğuşmalı kombi modeli. 28 kW yüksek sıcak su kapasitesi sayesinde 2 banyoda eşzamanlı kullanıma uygundur.</p>
            <ul>
                <li>A enerji sınıfı (XL profilinde A sıcak su)</li>
                <li>Çift işlemcili ana kart</li>
                <li>Frekans kontrollü pompa</li>
                <li>Otomatik hava tahliye fonksiyonu</li>
                <li>Yüksek basınç uyarı sistemi</li>
                <li>Donma emniyeti</li>
            </ul>',
            28411, 35236, 1, 1],

        ['yogusmali-kombi','demirdokum',
            'Demirdöküm Atron Condense P24 kW Yoğuşmalı Kombi','demirdokum-atron-condense-p24',
            'DEMIRDOKUM-ATRON-P24',
            'Ekonomik segment yoğuşmalı kombi. Türkiye\'nin en uygun fiyatlı yoğuşmalı modeli.',
            '<p>Ekonomik bütçeli kullanıcılar için Demirdöküm\'ün en uygun fiyatlı yoğuşmalı kombi modeli. 24 kW kapasiteli, 80-110 m² evler için yeterli.</p>',
            26409, 27521, 1, 0],

        ['yogusmali-kombi','demirdokum',
            'Demirdöküm Vintomix 24/28 kW Tam Yoğuşmalı Kombi','demirdokum-vintomix-24-28',
            'DEMIRDOKUM-VINTOMIX-2428',
            'Kompakt boyutlu, 24/28 kW, yerden ısıtma uyumlu yoğuşmalı kombi.',
            '<p>Demirdöküm Vintomix serisi, kompakt boyutlu ve hafif yapısıyla dar montaj alanlarında bile kolayca kurulur. Tam yoğuşmalı tasarım yerden ısıtma sistemleriyle %100 uyumludur.</p>',
            31825, 33165, 1, 0],

        ['yogusmali-kombi','demirdokum',
            'Demirdöküm Nitromix Ioni 24 kW Premium Yoğuşmalı Kombi','demirdokum-nitromix-ioni-24',
            'DEMIRDOKUM-NITROMIX-IONI24',
            'Premium segment, Vaillant teknolojisi, çelik eşanjör, yerden ısıtma için ideal.',
            '<p>Nitromix Ioni serisi, Demirdöküm\'ün premium yoğuşmalı kombilerindendir. Vaillant ortaklığı sayesinde Alman teknolojisi ve çelik eşanjör avantajıyla yerden ısıtma sistemlerinde sınırsız ömür sunar.</p>
            <ul>
                <li>Çelik eşanjör (paslanmaz)</li>
                <li>Frekans kontrollü pompa</li>
                <li>Yüksek modülasyon (1:10)</li>
                <li>Vaillant teknolojisi</li>
            </ul>',
            37372, 38945, 1, 0],

        ['yogusmali-kombi','bosch',
            'Bosch Condens 5700i 24 kW Yoğuşmalı Kombi','bosch-condens-5700i-24',
            'BOSCH-CONDENS-5700I-24',
            'Premium Alman kalitesi. Akıllı kontrol, sessiz çalışma, geniş sıcak su.',
            '<p>Bosch Condens 5700i, Avrupa standartlarında premium yoğuşmalı kombidir. Sessiz çalışma (40 dB), akıllı kontrol paneli ve uzaktan kumanda uygulamasıyla modern evler için idealdir.</p>',
            42500, 47800, 1, 1],

        ['yogusmali-kombi','vaillant',
            'Vaillant ecoTEC plus VUW 24 kW Tam Yoğuşmalı Kombi','vaillant-ecotec-plus-24',
            'VAILLANT-ECOTEC-VUW24',
            'En yüksek modülasyon (1:10), uzun ömür, Alman üretimi premium kombi.',
            '<p>Vaillant ecoTEC plus serisi, tam yoğuşmalı teknolojinin en gelişmiş örneklerindendir. 1:10 modülasyon oranı sayesinde sürekli sabit alev ile çalışır, açma-kapama çevrimi minimumda tutar, ömür ve verim maksimumdadır.</p>',
            48000, 53500, 0, 0],

        ['yogusmali-kombi','eca',
            'ECA Confeo Premix 24 kW Tam Yoğuşmalı Kombi','eca-confeo-premix-24',
            'ECA-CONFEO-PREMIX-24',
            'Yerli üretim premium yoğuşmalı kombi. Demirdöküm grubunun ECA markası.',
            '<p>ECA Confeo Premix, Demirdöküm grubunun ECA markası altında üretilen yerli premium yoğuşmalı kombidir. Türkiye\'nin yaygın servis ağı avantajıyla, premium özelliklerini orta segment fiyata sunar.</p>',
            29500, 32000, 0, 1],

        // ----- KLİMALAR -----
        ['inverter-klima','daikin',
            'Daikin Sensira ATXF35F 12000 BTU A++ Inverter Klima','daikin-sensira-atxf35f-12000-btu',
            'DAIKIN-SENSIRA-12000',
            'A++ enerji sınıfı, R32 gaz, sessiz çalışma. 18-22 m² oda için ideal.',
            '<p>Daikin Sensira ATXF35F, 12.000 BTU soğutma kapasitesi ile 18-22 m² odalar için tasarlanmış A++ inverter klimadır. R32 çevre dostu soğutucu gaz kullanır.</p>
            <ul>
                <li>Soğutma: 12.000 BTU/h (3.5 kW)</li>
                <li>Isıtma: 12.000 BTU/h (3.7 kW)</li>
                <li>Enerji: A++ (SEER 6.5)</li>
                <li>Soğutucu gaz: R32</li>
                <li>Ses (iç): 21-39 dB</li>
                <li>Coanda etkisi (havayı yukarı yönlendirir)</li>
            </ul>',
            33343, 36500, 1, 1],

        ['inverter-klima','mitsubishi-heavy',
            'Mitsubishi Heavy SRK35ZSP-W 12000 BTU A++ Inverter Klima','mitsubishi-heavy-srk35zsp-12000',
            'MITSUBISHI-SRK35ZSP-12000',
            'Premium Japon teknolojisi, sessiz çalışma, uzun ömür. Mitsubishi Heavy serisi.',
            '<p>Mitsubishi Heavy Industries Silver serisi 12.000 BTU inverter klima. Japon mühendislik kalitesi, dayanıklılık ve sarsılmaz performans.</p>',
            43680, 46800, 1, 1],

        ['inverter-klima','mitsubishi-heavy',
            'Mitsubishi Heavy SRK50ZS-W 18000 BTU A++ Premium','mitsubishi-heavy-srk50zs-18000',
            'MITSUBISHI-SRK50ZS-18000',
            'Geniş salon ve açık plan oturma için 18000 BTU premium inverter.',
            '<p>Mitsubishi Heavy Premium serisi 18.000 BTU. 25-40 m² odalar için yüksek performans, sessiz çalışma, Japon dayanıklılığı.</p>',
            79100, 82400, 0, 1],

        ['inverter-klima','bosch',
            'Bosch Climate 5000 12000 BTU Inverter Duvar Tipi Klima','bosch-climate-5000-12000',
            'BOSCH-CLIMATE-5000-12000',
            'Avrupa standardında inverter klima. Sessiz, ekonomik, A++ enerji.',
            '<p>Bosch Climate 5000 serisi 12.000 BTU inverter klima, Avrupa kalitesi ile yerli pazarda yaygın servis ağına sahiptir.</p>',
            34500, 37200, 0, 1],

        ['inverter-klima','lg',
            'LG Dualcool Plus PC18SQ 18000 BTU A++ Inverter','lg-dualcool-plus-18000',
            'LG-DUALCOOL-18000',
            'Geniş salon için akıllı özellikli LG inverter klima.',
            '<p>LG Dualcool Plus serisi, 18.000 BTU geniş salon klimasıdır. WiFi kontrol, sesli komut, akıllı ev entegrasyonu özellikleriyle modern kullanıcılar için tasarlanmıştır.</p>',
            40249, 43000, 0, 1],

        ['inverter-klima','vestel',
            'Vestel Inverter 12000 BTU A++ Duvar Tipi Klima','vestel-inverter-12000',
            'VESTEL-INVERTER-12000',
            'Yerli üretim, ekonomik, A++ inverter klima.',
            '<p>Vestel\'in yerli üretim 12.000 BTU inverter klimasi, ekonomik bütçe için ideal seçenektir. Türkiye genelinde yaygın servis ağı avantajı sunar.</p>',
            22000, 24500, 0, 1],

        ['inverter-klima','eca',
            'ECA Spylos 9000 BTU R32 Inverter Klima','eca-spylos-9000',
            'ECA-SPYLOS-9000',
            'Demirdöküm grubunun ECA markası. R32 gaz, kompakt 9000 BTU yatak odası klimasi.',
            '<p>ECA Spylos serisi, Demirdöküm grubunun yerli üretim klima markasıdır. 9.000 BTU kapasitesiyle 12-18 m² yatak odaları için idealdir.</p>',
            21000, 23200, 0, 0],

        // ----- ISI POMPASI -----
        ['isi-pompasi-urun','bosch',
            'Bosch CS3400i AWS Split 8 kW Hava-Su Isı Pompası','bosch-cs3400i-8kw',
            'BOSCH-CS3400I-8KW',
            'Premium hava kaynaklı ısı pompası. Yerden ısıtma + sıcak su üretir, kombi yerine geçer.',
            '<p>Bosch CS3400i AWS, hava-su split tipi ısı pompasıdır. R32 gazı, monofaze, COP 4.4 ile 1 kWh elektrikten 4.4 kWh ısı üretir. Yerden ısıtma sistemiyle %100 uyumlu, hem ısıtma hem soğutma yapar.</p>',
            165000, 178000, 1, 1],

        ['isi-pompasi-urun','daikin',
            'Daikin Altherma 3 R32 Split 11 kW Hava-Su Isı Pompası','daikin-altherma-3-11kw',
            'DAIKIN-ALTHERMA-3-11KW',
            'Premium Japon ısı pompası. Yerden ısıtma + sıcak su + soğutma — tek sistem.',
            '<p>Daikin Altherma 3, premium hava-su ısı pompasıdır. Çift hat ısıtma desteği (radyatör + yerden ısıtma), entegre 230L sıcak su tankı, mobil kontrol uygulaması.</p>',
            215000, 235000, 1, 0],
    ];

    foreach ($urunler as [$kat_slug, $marka_slug, $ad, $slug, $sku, $kisa, $aciklama, $fiyat, $eski, $one_cikan, $stok]) {
        try {
            $exists = db_get("SELECT id FROM urunler WHERE slug=?", [$slug]);
            if ($exists) continue;
            $kid = $kat_id[$kat_slug] ?? null;
            $mid = $marka_id[$marka_slug] ?? null;
            db_run("INSERT INTO urunler (kategori_id, marka_id, ad, slug, sku, kisa_aciklama, aciklama, fiyat, indirimli_fiyat, kdv_orani, stok, one_cikan, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 20, ?, ?, 1)",
                [$kid, $mid, $ad, $slug, $sku, $kisa, $aciklama, $eski, $fiyat, $stok, $one_cikan]);
            $sayilar['urunler']++;
        } catch (Throwable $e) { $hatalar[] = "Ürün $ad: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['urunler']}</strong> yeni ürün eklendi.";

    // =========================================================================
    // 5) KAMPANYALAR
    // =========================================================================
    $kampanyalar = [
        ['Demirdöküm Ademix 24 kW Kombi Paketi — Montaj Dahil','demirdokum-ademix-24kw-kombi-paketi',
            'Türkiye\'nin en sevilen kombisi + profesyonel montaj + 2 yıl işçilik garantisi. Peşin 80.000 ₺ veya 6 taksit 87.000 ₺.',
            '<h2>Demirdöküm Ademix 24 kW Yoğuşmalı Kombi Paketi</h2>
            <p>Türkiye\'nin en çok tercih edilen kombi modeli — Demirdöküm Ademix 24 kW — Azra Doğalgaz garantisiyle adresinize teslim ve montaj dahil sadece <strong>80.000 ₺</strong>!</p>
            <h3>Pakete Dahil Olanlar</h3>
            <ul>
                <li>Demirdöküm Ademix P 24/28 kW Tam Yoğuşmalı Kombi (orijinal Türk üretimi)</li>
                <li>Hermetik baca seti (1.5 m\'ye kadar)</li>
                <li>Yetkili bayi profesyonel montaj</li>
                <li>İlk devreye alma ve kullanım eğitimi</li>
                <li>2 yıl işçilik garantisi</li>
                <li>2 yıl üretici genel garantisi + 5 yıl eşanjör garantisi</li>
                <li>Ücretsiz keşif ve yazılı teklif</li>
                <li>Yetkili servis garanti belgesi</li>
            </ul>
            <h3>Ödeme Seçenekleri</h3>
            <ul>
                <li><strong>Peşin / Nakit:</strong> 80.000 ₺ (avantajlı)</li>
                <li><strong>Kredi Kartı 6 Taksit:</strong> 87.000 ₺ (aylık 14.500 ₺)</li>
                <li><strong>Kredi Kartı 9 Taksit:</strong> Anlaşmalı bankalar için bilgi alın</li>
            </ul>
            <h3>Neden Bu Paket?</h3>
            <p>2026 yılında Demirdöküm Ademix 24/28 kW kombinin piyasa fiyatı 28.500-35.500 ₺ aralığındadır. Bu kampanyada hem cihaz, hem profesyonel montaj, hem 2 yıl işçilik garantisi tek pakette sunulur. Adresinize ücretsiz keşfe gelir, kombinizi monte ederiz, kullanmaya hemen başlarsınız.</p>
            <p><strong>Kampanya yıl sonuna kadar geçerlidir!</strong></p>',
            '', 80000, 87000, 6, '2026-01-01', '2026-12-31'],

        ['Yerden Isıtma + Tam Yoğuşmalı Kombi Paketi','yerden-isitma-yogusmali-kombi-paketi',
            '100 m² eve komple yerden ısıtma sistemi + Demirdöküm Vintomix 24/28 + tüm tesisat. Anahtar teslim 195.000 ₺.',
            '<h2>Yerden Isıtma + Yoğuşmalı Kombi Anahtar Teslim Paket</h2>
            <p>Yeni inşaat veya komple tadilat müşterilerimiz için: 100 m² standart daireye komple yerden ısıtma sistemi + Demirdöküm tam yoğuşmalı kombi.</p>
            <h3>Pakete Dahil Olanlar</h3>
            <ul>
                <li>Demirdöküm Vintomix 24/28 kW Tam Yoğuşmalı Kombi</li>
                <li>PEX boru (16×2 mm, 50 yıl ömür)</li>
                <li>EPS şap altı izolasyon plakaları</li>
                <li>Folyo kaplı yansıtıcı tabaka</li>
                <li>Kollektör (her oda ayrı vanalı)</li>
                <li>Oda termostatı (kablosuz)</li>
                <li>Tüm tesisat işçiliği</li>
                <li>Şap çekimi (ek hizmet, anlaşmalı şap firmasından)</li>
                <li>2 yıl tesisat garantisi</li>
            </ul>
            <p><strong>Anahtar teslim:</strong> 195.000 ₺ (peşin) / 220.000 ₺ (12 taksit kart)</p>
            <p><em>* 100 m² nin üzerinde m² başına 1.500 ₺ ek ücret</em></p>',
            '', 195000, 220000, 12, '2026-01-01', '2026-12-31'],

        ['12000 BTU Inverter Klima + Montaj Paket Fiyat','12000-btu-klima-montaj-paketi',
            'Daikin Sensira 12000 BTU + profesyonel montaj. 38.000 ₺ peşin, 42.000 ₺ 6 taksit.',
            '<h2>Daikin Sensira 12000 BTU Inverter Klima + Montaj Paketi</h2>
            <p>Yatak odası, çocuk odası veya küçük oturma odası için en popüler kapasite. Daikin Sensira ATXF35F A++ inverter klima + profesyonel montaj.</p>
            <h3>Pakete Dahil</h3>
            <ul>
                <li>Daikin Sensira ATXF35F 12000 BTU klima (iç ve dış ünite)</li>
                <li>Profesyonel montaj (5 m bakır boru, drenaj, kablo dahil)</li>
                <li>Vakum çekme + R32 gaz dolumu</li>
                <li>2 yıl montaj garantisi</li>
                <li>Üretici 2 yıl genel garantisi</li>
            </ul>
            <p><strong>Peşin:</strong> 38.000 ₺ &nbsp;·&nbsp; <strong>6 Taksit Kart:</strong> 42.000 ₺ (aylık 7.000 ₺)</p>',
            '', 38000, 42000, 6, '2026-04-01', '2026-09-30'],

        ['Apartman Doğalgaz Dönüşüm Paketi','apartman-dogalgaz-donusum-paketi',
            'Apartmanınızdaki tüm daireler için tek seferlik avantajlı toplu doğalgaz dönüşüm paketi.',
            '<h2>Apartman Toplu Doğalgaz Dönüşümü</h2>
            <p>Apartman yöneticileri için: Tüm dairelerde aynı anda doğalgaz dönüşümü yaptırırsanız, daire başına %15-20 indirimle hizmet sunuyoruz.</p>
            <h3>Pakete Dahil</h3>
            <ul>
                <li>Tüm bina için tek koordinatör</li>
                <li>Kolon hattı projelendirme ve döşeme</li>
                <li>Her daire için bireysel proje çizimi</li>
                <li>İzmirgaz onay süreçleri</li>
                <li>Tesisat döşeme (her daire 1-2 gün)</li>
                <li>Sızdırmazlık testleri</li>
                <li>Toplu gaz açma randevusu</li>
                <li>Apartman karar metni desteği</li>
                <li>Toplu fiyat avantajı (%15-20)</li>
            </ul>
            <p>Yöneticilerimize yazılı paket teklifi sunmak için bize ulaşın.</p>',
            '', 0, 0, 0, '2026-01-01', '2026-12-31'],
    ];

    foreach ($kampanyalar as [$baslik, $slug, $kisa, $icerik, $gorsel, $nakit, $kart, $taksit, $bas, $bit]) {
        try {
            $exists = db_get("SELECT id FROM kampanyalar WHERE slug=?", [$slug]);
            if ($exists) continue;
            db_run("INSERT INTO kampanyalar (baslik, slug, kisa_aciklama, icerik, gorsel, nakit_fiyat, kart_fiyat, taksit_sayisi, baslangic, bitis, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [$baslik, $slug, $kisa, $icerik, $gorsel, $nakit ?: null, $kart ?: null, $taksit, $bas, $bit]);
            $sayilar['kampanyalar']++;
        } catch (Throwable $e) { $hatalar[] = "Kampanya $baslik: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['kampanyalar']}</strong> yeni kampanya eklendi.";

    // =========================================================================
    // 6) BLOG YAZILARI
    // =========================================================================
    $bloglar = [
        ['İzmir\'de Doğalgaz Aboneliği Nasıl Açılır? Adım Adım 2026 Rehberi','izmirde-dogalgaz-aboneligi-nasil-acilir',
            'Yeni doğalgaz aboneliği için gerekli belgeler, süreç adımları, İzmirgaz başvuru ve gaz açım süreci. 2026 güncel rehber.',
            '<h2>İzmir\'de Doğalgaz Aboneliği — 2026 Rehberi</h2>
            <p>İzmir\'de yeni doğalgaz aboneliği açtırma süreci 2026 itibariyle ortalama <strong>7-10 iş günü</strong> sürer. İşte adım adım ne yapmanız gerektiği:</p>

            <h3>Adım 1 — Yetkili Tesisat Firması Seçin</h3>
            <p>İlk ve en kritik adım, İzmirgaz tarafından sertifikalı yetkili iç tesisat firması ile çalışmaktır. Yetkisiz firmalar tarafından yapılan tesisat İzmirgaz tarafından kabul edilmez ve gaz açılmaz.</p>
            <p>Azra Doğalgaz İzmirgaz onaylı yetkili firmadır. Sertifikamızı talep ederek doğrulayabilirsiniz.</p>

            <h3>Adım 2 — Adresinize Keşif</h3>
            <p>Yetkili firma adresinize keşif ekibi gönderir. Bu aşamada belirlenenler:</p>
            <ul>
                <li>Sayaç yeri</li>
                <li>Kombi konumu</li>
                <li>Baca durumu (var mı, hermetik mi gerekli)</li>
                <li>Petek sayısı ve uzunluğu</li>
                <li>Mutfak ocak/fırın bağlantı noktası</li>
                <li>Gerekli boru metrajı</li>
            </ul>

            <h3>Adım 3 — Yazılı Teklif ve Sözleşme</h3>
            <p>Keşif sonrası yazılı teklif sunulur (malzeme + işçilik dökümlü). Teklif kabul edilirse iş sözleşmesi imzalanır.</p>

            <h3>Adım 4 — Proje ve İzmirgaz Onayı</h3>
            <p>Yetkili makine mühendisi tarafından proje çizilir, İzmirgaz online sistemine yüklenir. Onay süreci 2-5 iş günü.</p>

            <h3>Adım 5 — Tesisat Döşeme</h3>
            <p>Onaylı projeye göre tesisat döşenir. TS EN 15266 standartlarındaki çelik veya gasfil paslanmaz boru kullanılır. Sızdırmazlık testi (basınç testi) zorunludur.</p>

            <h3>Adım 6 — Güvence Bedeli ve Gaz Açma Randevusu</h3>
            <p>Müşteri abone merkezine giderek güvence bedeli yatırır (kullanım sonunda iade edilir). Sayaç firmaya teslim edilir.</p>

            <h3>Adım 7 — Gaz Arzı ve Uygunluk Belgesi</h3>
            <p>İzmirgaz teknik personeli ve yetkili firma yetkilisi adresinize gelir. Tesisat kontrol edilir, ocak/kombi gibi cihazların bağlı olması gerekir. Uygunluk durumunda gaz arzı sağlanır, <strong>Doğal Gaz Uygunluk Belgesi</strong> verilir.</p>

            <h3>Gerekli Belgeler</h3>
            <ul>
                <li><strong>Mülk sahibi için:</strong> Kimlik fotokopisi, tapu, DASK poliçesi</li>
                <li><strong>Kiracı için:</strong> Kimlik fotokopisi, kira sözleşmesi (DASK kiracıdan istenmez)</li>
                <li>Güvence bedeli (abonelik tipine göre değişir)</li>
            </ul>

            <p><strong>Toplam süre:</strong> 7-10 iş günü. Yoğun dönemlerde 14 güne kadar uzayabilir.</p>',
            'doğalgaz aboneliği, izmirgaz, doğalgaz tesisatı izmir, doğalgaz açtırma'],

        ['Kombi Seçerken Dikkat Edilecek 8 Önemli Kriter','kombi-secerken-dikkat-edilecek-8-onemli-kriter',
            'Kapasite, modülasyon, sıcak su debisi, marka, garanti — kombi seçimini etkileyen ana faktörlerin uzman analizi.',
            '<h2>Kombi Seçerken Bilmeniz Gereken Her Şey</h2>
            <p>Kombi, evinizde 10-15 yıl boyunca kullanacağınız bir cihazdır. Yanlış seçim hem konfor kaybı hem yüksek faturayla cezalandırılır. İşte 8 ana kriter:</p>

            <h3>1. Kapasite (kW)</h3>
            <p>Kabaca her 10 m² için 1.5-2 kW gerekir. Bölge katsayısı, izolasyon ve cephe yönü hesabı değiştirir. Yaklaşık tablo:</p>
            <ul>
                <li>80-100 m²: 18-20 kW</li>
                <li>100-120 m²: 24 kW (en yaygın)</li>
                <li>120-150 m²: 24-28 kW</li>
                <li>150-180 m²: 30 kW</li>
                <li>180+ m² / dubleks: 35 kW veya kazan sistemi</li>
            </ul>

            <h3>2. Yoğuşmalı / Tam Yoğuşmalı</h3>
            <p>2019\'dan beri Türkiye\'de satılan tüm kombiler yoğuşmalı olmak zorundadır. Tam yoğuşmalı kombiler düşük sıcaklıkta (55°C-35°C) çalışırken maksimum verim verir. Yerden ısıtmada mutlaka tam yoğuşmalı tercih edin.</p>

            <h3>3. Modülasyon Oranı</h3>
            <p>Modülasyon, kombinin alev boyunu (gaz akışını) ihtiyaca göre ayarlama yeteneğidir. <strong>1:8 ve üstü modülasyon</strong> oranı (örn. 3-24 kW arası ayarlanabilir) hem konfor hem tasarruf sağlar. Düşük modülasyonlu kombi sürekli açıp kapanır, parça yıpranması artar.</p>

            <h3>4. Sıcak Su Performansı</h3>
            <p>Aynı anda 2 banyoda sıcak su kullanılacaksa <strong>en az 24 kW kapasite</strong> ve dakika başına 12+ litre üretim önemlidir. Tek banyo yeterli ise 18-20 kW kombi de iş görür.</p>

            <h3>5. Eşanjör Tipi</h3>
            <ul>
                <li><strong>Paslanmaz çelik (Nitromix gibi):</strong> En uzun ömürlü, premium</li>
                <li><strong>Aluminyum (yaygın):</strong> İyi termik iletim, orta segment</li>
                <li><strong>Çift eşanjörlü (bitermik):</strong> Eski teknoloji, kaçınılmalı</li>
            </ul>

            <h3>6. Marka ve Servis Ağı</h3>
            <p>Tanınmış markalar genellikle 5+ yıl yedek parça desteği verir. Bilinmedik marka ekonomik gözükse de orta vadede arıza+parça maliyetiyle cezalandırır. Bölgenizdeki yetkili servis sayısını mutlaka araştırın.</p>

            <h3>7. Garanti ve Bayi Yetkilisi</h3>
            <p>Standart garanti: 2 yıl kombi, 5 yıl eşanjör. Premium markalarda 3+5 yıl. <strong>Yetkili tesisat firmasınca montaj</strong> garantinin geçerliliği için zorunludur — yetkisiz firma montajı garantiyi otomatik iptal eder.</p>

            <h3>8. Akıllı Özellikler</h3>
            <p>Modern kombiler WiFi kontrol, mobil app, akıllı termostat (Migo Connect, Bosch Junkers, Vaillant ecoNET) gibi özellikler sunar. %15-20 ek tasarruf sağlar.</p>

            <h3>2026 İçin Tavsiye Listesi</h3>
            <ul>
                <li><strong>Demirdöküm Ademix 24 kW</strong> — En çok satan, ekonomik tam yoğuşmalı, ~32.000 ₺</li>
                <li><strong>Bosch Condens 5700i 24 kW</strong> — Premium, sessiz, akıllı kontrol, ~42.500 ₺</li>
                <li><strong>Vaillant ecoTEC plus 24 kW</strong> — En yüksek modülasyon, uzun ömür, ~48.000 ₺</li>
                <li><strong>ECA Confeo Premix 24 kW</strong> — Yerli üretim, uygun fiyat, ~29.500 ₺</li>
                <li><strong>Demirdöküm Nitromix Ioni 24 kW</strong> — Premium, Vaillant teknolojisi, çelik eşanjör, ~37.500 ₺</li>
            </ul>',
            'kombi seçimi, demirdöküm ademix, yoğuşmalı kombi, kombi tavsiye'],

        ['Inverter Klima vs Standart Klima — Hangi Klimanız Olmalı?','inverter-klima-vs-standart-klima',
            'Inverter teknolojisi neden %30 elektrik tasarrufu sağlar? Standart klimadan farkları, ne zaman tercih edilmeli.',
            '<h2>Inverter Klima Standart Klimadan Ne Kadar Farklı?</h2>
            <p>Klima alırken karşınıza çıkan en önemli teknik karar: <strong>Inverter mi, Standart mı?</strong> İşte ikisi arasındaki temel farklar ve neden inverter\'ı tavsiye ettiğimiz:</p>

            <h3>Standart (On/Off) Klima Nasıl Çalışır?</h3>
            <p>Standart klima dur-kalk mantığıyla çalışır. Hedef sıcaklığa ulaşınca tamamen kapanır, sıcaklık tekrar yükselince yeniden tam güçle açılır. Bu sürekli "açılma-kapanma" hem konfor azaltır hem de:</p>
            <ul>
                <li>Her açılışta yüksek elektrik çekimi (start-up akımı)</li>
                <li>Kompresör yıpranması</li>
                <li>Sıcaklık dalgalanmaları (3-4°C oynama)</li>
                <li>Yüksek ses</li>
            </ul>

            <h3>Inverter Klima Nasıl Çalışır?</h3>
            <p>Inverter klima, kompresör hızını ihtiyaca göre ayarlar. Hedef sıcaklığa yaklaşınca düşük güçte sürekli çalışır, hiç durmaz. Bu sayede:</p>
            <ul>
                <li>%30 daha az elektrik tüketimi</li>
                <li>Sabit sıcaklık (±0.5°C)</li>
                <li>Çok daha sessiz çalışma</li>
                <li>Kompresör ömrü uzar (2-3 kat)</li>
                <li>Hızlı soğutma (start-up\'ta full hız)</li>
            </ul>

            <h3>Maliyet Analizi (12.000 BTU klima)</h3>
            <table border="1" cellpadding="8" style="border-collapse:collapse;width:100%">
                <tr><th>Kriter</th><th>Standart</th><th>Inverter A++</th></tr>
                <tr><td>Cihaz fiyatı</td><td>~18.000 ₺</td><td>~35.000 ₺</td></tr>
                <tr><td>Saatlik elektrik tüketimi</td><td>~1.5 kWh</td><td>~1.0 kWh</td></tr>
                <tr><td>Aylık (yaz, 6 saat/gün)</td><td>~270 kWh</td><td>~180 kWh</td></tr>
                <tr><td>Aylık fatura (3 ₺/kWh)</td><td>~810 ₺</td><td>~540 ₺</td></tr>
                <tr><td>Yıllık fark</td><td>—</td><td><strong>~3.240 ₺ tasarruf</strong></td></tr>
            </table>

            <p>Bu hesaba göre <strong>fiyat farkı (~17.000 ₺) yaklaşık 5 yılda kendini öder</strong>, sonra net kar başlar. Klimanın ortalama ömrü 10-15 yıl olduğu düşünülürse, inverter avantajı çok net.</p>

            <h3>Sonuç</h3>
            <p>2026 yılında klima alıyorsanız, <strong>kesinlikle inverter</strong> tercih edin. Tek istisna: yazlık veya senede 1-2 hafta kullanılacak klimalar için standart yeterli olabilir, ama düzenli kullanılan ev/ofis için inverter zorunluluk.</p>

            <p>Ayrıca: <strong>R32 gazı</strong> kullanan modeller daha çevreci ve verimli. Yeni alacağınız klima mutlaka R32 olmalı.</p>',
            'inverter klima, klima seçimi, A++ enerji, R32 gaz'],

        ['Yerden Isıtma vs Radyatör — 7 Soruda Karar','yerden-isitma-vs-radyator-karar-rehberi',
            'Yeni inşaat veya tadilat için yerden ısıtma mı radyatör mü? Maliyet, konfor, enerji verimi karşılaştırması.',
            '<h2>Yerden Isıtma vs Radyatör — Hangisi Sizin İçin?</h2>
            <p>Yeni inşaat veya komple tadilat aşamasındasınız ve ısıtma sistemi seçiminde tereddüt mü yaşıyorsunuz? İşte 7 soruda bilinçli karar:</p>

            <h3>1. İlk Yatırım Maliyeti</h3>
            <ul>
                <li><strong>Radyatör:</strong> 80-120 ₺/m² (sadece tesisat)</li>
                <li><strong>Yerden ısıtma:</strong> 600-900 ₺/m² (tesisat, şap altı dahil)</li>
            </ul>
            <p>100 m² ev için fark: ~50.000 ₺ ek yatırım yerden ısıtma için.</p>

            <h3>2. Enerji Verimi</h3>
            <p>Yerden ısıtma %15-25 daha az gaz tüketir. Bunun nedeni:</p>
            <ul>
                <li>Daha düşük çalışma sıcaklığı (35-45°C, radyatörde 65-80°C)</li>
                <li>Yoğuşmalı kombiyle %100 uyum (tam yoğuşma rejiminde çalışır)</li>
                <li>Homojen sıcaklık dağılımı (her yer aynı sıcaklıkta)</li>
                <li>Termostat etkisi maksimumda</li>
            </ul>

            <h3>3. Konfor</h3>
            <p>Yerden ısıtmanın konfor avantajı tartışmasız:</p>
            <ul>
                <li>Ayak sıcak, baş serin (ideal sağlık dağılımı)</li>
                <li>Hava kuruluğu yok (radyatörler havayı kurutur)</li>
                <li>Ses yok (radyatörler sıcak su sirkülasyonunda ses çıkarır)</li>
                <li>Toz dolaşımı yok (radyatörlerin ısıttığı hava akımı toz kaldırır)</li>
            </ul>

            <h3>4. Estetik</h3>
            <p>Yerden ısıtma tamamen görünmez. Radyatör peteklerini saklayacak yer aramak gerekmez. Mobilya ve dekorasyon serbestliği maksimum.</p>

            <h3>5. Isınma Hızı</h3>
            <p><strong>Radyatör hızlı (15-30 dk), yerden ısıtma yavaştır (1-2 saat)</strong>. Ama yerden ısıtma "kapalı" değil "düşük" konumda tutulur, hep sıcaklık vardır. Termostatla otomatik yönetildiğinde fark hissedilmez.</p>

            <h3>6. Bakım</h3>
            <p>İkisi de düşük bakım gerektirir. PEX boru sistemi 50+ yıl ömürlüdür. Sadece kollektör vanaları ve termostat yıllık kontrol gerektirir.</p>

            <h3>7. Tadilat Uyumu</h3>
            <ul>
                <li><strong>Yeni inşaat veya komple tadilat:</strong> Yerden ısıtma kesinlikle</li>
                <li><strong>Mevcut yaşanan ev:</strong> Radyatör pratik (yerden ısıtma için zemin yenilenmeli — şap dahil 200 ₺/m² ekstra)</li>
                <li><strong>Sadece banyolarda yerden ısıtma:</strong> Küçük yatırım, büyük konfor (~15.000 ₺)</li>
            </ul>

            <h3>Sonuç ve Tavsiye</h3>
            <p><strong>Yerden ısıtma şu durumlarda kesinlikle tercih edilmeli:</strong></p>
            <ul>
                <li>Yeni inşaat sahibi iseniz</li>
                <li>Komple tadilat (zemin değişimi) yapıyorsanız</li>
                <li>Uzun süre yaşayacağınız ev (yatırımın geri dönüşü 8-12 yıl)</li>
                <li>Yoğuşmalı kombi veya ısı pompası kullanacaksanız</li>
            </ul>
            <p><strong>Radyatör şu durumlarda mantıklı:</strong></p>
            <ul>
                <li>Mevcut binayı değiştirmeden hızlı çözüm</li>
                <li>Düşük bütçe öncelik</li>
                <li>Kısa süreli kalış planı (5 yıldan az)</li>
            </ul>

            <h3>Yerden Isıtma + Yoğuşmalı Kombi: Altın Kombinasyon</h3>
            <p>Yerden ısıtmanın tam potansiyelini görmek için <strong>tam yoğuşmalı kombi</strong> şarttır. Birlikte kullanıldığında %30 daha az gaz tüketimi mümkün olur.</p>',
            'yerden ısıtma, radyatör, yoğuşmalı kombi, ısıtma sistemi'],

        ['Isı Pompası Nedir? Doğalgazın Yerini Alabilir Mi?','isi-pompasi-nedir-dogalgazin-yerini-alir-mi',
            'Hava kaynaklı ısı pompası teknolojisi, çalışma prensibi, COP değeri, doğalgazla maliyet karşılaştırması.',
            '<h2>Isı Pompası — Geleceğin Isıtma Teknolojisi</h2>
            <p>Avrupa\'da yeni binalarda kombinin yerini hızla alan teknoloji: <strong>Isı pompası</strong>. Türkiye\'de de ısı pompası kullanımı 2024\'ten itibaren hızla yaygınlaşıyor.</p>

            <h3>Nasıl Çalışır?</h3>
            <p>Isı pompası, klimanızla aynı prensiple çalışır: dış havada bulunan ısıyı içeri pompalar. Klima yazın iç havayı dışa, kışın dış havayı içe pompalar. Modern ısı pompaları -15°C dış sıcaklıkta bile verim verebilir.</p>

            <h3>COP Değeri Nedir?</h3>
            <p>COP (Coefficient of Performance), 1 birim elektrik girişi ile kaç birim ısı çıkışı alındığını gösterir. Modern hava kaynaklı ısı pompası COP değeri:</p>
            <ul>
                <li>Optimum koşullarda (7°C dış): 4.0-4.5 (1 kWh elektrik = 4 kWh ısı)</li>
                <li>Soğukta (-7°C dış): 2.5-3.0</li>
                <li>Çok soğukta (-15°C dış): 2.0</li>
            </ul>
            <p>Karşılaştırma: Doğalgaz kombinin "COP" karşılığı verim oranı 0.92-0.96\'dır (yoğuşmalı). Yani <strong>1 kWh elektrik ile ısı pompası, 1 kWh doğalgazdan 4 kat fazla ısı üretir</strong>.</p>

            <h3>Maliyet Karşılaştırması (2026, 100 m² ev için kış ayı)</h3>
            <table border="1" cellpadding="8" style="border-collapse:collapse;width:100%">
                <tr><th>Sistem</th><th>Aylık Tüketim</th><th>Birim Fiyat</th><th>Aylık Fatura</th></tr>
                <tr><td>Doğalgaz Kombi (yoğuşmalı)</td><td>500 m³</td><td>5.5 ₺/m³</td><td>2.750 ₺</td></tr>
                <tr><td>Isı Pompası (COP 3.5)</td><td>800 kWh</td><td>3 ₺/kWh</td><td>2.400 ₺</td></tr>
                <tr><td>Isı Pompası + Solar Panel</td><td>200 kWh (net)</td><td>3 ₺/kWh</td><td><strong>600 ₺</strong></td></tr>
            </table>

            <h3>Avantajları</h3>
            <ul>
                <li>Hem ısıtma hem soğutma — tek sistem</li>
                <li>Doğalgaz altyapısı gerekmez</li>
                <li>Çevre dostu (doğrudan emisyon yok)</li>
                <li>Solar panelle birlikte sıfır enerji ev</li>
                <li>Düşük bakım (kombiden daha az)</li>
                <li>Sıcak su üretir (entegre tank ile)</li>
            </ul>

            <h3>Dezavantajları</h3>
            <ul>
                <li>Yüksek ilk yatırım (75-220 bin ₺)</li>
                <li>Soğuk bölgelerde verim düşer</li>
                <li>Yerden ısıtma sistemi zorunlu (radyatörle çalışsa da verim düşer)</li>
                <li>Dış ünite gürültüsü (modern modeller 50 dB altı)</li>
            </ul>

            <h3>Kimler İçin Uygun?</h3>
            <ul>
                <li><strong>Doğalgaz altyapısı olmayan bölgeler:</strong> Yazlık siteleri, kırsal alan, yeni gelişen mahalleler</li>
                <li><strong>Ege/Akdeniz bölgesi:</strong> Dış sıcaklık -10°C altına düşmüyor (İzmir ideal)</li>
                <li><strong>Yenilenebilir enerji odaklı yapılar:</strong> Güneş paneli ile birlikte sıfır enerji ev</li>
                <li><strong>Yeni inşaat:</strong> Yerden ısıtma sistemiyle birlikte tasarım</li>
            </ul>

            <h3>Sonuç</h3>
            <p>2026 yılında ısı pompası, özellikle Ege bölgesinde, doğalgazın güçlü alternatifi haline gelmiştir. İlk yatırım yüksek olsa da, 5-8 yıllık geri ödeme süresi ile uzun vadede karlıdır. Yeni inşaat veya komple tadilat planlıyorsanız mutlaka değerlendirmeniz gereken bir seçenek.</p>',
            'ısı pompası, hava kaynaklı, COP, doğalgaz alternatifi'],

        ['Doğalgaz Faturasında 12 Pratik Tasarruf Yöntemi','dogalgaz-faturasinda-12-pratik-tasarruf-yontemi',
            'Yıllık doğalgaz faturanızı %30-40 düşürebilecek 12 uygulanabilir teknik. Termostat, izolasyon, bakım vs.',
            '<h2>Doğalgaz Faturasında %30-40 Tasarruf — 12 Pratik Yöntem</h2>
            <p>Doğalgaz fiyatları her yıl artıyor. Ama doğru uygulamalarla mevcut sistemden çok daha fazla verim alabilirsiniz. İşte uzman tavsiyelerimiz:</p>

            <h3>1. Yıllık Kombi Bakımı Yaptırın</h3>
            <p>Bakım yapılmamış kombilerde verim %15-20 düşer. Yıllık 1.500 ₺\'lik bakım, 3.000-5.000 ₺ tasarruf sağlar.</p>

            <h3>2. Akıllı Termostat Kullanın</h3>
            <p>Mevcut "evde-yokum" mantığıyla çalışan akıllı termostat (örn. Tado, Honeywell, Migo Connect) %20\'ye varan tasarruf sağlar. Cihaz maliyeti 1.500-3.000 ₺, geri dönüşü 1 yıl.</p>

            <h3>3. Gece Sıcaklığını 17°C\'ye Düşürün</h3>
            <p>Uyurken yüksek sıcaklık gereksizdir. Her 1°C düşüş %6 tasarruf demektir. Gündüz 20-21°C, gece 17-18°C ideal.</p>

            <h3>4. Pencere ve Kapı İzolasyonunu Kontrol Edin</h3>
            <p>Eski fitiller, sileceler bozulur. 200-500 ₺\'lik fitil değişimi ile %10 tasarruf mümkün.</p>

            <h3>5. Petek Arkasına Alüminyum Folyo</h3>
            <p>Radyatörün arkasına yansıtıcı folyo (10-15 ₺/m) yapıştırarak duvara giden ısının %50\'sini odaya yansıtırsınız.</p>

            <h3>6. Pencere Önlerini Perdeyle Örtün</h3>
            <p>Gece pencerelerden %30 ısı kaybı olur. Kalın perde bunu yarıya indirir.</p>

            <h3>7. Yerden Isıtma İçin Tam Yoğuşmalı Kombi</h3>
            <p>Yerden ısıtmada düşük sıcaklık (35-45°C) çalışıldığı için tam yoğuşmalı kombi maksimum verim verir. Standart yoğuşmalı kombi yerden ısıtmada %15 daha çok gaz tüketir.</p>

            <h3>8. Kombi Su Basıncını Kontrol Edin</h3>
            <p>İdeal: 1-2 bar arası. 0.5 bar altında veya 3 bar üstünde verim ciddi düşer. Aylık kontrol kazandırır.</p>

            <h3>9. Petek Havasını Yılda 1 Kez Alın</h3>
            <p>Peteklerin içindeki hava ısı transferini engeller. Sezon başında havayı almak %10 verim artırır.</p>

            <h3>10. Yalıtım/Mantolama Yatırımı</h3>
            <p>Eski binada mantolama 80-150 ₺/m² maliyetle %50 yakıt tasarrufu sağlar. 100 m² evin yıllık 8.000-12.000 ₺ doğalgaz harcaması ortadan kalkar.</p>

            <h3>11. Kullanım Suyu Sıcaklığını 50°C\'ye Sınırlayın</h3>
            <p>Mutfak ve banyo için 50°C yeterlidir. 60-70°C\'ye çıkarmak gereksiz ve elektrik/gaz harcamasıdır. Aynı zamanda kireç oluşumunu azaltır.</p>

            <h3>12. Tüm Cihazları A++ veya A+++ Yenileyin</h3>
            <p>10+ yıllık eski kombinin yerine yoğuşmalı kombi: Yıllık tasarruf 4.000-7.000 ₺. Standart 10 yıllık klimadan A++ inverter\'a geçiş: Yıllık 2.500-3.500 ₺ tasarruf.</p>

            <h3>Bonus: Tarife Karşılaştırması</h3>
            <p>İzmirgaz farklı tarifelerle çalışır. Ev kullanıcıları için <strong>Konut Tarife II</strong> en uygunudur. Aylık tüketiminize göre uygun tarife belirleyin.</p>

            <h3>Yıllık Tasarruf Hesabı</h3>
            <p>100 m² standart bir daire için yıllık doğalgaz faturası 2026\'da ortalama 18.000-25.000 ₺\'dir. Yukarıdaki tavsiyelerin tümünü uygularsanız yıllık <strong>6.000-10.000 ₺ tasarruf</strong> mümkündür.</p>',
            'doğalgaz tasarruf, kombi bakım, akıllı termostat, fatura'],

        ['Klima Bakımı — Sezona Hazırlık 5 Adımda','klima-bakimi-sezon-hazirligi',
            'Yaz sezonu öncesi klima bakımı kontrol listesi. Filtre temizliği, gaz kontrolü, dış ünite bakımı.',
            '<h2>Klima Bakımı — Yaz Sezonu Hazırlığı</h2>
            <p>Klimanız 6-8 ay kullanılmadan kalmışsa, ilk açılışta sorunlu olabilir. İşte ilkbaharda yapmanız gereken 5 bakım adımı:</p>

            <h3>1. İç Ünite Filtrelerini Temizleyin</h3>
            <p>Filtreyi çıkarın, ılık suda yıkayın, kuruttuktan sonra geri takın. 2 ayda bir tekrarlayın. Kirli filtre %15 verim kaybı + sağlık problemi.</p>

            <h3>2. Dış Ünite Görsel Kontrol</h3>
            <p>Dış ünitenin etrafında yaprak, toz, kuş yuvası olup olmadığını kontrol edin. Hava giriş-çıkışlarını engelleyen şeyleri temizleyin.</p>

            <h3>3. Drenaj Hortumu Kontrolü</h3>
            <p>Tıkalı drenaj hortumu su sızmasına yol açar. Hortumu çıkarın, içine basınçlı su geçirin.</p>

            <h3>4. Yetkili Servis Bakımı (2 Yılda 1)</h3>
            <ul>
                <li>R32/R410A gaz basıncı kontrolü</li>
                <li>Evaporatör temizliği</li>
                <li>Kompresör ses-titreşim testi</li>
                <li>Termostat ve uzaktan kumanda kalibrasyonu</li>
                <li>Kondenser temizliği</li>
            </ul>
            <p>Bakım maliyeti 800-1.200 ₺. 2 yılda bir yeterli.</p>

            <h3>5. Test Çalıştırma</h3>
            <p>Tüm bakımlar bitince klimayı 30 dk soğutmada, 30 dk ısıtmada test edin. Anormal ses, koku, sızıntı varsa servisi çağırın.</p>

            <h3>Acil Durumlar</h3>
            <p>Klimadan su damlıyorsa, tuhaf koku geliyorsa veya verim düştüyse zaman kaybetmeden yetkili servisi arayın. Erken müdahale küçük arızayı büyük masraftan korur.</p>',
            'klima bakımı, klima filtresi, klima servis, sezon hazırlığı'],
    ];

    foreach ($bloglar as [$baslik, $slug, $ozet, $icerik, $etiketler]) {
        try {
            $exists = db_get("SELECT id FROM blog_yazilari WHERE slug=?", [$slug]);
            if ($exists) continue;
            db_run("INSERT INTO blog_yazilari (baslik, slug, ozet, icerik, yazar, etiketler, aktif, yayin_tarihi) VALUES (?, ?, ?, ?, 'Azra Doğalgaz Uzman Ekibi', ?, 1, ?)",
                [$baslik, $slug, $ozet, $icerik, $etiketler, date('Y-m-d H:i:s')]);
            $sayilar['blog']++;
        } catch (Throwable $e) { $hatalar[] = "Blog $baslik: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['blog']}</strong> yeni blog yazısı eklendi.";

    // =========================================================================
    // 7) PROJELER (örnek demo proje girişi)
    // =========================================================================
    $projeler = [
        ['Bornova Konut Sitesi — 48 Daire Doğalgaz Tesisat','bornova-konut-sitesi-dogalgaz',
            'Doğalgaz Tesisatı','Bornova/İzmir','2026-03',
            '48 daireli yeni konut sitesinde komple doğalgaz tesisat işi. Kolon hatları, sayaç odası, daire içi tesisatlar tamamlandı, İzmirgaz onayı alındı, gaz arzı sağlandı.',
            '<p>Bornova\'da inşaatı tamamlanan 48 daireli konut sitesinin komple doğalgaz tesisat projesini Şubat-Mart 2026\'da tamamladık.</p><h3>Kapsam</h3><ul><li>Sayaç odası tesisatı</li><li>Kolon hatları (8 katlı 6 blok)</li><li>Daire içi tesisatları (48 daire × ortalama 2 noktası)</li><li>Sızdırmazlık testleri</li><li>İzmirgaz onayı + toplu gaz açma</li></ul><p><strong>Süre:</strong> 25 iş günü</p>'],

        ['Karşıyaka Dubleks Villa — Yerden Isıtma + Kombi','karsiyaka-dubleks-villa-yerden-isitma',
            'Yerden Isıtma','Karşıyaka/İzmir','2026-02',
            '180 m² dubleks villaya komple yerden ısıtma sistemi + Demirdöküm Nitromix Ioni 28 kW kombi. PEX boru sistemi, kollektör, kablosuz oda termostatları.',
            '<p>Karşıyaka\'da 180 m² (90 m²×2 kat) dubleks villaya yerden ısıtma + premium kombi projesi.</p><h3>Sistem</h3><ul><li>Demirdöküm Nitromix Ioni 28 kW Yoğuşmalı Kombi</li><li>PEX 16×2 mm boru (toplam 1.350 m)</li><li>2 kat × 4 oda kollektör (8 zone)</li><li>Kablosuz oda termostatları</li><li>EPS izolasyon plakaları</li></ul>'],

        ['Buca Apartman Dairesi — Demirdöküm Ademix Kombi Montajı','buca-apartman-demirdokum-ademix',
            'Kombi Montajı','Buca/İzmir','2026-04',
            '120 m² dairede eski hermetik kombiden Demirdöküm Ademix 24/28 kW yoğuşmalı kombiye dönüşüm. 6 saatte tamamlandı.',
            '<p>Buca\'da 120 m² apartman dairesinde 12 yıllık eski hermetik kombinin yerine Demirdöküm Ademix P 24/28 kW tam yoğuşmalı kombi montajı.</p>'],

        ['Çiğli Ofis Binası — Multi Split Klima Sistemi','cigli-ofis-binasi-multi-split-klima',
            'Klima Montajı','Çiğli/İzmir','2026-04',
            '450 m² ofis binası — 8 iç ünite, 2 dış ünite multi split klima sistemi. Mitsubishi Heavy SRK serisi.',
            '<p>Çiğli\'de 450 m² ofis binasının komple klima sistemi. Toplam 8 iç ünite ve 2 dış üniteden oluşan multi split sistem.</p>'],

        ['Konak Restoran — Havalandırma + Davlumbaz','konak-restoran-havalandirma',
            'Havalandırma','Konak/İzmir','2026-03',
            '180 m² restoranda komple havalandırma, ısı geri kazanım sistemi, mutfak davlumbazı.',
            '<p>Konak\'ta yeni açılan restoran için komple mekanik havalandırma sistemi: ısı geri kazanım üniteli klimatizasyon, mutfak davlumbazı, salon havalandırma.</p>'],

        ['Gaziemir Müstakil Ev — Komple Mekanik Tesisat','gaziemir-mustakil-ev-mekanik-tesisat',
            'Mekanik Tesisat','Gaziemir/İzmir','2026-02',
            '160 m² müstakil ev — sıhhi tesisat + doğalgaz + yerden ısıtma + klima — anahtar teslim mekanik tesisat.',
            '<p>Gaziemir\'de 160 m² müstakil evde komple mekanik tesisat: sıhhi tesisat, doğalgaz hattı, yerden ısıtma sistemi, multi split klima. 35 iş gününde tamamlandı.</p>'],
    ];

    // projeler tablosu var mı kontrol
    try {
        db_run("SELECT 1 FROM projeler LIMIT 1");
        $projeler_var = true;
    } catch (Throwable $e) {
        $projeler_var = false;
        $hatalar[] = "⚠️ projeler tablosu yok — önce /migrate.php çalıştırılmalı.";
    }

    if ($projeler_var) {
        foreach ($projeler as [$baslik, $slug, $kategori, $lokasyon, $tarih, $ozet, $icerik]) {
            try {
                $exists = db_get("SELECT id FROM projeler WHERE slug=?", [$slug]);
                if ($exists) continue;
                db_run("INSERT INTO projeler (slug, baslik, kategori, ozet, icerik, lokasyon, tarih, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                    [$slug, $baslik, $kategori, $ozet, $icerik, $lokasyon, $tarih . '-01']);
                $sayilar['projeler']++;
            } catch (Throwable $e) { $hatalar[] = "Proje $baslik: " . $e->getMessage(); }
        }
        $mesajlar[] = "✅ <strong>{$sayilar['projeler']}</strong> yeni proje eklendi.";
    }

    // =========================================================================
    // 8) AYARLAR — KVKK & Gizlilik metinleri (DB'de yoksa default)
    // =========================================================================
    $varsayilan_ayarlar = [
        'firma_adres' => 'İzmir',
        'firma_calisma_saatleri' => 'Pazartesi-Cumartesi 08:00-20:00 / Pazar 09:00-18:00',
        'meta_anahtar_kelime' => 'izmir doğalgaz, demirdöküm yetkili bayi, kombi montaj izmir, klima izmir, yerden ısıtma izmir, izmirgaz onaylı tesisat',
    ];
    foreach ($varsayilan_ayarlar as $k => $v) {
        try {
            $exists = db_get("SELECT deger FROM ayarlar WHERE anahtar=?", [$k]);
            if (!$exists || empty($exists['deger'])) {
                db_run("INSERT INTO ayarlar (anahtar, deger) VALUES (?, ?) ON DUPLICATE KEY UPDATE deger=VALUES(deger)", [$k, $v]);
                $sayilar['ayarlar']++;
            }
        } catch (Throwable $e) { /* sessizce geç */ }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['ayarlar']}</strong> ayar default değerle dolduruldu.";

    // Kilit dosyası
    @file_put_contents($kilit, "v1.4.4 seed tamamlandı: " . date('Y-m-d H:i:s') . "\n" . json_encode($sayilar, JSON_UNESCAPED_UNICODE));
    $mesajlar[] = "🔒 Seed tamamlandı, kilit dosyası oluşturuldu: <code>.seed-1.4.4.lock</code>";
    }
}

$kilit_var = file_exists($kilit);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>İçerik Yükleme — v1.4.4 — Azra Doğalgaz</title>
<style>
    body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 40px 20px; line-height: 1.6; }
    .box { max-width: 820px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 12px rgba(15,23,42,.05); }
    h1 { font-size: 1.7rem; margin-bottom: 8px; color: #ff6b00; }
    .lead { color: #64748b; margin-bottom: 28px; }
    h2 { font-size: 1.2rem; margin: 28px 0 12px; color: #0f172a; border-bottom: 2px solid #fed7aa; padding-bottom: 6px; }
    .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 10px; font-size: .92rem; line-height: 1.6; }
    .alert-ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-info { background: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; }
    .alert-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    code { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: .9em; }
    .btn { display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #ff6b00, #f97316); color: #fff !important; border-radius: 50px; font-weight: 600; text-decoration: none; border: 0; cursor: pointer; font-size: 1rem; }
    .btn-out { background: #fff; color: #0f172a !important; border: 1px solid #e2e8f0; padding: 12px 24px; }
    ul { padding-left: 22px; margin: 14px 0; }
    li { margin-bottom: 6px; font-size: .95rem; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin: 24px 0 }
    .stat { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; padding: 14px; text-align: center }
    .stat .num { font-size: 1.6rem; font-weight: 800; color: #c2410c; }
    .stat .lbl { font-size: .75rem; color: #92400e; text-transform: uppercase; letter-spacing: .5px }
</style>
</head>
<body>
<div class="box">
    <h1>📚 İçerik Yükleme — Sektörel Veri Seed v1.4.4</h1>
    <p class="lead">Sektörel araştırma ile hazırlanmış gerçek ve güncel içerikleri DB'ye otomatik yükler. Mevcut kayıtlar korunur, sadece eksikler eklenir.</p>

    <?php foreach ($mesajlar as $m): ?>
        <div class="alert alert-ok"><?= $m ?></div>
    <?php endforeach; ?>
    <?php foreach ($hatalar as $h): ?>
        <div class="alert alert-err"><?= $h ?></div>
    <?php endforeach; ?>

    <?php if (empty($_POST['calistir'])): ?>

        <h2>Yapılacaklar</h2>
        <p>Aşağıdaki sektörel araştırma sonucu hazırlanan içerikler veritabanına eklenecek:</p>

        <div class="stats">
            <div class="stat"><div class="num">15</div><div class="lbl">Marka</div></div>
            <div class="stat"><div class="num">10</div><div class="lbl">Ürün Kategorisi</div></div>
            <div class="stat"><div class="num">12</div><div class="lbl">Detaylı Hizmet</div></div>
            <div class="stat"><div class="num">18</div><div class="lbl">Ürün</div></div>
            <div class="stat"><div class="num">4</div><div class="lbl">Kampanya</div></div>
            <div class="stat"><div class="num">7</div><div class="lbl">Blog Yazısı</div></div>
            <div class="stat"><div class="num">6</div><div class="lbl">Demo Proje</div></div>
        </div>

        <h2>Kapsam</h2>
        <ul>
            <li><strong>15 Marka:</strong> Demirdöküm, Bosch, Vaillant, Buderus, ECA, Baymak, Ariston, Daikin, Mitsubishi Electric, Mitsubishi Heavy, LG, Samsung, Vestel, Arçelik, Beko</li>
            <li><strong>10 Ürün Kategorisi:</strong> Yoğuşmalı/Hermetik Kombi, Inverter/Salon/Multi Split Klima, Isı Pompası, Yerden Isıtma, Radyatör, Termosifon, Tesisat Malzemesi</li>
            <li><strong>12 Detaylı Hizmet:</strong> İzmirgaz onaylı doğalgaz, dönüşüm, apartman tesisat, Demirdöküm yetkili kombi montajı, yıllık bakım, arıza servisi, inverter klima, multi split, yerden ısıtma, ısı pompası, sıhhi tesisat</li>
            <li><strong>18 Ürün:</strong> Demirdöküm Ademix/Atron/Vintomix/Nitromix kombiler, Bosch/Vaillant/ECA premium kombiler, Daikin/Mitsubishi/Bosch/LG/Vestel/ECA inverter klimalar, Bosch/Daikin ısı pompası — <strong>2026 Q1 güncel piyasa fiyatlarıyla</strong></li>
            <li><strong>4 Kampanya:</strong> Demirdöküm Ademix paketi (peşin 80K / 6 taksit 87K), Yerden ısıtma + kombi paketi, 12.000 BTU klima paketi, Apartman dönüşüm paketi</li>
            <li><strong>7 Blog Yazısı:</strong> İzmirgaz aboneliği rehberi, kombi seçimi, inverter vs standart klima, yerden ısıtma vs radyatör, ısı pompası rehberi, doğalgaz tasarrufu, klima bakımı (her biri 800-1500 kelime, SEO odaklı)</li>
            <li><strong>6 Demo Proje:</strong> Bornova konut sitesi, Karşıyaka villa, Buca daire, Çiğli ofis, Konak restoran, Gaziemir müstakil ev</li>
        </ul>

        <div class="alert alert-info">
            <strong>ℹ️ Önemli:</strong>
            <ul>
                <li>Mevcut kayıtlar <strong>korunur</strong>, sadece eksikler eklenir (slug bazlı kontrol).</li>
                <li>Bu işlem geri alınamaz — eklenen kayıtları admin panelinden tek tek silmeniz gerekir.</li>
                <li>Projeler tablosu için önce <code>/migrate.php</code> çalıştırılmış olmalı.</li>
            </ul>
        </div>

        <?php if ($kilit_var && empty($_GET['zorla'])): ?>
            <div class="alert alert-warn">
                ⚠️ Bu seed daha önce çalıştırılmış. Yine de çalıştırmak için (mevcut kayıtlar korunacak):<br><br>
                <a href="?zorla=1" class="btn btn-out">Zorla Çalıştır</a>
                <a href="<?= SITE_URL ?>/admin/" class="btn btn-out" style="margin-left:8px">Admin Paneli</a>
            </div>
        <?php else: ?>
            <form method="post" style="margin-top:24px">
                <?= csrf_field() ?>
                <input type="hidden" name="calistir" value="1">
                <button class="btn" type="submit">▶️ İçerik Seed'i Çalıştır</button>
                <a href="<?= SITE_URL ?>/admin/" class="btn btn-out" style="margin-left:8px">İptal</a>
            </form>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info" style="margin-top:24px;text-align:center">
            ✅ Seed işlemi tamamlandı.<br><br>
            <a href="<?= SITE_URL ?>/" class="btn">Siteyi Görüntüle</a>
            <a href="<?= SITE_URL ?>/admin/" class="btn btn-out" style="margin-left:8px">Admin Paneli</a>
        </div>
    <?php endif; ?>

    <hr style="margin:32px 0;border:0;border-top:1px solid #e2e8f0">
    <p style="font-size:.82rem;color:#94a3b8">
        <strong>Güvenlik notu:</strong> Bu dosya bir kez çalıştıktan sonra <code>.seed-1.4.4.lock</code> dosyası oluşturur. Tekrar çalıştırmak için <code>?zorla=1</code> parametresi eklenmeli veya kilit dosyası silinmelidir.
    </p>
</div>
</body>
</html>
