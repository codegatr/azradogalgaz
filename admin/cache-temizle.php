<?php
/**
 * Cache Temizleme Aracı
 * Yol: /admin/cache-temizle.php
 *
 * Browser ile ziyaret edince:
 *  - OPcache'i sıfırlar (PHP bytecode cache)
 *  - realpath cache'i temizler (file path resolution cache)
 *  - Session yenilemesi önerir
 *
 * Yeni sürümler yüklendi ama panelde değişiklik görünmüyorsa kullan.
 */
declare(strict_types=1);
require_once __DIR__ . '/_baslat.php';
admin_zorunlu();

$sonuc = [];
$bugun = date('Y-m-d H:i:s');

// 1) OPcache reset
if (function_exists('opcache_reset')) {
    $r = @opcache_reset();
    $sonuc[] = ['islem' => 'OPcache Reset',     'durum' => $r ? 'OK' : 'BAŞARISIZ'];
    if (function_exists('opcache_get_status')) {
        $st = @opcache_get_status(false);
        if (is_array($st)) {
            $sonuc[] = ['islem' => 'OPcache Cached Scripts', 'durum' => (string)($st['opcache_statistics']['num_cached_scripts'] ?? '?')];
            $sonuc[] = ['islem' => 'OPcache Memory Used',    'durum' => isset($st['memory_usage']['used_memory']) ? number_format((int)$st['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB' : '?'];
        }
    }
} else {
    $sonuc[] = ['islem' => 'OPcache', 'durum' => 'PHP\'de yok / kapalı'];
}

// 2) Realpath cache
clearstatcache(true);
$sonuc[] = ['islem' => 'Realpath Cache',  'durum' => 'OK'];

// 3) APCu (varsa)
if (function_exists('apcu_clear_cache')) {
    @apcu_clear_cache();
    $sonuc[] = ['islem' => 'APCu Clear',  'durum' => 'OK'];
}

// 4) Yarınki manifest filemtime'ı zorlamak için bir gizli touch
@touch(__DIR__ . '/../assets/css/admin.css');
$sonuc[] = ['islem' => 'admin.css filemtime', 'durum' => 'Yenilendi (browser cache busting için)'];

// Log
log_yaz('cache_temizle', 'Cache temizleme aracı çalıştırıldı', (int)$_kul['id']);

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Cache Temizlendi</h1>
        <p class="page-sub"><?= e($bugun) ?></p>
    </div>
    <a href="panel.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Panele Dön</a>
</div>

<div class="alert alert-ok">
    <i class="fas fa-check-circle"></i> <strong>Cache temizleme tamamlandı.</strong>
    Yeni dosya değişiklikleri (sidebar, modüller) artık görünmeli. Tarayıcıda <kbd>Ctrl+F5</kbd> yapmayı da unutma.
</div>

<div class="card">
    <h3>Sonuç</h3>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th>İşlem</th><th>Durum</th></tr></thead>
        <tbody>
        <?php foreach ($sonuc as $s): ?>
            <tr>
                <td><?= e($s['islem']) ?></td>
                <td><strong><?= e($s['durum']) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card">
    <h3>Hâlâ Sorun Varsa</h3>
    <p style="color:var(--c-muted);font-size:.92rem;line-height:1.7">
    1. <strong>Tarayıcı tam yenileme:</strong> <kbd>Ctrl+Shift+Delete</kbd> ile tüm site verisini sil, sonra <kbd>Ctrl+F5</kbd>.<br>
    2. <strong>DirectAdmin/cPanel OPcache reset:</strong> Hosting paneli → PHP Selector → Reset OPcache butonu.<br>
    3. <strong>Çıkış-giriş:</strong> Çıkış yap, browser kapat-aç, tekrar giriş yap.<br>
    4. <strong>Doğrudan URL:</strong> <code>azradogalgaz.com/admin/faturalar.php</code> adres çubuğuna yapıştır — sidebar gerekmeden çalışır.
    </p>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
