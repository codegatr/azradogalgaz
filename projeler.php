<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Projelerimiz — Azra Doğalgaz İzmir';
$sayfa_aciklama = 'İzmir genelinde tamamladığımız doğalgaz, kombi, klima, yerden ısıtma ve mekanik tesisat projelerimiz.';
$kanonik_url    = SITE_URL . '/projeler';

// Filtreleme
$kategori = $_GET['kategori'] ?? '';
$sayfa_no = max(1, (int)($_GET['s'] ?? 1));
$limit = 12;
$offset = ($sayfa_no - 1) * $limit;

// Projeleri çek (tablo varsa)
$projeler = [];
$toplam = 0;
$kategoriler = [];
try {
    $w = "WHERE aktif=1";
    $params = [];
    if ($kategori) { $w .= " AND kategori=?"; $params[] = $kategori; }

    $toplam = (int)db_get("SELECT COUNT(*) c FROM projeler $w", $params)['c'];
    $projeler = db_all("SELECT * FROM projeler $w ORDER BY tarih DESC, id DESC LIMIT $limit OFFSET $offset", $params);

    $kategoriler = db_all("SELECT DISTINCT kategori FROM projeler WHERE aktif=1 AND kategori IS NOT NULL AND kategori<>'' ORDER BY kategori");
} catch (Throwable $e) {
    // Tablo yok — demo placeholder göster
}

$tablo_yok = empty($projeler) && $toplam === 0;

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Projelerimiz</span>
        </div>
        <h1>Projelerimiz</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">İzmir genelinde 2.500'ü aşkın konut ve ticari projede başarılı uygulamalara imza attık. İşte tamamladığımız son işlerden örnekler.</p>
    </div>
</section>

<section class="s">
    <div class="container">

        <?php if ($kategoriler): ?>
        <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-bottom:36px">
            <a href="<?= SITE_URL ?>/projeler" class="btn btn-sm <?= $kategori === '' ? 'btn-primary' : 'btn-out' ?>">Tümü</a>
            <?php foreach ($kategoriler as $k): ?>
                <a href="<?= SITE_URL ?>/projeler?kategori=<?= urlencode($k['kategori']) ?>" class="btn btn-sm <?= $kategori === $k['kategori'] ? 'btn-primary' : 'btn-out' ?>"><?= e($k['kategori']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($tablo_yok): ?>
            <!-- Tablo henüz oluşmamış / kayıt yok — demo placeholder -->
            <div class="alert alert-info" style="max-width:720px;margin:0 auto 30px">
                <i class="fas fa-circle-info" style="margin-right:8px"></i>
                <div>
                    <strong>Proje galerisi yakında dolacak!</strong><br>
                    Tamamladığımız projeleri admin panelinden eklediğimizde burada listelenecek. Şu an için keşif için iletişime geçebilirsiniz.
                </div>
            </div>

            <div class="gallery">
                <?php
                $demo = [
                    ['Bornova Konut Sitesi', 'Doğalgaz Tesisatı', 'fa-fire-flame-curved'],
                    ['Karşıyaka Dubleks Villa', 'Yerden Isıtma + Kombi', 'fa-temperature-arrow-up'],
                    ['Buca Apartman Dairesi', 'Demirdöküm Ademix Kombi', 'fa-screwdriver-wrench'],
                    ['Çiğli Ofis Binası', 'Multi-Split Klima Sistemi', 'fa-snowflake'],
                    ['Konak Restoran', 'Havalandırma + Davlumbaz', 'fa-wind'],
                    ['Gaziemir Müstakil Ev', 'Komple Mekanik Tesisat', 'fa-toolbox'],
                ];
                foreach ($demo as $d): ?>
                <div class="gallery-item placeholder" style="aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:14px;padding:20px;text-align:center">
                    <i class="fas <?= e($d[2]) ?>" style="font-size:2.5rem;color:var(--c-primary);opacity:.6"></i>
                    <div>
                        <strong style="display:block;color:var(--c-text);font-size:.95rem;margin-bottom:4px"><?= e($d[0]) ?></strong>
                        <span style="font-size:.82rem;color:var(--c-muted)"><?= e($d[1]) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="gallery">
                <?php foreach ($projeler as $p): ?>
                <a href="<?= SITE_URL ?>/proje/<?= e($p['slug'] ?? $p['id']) ?>" class="gallery-item <?= $p['gorsel'] ? '' : 'placeholder' ?>">
                    <?php if (!empty($p['gorsel'])): ?>
                        <img src="<?= e($p['gorsel']) ?>" alt="<?= e($p['baslik']) ?>" loading="lazy">
                    <?php else: ?>
                        <i class="fas fa-hammer"></i>
                    <?php endif; ?>
                    <div class="info">
                        <strong style="display:block"><?= e($p['baslik']) ?></strong>
                        <?php if (!empty($p['kategori'])): ?>
                        <span style="font-size:.78rem;opacity:.85"><?= e($p['kategori']) ?> · <?= e($p['lokasyon'] ?? 'İzmir') ?></span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php
            $toplam_sayfa = max(1, (int)ceil($toplam / $limit));
            if ($toplam_sayfa > 1): ?>
            <div class="pager">
                <?php if ($sayfa_no > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['s' => $sayfa_no - 1])) ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                    <?php if ($i === $sayfa_no): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['s' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($sayfa_no < $toplam_sayfa): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['s' => $sayfa_no + 1])) ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Sıradaki proje sizin olabilir</h3>
            <p>Adresinize ücretsiz keşif gönderelim, ihtiyacınızı yerinde analiz edelim.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
