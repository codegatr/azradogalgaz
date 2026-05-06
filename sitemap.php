<?php
/**
 * Azra Doğalgaz — Dinamik XML Sitemap (v1.12.25)
 * 
 * Erişim:
 *   - https://azradogalgaz.com/sitemap.xml         → ana sitemap (tüm URL'ler + image)
 *   - https://azradogalgaz.com/sitemap-images.xml  → sadece görsel sitemap
 *
 * Özellikler:
 *   - W3C ISO-8601 lastmod (2026-04-29T08:30:00+03:00)
 *   - <image:image> namespace ile resim bilgileri (Google Image Search)
 *   - Trailing slash yok (canonical ile uyumlu)
 *   - HTTP cache header'ları (1 saat)
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('X-Robots-Tag: noindex, follow'); // sitemap'ın kendisi indekslenmesin

$tur = $_GET['tur'] ?? 'tum';

/** ISO-8601 W3C tarihi — Google sitemap.org standard */
function iso_tarih($zaman = null): string {
    if ($zaman === null) return date('c');
    if (is_string($zaman)) $zaman = strtotime($zaman) ?: time();
    return date('c', (int)$zaman);
}

$urls = [];

// ─── Sabit sayfalar ───
$sabit = [
    ['',                  '1.0', 'daily',   time()],
    ['hizmetler',         '0.9', 'weekly',  time()],
    ['urunler',           '0.9', 'weekly',  time()],
    ['kampanyalar',       '0.9', 'daily',   time()],
    ['hakkimizda',        '0.7', 'monthly', time()],
    ['iletisim',          '0.8', 'monthly', time()],
    ['blog',              '0.7', 'weekly',  time()],
    ['projeler',          '0.7', 'monthly', time()],
    ['sss',               '0.6', 'monthly', time()],
    ['kesif',             '0.9', 'monthly', time()],
    ['bilgi-bankasi',     '0.6', 'monthly', time()],
    ['kombi-hesaplama',   '0.7', 'monthly', time()],
    ['klima-hesaplama',   '0.7', 'monthly', time()],
    ['kvkk',              '0.3', 'yearly',  time()],
    ['gizlilik',          '0.3', 'yearly',  time()],
    ['cerez',             '0.2', 'yearly',  time()],
    ['iller',             '0.85','weekly',  time()],
];
foreach ($sabit as $s) {
    $urls[] = [
        'loc'        => SITE_URL . '/' . $s[0],
        'priority'   => $s[1],
        'changefreq' => $s[2],
        'lastmod'    => iso_tarih($s[3]),
    ];
}

// ─── v1.12.27: 81 il SEO landing URL'leri ───
// 81 il hub + 81 × 8 hizmet kombinasyonları = 729 URL
require_once __DIR__ . '/inc/iller.php';
$oncelik_priority = [1 => '0.95', 2 => '0.85', 3 => '0.7', 4 => '0.5'];
foreach (iller_listesi() as $il_slug => $il) {
    $p = $oncelik_priority[$il['oncelik']] ?? '0.5';
    // İl hub
    $urls[] = [
        'loc'        => SITE_URL . '/il/' . $il_slug,
        'priority'   => $p,
        'changefreq' => 'monthly',
        'lastmod'    => iso_tarih(),
    ];
    // İl × hizmet
    foreach (array_keys(hizmetler_listesi()) as $hizmet_slug) {
        $urls[] = [
            'loc'        => SITE_URL . '/il/' . $il_slug . '/' . $hizmet_slug,
            // İl × hizmet sayfaları il hub'tan biraz daha düşük öncelikli
            'priority'   => number_format(max(0.3, (float)$p - 0.1), 2),
            'changefreq' => 'monthly',
            'lastmod'    => iso_tarih(),
        ];
    }
}

