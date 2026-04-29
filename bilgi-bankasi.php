<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Doğalgaz Bilgi Bankası — Azra Doğalgaz';
$sayfa_aciklama = 'Doğalgaz tesisatı, kombi seçimi, klima, yerden ısıtma, ısı pompası rehberleri. Tüm sektörel bilgiler tek sayfada.';
$kanonik_url    = SITE_URL . '/bilgi-bankasi';

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Bilgi Bankası</span>
        </div>
        <h1>Doğalgaz Bilgi Bankası</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Doğalgaz tesisatı, kombi seçimi, klima, yerden ısıtma ve ısı pompası — bilmeniz gereken her şey tek sayfada.</p>
    </div>
</section>

<!-- İÇİNDEKİLER -->
<section style="background:var(--c-bg-alt);padding:30px 0;border-bottom:1px solid var(--c-line)">
    <div class="container">
        <div style="max-width:820px;margin:0 auto">
            <h3 style="font-family:var(--font-display);font-size:1.05rem;margin-bottom:14px;color:var(--c-muted);text-transform:uppercase;letter-spacing:1px;font-weight:700">İçindekiler</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="#dogalgaz-baglanti" class="btn btn-out btn-sm">1. Doğalgaz Bağlantı Süreci</a>
                <a href="#kombi-rehber" class="btn btn-out btn-sm">2. Kombi Seçim Rehberi</a>
                <a href="#klima-rehber" class="btn btn-out btn-sm">3. Klima Seçim Rehberi</a>
                <a href="#yerden-isitma" class="btn btn-out btn-sm">4. Yerden Isıtma vs Radyatör</a>
                <a href="#isi-pompasi" class="btn btn-out btn-sm">5. Isı Pompası Rehberi</a>
                <a href="#tasarruf" class="btn btn-out btn-sm">6. Enerji Tasarrufu</a>
            </div>
        </div>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">

<!-- 1 -->
<h2 id="dogalgaz-baglanti" style="scroll-margin-top:100px"><i class="fas fa-fire-flame-curved" style="color:var(--c-primary);margin-right:10px"></i>1. Doğalgaz Bağlantı Süreci (Adım Adım)</h2>
<p>İzmir'de yeni bir konuta doğalgaz çekmek istiyorsanız, izlemeniz gereken süreç aşağıdadır. Tüm aşamalar bölgenizdeki yerel doğalgaz dağıtım şirketi şartnamesine ve EPDK mevzuatına uygun olmalıdır.</p>

<h3>Adım 1 — Yetkili firma seçimi</h3>
<p>İlk ve en önemli adım, bölgenizdeki yerel doğalgaz dağıtım şirketi tarafından sertifikalı bir iç tesisat firması ile çalışmaktır. Yetkisiz firma ile yaptırılan tesisat <strong>dağıtım şirketi tarafından kabul edilmez</strong>, gaz açılmaz, baştan yapılması gerekir.</p>

<h3>Adım 2 — Keşif ve teklif</h3>
<p>Yetkili firma adresinize keşif ekibi gönderir. Ölçüm yapılır, kombi konumu, baca durumu, petek sayısı/uzunluğu, sayaç yeri belirlenir. <strong>Yazılı teklif sunulur.</strong> Teklif kabul edilirse sözleşme imzalanır.</p>

<h3>Adım 3 — Proje çizimi ve dağıtım şirketi onayı</h3>
<p>Yetkili makine mühendisi tarafından proje çizilir, yerel doğalgaz dağıtım şirketi online sistemine yüklenir. <strong>2-5 iş günü</strong> içinde teknik kontrol sonrası onaylanır. Eksik proje düzeltilmek üzere geri gönderilir.</p>

<h3>Adım 4 — Onay bedeli ve sözleşme</h3>
<p>Proje onayı sonrası firmanın onay bedeli ödemesi yapılır. Sigorta poliçesi, doğal gaz dönüşüm sözleşmesi ve diğer evraklar proje dosyasına yüklenir, arşivlenir.</p>

<h3>Adım 5 — Tesisat döşeme</h3>
<p>Onaylı projeye göre tesisat döşenir. Çayırova boru, gasfil paslanmaz, TS EN 15266 standartlarında borular kullanılır. <strong>Sızdırmazlık testi (basınç testi)</strong> mutlaka yapılır.</p>

<h3>Adım 6 — Güvence bedeli ve gaz açma randevusu</h3>
<p>Müşteri abone merkezine giderek <strong>güvence bedeli</strong>ni yatırır (kullanım sonunda iade edilir). Sayaç firmaya teslim edilir. Online sistem üzerinden gaz açma randevusu alınır.</p>

