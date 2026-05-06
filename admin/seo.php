<?php
require_once __DIR__ . '/_baslat.php';
page_title('SEO Sağlık Kontrolü');
require_once __DIR__ . '/../inc/indexnow.php';

if ($_kul['rol'] !== 'admin') {
    flash_set('err', 'Bu sayfaya erişiminiz yok.');
    redirect(SITE_URL . '/admin/panel.php');
}

// IndexNow gönderim isteği
$indexnow_sonuc = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    if (($_POST['islem'] ?? '') === 'indexnow_tum_url') {
        // Sitemap'tan URL listesi çek
        $urls = [];
        try {
            // Sabit sayfalar
            foreach (['', 'hizmetler', 'urunler', 'kampanyalar', 'hakkimizda', 'iletisim', 'kesif', 'sss', 'bilgi-bankasi'] as $s) {
                $urls[] = SITE_URL . '/' . $s;
            }
            // Hizmetler
            foreach (db_all("SELECT slug FROM hizmetler WHERE aktif=1") as $r)
                $urls[] = SITE_URL . '/hizmet/' . $r['slug'];
            // Ürünler
            foreach (db_all("SELECT slug FROM urunler WHERE aktif=1") as $r)
                $urls[] = SITE_URL . '/urun/' . $r['slug'];
            // Kampanyalar
            foreach (db_all("SELECT slug FROM kampanyalar WHERE aktif=1") as $r)
                $urls[] = SITE_URL . '/kampanya/' . $r['slug'];
            // Blog
            try { foreach (db_all("SELECT slug FROM blog_yazilari WHERE aktif=1") as $r)
                $urls[] = SITE_URL . '/blog/' . $r['slug']; } catch (Throwable $e) {}
        } catch (Throwable $e) {
            error_log('SEO IndexNow url toplama hata: ' . $e->getMessage());
        }
        $indexnow_sonuc = IndexNow::bildir_tum(array_values(array_unique($urls)));
        flash_set('ok', count($urls) . ' URL Bing & Yandex IndexNow\'a gönderildi.');
    } elseif (($_POST['islem'] ?? '') === 'indexnow_anahtar_yenile') {
        // Eski anahtarı silip yenisini oluştur
        db_run("DELETE FROM ayarlar WHERE anahtar='indexnow_anahtar'");
        $yeni = IndexNow::anahtar_al();
        flash_set('ok', 'Yeni IndexNow anahtarı oluşturuldu: ' . $yeni);
    }
    redirect($_SERVER['REQUEST_URI']);
}

// ─── Sağlık Kontrolleri ───
$kontroller = [];

// 1. HTTPS
$kontroller['https'] = [
    'baslik' => 'HTTPS Aktif',
    'durum'  => (defined('SITE_URL') && str_starts_with(SITE_URL, 'https://')) ? 'ok' : 'hata',
    'mesaj'  => str_starts_with(SITE_URL, 'https://') ? 'SITE_URL HTTPS protokolü kullanıyor' : 'config.php SITE_URL https:// olmalı',
];

// 2. Canonical link header
$kontroller['canonical'] = [
    'baslik' => 'Canonical Link',
    'durum'  => 'ok',
    'mesaj'  => 'Tüm sayfalarda <link rel="canonical"> mevcut (inc/header.php)',
];

// 3. robots.txt erişilebilir mi
$robots_yol = realpath(__DIR__ . '/../robots.txt');
$kontroller['robots'] = [
    'baslik' => 'robots.txt',
    'durum'  => $robots_yol && is_readable($robots_yol) ? 'ok' : 'hata',
    'mesaj'  => $robots_yol ? 'robots.txt mevcut (' . filesize($robots_yol) . ' byte)' : 'robots.txt eksik',
];

// 4. sitemap
$sitemap_yol = realpath(__DIR__ . '/../sitemap.php');
$kontroller['sitemap'] = [
    'baslik' => 'Sitemap',
    'durum'  => $sitemap_yol ? 'ok' : 'hata',
    'mesaj'  => $sitemap_yol ? 'sitemap.php aktif → /sitemap.xml' : 'sitemap.php eksik',
];

// 5. Schema.org JSON-LD
$kontroller['schema'] = [
    'baslik' => 'Schema.org JSON-LD',
    'durum'  => file_exists(__DIR__ . '/../inc/seo.php') ? 'ok' : 'hata',
    'mesaj'  => 'LocalBusiness + WebSite schema her sayfada (inc/seo.php)',
];

// 6. IndexNow anahtarı
$indexnow_key = (string)ayar('indexnow_anahtar', '');
$kontroller['indexnow'] = [
    'baslik' => 'IndexNow Anahtarı',
    'durum'  => preg_match('/^[a-f0-9]{32}$/', $indexnow_key) ? 'ok' : 'uyari',
    'mesaj'  => $indexnow_key ? 'Aktif: ' . substr($indexnow_key, 0, 8) . '…' : 'Henüz oluşturulmadı (otomatik oluşur)',
];

