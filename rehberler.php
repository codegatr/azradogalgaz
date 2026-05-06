<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/seo_konular.php';

$tel   = ayar('firma_telefon_1', '0546 790 78 77');
$firma = ayar('firma_unvan', 'Azra Doğalgaz');

$sayfa_baslik   = 'Bilgi Rehberi: Doğalgaz, Kombi, Klima, Tesisat | ' . $firma;
$sayfa_aciklama = 'Doğalgaz tesisatı, kombi seçimi, klima BTU hesaplama, ısı pompası, yerden ısıtma — sektörün en kapsamlı bilgi rehberi. Uzman cevapları.';
$sayfa_anahtar  = 'doğalgaz rehberi, kombi rehberi, klima rehberi, tesisat bilgi, doğalgaz kombi klima';
$kanonik_url    = SITE_URL . '/rehber';

$pillar_etiket = [
    'dogalgaz' => ['ad' => 'Doğalgaz Rehberleri',  'renk' => '#ea580c', 'ikon' => 'fire',                 'aciklama' => 'Doğalgaz tesisatı, dönüşüm, sızdırmazlık, mevzuat'],
    'kombi'    => ['ad' => 'Kombi Rehberleri',     'renk' => '#dc2626', 'ikon' => 'fire-flame-curved',   'aciklama' => 'Kombi seçimi, bakım, arıza kodları, marka karşılaştırma'],
    'klima'    => ['ad' => 'Klima Rehberleri',     'renk' => '#0284c7', 'ikon' => 'snowflake',           'aciklama' => 'BTU hesaplama, inverter teknoloji, bakım, montaj'],
    'tesisat'  => ['ad' => 'Tesisat Rehberleri',   'renk' => '#7c3aed', 'ikon' => 'wrench',              'aciklama' => 'Yerden ısıtma, ısı pompası, sıhhi tesisat, havalandırma'],
];

// Pillar'a göre grupla
$gruplu = [];
foreach (seo_konular() as $slug => $konu) {
    $gruplu[$konu['pillar']][$slug] = $konu;
}

