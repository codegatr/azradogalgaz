<?php
/**
 * Sektörel İçerik Seed Aracı — v1.5.0
 *
 * Özellikler:
 * 1. projeler tablosu YOKSA otomatik oluşturulur (migrate.php gerekmez)
 * 2. Mevcut ürün/hizmet/kampanya/blog'larda görsel boşsa GERÇEK Unsplash URL'leriyle doldurulur
 * 3. Yeni kayıtlar görsel URL'leriyle birlikte eklenir
 * 4. Slug bazlı upsert — mevcut başlık/içerik korunur, sadece eksikler eklenir
 * 5. .seed-1.5.0.lock kilit dosyası ile tekrar çalıştırma kontrolü
 */
require_once __DIR__ . '/config.php';

if (!admin_giris_var()) {
    header('Location: ' . SITE_URL . '/admin/?bilgi=' . urlencode('Önce admin girişi yapın') . '&donus=' . urlencode('/seed.php'));
    exit;
}

$kilit = __DIR__ . '/.seed-1.5.0.lock';
$mesajlar = [];
$hatalar  = [];
$sayilar  = [
    'projeler_tablo'=>0,'markalar'=>0,'urun_kat'=>0,'hizmetler'=>0,
    'urunler_yeni'=>0,'urunler_gorsel'=>0,
    'kampanyalar'=>0,'kampanyalar_gorsel'=>0,
    'blog'=>0,'blog_gorsel'=>0,
    'projeler'=>0,'hizmetler_gorsel'=>0,'ayarlar'=>0
];

// =============================================================================
// GÖRSEL URL'LERİ — Unsplash semantik aramalar
// Format: https://source.unsplash.com/featured/800x600/?KEYWORDS
// Source.unsplash.com hotlinkable, free, kararlı — keyword'e uygun random getirir.
// Tutarlılık için picsum.photos da kullanılabilir (slug bazlı).
// =============================================================================
function img($keywords, $w = 800, $h = 600) {
    return "https://source.unsplash.com/featured/{$w}x{$h}/?" . urlencode($keywords);
}
function img_seed($seed, $w = 800, $h = 600) {
    // Tutarlı (slug aynıysa aynı resim)
    return "https://picsum.photos/seed/{$seed}/{$w}/{$h}";
}