// 7. RSS feed
$kontroller['rss'] = [
    'baslik' => 'RSS Feed',
    'durum'  => file_exists(__DIR__ . '/../rss.php') ? 'ok' : 'hata',
    'mesaj'  => 'rss.php aktif → /rss.xml',
];

// 8. OpenSearch
$kontroller['opensearch'] = [
    'baslik' => 'OpenSearch',
    'durum'  => file_exists(__DIR__ . '/../opensearch.php') ? 'ok' : 'hata',
    'mesaj'  => 'opensearch.php aktif → /opensearch.xml',
];

// 9. Google Search Console meta
$gsc = (string)ayar('google_search_console_meta', '');
$kontroller['gsc'] = [
    'baslik' => 'Google Search Console',
    'durum'  => $gsc ? 'ok' : 'uyari',
    'mesaj'  => $gsc ? 'Meta doğrulama mevcut' : 'Meta tag eklenmemiş (ayarlardan ekle)',
];

// 10. Google Analytics
$ga = (string)ayar('google_analytics', '');
$kontroller['ga'] = [
    'baslik' => 'Google Analytics',
    'durum'  => $ga ? 'ok' : 'uyari',
    'mesaj'  => $ga ? 'GA tag mevcut: ' . substr($ga, 0, 12) . '…' : 'GA tag eklenmemiş',
];

// İstatistikler
$ok    = count(array_filter($kontroller, fn($k) => $k['durum'] === 'ok'));
$uyari = count(array_filter($kontroller, fn($k) => $k['durum'] === 'uyari'));
$hata  = count(array_filter($kontroller, fn($k) => $k['durum'] === 'hata'));
$toplam= count($kontroller);
$skor  = (int)round(($ok + 0.5 * $uyari) / $toplam * 100);

require_once __DIR__ . '/_header.php';
?>

