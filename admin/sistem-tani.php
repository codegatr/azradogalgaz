<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php'; // Sema'yı çalıştır
page_title('Sistem Tanılama');
admin_zorunlu();

// DB tabloları + kayıt sayıları
$gerekli_tablolar = [
    'cariler'              => 'Müşteri/tedarikçi cari kartları',
    'cari_hareketler'      => 'Borç/alacak/tahsilat/ödeme hareketleri',
    'faturalar'            => 'Satış/alış faturaları',
    'fatura_kalemleri'     => 'Fatura satır kalemleri',
    'fisler'               => 'Fiş kayıtları (tahsilat/ödeme/gider)',
    'fis_kalemleri'        => 'Fiş satır kalemleri',
    'bakim_hatirlaticilari'=> 'Bakım hatırlatma sistemi',
    'ayarlar'              => 'Sistem ayarları (GitHub token, vs.)',
    'kullanicilar'         => 'Admin kullanıcıları',
    'guncelleme_log'       => 'Güncelleme geçmişi',
];

$tablo_durumu = [];
foreach ($gerekli_tablolar as $tablo => $aciklama) {
    try {
        $sayim = db_get("SELECT COUNT(*) c FROM `$tablo`");
        $tablo_durumu[$tablo] = ['var' => true, 'sayi' => (int)$sayim['c'], 'aciklama' => $aciklama, 'hata' => null];
    } catch (Throwable $e) {
        $tablo_durumu[$tablo] = ['var' => false, 'sayi' => 0, 'aciklama' => $aciklama, 'hata' => $e->getMessage()];
    }
}

// Eksik tablo aksiyonu (manuel CREATE TABLE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    if (($_POST['islem'] ?? '') === 'sema_olustur') {
        try {
            require_once __DIR__ . '/../inc/sema-muhasebe.php'; // tekrar yükle
            // Yukarıda zaten çalıştı, biraz daha sıkı kontrol edelim:
            $sql_dosyasi = file_get_contents(__DIR__ . '/../inc/sema-muhasebe.php');
            // Sema dosyası exec yaparken hata olursa try/catch ile yutuluyor — error_log'a bak
            flash_set('ok', 'Şema yenilendi. Sayfayı yenile.');
        } catch (Throwable $e) {
            flash_set('err', 'Hata: ' . $e->getMessage());
        }
        redirect('sistem-tani.php');
    }
}

// PHP & sistem bilgileri
$mevcut_surum = (string)(json_decode(@file_get_contents(__DIR__ . '/../manifest.json'), true)['version'] ?? '?');
$opcache_aktif = function_exists('opcache_get_status') && @opcache_get_status(false) !== false;
$opcache_cnt = $opcache_aktif ? (@opcache_get_status(false)['opcache_statistics']['num_cached_scripts'] ?? '?') : '?';
$db_user_yetki = false;
try {
    db()->exec("CREATE TABLE IF NOT EXISTS _yetki_test (id INT)");
    db()->exec("DROP TABLE IF EXISTS _yetki_test");
    $db_user_yetki = true;
} catch (Throwable $e) {}

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Sistem Tanılama</h1>
        <p class="page-sub">DB tablo varlığı, sürüm, sistem sağlığı.</p>
    </div>
    <a href="panel.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Panel</a>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= e($f['tip']) ?>"><?= $f['msg'] ?></div>
<?php endforeach; ?>

<?php
$eksikler = array_filter($tablo_durumu, fn($t) => !$t['var']);
if ($eksikler):
?>
<div class="alert alert-err">
    <strong><i class="fas fa-triangle-exclamation"></i> <?= count($eksikler) ?> tablo eksik!</strong>
    Aşağıdaki tablolar veritabanında yok. <code>inc/sema-muhasebe.php</code> dosyası bu sayfayı yüklerken çalıştırılır — eğer hâlâ eksikse muhtemelen DB user'ın CREATE TABLE yetkisi yok ya da sema dosyası sunucuya yüklenmemiş.
    <form method="post" style="display:inline-block;margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="sema_olustur">
        <button class="btn btn-pri"><i class="fas fa-database"></i> Şemayı Yeniden Çalıştır</button>
    </form>
</div>
<?php endif; ?>

<div class="stats">
    <div class="stat">
        <div class="ico <?= $eksikler ? 'r' : 'g' ?>"><i class="fas fa-database"></i></div>
        <div><strong><?= count($tablo_durumu) - count($eksikler) ?> / <?= count($tablo_durumu) ?></strong><span>Tablo Mevcut</span></div>
    </div>
    <div class="stat">
        <div class="ico b"><i class="fas fa-code-branch"></i></div>
        <div><strong>v<?= e($mevcut_surum) ?></strong><span>Yüklü Sürüm</span></div>
    </div>
    <div class="stat">
        <div class="ico <?= $db_user_yetki ? 'g' : 'r' ?>"><i class="fas fa-key"></i></div>
        <div><strong><?= $db_user_yetki ? 'VAR' : 'YOK' ?></strong><span>CREATE TABLE Yetkisi</span></div>
    </div>
    <div class="stat">
        <div class="ico <?= $opcache_aktif ? 'g' : 'y' ?>"><i class="fas fa-rocket"></i></div>
        <div><strong><?= $opcache_aktif ? $opcache_cnt . ' script' : 'KAPALI' ?></strong><span>OPcache</span></div>
    </div>
