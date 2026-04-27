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

$toplam = (int)db_get("SELECT COUNT(*) c FROM urunler u WHERE $where", $params)['c'];
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

set_meta([
    'baslik'    => 'Ürünler — Kombi, Klima, Tesisat Malzemeleri | ' . SITE_TITLE,
    'aciklama'  => 'İzmir\'de Demirdöküm, Bosch, Vaillant, Baymak ve Daikin marka kombi, klima ve tesisat ürünleri. Yetkili bayi fiyatlarıyla.',
    'kelimeler' => 'demirdöküm kombi, bosch kombi, vaillant kombi, daikin klima, izmir kombi fiyat',
    'canonical' => SITE_URL . '/urunler' . ($sayfa > 1 ? '?sayfa=' . $sayfa : ''),
]);

$ekstra = schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Ürünler','item'=>SITE_URL.'/urunler'],
    ],
]);
set_meta(['extra_schema' => $ekstra]);

// QS yardımcı
function qs(array $degis): string {
    $g = $_GET; foreach ($degis as $k=>$v) $g[$k] = $v;
    foreach ($g as $k=>$v) if ($v === '' || $v === 0 || $v === '0') unset($g[$k]);
    return $g ? '?' . http_build_query($g) : '';
}

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>Ürünler</span>
        </nav>
        <h1>Ürünler</h1>
        <p>Demirdöküm, Bosch, Vaillant, Baymak ve daha fazlası — yetkili bayi güvencesiyle.</p>
    </div>
</section>

<section class="sec">
    <div class="container list-grid">
        <aside class="filter-side">
            <form class="filter-form" method="get" action="<?= SITE_URL ?>/urunler">
                <h4><i class="fas fa-magnifying-glass"></i> Arama</h4>
                <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Ürün ara…" class="filter-input">

                <?php if ($kategoriler): ?>
                <h4 style="margin-top:18px"><i class="fas fa-tags"></i> Kategori</h4>
                <select name="kategori" class="filter-input">
                    <option value="0">Tümü</option>
                    <?php foreach ($kategoriler as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= $kat_id===(int)$k['id']?'selected':'' ?>><?= e($k['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <?php if ($markalar): ?>
                <h4 style="margin-top:18px"><i class="fas fa-trademark"></i> Marka</h4>
                <select name="marka" class="filter-input">
                    <option value="0">Tümü</option>
                    <?php foreach ($markalar as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" <?= $marka_id===(int)$m['id']?'selected':'' ?>><?= e($m['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:18px" type="submit">
                    <i class="fas fa-filter"></i> Filtrele
                </button>
                <?php if ($arama || $kat_id || $marka_id): ?>
                    <a href="<?= SITE_URL ?>/urunler" class="filter-clear">Filtreleri Temizle</a>
                <?php endif; ?>
            </form>
        </aside>

        <div class="list-content">
            <div class="list-toolbar">
                <span><strong><?= $toplam ?></strong> ürün bulundu</span>
                <span class="muted">Sayfa <?= $sayfa ?> / <?= $toplam_sayfa ?></span>
            </div>

            <?php if ($urunler): ?>
                <div class="cards-grid">
                    <?php foreach ($urunler as $u): ?>
                        <article class="product-card">
                            <div class="thumb">
                                <?php if ($u['gorsel']): ?>
                                    <img src="<?= UPLOAD_URL . '/' . e($u['gorsel']) ?>" alt="<?= e($u['ad']) ?>" loading="lazy">
                                <?php else: ?>
                                    <i class="fas fa-fire-flame-curved"></i>
                                <?php endif; ?>
                                <?php if ((float)$u['indirimli_fiyat'] > 0 && $u['indirimli_fiyat'] < $u['fiyat']): ?>
                                    <span class="badge-discount">İndirim</span>
                                <?php elseif ($u['one_cikan']): ?>
                                    <span class="badge-featured">Öne Çıkan</span>
                                <?php endif; ?>
                            </div>
                            <div class="body">
                                <span class="brand"><?= e($u['marka_ad'] ?? '—') ?></span>
                                <h4><?= e($u['ad']) ?></h4>
                                <p class="desc"><?= e(mb_substr((string)$u['kisa_aciklama'], 0, 110)) ?></p>
                                <?php $f = (float)($u['indirimli_fiyat'] ?: $u['fiyat']); if ($f > 0): ?>
                                    <div class="price">
                                        <?php if ((float)$u['indirimli_fiyat'] > 0 && $u['indirimli_fiyat'] < $u['fiyat']): ?>
                                            <span class="old"><?= number_format((float)$u['fiyat'], 0, ',', '.') ?> ₺</span>
                                        <?php endif; ?>
                                        <?= number_format($f, 0, ',', '.') ?> ₺
                                    </div>
                                <?php endif; ?>
                                <a href="<?= SITE_URL ?>/urun/<?= e($u['slug']) ?>" class="btn btn-primary">İncele <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($toplam_sayfa > 1): ?>
                    <nav class="pagination">
                        <?php if ($sayfa > 1): ?>
                            <a href="<?= qs(['sayfa'=>$sayfa-1]) ?>"><i class="fas fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php for ($p=1; $p<=$toplam_sayfa; $p++):
                            if ($p > 3 && $p < $sayfa-1) continue;
                            if ($p < $toplam_sayfa-2 && $p > $sayfa+1) continue;
                        ?>
                            <a href="<?= qs(['sayfa'=>$p]) ?>" class="<?= $p===$sayfa?'active':'' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($sayfa < $toplam_sayfa): ?>
                            <a href="<?= qs(['sayfa'=>$sayfa+1]) ?>"><i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Aramanıza uygun ürün bulunamadı.</p>
                    <a href="<?= SITE_URL ?>/urunler" class="btn btn-outline">Tüm Ürünler</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