<h3>Adım 7 — Dağıtım şirketi kontrolü ve gaz açma</h3>
<p>Dağıtım şirketi teknik personeli ve sertifikalı firma yetkilisi adresinize gelir. Tesisat kontrol edilir, ocak/kombi gibi cihazların bağlı olması gerekir. Uygunluk durumunda gaz arzı sağlanır, <strong>Doğal Gaz Uygunluk Belgesi</strong> verilir.</p>

<h3>Adım 8 — Kombi devreye alma</h3>
<p>Yetkili kombi servisi ile görüşülerek kombi devreye alınır. Servis bu işlem sırasında Doğal Gaz Uygunluk Belgesi'ni talep eder.</p>

<blockquote><strong>Toplam süre:</strong> Standart bir daire için keşiften aktif kullanıma <strong>ortalama 7-10 iş günü</strong>'dür. Yoğun dönemlerde 14 güne kadar uzayabilir.</blockquote>

<!-- 2 -->
<h2 id="kombi-rehber" style="scroll-margin-top:100px"><i class="fas fa-screwdriver-wrench" style="color:var(--c-primary);margin-right:10px"></i>2. Kombi Seçim Rehberi</h2>
<p>Kombi, evinizde yıllarca kullanacağınız bir cihazdır. Yanlış seçim hem konfor kaybı hem yüksek faturayla cezalandırılır. İşte 6 ana kriter:</p>

<h3>2.1. Kapasite (kW)</h3>
<p>Kabaca her 10 m² için 1.5–2 kW gerekir. Bölge katsayısı, izolasyon ve cephe yönü hesabı değiştirir. <a href="<?= SITE_URL ?>/kombi-hesaplama">Online hesaplama aracımız</a> bu işi sizin için yapar.</p>

<h3>2.2. Yoğuşmalı / Tam Yoğuşmalı</h3>
<p>2019'dan beri Türkiye'de satılan tüm kombiler yoğuşmalı olmak zorundadır. Tam yoğuşmalı kombiler düşük sıcaklıkta (55°C-35°C) çalışırken maksimum verim verir. <strong>Yerden ısıtmada</strong> mutlaka tam yoğuşmalı tercih edin.</p>

<h3>2.3. Modülasyon Oranı</h3>
<p>Modülasyon, kombinin alev boyunu (gaz akışını) ihtiyaca göre ayarlama yeteneğidir. <strong>1:8 ve üstü modülasyon</strong> oranı (örn. 3-24 kW arası ayarlanabilir) hem konfor hem tasarruf sağlar. Düşük modülasyonlu kombi sürekli açıp kapanır, parça yıpranması artar.</p>

<h3>2.4. Sıcak Su Performansı</h3>
<p>Aynı anda 2 banyoda sıcak su kullanılacaksa <strong>en az 24 kW kapasite</strong> ve dakika başına 12+ litre üretim önemlidir. Tek banyo yeterli ise 18-20 kW kombi de iş görür.</p>

<h3>2.5. Marka ve Servis Ağı</h3>
<p>Tanınmış markalar genellikle 5+ yıl boyunca yedek parça desteği verir. Bilinmedik marka ekonomik gözükse de orta vadede arıza+parça maliyetiyle cezalandırır. <strong>Bölgenizdeki yetkili servis sayısını</strong> mutlaka araştırın.</p>

<h3>2.6. Garanti</h3>
<p>Standart garanti: 2 yıl kombi, 5 yıl eşanjör. Premium markalarda 3+5 yıl. <strong>Yetkili tesisat firmasınca montaj</strong> garantinin geçerliliği için zorunludur.</p>

<h3>Tavsiye Listesi (2026)</h3>
<ul>
    <li><strong>Demirdöküm Ademix 24 kW</strong> — Türkiye'nin en çok satan modeli, ekonomik ve tam yoğuşmalı</li>
    <li><strong>Bosch Condens 5700i 24 kW</strong> — Premium, sessiz, akıllı kontrol</li>
    <li><strong>Vaillant ecoTEC plus 24 kW</strong> — En yüksek modülasyon, uzun ömür</li>
    <li><strong>Buderus GB172i 24 kW</strong> — Alman kalitesi, geniş sıcak su</li>
    <li><strong>ECA Confeo Premix 24 kW</strong> — Yerli üretim, uygun fiyat</li>
</ul>

<!-- 3 -->
<h2 id="klima-rehber" style="scroll-margin-top:100px"><i class="fas fa-snowflake" style="color:var(--c-primary);margin-right:10px"></i>3. Klima Seçim Rehberi</h2>

