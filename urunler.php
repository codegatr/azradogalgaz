<?php
require_once __DIR__ . '/config.php';

$arama   = clean($_GET['q'] ?? '');
$kat_id  = (int)($_GET['kategori'] ?? 0);
$marka_id= (int)($_GET['marka'] ?? 0);
$sayfa   = max(1, (int)($_GET['sayfa'] ?? 1));
$limit   = 12;
$ofset   = ($sayfa - 1) * $limit;

$where  = "u.aktif=1";
$params = [];
if ($arama) {
    $where .= " AND (u.ad LIKE ? OR u.kisa_aciklama LIKE ? OR u.aciklama LIKE ?)";
    $params[] = "%$arama%"; $params[] = "%$arama%"; $params[] = "%$arama%";
}
if ($kat_id)   { $where .= " AND u.kategori_id = ?"; $params[] = $kat_id; }
if ($marka_id) { $where .= " AND u.marka_id = ?";    $params[] = $marka_id; }

$toplam = (int)(db_get("SELECT COUNT(*) c FROM urunler u WHERE $where", $params)['c'] ?? 0);
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$urunler = db_all(
    "SELECT u.*, m.ad marka_ad, k.ad kat_ad
     FROM urunler u
     LEFT JOIN markalar m ON m.id=u.marka_id
     LEFT JOIN urun_kategorileri k ON k.id=u.kategori_id
     WHERE $where
     ORDER BY u.one_cikan DESC, u.id DESC
     LIMIT $limit OFFSET $ofset",
    $params
);

$kategoriler = db_all("SELECT id, ad FROM urun_kategorileri WHERE aktif=1 ORDER BY sira ASC, ad ASC");
$markalar    = db_all("SELECT id, ad FROM markalar WHERE aktif=1 ORDER BY ad ASC");

$sayfa_baslik   = 'Ürünler — Kombi, Klima, Tesisat Malzemeleri | Azra Doğalgaz';
$sayfa_aciklama = 'İzmir\'de Demirdöküm, Bosch, Vaillant, Daikin, Mitsubishi marka kombi, klima ve tesisat ürünleri. Yetkili bayi fiyatlarıyla.';
$kanonik_url    = SITE_URL . '/urunler' . ($sayfa > 1 ? '?sayfa=' . $sayfa : '');

$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Ürünler','item'=>SITE_URL.'/urunler'],
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Ürünler</span>
        </div>
        <h1>Ürünlerimiz</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Kombi, klima, ısı pompası, radyatör, kazan ve tesisat ürünleri — yetkili bayi olduğumuz markaların orijinal ürünleri.</p>
    </div>
</section>

<section class="s">
    <div class="container">

        <!-- Filtre formu -->
        <form method="get" class="card" style="background:#fff;padding:20px;margin-bottom:30px">
            <div class="form-row cols-3" style="margin-bottom:12px">
                <div class="field">
                    <label>Ürün Ara</label>
                    <input type="text" name="q" value="<?= e($arama) ?>" class="input" placeholder="Kombi, klima, marka...">
                </div>
                <div class="field">
                    <label>Kategori</label>
                    <select name="kategori">
                        <option value="">Tümü</option>
                        <?php foreach ($kategoriler as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= $kat_id == $k['id'] ? 'selected' : '' ?>><?= e($k['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Marka</label>
                    <select name="marka">
                        <option value="">Tümü</option>
                        <?php foreach ($markalar as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" <?= $marka_id == $m['id'] ? 'selected' : '' ?>><?= e($m['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> Filtrele</button>
                <?php if ($arama || $kat_id || $marka_id): ?>
                <a href="<?= SITE_URL ?>/urunler" class="btn btn-out btn-sm">Filtreleri Temizle</a>
                <?php endif; ?>
                <span style="margin-left:auto;color:var(--c-muted);font-size:.88rem"><?= $toplam ?> ürün bulundu</span>
            </div>
        </form>

        <?php if ($urunler): ?>
        <div class="products">
            <?php foreach ($urunler as $u):
                $fiyat = (float)($u['indirimli_fiyat'] ?: $u['fiyat']);
                $eski  = (float)$u['fiyat'];
                $indirim = ($u['indirimli_fiyat'] && $eski > $fiyat);
            ?>
            <a href="<?= SITE_URL ?>/urun/<?= e($u['slug']) ?>" class="product-card" style="text-decoration:none;color:inherit">
                <div class="product-image">
                    <?php if ($u['gorsel']): ?>
                        <img src="<?= e(UPLOAD_URL . '/' . $u['gorsel']) ?>" alt="<?= e($u['ad']) ?>" loading="lazy">
                    <?php else: ?>
                        <i class="fas fa-fire-flame-curved" style="font-size:3rem;color:var(--c-primary);opacity:.4"></i>
                    <?php endif; ?>
                    <?php if ($indirim): ?><span class="badge">İndirim</span>
                    <?php elseif ($u['one_cikan']): ?><span class="badge">Öne Çıkan</span><?php endif; ?>
                </div>
                <div class="product-body">
                    <?php if (!empty($u['marka_ad'])): ?><span class="product-brand"><?= e($u['marka_ad']) ?></span><?php endif; ?>
                    <h4><?= e($u['ad']) ?></h4>
                    <?php if ($fiyat > 0): ?>
                    <div class="product-price">
                        <?php if ($indirim): ?><span class="old"><?= tl($eski) ?></span><?php endif; ?>
                        <?= tl($fiyat) ?>
                    </div>
                    <?php endif; ?>
                    <span class="btn btn-out btn-sm btn-block">Detaylar <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($toplam_sayfa > 1): ?>
        <div class="pager">
            <?php if ($sayfa > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$sayfa-1])) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($i=1;$i<=$toplam_sayfa;$i++):
                if ($i==$sayfa): ?>
                <span class="active"><?= $i ?></span>
                <?php else: ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$i])) ?>"><?= $i ?></a>
            <?php endif; endfor; ?>
            <?php if ($sayfa < $toplam_sayfa): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$sayfa+1])) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info" style="max-width:680px;margin:0 auto">
            <i class="fas fa-circle-info"></i>
            <div>
                <strong>Filtreye uygun ürün bulunamadı.</strong>
                <?php if ($arama || $kat_id || $marka_id): ?>
                <br><a href="<?= SITE_URL ?>/urunler" style="color:var(--c-primary);font-weight:600">Filtreleri temizleyin</a> veya farklı kelime deneyin.
                <?php else: ?>
                Ürünler henüz eklenmedi. Bilgi almak için bize ulaşın.
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
