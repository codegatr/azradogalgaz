<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');

if ($slug) {
    // ==================== DETAY ====================
    $y = db_get("SELECT * FROM blog_yazilari WHERE slug=? AND aktif=1", [$slug]);
    if (!$y) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

    // görüntülenme arttır
    db_run("UPDATE blog_yazilari SET goruntulenme=goruntulenme+1 WHERE id=?", [(int)$y['id']]);

    $diger = db_all("SELECT id,baslik,slug,ozet,gorsel,olusturma_tarihi
        FROM blog_yazilari WHERE id<>? AND aktif=1
        ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC LIMIT 4", [$y['id']]);

    set_meta([
        'baslik'    => e($y['meta_baslik'] ?: $y['baslik']) . ' | ' . SITE_TITLE,
        'aciklama'  => e($y['meta_aciklama'] ?: meta_aciklama((string)$y['ozet'])),
        'canonical' => SITE_URL . '/blog/' . e($slug),
        'og_image'  => $y['gorsel'] ? UPLOAD_URL . '/' . e($y['gorsel']) : SITE_URL . '/assets/img/og-default.jpg',
    ]);

    $ekstra = schema_org([
        '@context'=>'https://schema.org',
        '@type'=>'BreadcrumbList',
        'itemListElement'=>[
            ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>SITE_URL.'/blog'],
            ['@type'=>'ListItem','position'=>3,'name'=>$y['baslik'],'item'=>SITE_URL.'/blog/'.$slug],
        ],
    ]) . schema_org(array_filter([
        '@context'=>'https://schema.org',
        '@type'=>'BlogPosting',
        'headline'=>$y['baslik'],
        'description'=>$y['ozet'],
        'image'=>$y['gorsel'] ? UPLOAD_URL . '/' . $y['gorsel'] : null,
        'datePublished'=> ($y['yayin_tarihi'] ?: $y['olusturma_tarihi']),
        'dateModified'=> ($y['yayin_tarihi'] ?: $y['olusturma_tarihi']),
        'author'=>['@type'=>'Person','name'=>($y['yazar'] ?: SITE_TITLE)],
        'publisher'=>[
            '@type'=>'Organization',
            'name'=>SITE_TITLE,
            'logo'=>['@type'=>'ImageObject','url'=>SITE_URL.'/assets/img/logo.png'],
        ],
        'mainEntityOfPage'=>SITE_URL.'/blog/'.$slug,
    ]));
    set_meta(['extra_schema' => $ekstra]);

    require_once INC_PATH . '/header.php';
    ?>

    <section class="page-hero">
        <div class="container">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
                <i class="fas fa-chevron-right"></i>
                <a href="<?= SITE_URL ?>/blog">Blog</a>
                <i class="fas fa-chevron-right"></i>
                <span><?= e($y['baslik']) ?></span>
            </nav>
            <h1><?= e($y['baslik']) ?></h1>
            <div class="post-meta">
                <span><i class="fas fa-user"></i> <?= e($y['yazar']) ?></span>
                <span><i class="fas fa-calendar"></i> <?= tarih_tr($y['yayin_tarihi'] ?: $y['olusturma_tarihi']) ?></span>
                <span><i class="fas fa-eye"></i> <?= (int)$y['goruntulenme'] ?> görüntülenme</span>
            </div>
        </div>
    </section>

    <section class="sec">
        <div class="container detail-grid">
            <article class="detail-main">
                <?php if ($y['gorsel']): ?>
                    <div class="detail-image">
                        <img src="<?= UPLOAD_URL . '/' . e($y['gorsel']) ?>" alt="<?= e($y['baslik']) ?>" loading="eager">
                    </div>
                <?php endif; ?>
                <div class="content blog-content">
                    <?= $y['icerik'] ?>
                </div>

                <?php if ($y['etiketler']): ?>
                    <div class="post-tags">
                        <?php foreach (array_filter(array_map('trim', explode(',', (string)$y['etiketler']))) as $tag): ?>
                            <span class="tag"><i class="fas fa-tag"></i> <?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <aside class="detail-side">
                <div class="side-card cta-side">
                    <h4>Hemen Bilgi Alın</h4>
                    <p>Kombi, klima ve tesisat hizmetleri için bize ulaşın.</p>
                    <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn btn-primary" style="width:100%;justify-content:center">
                        <i class="fas fa-phone-volume"></i> <?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?>
                    </a>
                </div>

                <?php if ($diger): ?>
                <div class="side-card">
                    <h4>Diğer Yazılar</h4>
                    <ul class="side-list">
                        <?php foreach ($diger as $d): ?>
                            <li>
                                <a href="<?= SITE_URL ?>/blog/<?= e($d['slug']) ?>">
                                    <i class="fas fa-newspaper"></i> <?= e($d['baslik']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </section>

    <?php require_once INC_PATH . '/footer.php';
    return;
}

// ==================== LİSTE ====================
$arama = clean($_GET['q'] ?? '');
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 9;
$ofset = ($sayfa - 1) * $limit;

$where = "aktif=1";
$params = [];
if ($arama) {
    $where .= " AND (baslik LIKE ? OR ozet LIKE ? OR icerik LIKE ?)";
    $params[] = "%$arama%"; $params[] = "%$arama%"; $params[] = "%$arama%";
}
$toplam = (int)db_get("SELECT COUNT(*) c FROM blog_yazilari WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));
$yazilar = db_all(
    "SELECT * FROM blog_yazilari WHERE $where
     ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC
     LIMIT $limit OFFSET $ofset",
    $params
);

set_meta([
    'baslik'    => 'Blog — Doğalgaz, Kombi, Klima Rehberi | ' . SITE_TITLE,
    'aciklama'  => 'Doğalgaz tesisatı, kombi bakımı, klima kullanımı hakkında uzman rehberler ve ipuçları.',
    'canonical' => SITE_URL . '/blog' . ($sayfa>1 ? '?sayfa='.$sayfa : ''),
]);
$ekstra = schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'BreadcrumbList',
    'itemListElement'=>[
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>SITE_URL.'/blog'],
    ],
]);
set_meta(['extra_schema' => $ekstra]);

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>Blog</span>
        </nav>
        <h1>Blog</h1>
        <p>Kombi, doğalgaz ve klima dünyasından rehberler, ipuçları ve uzman görüşleri.</p>
    </div>