<h3>3.1. BTU Kapasitesi</h3>
<p>BTU, klimanın saatte ortamdan uzaklaştırabildiği ısı miktarıdır. Yetersiz BTU odayı soğutamaz, fazla BTU ise nemi alamadan sıcaklığı düşürür ve sağlıksız ortam yaratır. <a href="<?= SITE_URL ?>/klima-hesaplama">Online hesaplama aracımız</a> kullanın.</p>

<h3>3.2. Inverter mi, Standart mı?</h3>
<p><strong>Inverter klima mutlaka tercih edilmelidir.</strong> Standart klima dur-kalk yaparken inverter kompresör hızını ayarlayarak sürekli düşük güçte çalışır. <strong>%30 daha az elektrik</strong> tüketir, daha sessiz ve dayanıklıdır.</p>

<h3>3.3. Enerji Sınıfı</h3>
<p>A++ veya A+++ sınıfı tercih edilmelidir. A sınıfı klima %40-60 daha çok elektrik tüketir. SEER (sezonluk soğutma verimliliği) değeri 6.5+ olmalıdır.</p>

<h3>3.4. R32 Soğutucu Gaz</h3>
<p>R32, R410A'nın yerini alan yeni nesil çevre dostu gazdır. <strong>Yeni klima alıyorsanız mutlaka R32 olmalıdır</strong>. AB ve Türkiye yeni mevzuatları R32 lehine düzenliyor.</p>

<h3>3.5. Sessiz Çalışma</h3>
<p>İç ünite ses seviyesi: 19-25 dB sessiz, 26-35 dB normal, 36+ dB gürültülü. Yatak odası için <strong>22 dB altı</strong> tavsiye edilir. Dış ünite seviyesi de komşu sorunu yaşamamak için 50 dB altı olmalı.</p>

<h3>3.6. WiFi ve Akıllı Özellikler</h3>
<p>Modern klimalar mobil app, sesli kontrol, otomatik temizlik özellikleri sunar. Yaz tatilinden eve dönmeden klima açma gibi konforu önemsiyorsanız bu özellikler değerlidir.</p>

<!-- 4 -->
<h2 id="yerden-isitma" style="scroll-margin-top:100px"><i class="fas fa-temperature-arrow-up" style="color:var(--c-primary);margin-right:10px"></i>4. Yerden Isıtma vs Radyatör</h2>

<h3>Karşılaştırma Tablosu</h3>
<table style="width:100%;border-collapse:collapse;font-size:.95rem;margin:14px 0">
    <thead>
        <tr style="background:var(--c-bg-alt);border-bottom:2px solid var(--c-line)">
            <th style="padding:12px;text-align:left">Kriter</th>
            <th style="padding:12px;text-align:center">Yerden Isıtma</th>
            <th style="padding:12px;text-align:center">Radyatör</th>
        </tr>
    </thead>
    <tbody>
        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:12px"><strong>İlk Yatırım</strong></td><td style="text-align:center">Yüksek (~150-250 ₺/m²)</td><td style="text-align:center">Düşük (~80-120 ₺/m²)</td></tr>
        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:12px"><strong>Enerji Verimi</strong></td><td style="text-align:center">%15-25 daha tasarruflu</td><td style="text-align:center">Standart</td></tr>
        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:12px"><strong>Konfor</strong></td><td style="text-align:center">Homojen, ayak sıcak</td><td style="text-align:center">Lokal noktalarda sıcak</td></tr>
        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:12px"><strong>Estetik</strong></td><td style="text-align:center">Görünmez (mükemmel)</td><td style="text-align:center">Panel görünür</td></tr>
        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:12px"><strong>Isınma Hızı</strong></td><td style="text-align:center">Yavaş (1-2 saat)</td><td style="text-align:center">Hızlı (15-30 dk)</td></tr>
        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:12px"><strong>Tadilat Uyumu</strong></td><td style="text-align:center">Şap çekilmesi gerekir</td><td style="text-align:center">Mevcut binaya kolay</td></tr>
        <tr><td style="padding:12px"><strong>Bakım</strong></td><td style="text-align:center">Düşük (kollektör temizliği)</td><td style="text-align:center">Düşük (peteklerin havasının alınması)</td></tr>
    </tbody>
</table>

<h3>Hangisi sizin için uygun?</h3>
<ul>
    <li><strong>Yeni inşaat veya komple tadilat:</strong> Yerden ısıtma kesinlikle tercih edilmeli</li>
    <li><strong>Mevcut yaşanan ev:</strong> Radyatör pratik (yerden ısıtma için zemin yenilenmeli)</li>
    <li><strong>Banyo zeminleri:</strong> Sadece banyolarda yerden ısıtma — küçük yatırım, büyük konfor</li>
    <li><strong>Yoğuşmalı kombi + yerden ısıtma:</strong> En verimli kombinasyon (%30 yakıt tasarrufu)</li>