try {
    // Hizmetler — kapak resmi varsa image:image olarak ekle
    foreach (db_all("SELECT slug, kapak, COALESCE(guncelleme_tarihi, olusturma_tarihi) g FROM hizmetler WHERE aktif=1") as $r) {
        $u = [
            'loc' => SITE_URL . '/hizmet/' . $r['slug'],
            'priority' => '0.8', 'changefreq' => 'monthly',
            'lastmod' => iso_tarih($r['g']),
        ];
        if (!empty($r['kapak'])) $u['image'] = SITE_URL . '/' . ltrim((string)$r['kapak'], '/');
        $urls[] = $u;
    }
    // Ürünler
    foreach (db_all("SELECT slug, kapak, COALESCE(guncelleme_tarihi, olusturma_tarihi) g FROM urunler WHERE aktif=1") as $r) {
        $u = [
            'loc' => SITE_URL . '/urun/' . $r['slug'],
            'priority' => '0.7', 'changefreq' => 'weekly',
            'lastmod' => iso_tarih($r['g']),
        ];
        if (!empty($r['kapak'])) $u['image'] = SITE_URL . '/' . ltrim((string)$r['kapak'], '/');
        $urls[] = $u;
    }
    // Kampanyalar
    foreach (db_all("SELECT slug, kapak, olusturma_tarihi g FROM kampanyalar WHERE aktif=1") as $r) {
        $u = [
            'loc' => SITE_URL . '/kampanya/' . $r['slug'],
            'priority' => '0.9', 'changefreq' => 'daily',
            'lastmod' => iso_tarih($r['g']),
        ];
        if (!empty($r['kapak'])) $u['image'] = SITE_URL . '/' . ltrim((string)$r['kapak'], '/');
        $urls[] = $u;
    }
    // Blog yazıları
    foreach (db_all("SELECT slug, kapak, COALESCE(yayin_tarihi, olusturma_tarihi) g FROM blog_yazilari WHERE aktif=1") as $r) {
        $u = [
            'loc' => SITE_URL . '/blog/' . $r['slug'],
            'priority' => '0.6', 'changefreq' => 'monthly',
            'lastmod' => iso_tarih($r['g']),
        ];
        if (!empty($r['kapak'])) $u['image'] = SITE_URL . '/' . ltrim((string)$r['kapak'], '/');
        $urls[] = $u;
    }
    // Kategoriler
    foreach (db_all("SELECT slug FROM hizmet_kategorileri WHERE aktif=1") as $r) {
        $urls[] = [
            'loc' => SITE_URL . '/kategori/' . $r['slug'],
            'priority' => '0.7', 'changefreq' => 'monthly',
            'lastmod' => iso_tarih(),
        ];
    }
    // Projeler / Referanslar
    try {
        foreach (db_all("SELECT slug, kapak, COALESCE(guncelleme_tarihi, olusturma_tarihi) g FROM projeler WHERE aktif=1") as $r) {
            $u = [
                'loc' => SITE_URL . '/proje/' . $r['slug'],
                'priority' => '0.6', 'changefreq' => 'monthly',
                'lastmod' => iso_tarih($r['g']),
            ];
            if (!empty($r['kapak'])) $u['image'] = SITE_URL . '/' . ltrim((string)$r['kapak'], '/');
            $urls[] = $u;
        }
    } catch (Throwable $e) { /* tablo farklı */ }
    // Markalar
    try {
        foreach (db_all("SELECT slug, logo FROM markalar WHERE aktif=1") as $r) {
            if (!empty($r['slug'])) {
                $u = [
                    'loc' => SITE_URL . '/marka/' . $r['slug'],
                    'priority' => '0.5', 'changefreq' => 'monthly',
                    'lastmod' => iso_tarih(),
                ];
                if (!empty($r['logo'])) $u['image'] = SITE_URL . '/' . ltrim((string)$r['logo'], '/');
                $urls[] = $u;
            }
        }
    } catch (Throwable $e) { /* yok */ }
} catch (Throwable $e) {
    error_log('sitemap DB hata: ' . $e->getMessage());
}

// ─── Eğer sadece görsel sitemap istendiyse ───
if ($tur === 'images') {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    foreach ($urls as $u) {
        if (empty($u['image'])) continue;
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
        echo "    <image:image>\n";
        echo "      <image:loc>" . htmlspecialchars($u['image'], ENT_XML1) . "</image:loc>\n";
        echo "    </image:image>\n";
        echo "  </url>\n";
    }
    echo '</urlset>';
    exit;
}

// ─── Tüm URL'ler ───
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n"
   . '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    if (!empty($u['image'])) {
        echo "    <image:image>\n";
        echo "      <image:loc>" . htmlspecialchars($u['image'], ENT_XML1) . "</image:loc>\n";
        echo "    </image:image>\n";
    }
    echo "  </url>\n";
}
echo '</urlset>';
