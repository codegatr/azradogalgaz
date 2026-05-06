<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/iller.php';

$sayfa_baslik   = 'Hizmet Bölgelerimiz — Türkiye 81 İl | Azra Doğalgaz';
$sayfa_aciklama = 'İzmir merkezli Azra Doğalgaz, Türkiye genelinde 81 ilde doğalgaz tesisatı, kombi, klima ve sıhhi tesisat hizmetleri sunmaktadır. İlinizi seçin, hizmet detaylarını inceleyin.';
$sayfa_anahtar  = '81 il doğalgaz, türkiye doğalgaz tesisat, izmir merkezli kombi servisi, azra doğalgaz hizmet bölgeleri';
$kanonik_url    = SITE_URL . '/iller';

$tel = ayar('firma_telefon_1', '0546 790 78 77');
$firma = ayar('firma_unvan', 'Azra Doğalgaz');

// İlleri bölgeye göre grupla
$bolge_grup = [];
foreach (iller_listesi() as $slug => $il) {
    $bolge_grup[$il['bolge']][$slug] = $il;
}
$bolge_sira = ['Ege', 'Akdeniz', 'Marmara', 'İç Anadolu', 'Karadeniz', 'Doğu Anadolu', 'Güneydoğu Anadolu'];

// Schema — CollectionPage + tüm illeri ItemList olarak listele
$il_listesi_arr = iller_listesi();
$item_list = [];
$pos = 1;
foreach ($il_listesi_arr as $slug => $i) {
    $item_list[] = [
        '@type'    => 'ListItem',
        'position' => $pos++,
        'item'     => [
            '@type' => 'Place',
            'name'  => $i['ad'],
            'url'   => SITE_URL . '/il/' . $slug,
        ],
    ];
}
$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Hizmet Bölgeleri — 81 İl',
    'url'      => $kanonik_url,
    'description' => $sayfa_aciklama,
    'mainEntity' => [
        '@type' => 'ItemList',
        'numberOfItems' => count($il_listesi_arr),
        'itemListElement' => $item_list,
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<style>
.iller-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);color:#fff;padding:60px 0 50px;position:relative;overflow:hidden;text-align:center}
.iller-hero::before{content:'';position:absolute;top:-30%;left:-10%;width:520px;height:520px;background:radial-gradient(circle,rgba(245,158,11,.12) 0%,transparent 65%);border-radius:50%;pointer-events:none}
.iller-hero .container{position:relative;z-index:1;max-width:780px}
.iller-hero .badge{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.4);color:#fdba74;padding:6px 14px;border-radius:999px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:18px}
.iller-hero h1{font-family:var(--font-display);font-size:clamp(1.7rem,3.6vw,2.7rem);font-weight:800;line-height:1.15;margin-bottom:14px;letter-spacing:-.5px;color:#fff}
.iller-hero h1 strong{color:#fdba74}
.iller-hero p{font-size:1.05rem;color:#cbd5e1;line-height:1.65;max-width:640px;margin:0 auto 22px}

.iller-stats{padding:28px 0;background:#fff;border-bottom:1px solid #e2e8f0}
.iller-stats .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center;max-width:920px;margin:0 auto}
.iller-stats .stat .num{font-size:1.6rem;font-weight:800;color:#ea580c;line-height:1;font-family:var(--font-display)}
.iller-stats .stat .lbl{font-size:.78rem;color:#475569;margin-top:4px;letter-spacing:.5px;text-transform:uppercase;font-weight:600}

.iller-bolgeler{padding:50px 0;background:#f8fafc}
.iller-bolge{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:18px}
.iller-bolge h2{font-family:var(--font-display);font-size:1.25rem;font-weight:800;color:#0f172a;margin-bottom:5px;letter-spacing:-.3px;display:flex;align-items:center;gap:10px}
.iller-bolge h2 .ico{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#fef3c7,#fed7aa);color:#ea580c;display:flex;align-items:center;justify-content:center;font-size:.95rem}
.iller-bolge .alt{color:#475569;font-size:.88rem;margin-bottom:16px}
.iller-bolge .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:8px}
.iller-bolge a{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:inherit;transition:.15s;min-height:48px}
.iller-bolge a:hover{background:#fff7ed;border-color:#fed7aa;color:#ea580c}
.iller-bolge a .plaka{background:#0f172a;color:#fdba74;font-size:.7rem;font-weight:800;padding:3px 7px;border-radius:5px;letter-spacing:.5px;font-family:var(--font-display);min-width:28px;text-align:center}
.iller-bolge a .ad{font-weight:600;font-size:.92rem;color:#0f172a;flex:1}
.iller-bolge a:hover .ad{color:#ea580c}

.iller-cta{padding:50px 0;background:linear-gradient(135deg,#fff7ed,#fed7aa)}
.iller-cta .kart{background:#fff;border-radius:14px;padding:32px;text-align:center;box-shadow:0 8px 24px rgba(234,88,12,.12);max-width:680px;margin:0 auto}
.iller-cta h2{font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:8px;letter-spacing:-.3px}
.iller-cta p{color:#475569;margin-bottom:18px;font-size:.95rem;line-height:1.6}
.iller-cta .ctas{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.iller-cta .ctas a{padding:13px 24px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:.95rem;min-height:48px;border:1px solid transparent}
.iller-cta .ctas .pri{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff}
.iller-cta .ctas .sec{background:#0f172a;color:#fff}

@media (max-width:600px){
    .iller-stats .grid{grid-template-columns:repeat(2,1fr);gap:18px 10px}
    .iller-bolgeler{padding:30px 0}
    .iller-bolge{padding:18px}
    .iller-bolge .grid{grid-template-columns:repeat(auto-fill,minmax(140px,1fr))}
}
</style>

<section class="iller-hero">
    <div class="container">
        <div class="badge"><i class="fas fa-map-marked-alt"></i> Türkiye 81 İl Hizmet Ağı</div>
        <h1><strong>Türkiye genelinde</strong> hizmet bölgelerimiz</h1>
        <p>İzmir merkezli <strong><?= e($firma) ?></strong>, doğalgaz tesisatı, kombi, klima ve sıhhi tesisat hizmetleriyle Türkiye'nin <strong>81 ilinde</strong> sizinleyiz. İlinizi seçin, ücretsiz keşif talep edin.</p>
    </div>
</section>

<section class="iller-stats">
    <div class="container">
        <div class="grid">
            <div class="stat"><div class="num">81</div><div class="lbl">İl</div></div>
            <div class="stat"><div class="num">7</div><div class="lbl">Coğrafi Bölge</div></div>
            <div class="stat"><div class="num">8</div><div class="lbl">Hizmet Türü</div></div>
            <div class="stat"><div class="num">2.500+</div><div class="lbl">Tamamlanan Proje</div></div>
        </div>
    </div>
</section>

<section class="iller-bolgeler">
    <div class="container">
        <?php foreach ($bolge_sira as $bolge): if (!isset($bolge_grup[$bolge])) continue; ?>
            <article class="iller-bolge">
                <h2>
                    <span class="ico"><i class="fas fa-mountain-sun"></i></span>
                    <?= e($bolge) ?> Bölgesi
                    <span style="font-weight:600;color:#64748b;font-size:.85rem;margin-left:6px">· <?= count($bolge_grup[$bolge]) ?> il</span>
                </h2>
                <p class="alt"><?= e($bolge) ?> Bölgesi'ndeki <?= count($bolge_grup[$bolge]) ?> ilimizde tesisat ve iklimlendirme hizmetlerimiz. Detaylar için ile tıklayın.</p>
                <nav class="grid" aria-label="<?= e($bolge) ?> illeri">
                    <?php foreach ($bolge_grup[$bolge] as $slug => $il): ?>
                        <a href="<?= SITE_URL ?>/il/<?= e($slug) ?>" title="<?= e($il['ad']) ?> doğalgaz, kombi, klima hizmetleri">
                            <span class="plaka"><?= e($il['plaka']) ?></span>
                            <span class="ad"><?= e($il['ad']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="iller-cta">
    <div class="container">
        <div class="kart">
            <h2>Bölgenizden hizmet talep edin</h2>
            <p>İzmir merkezimizden veya en yakın ekibimizle adresinize ücretsiz keşif gönderiyoruz. Yazılı teklif alın, dilerseniz başlayın — bağlayıcı değil.</p>
            <div class="ctas">
                <a href="<?= SITE_URL ?>/kesif" class="pri"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
                <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="sec"><i class="fas fa-phone"></i> <?= e($tel) ?></a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
