<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/iller.php';

// URL pattern:
//   /il/izmir                           → İzmir tüm hizmetler hub
//   /il/izmir/dogalgaz-tesisati         → İzmir + Doğalgaz Tesisatı landing
//   /il/ankara/kombi-montaji            → Ankara + Kombi Montajı landing
$il_slug      = strtolower(trim($_GET['il'] ?? ''));
$hizmet_slug  = strtolower(trim($_GET['hizmet'] ?? ''));

$il = il_bul($il_slug);
if (!$il) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$hizmet = $hizmet_slug ? hizmet_bul($hizmet_slug) : null;
if ($hizmet_slug && !$hizmet) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// SEO meta — hizmet varsa hizmet+il, yoksa sadece il
$tel = ayar('firma_telefon_1', '0546 790 78 77');
$firma = ayar('firma_unvan', 'Azra Doğalgaz');

if ($hizmet) {
    $sayfa_baslik   = $il['ad'] . ' ' . $hizmet['ad'] . ' — ' . $firma;
    $sayfa_aciklama = $il['ad'] . ' ve çevresinde ' . mb_strtolower($hizmet['ad'], 'UTF-8') . ' hizmeti. ' . $hizmet['aciklama'] . '. Ücretsiz keşif, garantili işçilik.';
    $sayfa_anahtar  = mb_strtolower($il['ad'], 'UTF-8') . ' ' . mb_strtolower($hizmet['ad'], 'UTF-8') . ', ' . $il['ad'] . ' ' . $hizmet['fiil'] . ', ' . $il['plaka'] . ' plaka ' . $hizmet['ad'];
    $kanonik_url    = SITE_URL . '/il/' . $il_slug . '/' . $hizmet_slug;
    $h1             = $il['ad'] . ' ' . $hizmet['ad'];
    $alt_h1         = 'İzmir merkezli, ' . $il['ad'] . ' ve çevresinde ' . $hizmet['fiil'];
} else {
    $sayfa_baslik   = $il['ad'] . ' Doğalgaz, Kombi, Klima ve Tesisat Hizmetleri — ' . $firma;
    $sayfa_aciklama = $il['ad'] . ' ve çevresinde doğalgaz tesisatı, kombi montajı, klima kurulumu, sıhhi tesisat hizmetleri. Mevzuata uygun, garantili işçilik, ücretsiz keşif.';
    $sayfa_anahtar  = $il['ad'] . ' doğalgaz, ' . $il['ad'] . ' kombi, ' . $il['ad'] . ' klima, ' . $il['plaka'] . ' plaka tesisat';
    $kanonik_url    = SITE_URL . '/il/' . $il_slug;
    $h1             = $il['ad'] . '\'da Doğalgaz, Kombi ve İklimlendirme';
    $alt_h1         = $il['ad'] . ' ve çevresinde uçtan uca tesisat çözümleri';
}

// Schema.org — Service + LocalBusiness areaServed
$schema_jsonld = [];

if ($hizmet) {
    $schema_jsonld[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'Service',
        'name'     => $hizmet['ad'] . ' — ' . $il['ad'],
        'serviceType' => $hizmet['ad'],
        'provider' => [
            '@type' => 'HVACBusiness',
            'name'  => $firma,
            'telephone' => $tel,
            'url'   => SITE_URL,
        ],
        'areaServed' => [
            '@type' => 'AdministrativeArea',
            'name'  => $il['ad'],
            'containedInPlace' => [
                '@type' => 'Country',
                'name'  => 'Türkiye',
            ],
        ],
        'description' => $sayfa_aciklama,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'TRY',
            'priceSpecification' => [
                '@type' => 'PriceSpecification',
                'price' => preg_replace('/[^\d]/', '', $hizmet['fiyat_baslangic']),
                'priceCurrency' => 'TRY',
                'description' => 'Başlangıç fiyatı, kapsama göre değişebilir',
            ],
        ],
    ];
}

$schema_jsonld[] = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => array_filter([
        ['@type'=>'ListItem', 'position'=>1, 'name'=>'Anasayfa', 'item'=>SITE_URL],
        ['@type'=>'ListItem', 'position'=>2, 'name'=>'Hizmet Bölgeleri', 'item'=>SITE_URL.'/iller'],
        ['@type'=>'ListItem', 'position'=>3, 'name'=>$il['ad'], 'item'=>SITE_URL.'/il/'.$il_slug],
        $hizmet ? ['@type'=>'ListItem', 'position'=>4, 'name'=>$hizmet['ad'], 'item'=>$kanonik_url] : null,
    ]),
];

require_once __DIR__ . '/inc/header.php';
?>

