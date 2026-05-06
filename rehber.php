<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/seo_konular.php';

$slug = strtolower(trim($_GET['slug'] ?? ''));
$konu = seo_konu_bul($slug);

if (!$konu) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$tel   = ayar('firma_telefon_1', '0546 790 78 77');
$firma = ayar('firma_unvan', 'Azra Doğalgaz');

$sayfa_baslik   = $konu['baslik'] . ' | ' . $firma;
$sayfa_aciklama = $konu['meta_aciklama'];
$sayfa_anahtar  = implode(', ', $konu['anahtar_kelimeler']);
$kanonik_url    = SITE_URL . '/rehber/' . $slug;

// ─── Schema.org JSON-LD ───
$schema_jsonld = [];

// Ana içerik schema (Article veya HowTo)
if ($konu['schema_tip'] === 'HowTo') {
    $how_steps = [];
    foreach ($konu['icerik'] as $i => $paragraf) {
        $how_steps[] = [
            '@type' => 'HowToStep',
            'position' => $i + 1,
            'name' => 'Adım ' . ($i + 1),
            'text' => mb_substr(strip_tags($paragraf), 0, 300),
        ];
    }
    $schema_jsonld[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'HowTo',
        'name'     => $konu['h1'],
        'description' => $konu['meta_aciklama'],
        'totalTime' => 'PT' . $konu['okuma_dakika'] . 'M',
        'step' => $how_steps,
    ];
} else {
    $schema_jsonld[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'Article',
        'headline' => $konu['h1'],
        'description' => $konu['meta_aciklama'],
        'author' => [
            '@type' => 'Organization',
            'name'  => $firma,
            'url'   => SITE_URL,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name'  => $firma,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => SITE_URL . '/assets/img/logo.png',
            ],
        ],
        'datePublished' => '2026-01-01',
        'dateModified'  => date('Y-m-d'),
        'mainEntityOfPage' => $kanonik_url,
        'articleBody' => mb_substr(strip_tags(implode(' ', $konu['icerik'])), 0, 1000),
    ];
}

// FAQ schema — rich snippet için altın madeni
if (!empty($konu['sss'])) {
    $schema_jsonld[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => array_map(fn($s) => [
            '@type' => 'Question',
            'name'  => $s[0],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $s[1],
            ],
        ], $konu['sss']),
    ];
}

// Breadcrumb
$schema_jsonld[] = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem', 'position'=>1, 'name'=>'Anasayfa', 'item'=>SITE_URL],
        ['@type'=>'ListItem', 'position'=>2, 'name'=>'Bilgi Rehberi', 'item'=>SITE_URL.'/rehber'],
        ['@type'=>'ListItem', 'position'=>3, 'name'=>$konu['h1'], 'item'=>$kanonik_url],
    ],
];

// Pillar başlığı (görüntü için)
$pillar_etiket = [
    'dogalgaz' => ['ad' => 'Doğalgaz', 'renk' => '#ea580c', 'ikon' => 'fire'],
    'kombi'    => ['ad' => 'Kombi',    'renk' => '#dc2626', 'ikon' => 'fire-flame-curved'],
    'klima'    => ['ad' => 'Klima',    'renk' => '#0284c7', 'ikon' => 'snowflake'],
    'tesisat'  => ['ad' => 'Tesisat',  'renk' => '#7c3aed', 'ikon' => 'wrench'],
];
$p = $pillar_etiket[$konu['pillar']] ?? ['ad' => 'Bilgi', 'renk' => '#475569', 'ikon' => 'book'];

require_once __DIR__ . '/inc/header.php';
?>