</div>

<div class="card">
    <h3>Veritabanı Tabloları</h3>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr><th style="width:40px">#</th><th>Tablo</th><th>Açıklama</th><th class="num" style="text-align:right;width:90px">Kayıt</th><th style="width:90px">Durum</th></tr>
            </thead>
            <tbody>
                <?php foreach ($tablo_durumu as $tablo => $d): ?>
                    <tr>
                        <td><i class="fas <?= $d['var'] ? 'fa-check-circle' : 'fa-times-circle' ?>" style="color:<?= $d['var'] ? '#16a34a' : '#dc2626' ?>"></i></td>
                        <td><code><?= e($tablo) ?></code></td>
                        <td style="color:var(--c-muted);font-size:.88rem"><?= e($d['aciklama']) ?>
                            <?php if ($d['hata']): ?><br><small style="color:#dc2626;font-family:monospace"><?= e(mb_substr($d['hata'], 0, 100)) ?></small><?php endif; ?>
                        </td>
                        <td class="num" style="text-align:right;font-weight:600"><?= $d['var'] ? number_format((int)$d['sayi']) : '—' ?></td>
                        <td>
                            <?php if ($d['var']): ?>
                                <span class="badge badge-ok">VAR</span>
                            <?php else: ?>
                                <span class="badge badge-no">EKSİK</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Sistem Bilgisi</h3>
    <div class="tbl-wrap">
        <table class="tbl">
            <tr><td><strong>PHP Sürümü</strong></td><td><code><?= PHP_VERSION ?></code></td></tr>
            <tr><td><strong>MySQL Sürümü</strong></td><td><code><?= e((string)db()->getAttribute(PDO::ATTR_SERVER_VERSION)) ?></code></td></tr>
            <tr><td><strong>Web Server</strong></td><td><code><?= e($_SERVER['SERVER_SOFTWARE'] ?? '?') ?></code></td></tr>
            <tr><td><strong>Document Root</strong></td><td><code><?= e($_SERVER['DOCUMENT_ROOT'] ?? '?') ?></code></td></tr>
            <tr><td><strong>Yüklü Sürüm</strong></td><td><code>v<?= e($mevcut_surum) ?></code></td></tr>
            <tr><td><strong>OPcache</strong></td><td><?= $opcache_aktif ? 'Aktif (<code>' . $opcache_cnt . '</code> script cached)' : 'Kapalı veya yok' ?></td></tr>
            <tr><td><strong>CREATE TABLE yetkisi</strong></td><td><?= $db_user_yetki ? '<span class="badge badge-ok">VAR</span>' : '<span class="badge badge-no">YOK — DB user yetkisi gerekli</span>' ?></td></tr>
            <tr><td><strong>Sema Dosyası</strong></td><td>
                <?php $sm = __DIR__.'/../inc/sema-muhasebe.php'; ?>
                <?php if (file_exists($sm)): ?>
                    <span class="badge badge-ok">VAR</span> <?= round(filesize($sm)/1024,1) ?> KB
                <?php else: ?>
                    <span class="badge badge-no">YOK — Akıllı Güncelleme ile inc/sema-muhasebe.php çek!</span>
                <?php endif; ?>
            </td></tr>
        </table>
    </div>
</div>

<div class="card">
    <h3>Manuel SQL (CREATE TABLE) Komutları</h3>
    <p style="color:var(--c-muted);font-size:.9rem">
        Eğer "Şemayı Yeniden Çalıştır" butonu işe yaramadıysa, aşağıdaki SQL'i kopyalayıp <strong>phpMyAdmin → SQL</strong> sekmesinde manuel çalıştır:
    </p>
    <pre style="background:#0a0f1f;color:#aaffcc;font-family:monospace;font-size:.78rem;padding:14px;border-radius:6px;max-height:400px;overflow:auto"><?php
$sm = __DIR__.'/../inc/sema-muhasebe.php';
if (file_exists($sm)) {
    // SQL kısmını çıkar
    $icerik = file_get_contents($sm);
    if (preg_match_all('/db\(\)->exec\("(.+?)"\);?/s', $icerik, $m)) {
        foreach ($m[1] as $sql) {
            echo trim($sql) . ";\n\n";
        }
    } else {
        echo "Sema dosyası içeriği parse edilemedi.";
    }
} else {
    echo "inc/sema-muhasebe.php dosyası bulunamadı. Akıllı Güncelleme ile çek.";
}
?></pre>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