<style>
.il-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);color:#fff;padding:70px 0;position:relative;overflow:hidden}
.il-hero::before{content:'';position:absolute;top:-30%;right:-10%;width:480px;height:480px;background:radial-gradient(circle,rgba(245,158,11,.12) 0%,transparent 65%);border-radius:50%;pointer-events:none}
.il-hero .container{position:relative;z-index:1}
.il-hero .breadcrumb{display:flex;gap:8px;font-size:.82rem;color:#94a3b8;margin-bottom:14px;flex-wrap:wrap}
.il-hero .breadcrumb a{color:#cbd5e1;text-decoration:none}
.il-hero .breadcrumb a:hover{color:#fdba74}
.il-hero .breadcrumb i{font-size:.65rem;opacity:.5}
.il-hero .badge-il{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.18);border:1px solid rgba(245,158,11,.4);color:#fdba74;padding:6px 14px;border-radius:999px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:18px}
.il-hero h1{font-family:var(--font-display);font-size:clamp(1.6rem,4vw,2.8rem);font-weight:800;line-height:1.15;margin-bottom:14px;letter-spacing:-.5px;color:#fff}
.il-hero h1 strong{color:#fdba74}
.il-hero .alt-h1{font-size:1.05rem;color:#cbd5e1;line-height:1.6;max-width:640px;margin-bottom:24px}
.il-hero .ctas{display:flex;gap:12px;flex-wrap:wrap}
.il-hero .ctas a{padding:13px 24px;border-radius:10px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:9px;transition:.2s}
.il-hero .ctas .pri{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;box-shadow:0 8px 22px rgba(234,88,12,.35)}
.il-hero .ctas .pri:hover{transform:translateY(-2px)}
.il-hero .ctas .sec{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.18);color:#fff}
.il-hero .ctas .sec:hover{background:rgba(255,255,255,.1)}

.il-stats{padding:30px 0;background:#fff;border-bottom:1px solid #e2e8f0}
.il-stats .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center}
.il-stats .stat .num{font-size:1.6rem;font-weight:800;color:#ea580c;line-height:1}
.il-stats .stat .lbl{font-size:.82rem;color:#475569;margin-top:4px;letter-spacing:.3px}

.il-content{padding:50px 0;background:#f8fafc}
.il-content h2{font-family:var(--font-display);font-size:clamp(1.4rem,2.4vw,1.9rem);font-weight:800;color:#0f172a;margin-bottom:16px;letter-spacing:-.3px}
.il-content p{color:#334155;line-height:1.75;margin-bottom:14px;font-size:1rem}
.il-content .ic{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:30px;margin-bottom:18px}
.il-content .ic h2:first-child{margin-top:0}

.hizmet-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;margin-top:22px}
.hizmet-kart{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;text-decoration:none;color:inherit;transition:.2s;display:block}
.hizmet-kart:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.08);border-color:#fed7aa}
.hizmet-kart .ico-w{width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#fef3c7,#fed7aa);color:#ea580c;display:flex;align-items:center;justify-content:center;font-size:1.05rem;margin-bottom:10px}
.hizmet-kart h3{font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:5px;letter-spacing:-.2px}
.hizmet-kart p{font-size:.85rem;color:#475569;margin:0;line-height:1.55}
.hizmet-kart .meta{display:flex;gap:14px;margin-top:10px;font-size:.78rem;color:#64748b}
.hizmet-kart .meta strong{color:#ea580c;font-weight:700}

.il-listesi{padding:50px 0;background:#fff}
.il-listesi h2{font-family:var(--font-display);font-size:clamp(1.3rem,2.2vw,1.7rem);font-weight:800;color:#0f172a;margin-bottom:6px;text-align:center}
.il-listesi .alt{color:#475569;text-align:center;margin-bottom:26px;font-size:.95rem}
.il-pill-grid{display:flex;flex-wrap:wrap;justify-content:center;gap:7px;max-width:920px;margin:0 auto}
.il-pill{background:#f8fafc;border:1px solid #e2e8f0;color:#334155;padding:8px 14px;border-radius:999px;font-size:.85rem;font-weight:600;text-decoration:none;transition:.15s}
.il-pill:hover{background:#fff7ed;border-color:#fed7aa;color:#ea580c}
.il-pill.aktif{background:#ea580c;border-color:#ea580c;color:#fff;font-weight:700}

.il-cta{padding:50px 0;background:linear-gradient(135deg,#fff7ed,#fed7aa)}
.il-cta .kart{background:#fff;border-radius:14px;padding:30px;text-align:center;box-shadow:0 8px 24px rgba(234,88,12,.12);max-width:680px;margin:0 auto}
.il-cta h2{font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:8px;letter-spacing:-.3px}
.il-cta p{color:#475569;margin-bottom:18px;font-size:.95rem;line-height:1.6}
.il-cta .ctas{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.il-cta .ctas a{padding:12px 22px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:.92rem}
.il-cta .ctas .pri{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff}
.il-cta .ctas .sec{background:#0f172a;color:#fff}

@media (max-width:600px){
    .il-stats .grid{grid-template-columns:repeat(2,1fr);gap:18px 10px}
    .il-content .ic{padding:20px}
}
</style>

<section class="il-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Konum">
            <a href="<?= SITE_URL ?>/">Anasayfa</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/iller">Hizmet Bölgeleri</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/il/<?= e($il_slug) ?>"><?= e($il['ad']) ?></a>
            <?php if ($hizmet): ?>
                <i class="fas fa-chevron-right"></i>
                <span><?= e($hizmet['ad']) ?></span>
            <?php endif; ?>
        </nav>

        <div class="badge-il">
            <i class="fas fa-map-marker-alt"></i>
            <?= e($il['plaka']) ?> · <?= e($il['ad']) ?> · <?= e($il['bolge']) ?>
        </div>

        <h1><?= e($h1) ?></h1>
        <p class="alt-h1"><?= e($alt_h1) ?>. Ücretsiz keşif, yazılı teklif, sigortalı işçilik.</p>

        <div class="ctas">
            <a href="<?= SITE_URL ?>/kesif" class="pri"><i class="fas fa-clipboard-check"></i> <?= e($il['ad']) ?> İçin Ücretsiz Keşif</a>
            <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="sec"><i class="fas fa-phone"></i> <?= e($tel) ?></a>
        </div>
    </div>
</section>

<section class="il-stats">
    <div class="container">
        <div class="grid">
            <div class="stat"><div class="num"><?= number_format($il['nufus'], 0, ',', '.') ?></div><div class="lbl">NÜFUS (TÜİK 2024)</div></div>
            <div class="stat"><div class="num"><?= e($il['plaka']) ?></div><div class="lbl">PLAKA KODU</div></div>
            <div class="stat"><div class="num"><?= e($il['bolge']) ?></div><div class="lbl">COĞRAFİ BÖLGE</div></div>
            <div class="stat"><div class="num"><?= $il['dogalgaz'] ? '✓' : '—' ?></div><div class="lbl">DOĞALGAZ ALTYAPISI</div></div>
        </div>
    </div>
</section>

<section class="il-content">
    <div class="container">
        <?php if ($hizmet): ?>
            <article class="ic">
                <h2><?= e($il['ad']) ?> <?= e($hizmet['ad']) ?> Hizmeti</h2>
                <p>İzmir merkezli <strong><?= e($firma) ?></strong>, <?= e($il['ad']) ?> ve çevre ilçelerinde <strong><?= mb_strtolower(e($hizmet['ad']), 'UTF-8') ?></strong> hizmeti vermektedir. <?= e($hizmet['aciklama']) ?>.</p>
                <p>Doğalgaz, ısıtma ve iklimlendirme alanında 10+ yıl tecrübeyle, <?= e($il['ad']) ?>'da konuttan ticari yapıya, sıfır projeden tadilat ve revizyona kadar tüm <?= mb_strtolower(e($hizmet['ad']), 'UTF-8') ?> ihtiyaçlarınızı karşılıyoruz.</p>

                <h2>Hizmet Detayları</h2>
                <p><strong>Başlangıç fiyatı:</strong> <?= e($hizmet['fiyat_baslangic']) ?> · <strong>Tipik süre:</strong> <?= e($hizmet['sure']) ?></p>
                <p>Tüm uygulamalarımız ilgili mevzuata uygun şekilde, sertifikalı malzeme ve sigortalı işçilikle gerçekleştirilir. <?= e($il['ad']) ?> bölgesi için 2 yıl işçilik garantisi standardımızdır.</p>

                <h2>Neden Bizi Tercih Etmelisiniz?</h2>
                <ul style="color:#334155;line-height:1.85;font-size:1rem;padding-left:22px;margin-bottom:14px">
                    <li><strong><?= e($il['ad']) ?> ve çevresine</strong> hızlı erişim — ücretsiz keşif</li>
                    <li>Demirdöküm, Bosch, Vaillant <strong>yetkili bayilik</strong></li>
                    <li>Yazılı sözleşme, sürpriz fiyat olmaz</li>
                    <li>Sigortalı uygulama, 2 yıl işçilik garantisi</li>
                    <li>7/24 acil destek hattı</li>
                </ul>
            </article>

            <article class="ic">
                <h2><?= e($il['ad']) ?>'da Diğer Hizmetlerimiz</h2>
                <p><?= e($il['ad']) ?> bölgesine sunduğumuz tüm tesisat ve iklimlendirme hizmetleri:</p>
                <div class="hizmet-grid">
                    <?php foreach (hizmetler_listesi() as $hs => $h): if ($hs === $hizmet_slug) continue; ?>
                        <a class="hizmet-kart" href="<?= SITE_URL ?>/il/<?= e($il_slug) ?>/<?= e($hs) ?>">
                            <div class="ico-w"><i class="fas fa-<?= e($h['ikon']) ?>"></i></div>
                            <h3><?= e($il['ad']) ?> <?= e($h['ad']) ?></h3>
                            <p><?= e($h['aciklama']) ?></p>
                            <div class="meta"><span>⏱ <?= e($h['sure']) ?></span><strong>Başlangıç <?= e($h['fiyat_baslangic']) ?></strong></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>

        <?php else: ?>
            <article class="ic">
                <h2><?= e($il['ad']) ?>'da Tesisat ve İklimlendirme Hizmetleri</h2>
                <p><strong><?= e($firma) ?></strong>, İzmir merkezli faaliyet göstermekle birlikte <?= e($il['ad']) ?> ve çevresine de hizmet vermektedir. <?= e($il['bolge']) ?> Bölgesi'nde yer alan <?= e($il['ad']) ?>, <?= number_format($il['nufus'], 0, ',', '.') ?> nüfusuyla <?= $il['nufus'] > 1000000 ? 'büyükşehir' : 'önemli bir merkez' ?> konumundadır<?= $il['dogalgaz'] ? ' ve doğalgaz altyapısı mevcuttur' : '' ?>.</p>
                <p>Aşağıda <?= e($il['ad']) ?>'da hizmet verdiğimiz tüm alanları görebilirsiniz. Her bir hizmet için detaylı sayfalarımızı inceleyebilir, doğrudan arayarak ücretsiz keşif talep edebilirsiniz.</p>

                <div class="hizmet-grid">
                    <?php foreach (hizmetler_listesi() as $hs => $h): ?>
                        <a class="hizmet-kart" href="<?= SITE_URL ?>/il/<?= e($il_slug) ?>/<?= e($hs) ?>">
                            <div class="ico-w"><i class="fas fa-<?= e($h['ikon']) ?>"></i></div>
                            <h3><?= e($il['ad']) ?> <?= e($h['ad']) ?></h3>
                            <p><?= e($h['aciklama']) ?></p>
                            <div class="meta"><span>⏱ <?= e($h['sure']) ?></span><strong>Başlangıç <?= e($h['fiyat_baslangic']) ?></strong></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="ic">
                <h2><?= e($il['ad']) ?> Hakkında</h2>
                <p><?= e($il['ad']) ?>, <?= e($il['plaka']) ?> plaka kodlu, <?= e($il['bolge']) ?> Bölgesi'nde yer alan, TÜİK 2024 verilerine göre <?= number_format($il['nufus'], 0, ',', '.') ?> nüfuslu ilimizdir. <?php if ($il['dogalgaz']): ?>Şehir merkezinde doğalgaz altyapısı bulunmakta olup, hane bazında doğalgaza geçiş hızla devam etmektedir.<?php endif; ?> Gelişen şehirleşme ile birlikte konut ve ticari yapılarda kombi, klima ve doğalgaz tesisat ihtiyaçları da artmaktadır.</p>
                <p>Bölgede sunduğumuz tüm hizmetler için ücretsiz keşif politikamız geçerlidir. <?= e($il['ad']) ?>'a uzaklık ve proje kapsamına bağlı olarak ekibimiz adresinize gelir, ihtiyacı yerinde inceleyerek yazılı teklif sunar.</p>
            </article>
        <?php endif; ?>
    </div>
</section>

<section class="il-listesi">
    <div class="container">
        <h2>Tüm Hizmet Bölgelerimiz</h2>
        <p class="alt">Türkiye genelinde 81 ilde hizmet vermekteyiz. İlinizi seçin:</p>
        <nav class="il-pill-grid" aria-label="İl seçici">
            <?php foreach (iller_listesi() as $is => $i): ?>
                <a class="il-pill <?= $is === $il_slug ? 'aktif' : '' ?>" href="<?= SITE_URL ?>/il/<?= e($is) ?>"><?= e($i['ad']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<section class="il-cta">
    <div class="container">
        <div class="kart">
            <h2><?= e($il['ad']) ?>'da hizmet ihtiyacınız mı var?</h2>
            <p>Adresinize ücretsiz keşif gönderiyoruz. Yazılı teklif alın, dilerseniz başlayın — bağlayıcı değil.</p>
            <div class="ctas">
                <a href="<?= SITE_URL ?>/kesif" class="pri"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
                <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="sec"><i class="fas fa-phone"></i> <?= e($tel) ?></a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
