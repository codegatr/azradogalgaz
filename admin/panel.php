<?php
require_once __DIR__ . '/_baslat.php';
page_title('Dashboard');

// İstatistikler
$st = [
    'mesaj_yeni'   => (int)db_get("SELECT COUNT(*) c FROM iletisim_mesajlari WHERE durum='yeni'")['c'],
    'mesaj_top'    => (int)db_get("SELECT COUNT(*) c FROM iletisim_mesajlari")['c'],
    'urun'         => (int)db_get("SELECT COUNT(*) c FROM urunler WHERE aktif=1")['c'],
    'hizmet'       => (int)db_get("SELECT COUNT(*) c FROM hizmetler WHERE aktif=1")['c'],
    'kampanya'     => (int)db_get("SELECT COUNT(*) c FROM kampanyalar WHERE aktif=1")['c'],
    'cari'         => (int)db_get("SELECT COUNT(*) c FROM cariler WHERE aktif=1")['c'],
    'fatura'       => (int)db_get("SELECT COUNT(*) c FROM faturalar")['c'],
    'blog'         => (int)db_get("SELECT COUNT(*) c FROM blog_yazilari WHERE aktif=1")['c'],
];
$bekleyen_alacak = (float)(db_get("SELECT COALESCE(SUM(genel_toplam - odenen),0) t FROM faturalar WHERE odeme_durumu IN ('odenmedi','kismi') AND tip='satis'")['t'] ?? 0);

