<?php
/**
 * Azra Doğalgaz — Dinamik XML Sitemap
 * Erişim: https://azradogalgaz.com/sitemap.xml (.htaccess yönlendirmesi ile)
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [];

// Sabit sayfalar
$sabit = [
    ['',                  '1.0', 'daily',   date('Y-m-d')],
    ['hizmetler',         '0.9', 'weekly',  date('Y-m-d')],
    ['urunler',           '0.9', 'weekly',  date('Y-m-d')],
    ['kampanyalar',       '0.9', 'daily',   date('Y-m-d')],
    ['hakkimizda',        '0.7', 'monthly', date('Y-m-d')],
    ['iletisim',          '0.8', 'monthly', date('Y-m-d')],
    ['blog',              '0.7', 'weekly',  date('Y-m-d')],
    ['projeler',          '0.7', 'monthly', date('Y-m-d')],
    ['sss',               '0.6', 'monthly', date('Y-m-d')],
    ['bilgi-bankasi',     '0.6', 'monthly', date('Y-m-d')],
    ['kombi-hesaplama',   '0.7', 'monthly', date('Y-m-d')],
    ['klima-hesaplama',   '0.7', 'monthly', date('Y-m-d')],
    ['kvkk',              '0.3', 'yearly',  date('Y-m-d')],
    ['gizlilik',          '0.3', 'yearly',  date('Y-m-d')],
];
foreach ($sabit as $s) {
    $urls[] = [
        'loc'        => SITE_URL . '/' . $s[0],
        'priority'   => $s[1],
        'changefreq' => $s[2],
        'lastmod'    => $s[3],
    ];
}

try {
    // Hizmetler
    foreach (db_all("SELECT slug, COALESCE(guncelleme_tarihi, olusturma_tarihi) g FROM hizmetler WHERE aktif=1") as $r) {
        $urls[] = [
            'loc' => SITE_URL . '/hizmet/' . $r['slug'],
            'priority' => '0.8', 'changefreq' => 'monthly',
            'lastmod' => date('Y-m-d', strtotime($r['g'] ?? 'now')),
        ];
    }
    // Ürünler
    foreach (db_all("SELECT slug, COALESCE(guncelleme_tarihi, olusturma_tarihi) g FROM urunler WHERE aktif=1") as $r) {
        $urls[] = [
            'loc' => SITE_URL . '/urun/' . $r['slug'],
            'priority' => '0.7', 'changefreq' => 'weekly',
            'lastmod' => date('Y-m-d', strtotime($r['g'] ?? 'now')),
        ];
    }
    // Kampanyalar
    foreach (db_all("SELECT slug, olusturma_tarihi g FROM kampanyalar WHERE aktif=1") as $r) {
        $urls[] = [
            'loc' => SITE_URL . '/kampanya/' . $r['slug'],
            'priority' => '0.9', 'changefreq' => 'daily',
            'lastmod' => date('Y-m-d', strtotime($r['g'] ?? 'now')),
        ];
    }
    // Blog yazıları
    foreach (db_all("SELECT slug, COALESCE(yayin_tarihi, olusturma_tarihi) g FROM blog_yazilari WHERE aktif=1") as $r) {
        $urls[] = [
            'loc' => SITE_URL . '/blog/' . $r['slug'],
            'priority' => '0.6', 'changefreq' => 'monthly',
            'lastmod' => date('Y-m-d', strtotime($r['g'] ?? 'now')),
        ];
    }
    // Kategoriler
    foreach (db_all("SELECT slug FROM hizmet_kategorileri WHERE aktif=1") as $r) {
        $urls[] = [
            'loc' => SITE_URL . '/kategori/' . $r['slug'],
            'priority' => '0.7', 'changefreq' => 'monthly',
            'lastmod' => date('Y-m-d'),
        ];
    }
    // Projeler / Referanslar (tablo varsa)
    try {
        foreach (db_all("SELECT slug, COALESCE(guncelleme_tarihi, olusturma_tarihi) g FROM projeler WHERE aktif=1") as $r) {
            $urls[] = [
                'loc' => SITE_URL . '/proje/' . $r['slug'],
                'priority' => '0.6', 'changefreq' => 'monthly',
                'lastmod' => date('Y-m-d', strtotime($r['g'] ?? 'now')),
            ];
        }
    } catch (Throwable $e) { /* projeler tablosu yok veya farklı isimde */ }
    // Marka sayfaları (tablo varsa)
    try {
        foreach (db_all("SELECT slug FROM markalar WHERE aktif=1") as $r) {
            if (!empty($r['slug'])) {
                $urls[] = [
                    'loc' => SITE_URL . '/marka/' . $r['slug'],
                    'priority' => '0.5', 'changefreq' => 'monthly',
                    'lastmod' => date('Y-m-d'),
                ];
            }
        }
    } catch (Throwable $e) { /* yok */ }
} catch (Throwable $e) {
    // veritabanı henüz hazır değilse sadece sabit sayfaları döndür
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
