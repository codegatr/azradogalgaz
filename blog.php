<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');

if ($slug) {
    // ===== DETAY =====
    $y = db_get("SELECT * FROM blog_yazilari WHERE slug=? AND aktif=1", [$slug]);
    if (!$y) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

    db_run("UPDATE blog_yazilari SET goruntulenme=goruntulenme+1 WHERE id=?", [(int)$y['id']]);

    $diger = db_all("SELECT id,baslik,slug,ozet,gorsel,olusturma_tarihi
        FROM blog_yazilari WHERE id<>? AND aktif=1
        ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC LIMIT 4", [$y['id']]);

    $yayin = $y['yayin_tarihi'] ?: $y['olusturma_tarihi'];

    $sayfa_baslik   = e($y['meta_baslik'] ?: $y['baslik']) . ' | Blog — Azra Doğalgaz';
    $sayfa_aciklama = e($y['meta_aciklama'] ?: meta_aciklama((string)$y['ozet']));
    $kanonik_url    = SITE_URL . '/blog/' . e($slug);
    $og_resim       = $y['gorsel'] ? UPLOAD_URL . '/' . e($y['gorsel']) : SITE_URL . '/assets/img/og-default.jpg';

    $schema_jsonld = [
        [
            '@context'=>'https://schema.org',
            '@type'=>'BreadcrumbList',
            'itemListElement'=>[
                ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
                ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>SITE_URL.'/blog'],
                ['@type'=>'ListItem','position'=>3,'name'=>$y['baslik'],'item'=>SITE_URL.'/blog/'.$slug],
            ],
        ],
        array_filter([
            '@context'=>'https://schema.org',
            '@type'=>'BlogPosting',
            'headline'=>$y['baslik'],
            'description'=>$y['ozet'],
            'image'=>$y['gorsel'] ? UPLOAD_URL . '/' . $y['gorsel'] : null,
            'datePublished'=>$yayin,
            'dateModified'=>$yayin,
            'author'=>['@type'=>'Person','name'=>$y['yazar'] ?: 'Azra Doğalgaz'],
            'publisher'=>[
                '@type'=>'Organization',
                'name'=>'Azra Doğalgaz',
                'logo'=>['@type'=>'ImageObject','url'=>SITE_URL.'/assets/img/logo.png'],
            ],
            'mainEntityOfPage'=>SITE_URL.'/blog/'.$slug,
        ]),
    ];

    require_once __DIR__ . '/inc/header.php';
    ?>

    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
                <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
                <a href="<?= SITE_URL ?>/blog">Blog</a>
            </div>
            <h1 style="max-width:880px;margin:0 auto"><?= e($y['baslik']) ?></h1>
            <p style="color:var(--c-muted);font-size:.9rem;margin-top:14px">
                <i class="far fa-calendar"></i> <?= tarih_tr($yayin) ?>
                <?php if (!empty($y['yazar'])): ?> · <i class="fas fa-user"></i> <?= e($y['yazar']) ?><?php endif; ?>
                <?php if ((int)$y['goruntulenme'] > 0): ?> · <i class="far fa-eye"></i> <?= (int)$y['goruntulenme'] ?> okuma<?php endif; ?>
            </p>
        </div>
    </section>

    <section class="s">
        <div class="container">
            <article style="max-width:880px;margin:0 auto">
                <?php if (!empty($y['gorsel'])): ?>
                <div style="border-radius:var(--r-lg);overflow:hidden;margin-bottom:36px;aspect-ratio:16/9">
                    <img src="<?= e(UPLOAD_URL . '/' . $y['gorsel']) ?>" alt="<?= e($y['baslik']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php endif; ?>

                <?php if (!empty($y['ozet'])): ?>
                <div style="font-size:1.15rem;color:var(--c-text-2);font-style:italic;border-left:4px solid var(--c-primary);padding:14px 22px;background:var(--c-primary-l);border-radius:0 8px 8px 0;margin-bottom:36px;line-height:1.7">
                    <?= e($y['ozet']) ?>
                </div>
                <?php endif; ?>

                <div class="prose" style="margin:0">
                    <?php if (!empty($y['icerik'])): ?>
                        <?= $y['icerik'] ?>
                    <?php else: ?>
                        <p>Bu yazı için içerik henüz eklenmedi.</p>
                    <?php endif; ?>
                </div>

                <div style="margin-top:50px;padding:30px;background:var(--c-primary-l);border-radius:var(--r-lg);text-align:center">
                    <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:8px">Bu konuda yardım ister misiniz?</h3>
                    <p style="color:var(--c-text-2);margin-bottom:16px">Profesyonel ekibimiz yanınızda.</p>
                    <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
                </div>
            </article>

            <?php if ($diger): ?>
            <div style="margin-top:80px">
                <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;margin-bottom:24px;text-align:center">Diğer Yazılar</h2>
                <div class="services">
                    <?php foreach ($diger as $d): ?>
                    <a href="<?= SITE_URL ?>/blog/<?= e($d['slug']) ?>" class="service-card" style="text-decoration:none;color:inherit">
                        <div class="service-image" style="background:var(--c-blue-l)">
                            <?php if (!empty($d['gorsel'])): ?>
                                <img src="<?= e(UPLOAD_URL.'/'.$d['gorsel']) ?>" alt="<?= e($d['baslik']) ?>" style="width:100%;height:100%;object-fit:cover">
                            <?php else: ?>
                                <i class="fas fa-newspaper" style="background:var(--grad-blue);-webkit-background-clip:text;background-clip:text;color:transparent"></i>
                            <?php endif; ?>
                        </div>
                        <div class="service-body">
                            <h3 style="font-size:1rem"><?= e($d['baslik']) ?></h3>
                            <?php if (!empty($d['ozet'])): ?>
                            <p style="font-size:.88rem"><?= e(mb_strimwidth($d['ozet'], 0, 100, '…', 'UTF-8')) ?></p>
                            <?php endif; ?>
                            <span class="service-link">Devamı <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

// ===== LİSTELEME =====
$arama = clean($_GET['q'] ?? '');
$pn = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 12;
$ofset = ($pn - 1) * $limit;

$where = "aktif=1";
$params = [];
if ($arama) {
    $where .= " AND (baslik LIKE ? OR ozet LIKE ? OR icerik LIKE ?)";
    $params[] = "%$arama%"; $params[] = "%$arama%"; $params[] = "%$arama%";
}

$toplam = (int)(db_get("SELECT COUNT(*) c FROM blog_yazilari WHERE $where", $params)['c'] ?? 0);
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$yazilar = db_all("SELECT id, baslik, slug, ozet, gorsel, yayin_tarihi, olusturma_tarihi, yazar
    FROM blog_yazilari WHERE $where
    ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC
    LIMIT $limit OFFSET $ofset", $params);

$sayfa_baslik   = 'Blog — Doğalgaz, Kombi, Klima Rehberleri | Azra Doğalgaz';
$sayfa_aciklama = 'Doğalgaz tesisat, kombi seçimi, klima ve mekanik tesisat hakkında uzman görüşler ve rehberler.';
$kanonik_url    = SITE_URL . '/blog' . ($pn > 1 ? '?sayfa=' . $pn : '');

$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>SITE_URL.'/blog'],
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Blog</span>
        </div>
        <h1>Blog</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Doğalgaz, kombi, klima ve mekanik tesisat hakkında uzman görüşler, rehberler ve sektörel haberler.</p>
    </div>
</section>

<section class="s">
    <div class="container">

        <form method="get" style="max-width:540px;margin:0 auto 36px;display:flex;gap:8px">
            <input type="text" name="q" value="<?= e($arama) ?>" class="input" placeholder="Yazılar arasında arayın...">
            <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i></button>
        </form>

        <?php if ($yazilar): ?>
        <div class="services">
            <?php foreach ($yazilar as $y):
                $tar = $y['yayin_tarihi'] ?: $y['olusturma_tarihi'];
            ?>
            <a href="<?= SITE_URL ?>/blog/<?= e($y['slug']) ?>" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image" style="background:var(--c-blue-l)">
                    <?php if (!empty($y['gorsel'])): ?>
                        <img src="<?= e(UPLOAD_URL.'/'.$y['gorsel']) ?>" alt="<?= e($y['baslik']) ?>" style="width:100%;height:100%;object-fit:cover" loading="lazy">
                    <?php else: ?>
                        <i class="fas fa-newspaper" style="background:var(--grad-blue);-webkit-background-clip:text;background-clip:text;color:transparent"></i>
                    <?php endif; ?>
                </div>
                <div class="service-body">
                    <p style="color:var(--c-muted);font-size:.78rem;margin-bottom:6px"><i class="far fa-calendar"></i> <?= tarih_tr($tar) ?></p>
                    <h3><?= e($y['baslik']) ?></h3>
                    <?php if (!empty($y['ozet'])): ?>
                    <p><?= e(mb_strimwidth($y['ozet'], 0, 130, '…', 'UTF-8')) ?></p>
                    <?php endif; ?>
                    <span class="service-link">Devamını Oku <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($toplam_sayfa > 1): ?>
        <div class="pager">
            <?php if ($pn > 1): ?><a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$pn-1])) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
            <?php for ($i=1;$i<=$toplam_sayfa;$i++):
                if ($i==$pn): ?><span class="active"><?= $i ?></span>
                <?php else: ?><a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$i])) ?>"><?= $i ?></a>
            <?php endif; endfor; ?>
            <?php if ($pn < $toplam_sayfa): ?><a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$pn+1])) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info" style="max-width:680px;margin:0 auto">
            <i class="fas fa-circle-info"></i>
            <div>
                <?php if ($arama): ?>
                <strong>Aramanız için sonuç bulunamadı.</strong> <a href="<?= SITE_URL ?>/blog">Tüm yazıları görüntüleyin</a>.
                <?php else: ?>
                <strong>Henüz blog yazısı eklenmedi.</strong> Yakında uzman içeriklerle dönmüş olacağız.
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