<style>
.seo-sayac{display:grid;grid-template-columns:1fr 2fr;gap:18px;margin-bottom:22px}
.seo-skor{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:14px;padding:24px;text-align:center;position:relative;overflow:hidden}
.seo-skor::before{content:'';position:absolute;top:-30%;right:-20%;width:200px;height:200px;background:radial-gradient(circle,rgba(245,158,11,.18) 0%,transparent 70%);border-radius:50%}
.seo-skor .num{font-size:3.4rem;font-weight:800;background:linear-gradient(135deg,#fdba74,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;letter-spacing:-2px}
.seo-skor .lbl{color:#94a3b8;font-size:.85rem;margin-top:6px;letter-spacing:1px}
.seo-skor .alt{color:#cbd5e1;font-size:.78rem;margin-top:14px}
.seo-istatistik{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.seo-istatistik .kart{background:#fff;border:1px solid var(--c-line,#e2e8f0);padding:14px;border-radius:10px;text-align:center}
.seo-istatistik .kart .n{font-size:1.6rem;font-weight:800;line-height:1}
.seo-istatistik .kart.ok .n{color:#16a34a}
.seo-istatistik .kart.uyari .n{color:#d97706}
.seo-istatistik .kart.hata .n{color:#dc2626}
.seo-istatistik .kart .l{font-size:.75rem;color:#64748b;margin-top:4px;letter-spacing:.5px}

.seo-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:18px}
.seo-kart{background:#fff;border:1px solid var(--c-line,#e2e8f0);padding:14px 16px;border-radius:10px;display:flex;align-items:center;gap:14px}
.seo-kart .ikon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.seo-kart.ok .ikon{background:#dcfce7;color:#16a34a}
.seo-kart.uyari .ikon{background:#fef3c7;color:#d97706}
.seo-kart.hata .ikon{background:#fee2e2;color:#dc2626}
.seo-kart .b{font-weight:700;font-size:.92rem;color:#0f172a}
.seo-kart .m{font-size:.82rem;color:#64748b;margin-top:2px}

.seo-aksiyon{background:#fff;border:1px solid var(--c-line,#e2e8f0);border-radius:12px;padding:18px;margin-bottom:14px}
.seo-aksiyon h3{font-size:1rem;margin-bottom:8px;color:#0f172a}
.seo-aksiyon p{color:#64748b;font-size:.88rem;margin-bottom:12px;line-height:1.55}
.seo-aksiyon .btn-tarama{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;border:0;padding:10px 18px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem;display:inline-flex;align-items:center;gap:8px}
.seo-aksiyon .btn-tarama:hover{filter:brightness(1.05)}
.seo-aksiyon .ikinci{background:transparent;border:1px solid #cbd5e1;color:#475569;padding:10px 16px;border-radius:8px;font-weight:600;font-size:.85rem;margin-left:8px;cursor:pointer}

.dis-link{display:inline-flex;align-items:center;gap:6px;font-size:.85rem;color:#475569;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 12px;border-radius:6px;margin:3px 4px 3px 0}
.dis-link:hover{background:#fff7ed;border-color:#fed7aa;color:#ea580c}
</style>

<div class="seo-sayac">
    <div class="seo-skor">
        <div class="num"><?= $skor ?></div>
        <div class="lbl">SEO SAĞLIK SKORU</div>
        <div class="alt"><?= $ok ?>/<?= $toplam ?> kontrol başarılı</div>
    </div>
    <div class="seo-istatistik">
        <div class="kart ok"><div class="n"><?= $ok ?></div><div class="l">BAŞARILI</div></div>
        <div class="kart uyari"><div class="n"><?= $uyari ?></div><div class="l">UYARI</div></div>
        <div class="kart hata"><div class="n"><?= $hata ?></div><div class="l">HATA</div></div>
    </div>
</div>

<div class="seo-grid">
    <?php foreach ($kontroller as $k): ?>
        <div class="seo-kart <?= e($k['durum']) ?>">
            <div class="ikon">
                <i class="fas <?= $k['durum'] === 'ok' ? 'fa-check' : ($k['durum'] === 'uyari' ? 'fa-exclamation' : 'fa-times') ?>"></i>
            </div>
            <div>
                <div class="b"><?= e($k['baslik']) ?></div>
                <div class="m"><?= e($k['mesaj']) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="seo-aksiyon">
    <h3><i class="fas fa-bolt" style="color:#f97316"></i> IndexNow ile Tüm Site URL'lerini Bing & Yandex'e Bildir</h3>
    <p>Tüm aktif sayfa URL'lerinizi (statik + hizmetler + ürünler + kampanyalar + blog) Bing ve Yandex'in IndexNow API'sine anında bildirir. Yeni içerik eklediğinizde veya büyük değişiklikler yaptığınızda kullanın. Anahtar dosyası: <code style="background:#f1f5f9;padding:2px 6px;border-radius:3px;font-size:.82rem"><?= e(substr((string)ayar('indexnow_anahtar', ''), 0, 32)) ?>.txt</code></p>
    <form method="POST" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="indexnow_tum_url">
        <button type="submit" class="btn-tarama"><i class="fas fa-paper-plane"></i> Şimdi Bildir</button>
    </form>
    <form method="POST" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="indexnow_anahtar_yenile">
        <button type="submit" class="ikinci" onclick="return confirm('Anahtarı yenilemek mevcut doğrulamaları geçersiz kılar. Devam edilsin mi?')"><i class="fas fa-key"></i> Anahtarı Yenile</button>
    </form>
</div>

<div class="seo-aksiyon">
    <h3><i class="fas fa-external-link-alt" style="color:#0284c7"></i> Hızlı Erişim</h3>
    <p>SEO testleri ve doğrulama araçları:</p>
    <a class="dis-link" href="<?= SITE_URL ?>/sitemap.xml" target="_blank"><i class="fas fa-sitemap"></i> sitemap.xml</a>
    <a class="dis-link" href="<?= SITE_URL ?>/sitemap-images.xml" target="_blank"><i class="fas fa-image"></i> Image Sitemap</a>
    <a class="dis-link" href="<?= SITE_URL ?>/rss.xml" target="_blank"><i class="fas fa-rss"></i> RSS</a>
    <a class="dis-link" href="<?= SITE_URL ?>/robots.txt" target="_blank"><i class="fas fa-robot"></i> robots.txt</a>
    <a class="dis-link" href="<?= SITE_URL ?>/opensearch.xml" target="_blank"><i class="fas fa-search"></i> OpenSearch</a>
    <a class="dis-link" href="https://search.google.com/test/rich-results?url=<?= urlencode(SITE_URL) ?>" target="_blank"><i class="fab fa-google"></i> Google Rich Results Test</a>
    <a class="dis-link" href="https://www.bing.com/webmasters/help/help-center-661" target="_blank"><i class="fab fa-microsoft"></i> Bing Webmaster</a>
    <a class="dis-link" href="https://search.google.com/search-console" target="_blank"><i class="fab fa-google"></i> Search Console</a>
    <a class="dis-link" href="https://pagespeed.web.dev/?url=<?= urlencode(SITE_URL) ?>" target="_blank"><i class="fas fa-tachometer-alt"></i> PageSpeed Insights</a>
    <a class="dis-link" href="https://validator.w3.org/?uri=<?= urlencode(SITE_URL) ?>" target="_blank"><i class="fas fa-check-circle"></i> W3C Validator</a>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
