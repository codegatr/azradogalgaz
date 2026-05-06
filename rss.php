<?php
/**
 * Azra Doğalgaz — RSS 2.0 Feed (v1.12.25)
 * Erişim: https://azradogalgaz.com/rss.xml
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$firma = (string)ayar('firma_unvan', 'Azra Doğalgaz');
$desc  = (string)ayar('site_aciklama', defined('SITE_DESC') ? SITE_DESC : 'İzmir doğalgaz tesisat hizmetleri');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
echo '<channel>' . "\n";
echo '  <title>' . htmlspecialchars($firma, ENT_XML1) . '</title>' . "\n";
echo '  <link>' . SITE_URL . '</link>' . "\n";
echo '  <atom:link href="' . SITE_URL . '/rss.xml" rel="self" type="application/rss+xml"/>' . "\n";
echo '  <description>' . htmlspecialchars($desc, ENT_XML1) . '</description>' . "\n";
echo '  <language>tr-TR</language>' . "\n";
echo '  <copyright>© ' . date('Y') . ' ' . htmlspecialchars($firma, ENT_XML1) . '</copyright>' . "\n";
echo '  <lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
echo '  <generator>Azra Doğalgaz CMS v1.12.25</generator>' . "\n";
echo '  <image>' . "\n";
echo '    <url>' . SITE_URL . '/assets/img/logo.png</url>' . "\n";
echo '    <title>' . htmlspecialchars($firma, ENT_XML1) . '</title>' . "\n";
echo '    <link>' . SITE_URL . '</link>' . "\n";
echo '  </image>' . "\n";

$items = [];
try {
    foreach (db_all("SELECT slug, baslik, aciklama, COALESCE(yayin_tarihi, olusturma_tarihi) tarih
                     FROM blog_yazilari WHERE aktif=1
                     ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC LIMIT 30") as $r) {
        $items[] = [
            'tip' => 'blog',
            'slug' => $r['slug'],
            'baslik' => $r['baslik'],
            'aciklama' => $r['aciklama'] ?? '',
            'tarih' => $r['tarih'],
        ];
    }
} catch (Throwable $e) { /* blog tablosu yoksa atla */ }

try {
    foreach (db_all("SELECT slug, baslik, aciklama, olusturma_tarihi tarih
                     FROM kampanyalar WHERE aktif=1
                     ORDER BY olusturma_tarihi DESC LIMIT 20") as $r) {
        $items[] = [
            'tip' => 'kampanya',
            'slug' => $r['slug'],
            'baslik' => '[Kampanya] ' . $r['baslik'],
            'aciklama' => $r['aciklama'] ?? '',
            'tarih' => $r['tarih'],
        ];
    }
} catch (Throwable $e) {}

// Tarihe göre sırala
usort($items, fn($a, $b) => strtotime((string)$b['tarih']) - strtotime((string)$a['tarih']));

foreach (array_slice($items, 0, 30) as $it) {
    $url = SITE_URL . '/' . $it['tip'] . '/' . $it['slug'];
    echo '  <item>' . "\n";
    echo '    <title>' . htmlspecialchars((string)$it['baslik'], ENT_XML1) . '</title>' . "\n";
    echo '    <link>' . htmlspecialchars($url, ENT_XML1) . '</link>' . "\n";
    echo '    <guid isPermaLink="true">' . htmlspecialchars($url, ENT_XML1) . '</guid>' . "\n";
    echo '    <pubDate>' . date('r', strtotime((string)$it['tarih'])) . '</pubDate>' . "\n";
    echo '    <description>' . htmlspecialchars((string)$it['aciklama'], ENT_XML1) . '</description>' . "\n";
    echo '  </item>' . "\n";
}

echo '</channel>' . "\n";
echo '</rss>';