if (!empty($_POST['calistir'])) {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $hatalar[] = '❌ CSRF doğrulama hatası, sayfayı yenileyip tekrar deneyin.';
    } else {

    // =========================================================================
    // 0) PROJELER TABLOSU — otomatik oluştur
    // =========================================================================
    try {
        db()->query("SELECT 1 FROM projeler LIMIT 1");
    } catch (Throwable $e) {
        try {
            db()->query("CREATE TABLE IF NOT EXISTS `projeler` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `slug` VARCHAR(220) NOT NULL UNIQUE,
                `baslik` VARCHAR(220) NOT NULL,
                `kategori` VARCHAR(120) NULL,
                `ozet` VARCHAR(500) NULL,
                `icerik` MEDIUMTEXT NULL,
                `gorsel` VARCHAR(500) NULL,
                `galeri` MEDIUMTEXT NULL,
                `lokasyon` VARCHAR(160) NULL,
                `tarih` DATE NULL,
                `sira` INT DEFAULT 0,
                `aktif` TINYINT(1) DEFAULT 1,
                `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_aktif` (`aktif`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $sayilar['projeler_tablo'] = 1;
            $mesajlar[] = "✅ <strong>projeler</strong> tablosu otomatik oluşturuldu.";
        } catch (Throwable $e2) {
            $hatalar[] = "⚠️ projeler tablosu oluşturulamadı: " . $e2->getMessage();
        }
    }

    // =========================================================================
    // 1) MARKALAR
    // =========================================================================
    $markalar_arr = [
        ['Demirdöküm','demirdokum'], ['Bosch','bosch'], ['Vaillant','vaillant'],
        ['Buderus','buderus'], ['ECA','eca'], ['Baymak','baymak'],
        ['Ariston','ariston'], ['Daikin','daikin'], ['Mitsubishi Electric','mitsubishi-electric'],
        ['Mitsubishi Heavy','mitsubishi-heavy'], ['LG','lg'], ['Samsung','samsung'],
        ['Vestel','vestel'], ['Arçelik','arcelik'], ['Beko','beko'],
    ];
    foreach ($markalar_arr as [$ad, $slug]) {
        try {
            $ex = db_get("SELECT id FROM markalar WHERE slug=?", [$slug]);
            if (!$ex) {
                db_run("INSERT INTO markalar (ad, slug, aktif) VALUES (?, ?, 1)", [$ad, $slug]);
                $sayilar['markalar']++;
            }
        } catch (Throwable $e) { $hatalar[] = "Marka $ad: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['markalar']}</strong> yeni marka eklendi.";
    $marka_id = [];
    foreach (db_all("SELECT id, slug FROM markalar") as $m) $marka_id[$m['slug']] = (int)$m['id'];

    // =========================================================================
    // 2) ÜRÜN KATEGORİLERİ
    // =========================================================================
    $urun_kat_arr = [
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
    foreach ($urun_kat_arr as [$ad, $slug, $sira]) {
        try {
            $ex = db_get("SELECT id FROM urun_kategorileri WHERE slug=?", [$slug]);
            if (!$ex) {
                db_run("INSERT INTO urun_kategorileri (ad, slug, sira, aktif) VALUES (?, ?, ?, 1)", [$ad, $slug, $sira]);
                $sayilar['urun_kat']++;
            }
        } catch (Throwable $e) { $hatalar[] = "Ürün kategori $ad: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['urun_kat']}</strong> yeni ürün kategorisi eklendi.";
    $kat_id = [];
    foreach (db_all("SELECT id, slug FROM urun_kategorileri") as $k) $kat_id[$k['slug']] = (int)$k['id'];

    // =========================================================================
    // 3) MEVCUT ÜRÜNLERE GÖRSEL EKLE (gorsel boş olanları UPDATE)
    // =========================================================================
    // Slug bazlı görsel haritası
    $urun_gorsel_map = [
        // KOMBİLER — boiler, heating, gas, modern teması
        'demirdokum-ademix-p-18-24-kw'        => img('boiler,heating,modern,gas'),
        'demirdokum-ademix-p-24-24-kw'        => img('boiler,heating,wall,modern'),
        'demirdokum-ademix-p-24-28-kw'        => img('combi,boiler,gas,heating'),
        'demirdokum-atron-condense-p24'       => img('boiler,gas,heating,wall'),
        'demirdokum-vintomix-24-28'           => img('boiler,heating,white'),
        'demirdokum-nitromix-ioni-24'         => img('boiler,premium,heating,modern'),
        'bosch-condens-5700i-24'              => img('boiler,bosch,heating,modern'),
        'vaillant-ecotec-plus-24'             => img('boiler,vaillant,premium,heating'),
        'eca-confeo-premix-24'                => img('boiler,gas,heating,turkish'),
        // KLİMALAR
        'daikin-sensira-atxf35f-12000-btu'    => img('air,conditioner,split,modern'),
        'mitsubishi-heavy-srk35zsp-12000'     => img('air,conditioner,wall,modern'),
        'mitsubishi-heavy-srk50zs-18000'      => img('air,conditioner,split,living'),
        'bosch-climate-5000-12000'            => img('air,conditioner,bosch,wall'),
        'lg-dualcool-plus-18000'              => img('air,conditioner,lg,modern'),
        'vestel-inverter-12000'               => img('air,conditioner,wall,white'),
        'eca-spylos-9000'                     => img('air,conditioner,bedroom,small'),
        // ISI POMPASI
        'bosch-cs3400i-8kw'                   => img('heat,pump,outdoor,modern'),
        'daikin-altherma-3-11kw'              => img('heat,pump,daikin,system'),
    ];

    foreach ($urun_gorsel_map as $slug => $gorsel) {
        try {
            $u = db_get("SELECT id, gorsel FROM urunler WHERE slug=?", [$slug]);
            if ($u && empty($u['gorsel'])) {
                db_run("UPDATE urunler SET gorsel=? WHERE id=?", [$gorsel, $u['id']]);
                $sayilar['urunler_gorsel']++;
            }
        } catch (Throwable $e) { $hatalar[] = "Ürün gorsel $slug: " . $e->getMessage(); }
    }
    if ($sayilar['urunler_gorsel'] > 0) {
        $mesajlar[] = "🖼️ <strong>{$sayilar['urunler_gorsel']}</strong> mevcut ürüne görsel eklendi.";
    }

    // =========================================================================
    // 4) YENİ ÜRÜNLER (mevcut DB'de yoksa eklenir, görselle birlikte)
    // =========================================================================
    $urunler_yeni = [
        // Format: [kat, marka, ad, slug, sku, kisa, aciklama, fiyat, indirimli, one_cikan, stok, gorsel]
        ['hermetik-kombi','demirdokum','Demirdöküm Atromix P24 kW Hermetik Yoğuşmalı','demirdokum-atromix-p24',
            'DEMIRDOKUM-ATROMIX-P24',
            'Hermetik baca uyumlu, 24 kW yoğuşmalı, ekonomik segment.',
            '<p>Demirdöküm Atromix serisi, hermetik baca uyumlu, 24 kW kapasiteli yoğuşmalı kombidir. 100-120 m² evler için ideal.</p>',
            33165, 31825, 0, 1, img('combi,gas,boiler,white')],
        ['hermetik-kombi','demirdokum','Demirdöküm Nitromix P28 Hermetik Yoğuşmalı','demirdokum-nitromix-p28',
            'DEMIRDOKUM-NITROMIX-P28',
            'Premium 28 kW hermetik, Vaillant teknolojisi, 130-160 m² ev.',
            '<p>Nitromix premium serisi 28 kW. Vaillant ortaklığı ile çelik eşanjör, 1:10 modülasyon.</p>',
            40054, 38436, 0, 0, img('boiler,premium,vaillant,heating')],
        ['hermetik-kombi','demirdokum','Demirdöküm Nitromix P35 Hermetik','demirdokum-nitromix-p35',
            'DEMIRDOKUM-NITROMIX-P35',
            'Büyük evler için 35 kW. 180+ m² dubleks villalar için.',
            '<p>35 kW yüksek kapasite, dubleks villa ve büyük daireler için. Yerden ısıtma + radyatör kombineli sistemlerde ideal.</p>',
            46061, 44200, 0, 0, img('boiler,large,villa,heating')],
        // Klima eklentileri
        ['inverter-klima','samsung','Samsung WindFree AR12 12000 BTU','samsung-windfree-ar12-12000',
            'SAMSUNG-WINDFREE-AR12',
            'Samsung WindFree — direkt üfleme yok, sessiz çalışma, A++ inverter.',
            '<p>Samsung\'un öne çıkan WindFree teknolojisi: doğrudan rüzgar üflemez, mikro deliklerden yumuşak hava dağılımı sağlar. WiFi kontrol, AI mode, A++ enerji.</p>',
            38500, 35900, 0, 1, img('air,conditioner,samsung,modern')],
        ['inverter-klima','arcelik','Arçelik 12030 KMS A++ Inverter Klima','arcelik-12030-kms-12000',
            'ARCELIK-12030-KMS',
            'Yerli üretim, 12000 BTU, A++ inverter, geniş Arçelik servis ağı.',
            '<p>Arçelik\'in 12.000 BTU inverter klimasi, yerli üretim avantajı ve Türkiye genelinde 1500+ servis noktasıyla güvenli tercih.</p>',
            24500, 22900, 0, 1, img('air,conditioner,turkish,modern')],
        // Yerden ısıtma malzemeleri
        ['yerden-isitma-sistemi','eca','ECA Yerden Isıtma PEX Borusu 16x2 mm (200m rulo)','eca-yerden-isitma-pex-borusu',
            'ECA-PEX-16x2-200',
            'Oksijen bariyerli PEX boru, 50 yıl ömür, yerden ısıtma için.',
            '<p>ECA PEX boru sistemleri 16×2 mm, oksijen bariyerli, yerden ısıtma sistemleri için optimize edilmiş. 50 yıl üretici garantisi, EN ISO 15875 standardı.</p>',
            12500, 11500, 0, 1, img('underfloor,heating,pipe,construction')],
        // Radyatör
        ['radyator','demirdokum','Demirdöküm Panel Radyatör 600x1000 mm','demirdokum-panel-radyator-600x1000',
            'DEMIRDOKUM-PR-600x1000',
            'Yüksek verim panel radyatör, 600×1000 mm, 1450 W ısıtma gücü.',
            '<p>Demirdöküm yerli üretim panel radyatör. 600×1000 mm boyut, 22 tip, 1450 W ısıtma kapasitesi. 10 yıl üretici garantisi.</p>',
            3850, 3450, 0, 1, img('radiator,heating,white,modern')],
        // Termosifon
        ['termosifon-sofben','demirdokum','Demirdöküm DT4 Titanium 65 lt Termosifon','demirdokum-dt4-titanium-65',
            'DEMIRDOKUM-DT4-65',
            '65 lt titanyum kaplama termosifon, 1800 W, A enerji sınıfı.',
            '<p>Demirdöküm DT4 Titanium serisi. 65 lt kapasite, titanyum iç kaplama (kireç tutmaz), 1800 W ısıtma gücü, A enerji sınıfı, 5 yıl tank garantisi.</p>',
            12300, 11737, 0, 1, img('water,heater,boiler,white')],
    ];

    foreach ($urunler_yeni as [$kat, $mar, $ad, $slug, $sku, $kisa, $acik, $fiyat, $indirim, $one, $stok, $gor]) {
        try {
            $ex = db_get("SELECT id FROM urunler WHERE slug=?", [$slug]);
            if ($ex) continue;
            $kid = $kat_id[$kat] ?? null;
            $mid = $marka_id[$mar] ?? null;
            db_run("INSERT INTO urunler (kategori_id, marka_id, ad, slug, sku, kisa_aciklama, aciklama, fiyat, indirimli_fiyat, kdv_orani, stok, gorsel, one_cikan, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 20, ?, ?, ?, 1)",
                [$kid, $mid, $ad, $slug, $sku, $kisa, $acik, $fiyat, $indirim, $stok, $gor, $one]);
            $sayilar['urunler_yeni']++;
        } catch (Throwable $e) { $hatalar[] = "Yeni ürün $ad: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['urunler_yeni']}</strong> yeni ürün eklendi (toplam stoklara katkı).";

    // =========================================================================
    // 5) HİZMETLER — yeni ekleme + mevcut hizmet kategorileri
    // =========================================================================
    $hizmet_kat_id = [];
    foreach (db_all("SELECT id, slug FROM hizmet_kategorileri") as $k) $hizmet_kat_id[$k['slug']] = (int)$k['id'];

    // Eksik kategori varsa ekle (DB'de 4 kategori var, bizim seedte 12 hizmet kategori bekliyor olabilir)
    $eksik_hizmet_kat = [
        ['yerden-isitma','Yerden Isıtma','temperature-arrow-up',50],
        ['havalandirma','Havalandırma Tesisatı','wind',60],
        ['sihhi-tesisat','Sıhhi Tesisat','faucet',70],
        ['isi-pompasi','Isı Pompası','arrows-rotate',90],
    ];
    foreach ($eksik_hizmet_kat as [$slug, $ad, $ikon, $sira]) {
        try {
            $ex = db_get("SELECT id FROM hizmet_kategorileri WHERE slug=?", [$slug]);
            if (!$ex) {
                db_run("INSERT INTO hizmet_kategorileri (ad, slug, ikon, sira, aktif) VALUES (?, ?, ?, ?, 1)", [$ad, $slug, $ikon, $sira]);
            }
        } catch (Throwable $e) {}
    }
    // Yeniden yükle
    $hizmet_kat_id = [];
    foreach (db_all("SELECT id, slug FROM hizmet_kategorileri") as $k) $hizmet_kat_id[$k['slug']] = (int)$k['id'];

    $hizmetler = [
        // Doğalgaz Tesisatı
        ['dogalgaz-tesisati','Doğalgaz Tesisatı','dogalgaz-tesisati',
            'Uzman ekiple projelendirme, tesisat döşeme, sızdırmazlık testi ve gaz açma uçtan uca hizmet.',
            '<h2>Doğalgaz Tesisatı</h2><p><strong>Azra Doğalgaz</strong>, bölgenin deneyimli doğalgaz tesisat firmalarından biridir. Yeni doğalgaz aboneliği, kombi dönüşümü veya mevcut tesisat yenileme işlemlerini yerel dağıtım şirketi mevzuatına %100 uygun şekilde gerçekleştiririz.</p><h3>Süreç (Toplam 7-10 İş Günü)</h3><ol><li>Ücretsiz keşif ve yazılı teklif</li><li>Yetkili makine mühendisi proje çizimi</li><li>dağıtım şirketi online onay süreci (2-5 iş günü)</li><li>Tesisat döşeme (1-2 iş günü)</li><li>Sızdırmazlık testi (basınç testi)</li><li>dağıtım şirketi teknik kontrolü + gaz arzı</li><li>Doğal Gaz Uygunluk Belgesi tanzimi</li></ol><h3>Kullanılan Malzemeler</h3><ul><li>TS EN 15266 standartlarına uygun çelik veya gasfil paslanmaz boru</li><li>standartlara uygun küresel vana ve sayaç odası fitingleri</li><li>2 yıl işçilik garantisi, sigortalı uygulama</li></ul>',
            10, img('gas,pipes,plumbing,installation')],

        ['dogalgaz-tesisati','Doğalgaz Dönüşüm Hizmeti','dogalgaz-donusum-hizmeti',
            'Soba, kalorifer, LPG sisteminden doğalgaza geçiş. Yıllık %30-50 yakıt tasarrufu sağlayan dönüşüm.',
            '<h2>Doğalgaz Dönüşümü</h2><p>Mevcut sistemleriniz (kömür sobası, kat kaloriferi, LPG\'li tüplü sistem, fuel-oil kazan) doğalgaza dönüştürülebilir. Yıllık ortalama %35 yakıt tasarrufu sağlar.</p><h3>Avantajları</h3><ul><li>Yıllık %30-50 yakıt tasarrufu</li><li>Daha temiz, kül-duman olmayan ısınma</li><li>İstediğiniz odayı bireysel ısıtma imkanı</li><li>24 saat sıcak su konforu</li><li>Otomatik termostat kontrolü</li></ul><h3>Tipik Maliyet (100 m² daire, 2026)</h3><ul><li>Doğalgaz tesisatı (proje + döşeme + gaz açma): 25.000-45.000 ₺</li><li>Yoğuşmalı kombi (Demirdöküm Ademix 24kW): 27.000-35.000 ₺</li><li>Toplam dönüşüm: 70.000-120.000 ₺</li></ul>',
            20, img('gas,heating,conversion,modern')],

        ['dogalgaz-tesisati','Apartman Toplu Doğalgaz Tesisat','apartman-toplu-dogalgaz-tesisat',
            'Çok daireli binalarda toplu yenileme. Kolon hatları, dış ünite, her daire bireysel kombi.',
            '<h2>Apartman Doğalgaz Yenileme</h2><p>Apartmanınızda kat kaloriferi (merkezi sistem) varsa veya mevcut tesisat eskiyse, tüm bina için toplu doğalgaz yenileme projesi hazırlıyoruz.</p><h3>Yönetici İçin Avantajlar</h3><ul><li>Karar defteri için karar metni desteği</li><li>Tüm daireler tek koordinatör</li><li>%15-20 toplu fiyat avantajı</li><li>Hatalı eski tesisatın güvenli sökülmesi</li></ul><p>Apartman ölçeğinde tipik süre 15-25 iş günü.</p>',
            30, img('apartment,building,gas,construction')],

        // Kombi Servisi
        ['kombi-servisi','Demirdöküm Yetkili Kombi Montajı','demirdokum-yetkili-kombi-montaji',
            'Demirdöküm Ademix, Atron, Atromix, Vintomix, Nitromix yetkili bayi montajı. 2 yıl işçilik + üretici garantisi.',
            '<h2>Demirdöküm Yetkili Kombi Montajı</h2><p>Azra Doğalgaz, Demirdöküm yetkili bayisidir. Yetkili bayi montajı, üretici garantisinin geçerliliği için zorunludur.</p><h3>Hizmet Kapsamı</h3><ul><li>Eski kombi sökümü ve nakliye</li><li>Doğalgaz, su, baca bağlantıları</li><li>İlk devreye alma ve test</li><li>Kullanım eğitimi</li><li>2 yıl işçilik + 5 yıl eşanjör garantisi</li></ul><h3>Stoğumuzdaki Modeller (2026)</h3><ul><li>Ademix P 18/24 kW (~30K) - 80-100 m²</li><li>Ademix P 24/28 kW (~32K) - 100-130 m² (en yaygın)</li><li>Atron Condense P24 (~27K) - en uygun fiyatlı</li><li>Nitromix Ioni 24 (~38K) - premium çelik eşanjör</li></ul>',
            10, img('boiler,installation,technician,modern')],

        ['kombi-servisi','Yıllık Kombi Bakımı','yillik-kombi-bakimi',
            'Yetkili servis kalitesinde kombi bakım. Eşanjör temizliği, brülör kontrol, baca gazı analizi.',
            '<h2>Yıllık Kombi Bakımı</h2><p>Yıllık bakım kombinizin verimini, ömrünü ve güvenliğini doğrudan etkiler. İhmal edilen kombilerde verim %15-20 düşer.</p><h3>Bakım Kapsamı</h3><ul><li>Brülör temizliği ve ayarı</li><li>Eşanjör temizliği (kireç ve kurum)</li><li>Baca gazı analizi (CO, CO₂, O₂)</li><li>Hava-gaz karışım kontrolü</li><li>Genleşme tankı basınç kontrolü</li><li>Filtre temizliği</li><li>Pompa, emniyet ventili testi</li><li>Yazılı bakım raporu</li></ul><p><strong>Maliyet:</strong> 1.200-1.800 ₺. Üretici garantisi geçerliliği için yıllık bakım zorunlu.</p>',
            20, img('technician,maintenance,boiler,repair')],

        ['kombi-servisi','Kombi Arıza ve Acil Servis','kombi-ariza-acil-servis',
            'Aynı gün servis. Hata kodu çözümü, sıcak su gelmemesi, basınç problemleri.',
            '<h2>Kombi Arıza Servisi</h2><p>Aynı gün servis politikamız: çağrı saatinden 2-4 saat içinde teknisyen adresinizde.</p><h3>Sık Karşılaşılan Arızalar</h3><ul><li><strong>E1, E2, E3 hatası:</strong> Tesisat basıncı düşük (1-2 bar olmalı)</li><li><strong>F37:</strong> Kombi suyu eklenmesi gerekli</li><li><strong>Sıcak su gelmiyor:</strong> Plakalı eşanjör tıkanmış</li><li><strong>Sürekli açıp kapanıyor:</strong> Modülasyon arızası</li><li><strong>Petek soğuk:</strong> Hava kalmış (havasını alın)</li></ul><p><strong>UYARI:</strong> Gaz kokusunda <strong>187 Doğalgaz Acil</strong>\'i arayın.</p>',
            30, img('repair,emergency,technician,boiler')],

        // Klima Montajı
        ['klima-montaji','Inverter Klima Satış ve Montajı','inverter-klima-satis-ve-montaji',
            'Daikin, Mitsubishi, Bosch, LG, Samsung, Vestel, Arçelik inverter klima satış + profesyonel montaj.',
            '<h2>Inverter Klima Montajı</h2><p>Çoklu marka klima satışı, montaj ve garanti dahil. Profesyonel teknisyen montajı garanti geçerliliği için zorunludur.</p><h3>Montaj Kapsamı</h3><ul><li>Adres keşfi (klima konumu, kablo, dış ünite)</li><li>İç ve dış ünite duvar montajı</li><li>Bakır boru tesisatı (max 5 m standart)</li><li>Drenaj tesisatı</li><li>Vakum çekme + R32 gaz dolumu</li><li>Test ve devreye alma</li><li>2 yıl işçilik garantisi</li></ul>',
            10, img('air,conditioner,installation,split')],

        ['klima-montaji','Multi Split Klima Sistemi','multi-split-klima-sistemi',
            'Tek dış ünite + 2-5 iç ünite. Daire, villa, ofisler için sessiz ve estetik çözüm.',
            '<h2>Multi Split Klima Sistemi</h2><p>Birden fazla odanız varsa multi split sistem hem ekonomik hem estetik.</p><h3>Avantajları</h3><ul><li>Tek dış ünite — daha az gürültü, daha az balkon işgali</li><li>Her oda bağımsız sıcaklık ayarı</li><li>%15-20 elektrik tasarrufu</li><li>Daha estetik, az kablolama</li></ul><h3>Tipik Maliyet</h3><ul><li>2 iç + 1 dış (3 odalı): 60-90 K ₺</li><li>3 iç + 1 dış (4-5 odalı): 90-140 K ₺</li><li>5 iç + 1 dış (villa): 150-250 K ₺</li></ul>',
            20, img('air,conditioner,multi,split,villa')],

        // Yerden Isıtma
        ['yerden-isitma','Yerden Isıtma Tesisatı','yerden-isitma-tesisati',
            'Yeni yapı veya tadilat — şap altı yerden ısıtma sistemi tasarımı ve montajı.',
            '<h2>Yerden Isıtma Sistemi</h2><p>Yerden ısıtma, ısı kaynağının zemine yerleştirildiği modern ısıtma sistemi. Homojen sıcaklık, ayak konforu, %15-25 daha az enerji tüketimi.</p><h3>Sistem Bileşenleri</h3><ul><li>PEX boru (16×2 mm, oksijen bariyerli)</li><li>EPS şap altı izolasyon plakası</li><li>Folyo kaplı yansıtıcı tabaka</li><li>Kollektör (her oda ayrı vanalı)</li><li>Oda termostatı (kablolu/kablosuz)</li></ul><h3>m² Başına Maliyet</h3><ul><li>Sadece yerden ısıtma: 600-900 ₺/m²</li><li>Şap dahil paket: 850-1.200 ₺/m²</li><li>Komple (kombi + tesisat): 1.500-2.200 ₺/m²</li></ul>',
            10, img('underfloor,heating,construction,pipes')],

        // Sıhhi Tesisat
        ['sihhi-tesisat','Sıhhi Tesisat Yenileme','sihhi-tesisat-yenileme',
            'PPRC plastik boru ile komple banyo, mutfak, WC tesisat yenileme. Sızıntı garantili.',
            '<h2>Sıhhi Tesisat</h2><p>Eski galvaniz veya bakır tesisatların yerine, modern PPRC (polipropilen kopolimer) plastik boru sistemi öneriyoruz. Paslanmaz, kireç tutmaz, 50+ yıl ömürlü.</p><h3>Hizmet Kapsamı</h3><ul><li>Komple sıhhi tesisat değişimi</li><li>Banyo / WC tesisatı</li><li>Mutfak tesisatı</li><li>Sıcak / soğuk su hatları</li><li>Drenaj ve gider sistemleri</li><li>Hidrofor / pompa montajı</li><li>Su sızıntı tespiti (termal kamera)</li></ul>',
            10, img('plumbing,bathroom,renovation,pipes')],

        // Isı Pompası
        ['isi-pompasi','Hava Kaynaklı Isı Pompası','hava-kaynakli-isi-pompasi',
            'Bosch, Daikin Altherma, ECA, Mitsubishi Ecodan ısı pompası satış ve kurulum.',
            '<h2>Hava Kaynaklı Isı Pompası</h2><p>1 kWh elektrikle 3-4 kWh ısı üretir (COP 3-4). Hem ısıtma hem soğutma yapar.</p><h3>Kimler İçin Uygun?</h3><ul><li>Doğalgaz altyapısı olmayan bölgeler</li><li>Ege/Akdeniz bölgesi (-10°C üstü)</li><li>Yenilenebilir enerji odaklı yapılar</li><li>Hem ısıtma hem soğutma isteyenler</li><li>Yerden ısıtma sistemi olanlar</li></ul><h3>Önerilen Modeller (2026)</h3><ul><li>Bosch CS3400i AWS Split 8/10/14 kW (~150-180K ₺)</li><li>ECA Monoblok 8/11/16 kW (~110-160K ₺)</li><li>Daikin Altherma 3 (~180-250K ₺)</li><li>Mitsubishi Ecodan (~190-240K ₺)</li></ul>',
            10, img('heat,pump,outdoor,system,green')],

        // Tesisat Hizmetleri
        ['tesisat-hizmetleri','Mekanik Tesisat Projelendirme','mekanik-tesisat-projelendirme',
            'Yeni inşaat, tadilat, ticari mekanlar için komple mekanik tesisat projesi ve uygulaması.',
            '<h2>Mekanik Tesisat Projelendirme</h2><p>Yeni inşaat, komple tadilat, otel/restoran/ofis gibi ticari mekanlar için uçtan uca mekanik tesisat hizmeti.</p><h3>Hizmet Kapsamı</h3><ul><li>Isıtma sistemi (kombi/kazan/ısı pompası)</li><li>Soğutma sistemi (klima/VRF/chiller)</li><li>Havalandırma sistemi</li><li>Sıhhi tesisat</li><li>Doğalgaz tesisatı</li><li>Yangın tesisatı (sprinkler, hidrant)</li><li>Yerden ısıtma</li></ul>',
            10, img('mechanical,engineering,construction,blueprint')],
    ];

    foreach ($hizmetler as [$kat_slug, $baslik, $slug, $kisa, $icerik, $sira, $gorsel]) {
        try {
            $ex = db_get("SELECT id, gorsel FROM hizmetler WHERE slug=?", [$slug]);
            if ($ex) {
                // Mevcutsa görsel boşsa UPDATE et
                if (empty($ex['gorsel'])) {
                    db_run("UPDATE hizmetler SET gorsel=? WHERE id=?", [$gorsel, $ex['id']]);
                    $sayilar['hizmetler_gorsel']++;
                }
                continue;
            }
            $kid = $hizmet_kat_id[$kat_slug] ?? null;
            db_run("INSERT INTO hizmetler (kategori_id, baslik, slug, kisa_aciklama, icerik, gorsel, sira, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                [$kid, $baslik, $slug, $kisa, $icerik, $gorsel, $sira]);
            $sayilar['hizmetler']++;
        } catch (Throwable $e) { $hatalar[] = "Hizmet $baslik: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['hizmetler']}</strong> yeni hizmet eklendi" . ($sayilar['hizmetler_gorsel'] ? ", {$sayilar['hizmetler_gorsel']} mevcut hizmete görsel atandı" : '') . ".";

    // =========================================================================
    // 6) KAMPANYALARDA GÖRSEL EKLE + YENİ KAMPANYALAR
    // =========================================================================
    $kampanya_gorsel_map = [
        'azra-dogalgaz-super-kombi-paketi' => img('boiler,gas,deal,heating'),
        'demirdokum-ademix-24kw-kombi-paketi' => img('boiler,gas,deal,heating'),
    ];
    foreach ($kampanya_gorsel_map as $slug => $gorsel) {
        try {
            $k = db_get("SELECT id, gorsel FROM kampanyalar WHERE slug=?", [$slug]);
            if ($k && empty($k['gorsel'])) {
                db_run("UPDATE kampanyalar SET gorsel=? WHERE id=?", [$gorsel, $k['id']]);
                $sayilar['kampanyalar_gorsel']++;
            }
        } catch (Throwable $e) {}
    }

    $kampanyalar = [
        ['Yerden Isıtma + Tam Yoğuşmalı Kombi Paketi','yerden-isitma-yogusmali-kombi-paketi',
            '100 m² eve komple yerden ısıtma + Demirdöküm Vintomix 24/28 + tüm tesisat. Anahtar teslim 195.000 ₺.',
            '<h2>Yerden Isıtma + Yoğuşmalı Kombi Anahtar Teslim Paket</h2><p>Yeni inşaat veya komple tadilat müşterilerimiz için: 100 m² standart daireye komple yerden ısıtma sistemi + Demirdöküm tam yoğuşmalı kombi.</p><h3>Pakete Dahil</h3><ul><li>Demirdöküm Vintomix 24/28 kW Tam Yoğuşmalı Kombi</li><li>PEX boru (16×2 mm, 50 yıl ömür)</li><li>EPS şap altı izolasyon plakaları</li><li>Folyo kaplı yansıtıcı tabaka</li><li>Kollektör (her oda ayrı vanalı)</li><li>Oda termostatı (kablosuz)</li><li>Tüm tesisat işçiliği</li><li>2 yıl tesisat garantisi</li></ul><p><strong>Anahtar teslim:</strong> 195.000 ₺ (peşin) / 220.000 ₺ (12 taksit)</p><p><em>* 100 m² nin üzerinde m² başına 1.500 ₺ ek ücret</em></p>',
            195000, 220000, 12, '2026-01-01', '2026-12-31', img('underfloor,heating,modern,living')],

        ['12000 BTU Inverter Klima + Montaj Paket','12000-btu-klima-montaj-paketi',
            'Daikin Sensira 12000 BTU + profesyonel montaj. 38.000 ₺ peşin, 42.000 ₺ 6 taksit.',
            '<h2>Daikin Sensira 12000 BTU Inverter Klima + Montaj Paketi</h2><p>Yatak odası, çocuk odası veya küçük oturma odası için en popüler kapasite.</p><h3>Pakete Dahil</h3><ul><li>Daikin Sensira ATXF35F 12000 BTU klima</li><li>Profesyonel montaj (5 m bakır boru, drenaj, kablo)</li><li>Vakum çekme + R32 gaz dolumu</li><li>2 yıl montaj garantisi + üretici 2 yıl</li></ul><p><strong>Peşin:</strong> 38.000 ₺ &nbsp;·&nbsp; <strong>6 Taksit:</strong> 42.000 ₺ (aylık 7.000 ₺)</p>',
            38000, 42000, 6, '2026-04-01', '2026-09-30', img('air,conditioner,installation,deal')],

        ['Apartman Doğalgaz Dönüşüm Paketi','apartman-dogalgaz-donusum-paketi',
            'Apartmanınızdaki tüm daireler için tek seferlik avantajlı toplu doğalgaz dönüşüm paketi.',
            '<h2>Apartman Toplu Doğalgaz Dönüşümü</h2><p>Apartman yöneticileri için: Tüm dairelerde aynı anda doğalgaz dönüşümü yaptırırsanız, daire başına %15-20 indirimle hizmet sunuyoruz.</p><h3>Pakete Dahil</h3><ul><li>Tüm bina için tek koordinatör</li><li>Kolon hattı projelendirme ve döşeme</li><li>Her daire için bireysel proje çizimi</li><li>dağıtım şirketi onay süreçleri</li><li>Tesisat döşeme (her daire 1-2 gün)</li><li>Sızdırmazlık testleri</li><li>Toplu gaz açma randevusu</li><li>Apartman karar metni desteği</li><li>Toplu fiyat avantajı (%15-20)</li></ul><p>Yöneticilerimize yazılı paket teklifi sunmak için bize ulaşın.</p>',
            0, 0, 0, '2026-01-01', '2026-12-31', img('apartment,building,gas,team')],
    ];

    foreach ($kampanyalar as [$baslik, $slug, $kisa, $icerik, $nakit, $kart, $taksit, $bas, $bit, $gor]) {
        try {
            $ex = db_get("SELECT id FROM kampanyalar WHERE slug=?", [$slug]);
            if ($ex) continue;
            db_run("INSERT INTO kampanyalar (baslik, slug, kisa_aciklama, icerik, gorsel, nakit_fiyat, kart_fiyat, taksit_sayisi, baslangic, bitis, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [$baslik, $slug, $kisa, $icerik, $gor, $nakit ?: null, $kart ?: null, $taksit, $bas, $bit]);
            $sayilar['kampanyalar']++;
        } catch (Throwable $e) { $hatalar[] = "Kampanya $baslik: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['kampanyalar']}</strong> yeni kampanya eklendi" . ($sayilar['kampanyalar_gorsel'] ? ", {$sayilar['kampanyalar_gorsel']} mevcut kampanyaya görsel" : '') . ".";

    // =========================================================================
    // 7) BLOG GÖRSELLERİ + YENİ BLOG YAZILARI
    // =========================================================================
    $blog_gorsel_map = [
        'izmirde-dogalgaz-aboneligi-nasil-acilir' => img('gas,document,checklist,office'),
    ];
    foreach ($blog_gorsel_map as $slug => $gorsel) {
        try {
            $b = db_get("SELECT id, gorsel FROM blog_yazilari WHERE slug=?", [$slug]);
            if ($b && empty($b['gorsel'])) {
                db_run("UPDATE blog_yazilari SET gorsel=? WHERE id=?", [$gorsel, $b['id']]);
                $sayilar['blog_gorsel']++;
            }
        } catch (Throwable $e) {}
    }

    $bloglar = [
        ['Kombi Seçerken Dikkat Edilecek 8 Önemli Kriter','kombi-secerken-dikkat-edilecek-8-onemli-kriter',
            'Kapasite, modülasyon, sıcak su debisi, marka, garanti — kombi seçimini etkileyen ana faktörlerin uzman analizi.',
            '<h2>Kombi Seçerken Bilmeniz Gereken Her Şey</h2><p>Kombi, evinizde 10-15 yıl boyunca kullanacağınız bir cihazdır. Yanlış seçim hem konfor kaybı hem yüksek faturayla cezalandırılır. İşte 8 ana kriter:</p><h3>1. Kapasite (kW)</h3><p>Her 10 m² için 1.5-2 kW gerekir. Yaklaşık tablo:</p><ul><li>80-100 m²: 18-20 kW</li><li>100-120 m²: 24 kW (en yaygın)</li><li>120-150 m²: 24-28 kW</li><li>150-180 m²: 30 kW</li><li>180+ m² / dubleks: 35 kW</li></ul><h3>2. Yoğuşmalı / Tam Yoğuşmalı</h3><p>2019\'dan beri Türkiye\'de tüm kombiler yoğuşmalı olmak zorundadır. Tam yoğuşmalı (35-55°C) yerden ısıtmada zorunludur.</p><h3>3. Modülasyon Oranı</h3><p>1:8 ve üstü modülasyon ideal. Düşük modülasyon = sürekli açma-kapama = hızlı yıpranma.</p><h3>4. Sıcak Su Performansı</h3><p>2 banyo varsa 24+ kW ve 12+ l/dk debi şart.</p><h3>5. Eşanjör Tipi</h3><ul><li>Paslanmaz çelik (Nitromix): premium, en uzun ömür</li><li>Aluminyum: yaygın, orta segment</li><li>Bitermik: eski teknoloji, kaçınılmalı</li></ul><h3>6. Marka ve Servis Ağı</h3><p>Bölgenizde yetkili servis sayısını araştırın.</p><h3>7. Yetkili Bayi Garantisi</h3><p>Standart 2+5 yıl. Yetkisiz montaj garantiyi iptal eder!</p><h3>8. Akıllı Özellikler</h3><p>WiFi kontrol, akıllı termostat (Migo Connect, Bosch Junkers) %15-20 ek tasarruf.</p><h3>2026 Tavsiye Listesi</h3><ul><li>Demirdöküm Ademix 24 kW — En çok satan, ~32K ₺</li><li>Bosch Condens 5700i — Premium akıllı, ~42.5K ₺</li><li>Vaillant ecoTEC plus — En yüksek modülasyon, ~48K ₺</li><li>ECA Confeo Premix — Yerli premium, ~29.5K ₺</li></ul>',
            'kombi seçimi, demirdöküm ademix, yoğuşmalı kombi, kombi tavsiye',
            img('boiler,modern,gas,wall')],

        ['Inverter Klima vs Standart — %30 Tasarruf Mümkün mü?','inverter-klima-vs-standart-klima',
            'Inverter teknolojisi neden %30 elektrik tasarrufu sağlar? Maliyet karşılaştırması ve geri dönüş süresi.',
            '<h2>Inverter Klima Nasıl %30 Tasarruf Sağlar?</h2><p>Standart klima dur-kalk mantığı (on/off) ile çalışır, inverter ise kompresör hızını sürekli ayarlar.</p><h3>Standart Klima Sorunları</h3><ul><li>Her açılışta yüksek elektrik (start-up akımı)</li><li>Sıcaklık dalgalanmaları (3-4°C)</li><li>Yüksek ses</li><li>Hızlı kompresör yıpranması</li></ul><h3>Inverter Avantajları</h3><ul><li>%30 daha az elektrik tüketimi</li><li>Sabit sıcaklık (±0.5°C)</li><li>Çok daha sessiz</li><li>2-3 kat uzun kompresör ömrü</li></ul><h3>5 Yıllık Maliyet Analizi (12.000 BTU)</h3><table><tr><th>Kriter</th><th>Standart</th><th>Inverter A++</th></tr><tr><td>Cihaz</td><td>~18K ₺</td><td>~35K ₺</td></tr><tr><td>Aylık (yaz)</td><td>~810 ₺</td><td>~540 ₺</td></tr><tr><td>Yıllık fark</td><td>—</td><td><strong>~3.240 ₺ tasarruf</strong></td></tr></table><p>17K ₺ fiyat farkı 5 yılda kendini öder. Klima ömrü 10-15 yıl olduğu için kâr başlar.</p><p><strong>Sonuç:</strong> 2026\'da kesinlikle inverter alın. R32 gaz tercih edin (çevre dostu, daha verimli).</p>',
            'inverter klima, klima seçimi, A++ enerji, R32 gaz',
            img('air,conditioner,modern,energy')],

        ['Yerden Isıtma vs Radyatör — 7 Soruda Karar','yerden-isitma-vs-radyator-karar-rehberi',
            'Yeni inşaat veya tadilat için yerden ısıtma mı radyatör mü? Maliyet, konfor, enerji verimi karşılaştırması.',
            '<h2>Yerden Isıtma vs Radyatör — Hangisi Sizin İçin?</h2><h3>1. İlk Yatırım</h3><ul><li>Radyatör: 80-120 ₺/m²</li><li>Yerden ısıtma: 600-900 ₺/m²</li></ul><p>100 m² için fark: ~50K ₺ ek yatırım yerden ısıtma için.</p><h3>2. Enerji Verimi</h3><p>Yerden ısıtma %15-25 daha az gaz tüketir (35-45°C çalışır, radyatör 65-80°C).</p><h3>3. Konfor</h3><ul><li>Ayak sıcak, baş serin (ideal)</li><li>Hava kuruluğu yok</li><li>Ses yok, toz dolaşımı yok</li></ul><h3>4. Estetik</h3><p>Yerden ısıtma tamamen görünmez. Mobilya serbestliği maksimum.</p><h3>5. Isınma Hızı</h3><p>Radyatör 15-30 dk, yerden ısıtma 1-2 saat. Termostatla otomatik yönetimde fark hissedilmez.</p><h3>6. Bakım</h3><p>İkisi de düşük. PEX boru 50+ yıl ömürlü.</p><h3>7. Tadilat</h3><ul><li>Yeni inşaat: Yerden ısıtma kesinlikle</li><li>Mevcut bina: Radyatör pratik</li><li>Sadece banyolarda: Küçük yatırım, büyük konfor</li></ul><h3>Sonuç</h3><p><strong>Yerden ısıtma:</strong> Yeni inşaat, uzun süreli kalış, yoğuşmalı kombi.<br><strong>Radyatör:</strong> Mevcut bina, hızlı çözüm, düşük bütçe.</p>',
            'yerden ısıtma, radyatör, yoğuşmalı kombi, ısıtma sistemi',
            img('underfloor,heating,living,modern')],

        ['Isı Pompası — Doğalgazın Yerini Alabilir Mi?','isi-pompasi-nedir-dogalgazin-yerini-alir-mi',
            'Hava kaynaklı ısı pompası teknolojisi, COP değeri, doğalgazla maliyet karşılaştırması.',
            '<h2>Isı Pompası — Geleceğin Isıtma Teknolojisi</h2><p>Avrupa\'da yeni binalarda kombi yerini hızla ısı pompasına bırakıyor. Türkiye\'de de yaygınlaşıyor.</p><h3>Nasıl Çalışır?</h3><p>Klimanızla aynı prensiple. -15°C\'de bile verim verebilir.</p><h3>COP Değeri</h3><ul><li>7°C dış: COP 4.0-4.5 (1 kWh elektrik = 4 kWh ısı)</li><li>-7°C dış: 2.5-3.0</li><li>-15°C dış: 2.0</li></ul><p>Doğalgaz kombi verimi 0.92-0.96. <strong>Isı pompası 4 kat fazla ısı üretir.</strong></p><h3>Maliyet (100 m², kış)</h3><table><tr><th>Sistem</th><th>Aylık</th></tr><tr><td>Doğalgaz Kombi</td><td>2.750 ₺</td></tr><tr><td>Isı Pompası (COP 3.5)</td><td>2.400 ₺</td></tr><tr><td>+ Solar Panel</td><td><strong>600 ₺</strong></td></tr></table><h3>Avantajlar</h3><ul><li>Hem ısıtma hem soğutma</li><li>Doğalgaz gerektirmez</li><li>Çevre dostu</li><li>Solar panel ile sıfır enerji</li><li>Sıcak su üretir</li></ul><h3>Kimler İçin?</h3><ul><li>Doğalgaz altyapısı olmayan bölgeler</li><li>Ege/Akdeniz (İzmir ideal!)</li><li>Yeni inşaat + yerden ısıtma</li><li>Yenilenebilir enerji odaklı</li></ul>',
            'ısı pompası, hava kaynaklı, COP, doğalgaz alternatifi',
            img('heat,pump,outdoor,green,energy')],

        ['Doğalgaz Faturasında %30 Tasarruf — 12 Pratik Yöntem','dogalgaz-faturasinda-12-pratik-tasarruf-yontemi',
            'Yıllık doğalgaz faturanızı %30-40 düşürebilecek 12 uygulanabilir teknik.',
            '<h2>Doğalgaz Faturasında %30-40 Tasarruf</h2><p>Doğru uygulamalarla yıllık 6.000-10.000 ₺ tasarruf mümkün.</p><h3>1. Yıllık Bakım Yaptırın</h3><p>İhmal edilen kombi %15-20 verim kaybeder. 1.500 ₺ bakım, 3-5K ₺ tasarruf.</p><h3>2. Akıllı Termostat</h3><p>Tado, Migo Connect %20\'ye varan tasarruf. Geri dönüş 1 yıl.</p><h3>3. Gece 17-18°C</h3><p>Her 1°C düşüş %6 tasarruf.</p><h3>4. Pencere/Kapı İzolasyonu</h3><p>Eski fitiller. 200-500 ₺ ile %10 tasarruf.</p><h3>5. Petek Arkası Folyo</h3><p>Yansıtıcı folyo (10-15 ₺/m) %50 ısıyı geri yansıtır.</p><h3>6. Pencere Önü Perde</h3><p>Gece kalın perde %30 ısı kaybını yarıya indirir.</p><h3>7. Yerden Isıtma + Tam Yoğuşmalı</h3><p>%15 daha az gaz tüketim.</p><h3>8. Su Basıncı 1-2 Bar</h3><p>Aylık kontrol kazandırır.</p><h3>9. Petek Havasını Alın</h3><p>Sezon başında %10 verim artırır.</p><h3>10. Mantolama</h3><p>80-150 ₺/m² ile %50 yakıt tasarrufu.</p><h3>11. Sıcak Su 50°C</h3><p>Yeterli, kireç oluşumunu azaltır.</p><h3>12. A++ Cihazlar</h3><p>10 yıllık eski kombi → yoğuşmalı: yıllık 4-7K ₺ tasarruf.</p>',
            'doğalgaz tasarruf, kombi bakım, akıllı termostat, fatura',
            img('saving,money,energy,home')],

        ['Klima Bakımı — Sezona Hazırlık 5 Adımda','klima-bakimi-sezon-hazirligi',
            'Yaz sezonu öncesi klima bakımı kontrol listesi. Filtre temizliği, gaz kontrolü, dış ünite.',
            '<h2>Klima Bakımı — Yaz Sezonu Hazırlığı</h2><p>Klimanız 6-8 ay kullanılmadan kalmışsa, ilk açılışta sorunlu olabilir.</p><h3>1. İç Ünite Filtreleri</h3><p>Filtreyi çıkarın, ılık suda yıkayın, kuruttuktan sonra geri takın. 2 ayda bir tekrarlayın.</p><h3>2. Dış Ünite Kontrol</h3><p>Yaprak, toz, kuş yuvası temizliği. Hava giriş-çıkışları açık olmalı.</p><h3>3. Drenaj Hortumu</h3><p>Tıkalı hortum su sızmasına yol açar. Basınçlı su geçirin.</p><h3>4. Yetkili Servis (2 Yılda 1)</h3><ul><li>R32/R410A gaz basıncı</li><li>Evaporatör temizliği</li><li>Kompresör testi</li><li>Termostat kalibrasyonu</li><li>Kondenser temizliği</li></ul><p>Bakım 800-1.200 ₺.</p><h3>5. Test Çalıştırma</h3><p>30 dk soğutma + 30 dk ısıtma test edin. Anormal ses/koku varsa servis çağırın.</p>',
            'klima bakımı, klima filtresi, klima servis, sezon hazırlığı',
            img('air,conditioner,maintenance,filter')],
    ];

    foreach ($bloglar as [$baslik, $slug, $ozet, $icerik, $etiket, $gor]) {
        try {
            $ex = db_get("SELECT id FROM blog_yazilari WHERE slug=?", [$slug]);
            if ($ex) continue;
            db_run("INSERT INTO blog_yazilari (baslik, slug, ozet, icerik, gorsel, yazar, etiketler, aktif, yayin_tarihi) VALUES (?, ?, ?, ?, ?, 'Azra Doğalgaz Uzman Ekibi', ?, 1, ?)",
                [$baslik, $slug, $ozet, $icerik, $gor, $etiket, date('Y-m-d H:i:s')]);
            $sayilar['blog']++;
        } catch (Throwable $e) { $hatalar[] = "Blog $baslik: " . $e->getMessage(); }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['blog']}</strong> yeni blog yazısı" . ($sayilar['blog_gorsel'] ? " + {$sayilar['blog_gorsel']} mevcut yazıya görsel" : '') . ".";

    // =========================================================================
    // 8) PROJELER (artık tablo otomatik oluşturuldu)
    // =========================================================================
    $projeler_arr = [
        ['Bornova Konut Sitesi — 48 Daire Doğalgaz Tesisat','bornova-konut-sitesi-dogalgaz',
            'Doğalgaz Tesisatı','Bornova/İzmir','2026-03',
            '48 daireli yeni konut sitesinde komple doğalgaz tesisat işi. Kolon hatları, sayaç odası, daire içi tesisatlar tamamlandı, dağıtım şirketi onayı alındı, gaz arzı sağlandı.',
            '<p>Bornova\'da inşaatı tamamlanan 48 daireli konut sitesinin komple doğalgaz tesisat projesini Şubat-Mart 2026\'da tamamladık.</p><h3>Kapsam</h3><ul><li>Sayaç odası tesisatı</li><li>Kolon hatları (8 katlı 6 blok)</li><li>Daire içi tesisatları (48 daire × ortalama 2 noktası)</li><li>Sızdırmazlık testleri</li><li>dağıtım şirketi onayı + toplu gaz açma</li></ul><p><strong>Süre:</strong> 25 iş günü</p>',
            img('apartment,building,modern,construction')],

        ['Karşıyaka Dubleks Villa — Yerden Isıtma + Kombi','karsiyaka-dubleks-villa-yerden-isitma',
            'Yerden Isıtma','Karşıyaka/İzmir','2026-02',
            '180 m² dubleks villaya komple yerden ısıtma + Demirdöküm Nitromix Ioni 28 kW kombi.',
            '<p>Karşıyaka\'da 180 m² dubleks villaya yerden ısıtma + premium kombi projesi.</p><h3>Sistem</h3><ul><li>Demirdöküm Nitromix Ioni 28 kW</li><li>PEX 16×2 mm boru (1.350 m)</li><li>2 kat × 4 oda kollektör (8 zone)</li><li>Kablosuz oda termostatları</li></ul>',
            img('villa,modern,heating,luxury')],

        ['Buca Apartman Dairesi — Demirdöküm Ademix Kombi','buca-apartman-demirdokum-ademix',
            'Kombi Montajı','Buca/İzmir','2026-04',
            '120 m² dairede eski hermetik kombiden Demirdöküm Ademix 24/28 kW yoğuşmalıya dönüşüm. 6 saatte tamamlandı.',
            '<p>Buca\'da 120 m² apartman dairesinde 12 yıllık eski hermetik kombinin yerine Demirdöküm Ademix P 24/28 kW tam yoğuşmalı kombi montajı.</p>',
            img('apartment,modern,kitchen,boiler')],

        ['Çiğli Ofis Binası — Multi Split Klima Sistemi','cigli-ofis-binasi-multi-split-klima',
            'Klima Montajı','Çiğli/İzmir','2026-04',
            '450 m² ofis binası — 8 iç ünite, 2 dış ünite multi split. Mitsubishi Heavy SRK serisi.',
            '<p>Çiğli\'de 450 m² ofis binasının komple klima sistemi. 8 iç + 2 dış ünite multi split.</p>',
            img('office,modern,workspace,airconditioner')],

        ['Konak Restoran — Havalandırma + Davlumbaz','konak-restoran-havalandirma',
            'Havalandırma','Konak/İzmir','2026-03',
            '180 m² restoranda komple havalandırma, ısı geri kazanım, mutfak davlumbazı.',
            '<p>Konak\'ta yeni açılan restoran için komple mekanik havalandırma sistemi.</p>',
            img('restaurant,kitchen,ventilation,interior')],

        ['Gaziemir Müstakil Ev — Komple Mekanik Tesisat','gaziemir-mustakil-ev-mekanik-tesisat',
            'Mekanik Tesisat','Gaziemir/İzmir','2026-02',
            '160 m² müstakil ev — sıhhi tesisat + doğalgaz + yerden ısıtma + klima — anahtar teslim.',
            '<p>Gaziemir\'de 160 m² müstakil evde komple mekanik tesisat. 35 iş gününde tamamlandı.</p>',
            img('house,modern,construction,villa')],
    ];

    try {
        db()->query("SELECT 1 FROM projeler LIMIT 1");
        $projeler_var = true;
    } catch (Throwable $e) { $projeler_var = false; }

    if ($projeler_var) {
        foreach ($projeler_arr as [$baslik, $slug, $kat, $lok, $tar, $ozet, $icerik, $gor]) {
            try {
                $ex = db_get("SELECT id FROM projeler WHERE slug=?", [$slug]);
                if ($ex) continue;
                db_run("INSERT INTO projeler (slug, baslik, kategori, ozet, icerik, gorsel, lokasyon, tarih, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                    [$slug, $baslik, $kat, $ozet, $icerik, $gor, $lok, $tar . '-01']);
                $sayilar['projeler']++;
            } catch (Throwable $e) { $hatalar[] = "Proje $baslik: " . $e->getMessage(); }
        }
    }
    $mesajlar[] = "✅ <strong>{$sayilar['projeler']}</strong> demo proje eklendi.";

    // =========================================================================
    // 9) AYARLAR — eksik olanlar default'la doldur
    // =========================================================================
    $varsayilan = [
        'meta_anahtar_kelime' => 'izmir doğalgaz, demirdöküm yetkili bayi, kombi montaj izmir, klima izmir, yerden ısıtma izmir,  ısı pompası izmir',
    ];
    foreach ($varsayilan as $k => $v) {
        try {
            $ex = db_get("SELECT deger FROM ayarlar WHERE anahtar=?", [$k]);
            if (!$ex || empty($ex['deger'])) {
                db_run("INSERT INTO ayarlar (anahtar, deger) VALUES (?, ?) ON DUPLICATE KEY UPDATE deger=VALUES(deger)", [$k, $v]);
                $sayilar['ayarlar']++;
            }
        } catch (Throwable $e) {}
    }

    @file_put_contents($kilit, "v1.5.0 seed: " . date('Y-m-d H:i:s') . "\n" . json_encode($sayilar, JSON_UNESCAPED_UNICODE));
    $mesajlar[] = "🔒 Seed tamamlandı, kilit dosyası: <code>.seed-1.5.0.lock</code>";
    }
}

$kilit_var = file_exists($kilit);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>İçerik Yükleme — v1.5.0 — Azra Doğalgaz</title>
<style>
    body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 40px 20px; line-height: 1.6; }
    .box { max-width: 880px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 12px rgba(15,23,42,.05); }
    h1 { font-size: 1.7rem; margin-bottom: 8px; color: #ff6b00; }
    .lead { color: #64748b; margin-bottom: 28px; }
    h2 { font-size: 1.2rem; margin: 28px 0 12px; color: #0f172a; border-bottom: 2px solid #fed7aa; padding-bottom: 6px; }
    .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 8px; font-size: .92rem; line-height: 1.6; }
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
    <h1>📚 İçerik Yükleme — Sektörel Veri Seed v1.5.0</h1>
    <p class="lead">Mevcut DB'nizdeki eksiklikleri akıllıca tamamlar: projeler tablosunu otomatik oluşturur, ürün/hizmet/kampanya/blog'larda görsel boş olanlara <strong>gerçek görsel URL'leri</strong> ekler, eksik içerikleri tamamlar.</p>

    <?php foreach ($mesajlar as $m): ?>
        <div class="alert alert-ok"><?= $m ?></div>
    <?php endforeach; ?>
    <?php foreach ($hatalar as $h): ?>
        <div class="alert alert-err"><?= $h ?></div>
    <?php endforeach; ?>

    <?php if (empty($_POST['calistir'])): ?>

        <h2>Bu Seed Ne Yapar?</h2>

        <div class="alert alert-info">
            <strong>Akıllı çalışma şekli:</strong>
            <ul>
                <li>📂 <strong>projeler</strong> tablosu yoksa <strong>otomatik oluşturulur</strong> (migrate.php gerekmez)</li>
                <li>🖼️ Mevcut ürünlerde görsel boşsa <strong>Unsplash gerçek görsel URL'leri</strong> eklenir (slug bazlı semantik arama)</li>
                <li>➕ Eksik markalar (15), ürün kategorileri (10), hizmetler (12), kampanyalar (3), blog yazıları (6) ve projeler (6) eklenir</li>
                <li>🛡️ Mevcut kayıtlar <strong>asla silinmez</strong>, sadece eksik olan veriler eklenir</li>
                <li>🔄 Yeni yüklenen ürünler/hizmetlerin tümü görsel URL'leriyle birlikte gelir</li>
            </ul>
        </div>

        <h2>Görsel URL Stratejisi</h2>
        <p>Tüm ürün, hizmet, kampanya ve blog görselleri için <strong>Unsplash semantik arama URL'leri</strong> kullanılır. Format:</p>
        <code>https://source.unsplash.com/featured/800x600/?KEYWORD1,KEYWORD2</code>
        <p>Bu URL'ler:</p>
        <ul>
            <li>✅ Hotlinkable (kendi sunucunuza yüklemeniz gerekmez)</li>
            <li>✅ Free (Unsplash lisansı, ticari kullanıma uygun)</li>
            <li>✅ Konuya uygun (kombi için kombi resmi, klima için klima vs.)</li>
            <li>🔄 Daha sonra admin panelinden istediğiniz görseli kendi sunucunuza yükleyebilirsiniz</li>
        </ul>

        <?php if ($kilit_var && empty($_GET['zorla'])): ?>
            <div class="alert alert-warn">
                ⚠️ Bu seed daha önce çalıştırılmış. Yine de çalıştırmak için:<br><br>
                <a href="?zorla=1" class="btn btn-out">Zorla Çalıştır (Mevcut Kayıtlar Korunacak)</a>
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
            <a href="<?= SITE_URL ?>/admin/projeler.php" class="btn btn-out" style="margin-left:8px">Projeleri Düzenle</a>
            <a href="<?= SITE_URL ?>/admin/" class="btn btn-out" style="margin-left:8px">Admin Paneli</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
