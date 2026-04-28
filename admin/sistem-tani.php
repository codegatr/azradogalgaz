<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/migrator.php';
page_title('Sistem Tanılama');
admin_zorunlu();

$M = new Migrator(__DIR__ . '/..');

// Aksiyonlar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'migrasyon_uygula') {
        $sonuc = $M->bekleyenleri_uygula();
        if ($sonuc['ok']) {
            $sayi = count($sonuc['uygulananlar']);
            if ($sayi) {
                flash_set('ok', "$sayi migration uygulandı. Tablolar oluşturuldu.");
                log_yaz('migrasyon_uygula', "$sayi migration uygulandı", (int)$_kul['id']);
                $M->sentinel_kaydet();
            } else {
                flash_set('ok', 'Bekleyen migration yok, sistem güncel.');
            }
        } else {
            flash_set('err', 'Migration hatası: ' . ($sonuc['hatalar'][0]['hata'] ?? '?'));
        }
        redirect('sistem-tani.php');
    }

    if ($islem === 'migrasyon_yeniden') {
        $dosya = basename((string)($_POST['dosya'] ?? ''));
        $r = $M->uygula($dosya);
        flash_set($r['ok'] ? 'ok' : 'err', $r['ok'] ? "Yeniden uygulandı: $dosya ({$r['stmts']} statement, {$r['sure_ms']}ms)" : "Hata: " . ($r['hata'] ?? '?'));
        redirect('sistem-tani.php');
    }
}