</ul>

<!-- 5 -->
<h2 id="isi-pompasi" style="scroll-margin-top:100px"><i class="fas fa-arrows-rotate" style="color:var(--c-primary);margin-right:10px"></i>5. Isı Pompası Rehberi</h2>
<p>Isı pompası, dış havadaki düşük sıcaklıktaki ısıyı (hatta 0°C'de bile) içeri pompalayan elektrikli bir sistemdir. <strong>1 kWh elektrikle 3-4 kWh ısı üretir</strong> (COP 3-4) — bu nedenle elektrikli ısıtmadan 3-4 kat ucuza gelir, hatta yer yer doğalgazdan da ucuzdur.</p>

<h3>Kimler için uygundur?</h3>
<ul>
    <li><strong>Doğalgaz altyapısı olmayan bölgeler</strong> (yazlık siteleri, kırsal alan)</li>
    <li><strong>Ege/Akdeniz bölgesinde yaşayanlar</strong> (dış sıcaklık -10°C altına düşmüyor)</li>
    <li><strong>Yenilenebilir enerji odaklı yapılar</strong> (güneş paneli ile birlikte sıfır enerji ev)</li>
    <li><strong>Hem ısıtma hem soğutma isteyenler</strong> (tek sistem ile 12 ay kullanım)</li>
</ul>

<h3>Tipler</h3>
<ul>
    <li><strong>Hava-Hava (split klima türü):</strong> En basit, klima gibi çalışır</li>
    <li><strong>Hava-Su (monoblok / split):</strong> Yerden ısıtma + sıcak su üretir, kombi yerine geçer</li>
    <li><strong>Toprak kaynaklı:</strong> En verimli ama yüksek montaj maliyeti</li>
</ul>

<h3>Maliyet (2026)</h3>
<p>8-16 kW arası hava-su monoblok ısı pompası: <strong>75.000 – 200.000 ₺</strong> (cihaz). Montaj + tesisat ek 30-60 bin TL. İlk yatırım yüksek, geri ödeme süresi 5-8 yıl. Çevre dostu enerji teşviklerinden yararlanma imkanı vardır.</p>

<h3>Önerilen Modeller</h3>
<ul>
    <li><strong>Bosch CS3400i AWS Split</strong> — 8/10/14 kW R32 monofaze</li>
    <li><strong>ECA Monoblok Isı Pompası</strong> — 8/11/16 kW kontrol paneli dahil</li>
    <li><strong>Daikin Altherma 3</strong> — Premium R32 split sistem</li>
    <li><strong>Mitsubishi Ecodan</strong> — Yüksek verim, sessiz çalışma</li>
</ul>

<!-- 6 -->
<h2 id="tasarruf" style="scroll-margin-top:100px"><i class="fas fa-leaf" style="color:var(--c-primary);margin-right:10px"></i>6. Enerji Tasarrufu — 12 Pratik Tavsiye</h2>
<ol>
    <li><strong>Yıllık kombi bakımı yaptırın</strong> — verim %15 düşer ihmal edildiğinde</li>
    <li><strong>Termostat kullanın</strong> — odadaki sıcaklığa göre otomatik ayar, %20 tasarruf</li>
    <li><strong>Gece sıcaklığı 17°C'ye düşürün</strong> — uyurken yüksek sıcaklık gereksiz</li>
    <li><strong>Pencere/kapı yalıtımını kontrol edin</strong> — fitil ve sileceklerle %10 tasarruf</li>
    <li><strong>Petek arkasına alüminyum folyo</strong> — duvara giden ısıyı odaya yansıtır</li>
    <li><strong>Pencere önlerini perdeyle örtün</strong> — gece ısı kaybını azaltır</li>
    <li><strong>Yerden ısıtma için tam yoğuşmalı kombi</strong> seçin — %25 daha az gaz</li>
    <li><strong>Klimada Eco modunu kullanın</strong> — 24°C-26°C arası sıcaklık</li>
    <li><strong>Klimada filtre temizliği</strong> — 2 ayda bir, %15 verim artışı</li>
    <li><strong>A++ enerji sınıflı cihaz tercih edin</strong> — uzun vadede yatırım</li>
    <li><strong>LED aydınlatma kullanın</strong> — klima yükünü azaltır</li>
    <li><strong>Yalıtım/mantolama yatırımı</strong> — kombi/klima faturasını yarıya düşürür</li>
</ol>

        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Profesyonel görüş için keşfimizi talep edin</h3>
            <p>Uzman ekibimiz adresinize gelir, durumu inceler, en uygun çözümü önerir.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