</section>

<section class="sec">
    <div class="container">
        <form class="blog-search" method="get" action="<?= SITE_URL ?>/blog">
            <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Yazılarda ara…" class="filter-input">
            <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Ara</button>
        </form>

        <?php if ($yazilar): ?>
            <div class="cards-grid">
                <?php foreach ($yazilar as $y): ?>
                    <article class="blog-card">
                        <a href="<?= SITE_URL ?>/blog/<?= e($y['slug']) ?>" class="thumb">
                            <?php if ($y['gorsel']): ?>
                                <img src="<?= UPLOAD_URL . '/' . e($y['gorsel']) ?>" alt="<?= e($y['baslik']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="thumb-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                        </a>
                        <div class="body">
                            <span class="post-date"><i class="fas fa-calendar"></i> <?= tarih_tr($y['yayin_tarihi'] ?: $y['olusturma_tarihi']) ?></span>
                            <h4><a href="<?= SITE_URL ?>/blog/<?= e($y['slug']) ?>"><?= e($y['baslik']) ?></a></h4>
                            <p><?= e(mb_substr((string)$y['ozet'], 0, 130)) ?></p>
                            <a href="<?= SITE_URL ?>/blog/<?= e($y['slug']) ?>" class="svc-link">Devamını Oku <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($toplam_sayfa > 1):
                $base = SITE_URL . '/blog' . ($arama ? '?q='.urlencode($arama).'&' : '?');
            ?>
                <nav class="pagination">
                    <?php if ($sayfa > 1): ?>
                        <a href="<?= $base ?>sayfa=<?= $sayfa-1 ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($p=1;$p<=$toplam_sayfa;$p++): ?>
                        <a href="<?= $base ?>sayfa=<?= $p ?>" class="<?= $p===$sayfa?'active':'' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <?php if ($sayfa < $toplam_sayfa): ?>
                        <a href="<?= $base ?>sayfa=<?= $sayfa+1 ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <p>Henüz yazı bulunmamaktadır. Yakında ilginç içeriklerle buradayız!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