// Bakım hatırlatıcıları (tablo varsa)
$bakim_yaklasan = [];
$bakim_gecmis_say = 0;
$bakim_ay_say = 0;
try {
    $bakim_yaklasan = db_all("SELECT id, musteri_ad, telefon, urun_tipi, marka, sonraki_bakim_tarihi
        FROM bakim_hatirlaticilari
        WHERE durum='aktif' AND sonraki_bakim_tarihi <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY sonraki_bakim_tarihi ASC LIMIT 8");
    $bakim_gecmis_say = (int)(db_get("SELECT COUNT(*) c FROM bakim_hatirlaticilari WHERE durum='aktif' AND sonraki_bakim_tarihi < CURDATE()")['c'] ?? 0);
    $bakim_ay_say     = (int)(db_get("SELECT COUNT(*) c FROM bakim_hatirlaticilari WHERE durum='aktif' AND sonraki_bakim_tarihi BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")['c'] ?? 0);
} catch (Throwable $e) { /* tablo henüz yok */ }

$son_mesajlar = db_all("SELECT id, ad_soyad, telefon, konu, durum, olusturma_tarihi
    FROM iletisim_mesajlari ORDER BY id DESC LIMIT 6");
$son_loglar = db_all("SELECT tip, mesaj, ip, olusturma_tarihi
    FROM log_kayitlari ORDER BY id DESC LIMIT 8");

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Hoş geldin, <?= e(explode(' ', $_kul['ad'])[0]) ?> 👋</h1>
        <p class="page-sub">Bugün <?= tarih_tr(date('Y-m-d')) ?> · Son giriş: <?= tarih_tr(db_get("SELECT son_giris FROM kullanicilar WHERE id=?",[$_kul['id']])['son_giris'] ?? '', true) ?></p>
    </div>
    <a href="<?= SITE_URL ?>/" target="_blank" class="btn btn-out"><i class="fas fa-external-link-alt"></i> Siteyi Gör</a>
</div>

<?php
// v1.3.1 — Güncelleme bildirimi (manifest tabanlı)
$_manifest_yol = __DIR__ . '/../manifest.json';
$_son_guncelleme_log = db_get("SELECT olusturma_tarihi, yeni_surum, durum FROM guncelleme_log ORDER BY id DESC LIMIT 1");
?>
<?php if ($_son_guncelleme_log): ?>
<div class="alert alert-info" style="margin-bottom:14px">
    <i class="fas fa-cloud-arrow-down"></i>
    Son güncelleme: <strong>v<?= e($_son_guncelleme_log['yeni_surum'] ?? '?') ?></strong>
    · <?= tarih_tr($_son_guncelleme_log['olusturma_tarihi'], true) ?>
    · <a href="<?= SITE_URL ?>/admin/guncelleme.php" style="color:var(--c-orange)">Güncelleme Merkezi →</a>
</div>
<?php endif; ?>

<!-- İstatistikler -->
<div class="stats">
    <div class="stat">
        <div class="ico o"><i class="fas fa-envelope-open-text"></i></div>
        <div><strong><?= $st['mesaj_yeni'] ?></strong><span>Yeni Mesaj</span></div>
    </div>
    <div class="stat">
        <div class="ico b"><i class="fas fa-fire-flame-curved"></i></div>
        <div><strong><?= $st['urun'] ?></strong><span>Aktif Ürün</span></div>
    </div>
    <div class="stat">
        <div class="ico g"><i class="fas fa-tools"></i></div>
        <div><strong><?= $st['hizmet'] ?></strong><span>Aktif Hizmet</span></div>
    </div>
    <div class="stat">
        <div class="ico y"><i class="fas fa-bullhorn"></i></div>
        <div><strong><?= $st['kampanya'] ?></strong><span>Aktif Kampanya</span></div>
    </div>
    <div class="stat">
        <div class="ico o"><i class="fas fa-users"></i></div>
        <div><strong><?= $st['cari'] ?></strong><span>Cari Hesap</span></div>
    </div>
    <div class="stat">
        <div class="ico b"><i class="fas fa-file-invoice-dollar"></i></div>
        <div><strong><?= $st['fatura'] ?></strong><span>Toplam Fatura</span></div>
    </div>
    <div class="stat">
        <div class="ico r"><i class="fas fa-coins"></i></div>
        <div><strong><?= number_format($bekleyen_alacak, 0, ',', '.') ?> ₺</strong><span>Bekleyen Alacak</span></div>
    </div>
    <div class="stat">
        <div class="ico g"><i class="fas fa-newspaper"></i></div>
        <div><strong><?= $st['blog'] ?></strong><span>Blog Yazısı</span></div>
    </div>
</div>

<div class="form-row cols-2" style="margin-bottom:0">

    <!-- Son mesajlar -->
    <div class="card">
        <div class="page-head" style="margin-bottom:14px">
            <h3>Son İletişim Mesajları</h3>
            <a href="<?= SITE_URL ?>/admin/iletisim-mesajlari.php" class="btn btn-out btn-sm">Tümünü Gör</a>
        </div>
        <?php if ($son_mesajlar): ?>
            <?php foreach ($son_mesajlar as $m): ?>
                <div class="list-item <?= $m['durum']==='yeni'?'unread':'' ?>">
                    <div class="li-head">
                        <strong><?= e($m['ad_soyad']) ?></strong>
                        <span class="badge <?= $m['durum']==='yeni'?'badge-warn':'badge-no' ?>"><?= e($m['durum']) ?></span>
                    </div>
                    <div class="li-meta">
                        <?php if ($m['telefon']): ?><span><i class="fas fa-phone"></i><?= e($m['telefon']) ?></span><?php endif; ?>
                        <?php if ($m['konu']): ?><span><i class="fas fa-tag"></i><?= e($m['konu']) ?></span><?php endif; ?>
                        <span><i class="fas fa-clock"></i><?= tarih_tr($m['olusturma_tarihi'], true) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="tbl"><div class="empty"><i class="fas fa-inbox" style="font-size:1.6rem;display:block;margin-bottom:8px"></i>Henüz mesaj yok.</div></div>
        <?php endif; ?>
    </div>

    <!-- Son sistem logları -->
    <div class="card">
        <div class="page-head" style="margin-bottom:14px">
            <h3>Son Sistem Aktiviteleri</h3>
        </div>
        <?php if ($son_loglar): ?>
            <?php foreach ($son_loglar as $l):
                $cls = match($l['tip']){
                    'login_ok'   => 'badge-ok',
                    'login_fail' => 'badge-danger',
                    'logout'     => 'badge-no',
                    default      => 'badge-info',
                };
            ?>
                <div class="list-item">
                    <div class="li-head">
                        <span><?= e(mb_substr((string)$l['mesaj'], 0, 80)) ?></span>
                        <span class="badge <?= $cls ?>"><?= e($l['tip']) ?></span>
                    </div>
                    <div class="li-meta">
                        <span><i class="fas fa-network-wired"></i><?= e($l['ip']) ?></span>
                        <span><i class="fas fa-clock"></i><?= tarih_tr($l['olusturma_tarihi'], true) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="tbl"><div class="empty">Henüz log kaydı yok.</div></div>
        <?php endif; ?>
    </div>
</div>

<!-- Yaklaşan Bakımlar -->
<?php if ($bakim_yaklasan): ?>
<div class="card" style="border-left:3px solid <?= $bakim_gecmis_say > 0 ? 'var(--c-red)' : 'var(--c-orange)' ?>">
    <div class="page-head" style="margin-bottom:14px">
        <h3>
            <i class="fas fa-bell" style="color:<?= $bakim_gecmis_say > 0 ? 'var(--c-red)' : 'var(--c-orange)' ?>"></i>
            Yaklaşan / Gecikmiş Bakımlar
            <small style="font-weight:400;color:var(--c-muted)">(<?= $bakim_gecmis_say ?> gecikmiş, <?= $bakim_ay_say ?> 30 gün içinde)</small>
        </h3>
        <a href="<?= SITE_URL ?>/admin/bakim-hatirlaticilari.php" class="btn btn-out btn-sm">Tümünü Gör</a>
    </div>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th>Müşteri</th><th>Tip / Marka</th><th>Telefon</th><th>Bakım Tarihi</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bakim_yaklasan as $bk):
            $kalan = (int)floor((strtotime($bk['sonraki_bakim_tarihi']) - strtotime(date('Y-m-d'))) / 86400);
            $renk = $kalan < 0 ? 'var(--c-red)' : ($kalan < 7 ? 'var(--c-orange)' : 'var(--c-blue)');
        ?>
            <tr>
                <td><strong><?= e($bk['musteri_ad']) ?></strong></td>
                <td><span class="badge badge-info"><?= ucfirst($bk['urun_tipi']) ?></span> <?= e((string)$bk['marka']) ?></td>
                <td><?= e((string)$bk['telefon']) ?: '-' ?></td>
                <td>
                    <strong style="color:<?= $renk ?>" class="num"><?= tarih_tr($bk['sonraki_bakim_tarihi']) ?></strong>
                    <small style="color:<?= $renk ?>;display:block"><?= $kalan < 0 ? abs($kalan).' gün gecikmiş' : ($kalan === 0 ? 'BUGÜN' : $kalan.' gün kaldı') ?></small>
                </td>
                <td><a href="<?= SITE_URL ?>/admin/bakim-hatirlaticilari.php?duzenle=<?= $bk['id'] ?>" class="btn btn-out btn-sm">Aç</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<!-- Hızlı bağlantılar -->
<div class="card">
    <h3>Hızlı Bağlantılar</h3>
    <div class="form-row cols-3">
        <a href="<?= SITE_URL ?>/admin/cariler.php?ekle=1" class="btn btn-out" style="padding:18px;flex-direction:column"><i class="fas fa-user-plus" style="font-size:1.3rem;margin-bottom:6px"></i>Yeni Cari</a>
        <a href="<?= SITE_URL ?>/admin/faturalar.php?ekle=1" class="btn btn-out" style="padding:18px;flex-direction:column"><i class="fas fa-file-invoice" style="font-size:1.3rem;margin-bottom:6px"></i>Yeni Fatura</a>
        <a href="<?= SITE_URL ?>/admin/bakim-hatirlaticilari.php?ekle=1" class="btn btn-out" style="padding:18px;flex-direction:column"><i class="fas fa-bell" style="font-size:1.3rem;margin-bottom:6px"></i>Bakım Hatırlatıcı</a>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