$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Bilgi Rehberi',
    'url'      => $kanonik_url,
    'description' => $sayfa_aciklama,
    'mainEntity' => [
        '@type' => 'ItemList',
        'numberOfItems' => count(seo_konular()),
        'itemListElement' => array_values(array_map(function($i, $slug, $konu) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => SITE_URL . '/rehber/' . $slug,
                'name' => $konu['h1'],
            ];
        }, array_keys(array_keys(seo_konular())), array_keys(seo_konular()), array_values(seo_konular()))),
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<style>
.rh-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);color:#fff;padding:60px 0 50px;text-align:center;position:relative;overflow:hidden}
.rh-hero::before{content:'';position:absolute;top:-30%;left:-10%;width:520px;height:520px;background:radial-gradient(circle,rgba(245,158,11,.1) 0%,transparent 65%);border-radius:50%;pointer-events:none}
.rh-hero .container{position:relative;z-index:1;max-width:780px}
.rh-hero .badge{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.4);color:#fdba74;padding:6px 14px;border-radius:999px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:18px}
.rh-hero h1{font-family:var(--font-display);font-size:clamp(1.8rem,3.6vw,2.8rem);font-weight:800;line-height:1.15;margin-bottom:14px;letter-spacing:-.5px}
.rh-hero h1 strong{color:#fdba74}
.rh-hero p{font-size:1.05rem;color:#cbd5e1;line-height:1.65;max-width:640px;margin:0 auto 22px}

.rh-pillar{padding:40px 0;background:#f8fafc;border-bottom:1px solid #e2e8f0}
.rh-pillar:nth-child(odd){background:#fff}
.rh-pillar .pillar-head{display:flex;align-items:center;gap:14px;margin-bottom:20px;max-width:880px;margin-left:auto;margin-right:auto}
.rh-pillar .pillar-head .ico{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;flex-shrink:0}
.rh-pillar .pillar-head h2{font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:3px;letter-spacing:-.4px}
.rh-pillar .pillar-head p{color:#475569;font-size:.92rem;margin:0}

.rh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;max-width:880px;margin:0 auto}
.rh-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;text-decoration:none;color:inherit;transition:.2s;display:flex;flex-direction:column;min-height:160px}
.rh-card:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(15,23,42,.08);border-color:#fed7aa}
.rh-card h3{font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:6px;line-height:1.35;letter-spacing:-.2px}
.rh-card p{font-size:.85rem;color:#475569;line-height:1.55;margin-bottom:10px;flex:1}
.rh-card .meta{display:flex;align-items:center;gap:10px;font-size:.78rem;color:#64748b;padding-top:10px;border-top:1px solid #f1f5f9}
.rh-card .meta i{color:#ea580c}

.rh-cta{padding:50px 0;background:linear-gradient(135deg,#fff7ed,#fed7aa)}
.rh-cta .container{max-width:680px;text-align:center}
.rh-cta h2{font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:8px;letter-spacing:-.3px}
.rh-cta p{color:#475569;margin-bottom:20px;line-height:1.65}
.rh-cta .ctas{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.rh-cta .ctas a{padding:13px 24px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:.95rem;min-height:48px}
.rh-cta .ctas .pri{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff}
.rh-cta .ctas .sec{background:#0f172a;color:#fff}
</style>

<section class="rh-hero">
    <div class="container">
        <div class="badge"><i class="fas fa-book-open"></i> Sektörün En Kapsamlı Bilgi Rehberi</div>
        <h1><strong>Doğalgaz, Kombi, Klima ve Tesisat</strong> Hakkında Bilmek İstediğiniz Her Şey</h1>
        <p>İzmir merkezli <strong><?= e($firma) ?></strong> uzmanları tarafından hazırlanan kapsamlı rehberler. Mevzuat, fiyat, marka karşılaştırma, kurulum süreçleri ve sıkça sorulan sorular.</p>
    </div>
</section>

<?php foreach ($pillar_etiket as $pillar_slug => $p):
    if (empty($gruplu[$pillar_slug])) continue; ?>
    <section class="rh-pillar">
        <div class="container">
            <div class="pillar-head">
                <div class="ico" style="background:<?= e($p['renk']) ?>"><i class="fas fa-<?= e($p['ikon']) ?>"></i></div>
                <div>
                    <h2><?= e($p['ad']) ?></h2>
                    <p><?= e($p['aciklama']) ?> · <?= count($gruplu[$pillar_slug]) ?> rehber</p>
                </div>
            </div>

            <div class="rh-grid">
                <?php foreach ($gruplu[$pillar_slug] as $slug => $konu): ?>
                    <a class="rh-card" href="<?= SITE_URL ?>/rehber/<?= e($slug) ?>">
                        <h3><?= e($konu['h1']) ?></h3>
                        <p><?= e(mb_substr($konu['meta_aciklama'], 0, 130)) ?>…</p>
                        <div class="meta">
                            <span><i class="far fa-clock"></i> <?= (int)$konu['okuma_dakika'] ?> dk</span>
                            <?php if (!empty($konu['sss'])): ?>
                                <span><i class="far fa-circle-question"></i> <?= count($konu['sss']) ?> SSS</span>
                            <?php endif; ?>
                            <span style="margin-left:auto;color:<?= e($p['renk']) ?>;font-weight:700"><i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endforeach; ?>

<section class="rh-cta">
    <div class="container">
        <h2>Aradığınız bilgiyi bulamadınız mı?</h2>
        <p>Uzman ekibimizle telefonla görüşebilir veya site içi formdan sorunuzu iletebilirsiniz. Size detaylı yanıt vermekten memnuniyet duyarız.</p>
        <div class="ctas">
            <a href="<?= SITE_URL ?>/iletisim" class="pri"><i class="fas fa-envelope-open-text"></i> Soru Sor</a>
            <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="sec"><i class="fas fa-phone"></i> <?= e($tel) ?></a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