// DB tabloları + kayıt sayıları
$gerekli_tablolar = [
    'cariler'              => 'Müşteri/tedarikçi cari kartları',
    'cari_hareketler'      => 'Borç/alacak/tahsilat/ödeme hareketleri',
    'faturalar'            => 'Satış/alış faturaları',
    'fatura_kalemleri'     => 'Fatura satır kalemleri',
    'fisler'               => 'Fiş kayıtları',
    'fis_kalemleri'        => 'Fiş satır kalemleri',
    'bakim_hatirlaticilari'=> 'Bakım hatırlatma sistemi',
    'migrasyonlar'         => 'Uygulanan migration takibi',
    'ayarlar'              => 'Sistem ayarları',
    'kullanicilar'         => 'Admin kullanıcıları',
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

// Migration durumu
$tum_migrasyonlar = $M->tum_dosyalar();
$uygulanmis      = $M->uygulanmislar();
$bekleyenler     = $M->bekleyenler();

// Sistem
$mevcut_surum = (string)(json_decode(@file_get_contents(__DIR__ . '/../manifest.json'), true)['version'] ?? '?');
$opcache_aktif = function_exists('opcache_get_status') && @opcache_get_status(false) !== false;
$opcache_cnt = $opcache_aktif ? (@opcache_get_status(false)['opcache_statistics']['num_cached_scripts'] ?? '?') : '?';
$db_user_yetki = false;
try {
    db()->exec("CREATE TABLE IF NOT EXISTS _yetki_test (id INT)");
    db()->exec("DROP TABLE IF EXISTS _yetki_test");
    $db_user_yetki = true;
} catch (Throwable $e) {}

$eksik_tablolar = array_filter($tablo_durumu, fn($t) => !$t['var']);

require_once __DIR__ . '/_header.php';
$csrf = csrf_field();
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Sistem Tanılama</h1>
        <p class="page-sub">DB tablo varlığı, migration durumu, sistem sağlığı.</p>
    </div>
    <a href="panel.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Panel</a>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= e($f['tip']) ?>"><?= $f['msg'] ?></div>
<?php endforeach; ?>

<?php if ($bekleyenler): ?>
<div class="alert alert-err">
    <strong><i class="fas fa-triangle-exclamation"></i> <?= count($bekleyenler) ?> bekleyen migration var!</strong>
    Aşağıdaki SQL dosyaları henüz uygulanmamış. Bu yüzden Faturalar/Fişler/Cariler boş gözüküyor olabilir.
    <form method="post" style="display:inline-block;margin-top:10px">
        <?= $csrf ?>
        <input type="hidden" name="islem" value="migrasyon_uygula">
        <button class="btn btn-pri"><i class="fas fa-database"></i> Bekleyen Migrasyonları Uygula (<?= count($bekleyenler) ?>)</button>
    </form>
</div>
<?php endif; ?>

<div class="stats">
    <div class="stat">
        <div class="ico <?= $eksik_tablolar ? 'r' : 'g' ?>"><i class="fas fa-database"></i></div>
        <div><strong><?= count($tablo_durumu) - count($eksik_tablolar) ?> / <?= count($tablo_durumu) ?></strong><span>Tablo Mevcut</span></div>
    </div>
    <div class="stat">
        <div class="ico <?= $bekleyenler ? 'y' : 'g' ?>"><i class="fas fa-list-check"></i></div>
        <div><strong><?= count($uygulanmis) ?> / <?= count($tum_migrasyonlar) ?></strong><span>Migration Uygulandı</span></div>
    </div>
    <div class="stat">
        <div class="ico <?= $db_user_yetki ? 'g' : 'r' ?>"><i class="fas fa-key"></i></div>
        <div><strong><?= $db_user_yetki ? 'VAR' : 'YOK' ?></strong><span>CREATE TABLE Yetkisi</span></div>
    </div>
    <div class="stat">
        <div class="ico b"><i class="fas fa-code-branch"></i></div>
        <div><strong>v<?= e($mevcut_surum) ?></strong><span>Yüklü Sürüm</span></div>
    </div>
</div>

<div class="card">
    <h3>Migration Durumu</h3>
    <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px"><code>migrations/</code> klasöründeki SQL dosyaları sırayla uygulanır. Uygulananlar <code>migrasyonlar</code> tablosunda izlenir, aynı dosya iki kez çalışmaz.</p>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th style="width:40px">#</th><th>Dosya</th><th style="width:90px">Durum</th><th style="width:170px">Uygulama Tarihi</th><th style="width:80px">Süre</th><th style="width:120px">İşlem</th></tr></thead>
        <tbody>
        <?php if (!$tum_migrasyonlar): ?>
            <tr><td colspan="6" class="empty">migrations/ klasörü boş veya yok.</td></tr>
        <?php else: foreach ($tum_migrasyonlar as $i => $d):
            $u = $uygulanmis[$d] ?? null;
            $durum = $u ? ($u['sonuc'] === 'ok' ? 'uygulandi' : 'hata') : 'beklemede';
        ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><code><?= e($d) ?></code></td>
                <td>
                    <?php if ($durum === 'uygulandi'): ?>
                        <span class="badge badge-ok">UYGULANDI</span>
                    <?php elseif ($durum === 'hata'): ?>
                        <span class="badge badge-no">HATA</span>
                    <?php else: ?>
                        <span class="badge badge-warn">BEKLİYOR</span>
                    <?php endif; ?>
                </td>
                <td class="num"><?= $u ? e($u['uygulama_tarihi']) : '—' ?></td>
                <td class="num">—</td>
                <td>
                    <form method="post" style="display:inline">
                        <?= $csrf ?>
                        <input type="hidden" name="islem" value="migrasyon_yeniden">
                        <input type="hidden" name="dosya" value="<?= e($d) ?>">
                        <button class="btn btn-out btn-sm" type="submit"><i class="fas fa-rotate"></i> Çalıştır</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card">
    <h3>Veritabanı Tabloları</h3>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th style="width:40px">#</th><th>Tablo</th><th>Açıklama</th><th class="num" style="text-align:right;width:90px">Kayıt</th><th style="width:90px">Durum</th></tr></thead>
        <tbody>
            <?php foreach ($tablo_durumu as $tablo => $d): ?>
                <tr>
                    <td><i class="fas <?= $d['var'] ? 'fa-check-circle' : 'fa-times-circle' ?>" style="color:<?= $d['var'] ? '#16a34a' : '#dc2626' ?>"></i></td>
                    <td><code><?= e($tablo) ?></code></td>
                    <td style="color:var(--c-muted);font-size:.88rem"><?= e($d['aciklama']) ?>
                        <?php if ($d['hata']): ?><br><small style="color:#dc2626;font-family:monospace"><?= e(mb_substr($d['hata'], 0, 100)) ?></small><?php endif; ?>
                    </td>
                    <td class="num" style="text-align:right;font-weight:600"><?= $d['var'] ? number_format((int)$d['sayi']) : '—' ?></td>
                    <td><?= $d['var'] ? '<span class="badge badge-ok">VAR</span>' : '<span class="badge badge-no">EKSİK</span>' ?></td>
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
        <tr><td><strong>Yüklü Sürüm</strong></td><td><code>v<?= e($mevcut_surum) ?></code></td></tr>
        <tr><td><strong>OPcache</strong></td><td><?= $opcache_aktif ? 'Aktif (<code>' . $opcache_cnt . '</code> script cached)' : 'Kapalı veya yok' ?></td></tr>
        <tr><td><strong>CREATE TABLE yetkisi</strong></td><td><?= $db_user_yetki ? '<span class="badge badge-ok">VAR</span>' : '<span class="badge badge-no">YOK — DB user yetkisi gerekli, hosting yöneticisine sor</span>' ?></td></tr>
        <tr><td><strong>migrations/ klasörü</strong></td><td>
            <?php $mp = __DIR__.'/../migrations'; ?>
            <?php if (is_dir($mp)): ?>
                <span class="badge badge-ok">VAR</span> <?= count(glob($mp.'/*.sql')) ?> SQL dosyası
            <?php else: ?>
                <span class="badge badge-no">YOK — Akıllı Güncelleme ile çek</span>
            <?php endif; ?>
        </td></tr>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