<style>
.r-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);color:#fff;padding:50px 0 40px;position:relative;overflow:hidden}
.r-hero::before{content:'';position:absolute;top:-30%;right:-10%;width:480px;height:480px;background:radial-gradient(circle,rgba(245,158,11,.1) 0%,transparent 60%);border-radius:50%;pointer-events:none}
.r-hero .container{position:relative;z-index:1;max-width:880px}
.r-hero .breadcrumb{display:flex;gap:8px;font-size:.82rem;color:#94a3b8;margin-bottom:14px;flex-wrap:wrap}
.r-hero .breadcrumb a{color:#cbd5e1;text-decoration:none}
.r-hero .breadcrumb a:hover{color:#fdba74;text-decoration:underline}
.r-hero .breadcrumb i{font-size:.65rem;opacity:.5}
.r-hero .pillar-rozet{display:inline-flex;align-items:center;gap:8px;color:#fff;padding:6px 14px;border-radius:999px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px}
.r-hero h1{font-family:var(--font-display);font-size:clamp(1.6rem,3.4vw,2.6rem);font-weight:800;line-height:1.2;margin-bottom:14px;letter-spacing:-.5px;color:#fff}
.r-hero .alt{font-size:1rem;color:#cbd5e1;line-height:1.65;max-width:720px}
.r-hero .meta{display:flex;gap:18px;margin-top:18px;flex-wrap:wrap;font-size:.85rem;color:#94a3b8}
.r-hero .meta span{display:inline-flex;align-items:center;gap:6px}

.r-content{padding:40px 0;background:#fff}
.r-content .container{max-width:760px}
.r-content article p{font-size:1.05rem;line-height:1.85;color:#1e293b;margin-bottom:20px}
.r-content article p:first-child::first-letter{font-size:3rem;font-weight:800;float:left;line-height:.85;margin:6px 10px 0 0;color:#ea580c;font-family:var(--font-display)}

.r-sss{padding:40px 0;background:linear-gradient(180deg,#f8fafc,#fff)}
.r-sss .container{max-width:760px}
.r-sss h2{font-family:var(--font-display);font-size:1.7rem;font-weight:800;color:#0f172a;margin-bottom:6px;letter-spacing:-.4px}
.r-sss .alt{color:#475569;margin-bottom:24px;font-size:.95rem}
.r-sss .item{background:#fff;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px;overflow:hidden;transition:.15s}
.r-sss .item:hover{border-color:#fed7aa}
.r-sss .item summary{cursor:pointer;padding:16px 18px;font-weight:700;color:#0f172a;font-size:1rem;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:14px;min-height:48px}
.r-sss .item summary::-webkit-details-marker{display:none}
.r-sss .item summary::after{content:'+';font-size:1.4rem;color:#ea580c;font-weight:600;flex-shrink:0;transition:transform .2s}
.r-sss .item[open] summary::after{transform:rotate(45deg)}
.r-sss .item .cevap{padding:0 18px 18px;color:#334155;line-height:1.75;font-size:.97rem}

.r-ilgili{padding:40px 0;background:#f8fafc}
.r-ilgili .container{max-width:880px}
.r-ilgili h2{font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:#0f172a;margin-bottom:18px;letter-spacing:-.3px}
.r-ilgili .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px}
.r-ilgili a{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;text-decoration:none;color:inherit;display:flex;align-items:flex-start;gap:14px;transition:.2s;min-height:80px}
.r-ilgili a:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.08);border-color:#fed7aa}
.r-ilgili a .ico{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0}
.r-ilgili a h3{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:3px;letter-spacing:-.2px}
.r-ilgili a p{font-size:.82rem;color:#64748b;line-height:1.5;margin:0}

.r-cta{padding:40px 0;background:#0f172a;color:#fff}
.r-cta .container{max-width:680px;text-align:center}
.r-cta h2{font-family:var(--font-display);font-size:1.5rem;font-weight:800;margin-bottom:8px;letter-spacing:-.3px}
.r-cta p{color:#cbd5e1;margin-bottom:20px;line-height:1.6}
.r-cta .ctas{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.r-cta .ctas a{padding:13px 24px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:.95rem;min-height:48px}
.r-cta .ctas .pri{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff}
.r-cta .ctas .sec{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2)}

.r-keys{padding:30px 0;background:#fff;border-top:1px solid #e2e8f0}
.r-keys .container{max-width:880px}
.r-keys h3{font-size:.78rem;color:#64748b;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:10px}
.r-keys .pills{display:flex;flex-wrap:wrap;gap:6px}
.r-keys .pills span{background:#f1f5f9;color:#475569;padding:5px 11px;border-radius:999px;font-size:.78rem;font-weight:500}

@media (max-width:600px){
    .r-content article p:first-child::first-letter{font-size:2.4rem;margin-right:6px}
}
</style>

<section class="r-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Konum">
            <a href="<?= SITE_URL ?>/">Anasayfa</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/rehber">Bilgi Rehberi</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= e($konu['h1']) ?></span>
        </nav>

        <div class="pillar-rozet" style="background:<?= e($p['renk']) ?>;">
            <i class="fas fa-<?= e($p['ikon']) ?>"></i>
            <?= e($p['ad']) ?>
        </div>

        <h1><?= e($konu['h1']) ?></h1>
        <p class="alt"><?= e($konu['meta_aciklama']) ?></p>

        <div class="meta">
            <span><i class="far fa-clock"></i> <?= (int)$konu['okuma_dakika'] ?> dk okuma</span>
            <span><i class="far fa-calendar-check"></i> Güncel: <?= date('d.m.Y') ?></span>
            <span><i class="fas fa-circle-check"></i> Uzman tarafından hazırlanmıştır</span>
        </div>
    </div>
</section>

<section class="r-content">
    <div class="container">
        <article>
            <?php foreach ($konu['icerik'] as $paragraf): ?>
                <p><?= $paragraf ?></p>
            <?php endforeach; ?>
        </article>
    </div>
</section>

<?php if (!empty($konu['sss'])): ?>
<section class="r-sss">
    <div class="container">
        <h2>Sıkça Sorulan Sorular</h2>
        <p class="alt">Bu konuyla ilgili en çok merak edilenler ve uzman cevapları.</p>

        <?php foreach ($konu['sss'] as $sss): ?>
            <details class="item">
                <summary><?= e($sss[0]) ?></summary>
                <div class="cevap"><?= e($sss[1]) ?></div>
            </details>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php
// İlgili konular kartları
$ilgili_kartlar = [];
foreach ($konu['ilgili_konular'] ?? [] as $i_slug) {
    $i_konu = seo_konu_bul($i_slug);
    if ($i_konu) $ilgili_kartlar[$i_slug] = $i_konu;
}
?>
<?php if (!empty($ilgili_kartlar)): ?>
<section class="r-ilgili">
    <div class="container">
        <h2>İlgili Rehberler</h2>
        <div class="grid">
            <?php foreach ($ilgili_kartlar as $i_slug => $i_konu):
                $ip = $pillar_etiket[$i_konu['pillar']] ?? $p; ?>
                <a href="<?= SITE_URL ?>/rehber/<?= e($i_slug) ?>">
                    <div class="ico" style="background:<?= e($ip['renk']) ?>"><i class="fas fa-<?= e($ip['ikon']) ?>"></i></div>
                    <div>
                        <h3><?= e($i_konu['h1']) ?></h3>
                        <p><?= e(mb_substr($i_konu['meta_aciklama'], 0, 90)) ?>…</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="r-keys">
    <div class="container">
        <h3>Bu Sayfanın Kapsamı</h3>
        <div class="pills">
            <?php foreach ($konu['anahtar_kelimeler'] as $kw): ?>
                <span><?= e($kw) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="r-cta">
    <div class="container">
        <h2>Profesyonel destek mi gerekiyor?</h2>
        <p>Bu konuda uzman ekibimizle ücretsiz keşif talep edin, yazılı teklif alın.</p>
        <div class="ctas">
            <a href="<?= SITE_URL ?>/kesif" class="pri"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif</a>
            <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="sec"><i class="fas fa-phone"></i> <?= e($tel) ?></a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
