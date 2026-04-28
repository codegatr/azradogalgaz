<?php
require_once __DIR__ . '/_baslat.php';
page_title('Sistem Logları');

if (($_kul['rol'] ?? '') !== 'admin') {
    flash_set('err', 'Bu sayfaya yalnızca admin yetkili kullanıcılar erişebilir.');
    redirect(SITE_URL . '/admin/panel.php');
}

// Log temizleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $islem = $_POST['islem'] ?? '';
    if ($islem === 'temizle') {
        $gun = (int)($_POST['gun'] ?? 30);
        $silinen = db_run("DELETE FROM log_kayitlari WHERE olusturma_tarihi < DATE_SUB(NOW(), INTERVAL ? DAY)", [$gun]);
        log_yaz('log_temizle', "$gun günden eski loglar silindi ($silinen kayıt)", (int)$_kul['id']);
        flash_set('ok', "$silinen log kaydı silindi.");
        redirect(SITE_URL . '/admin/loglar.php');
    }
}

// Filtre
$f_tip   = trim((string)($_GET['tip'] ?? ''));
$f_arama = trim((string)($_GET['q'] ?? ''));
$sayfa   = max(1, (int)($_GET['sayfa'] ?? 1));
$limit   = 50;
$ofset   = ($sayfa - 1) * $limit;

$where = "1=1"; $params = [];
if ($f_tip) { $where .= " AND tip=?"; $params[] = $f_tip; }
if ($f_arama) {
    $where .= " AND (mesaj LIKE ? OR ip LIKE ?)";
    $params[] = "%$f_arama%"; $params[] = "%$f_arama%";
}

$toplam = (int)(db_get("SELECT COUNT(*) c FROM log_kayitlari WHERE $where", $params)['c'] ?? 0);
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$loglar = db_all("SELECT l.*, k.ad kullanici_ad
    FROM log_kayitlari l LEFT JOIN kullanicilar k ON k.id=l.kullanici_id
    WHERE $where ORDER BY l.id DESC LIMIT $limit OFFSET $ofset", $params);

// Tip listesi
$tipler = db_all("SELECT DISTINCT tip FROM log_kayitlari ORDER BY tip ASC");

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Sistem Logları</h1>
        <p class="page-sub">Sistem aktivite kayıtları. Toplam <strong><?= $toplam ?></strong> kayıt.</p>
    </div>
    <button class="btn btn-out" data-modal="temizleModal"><i class="fas fa-broom"></i> Eski Logları Temizle</button>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<form method="get" class="card" style="margin-bottom:18px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <select name="tip" class="input" style="max-width:200px">
        <option value="">Tüm Tipler</option>
        <?php foreach ($tipler as $t): ?>
        <option value="<?= e($t['tip']) ?>" <?= $f_tip===$t['tip']?'selected':'' ?>><?= e($t['tip']) ?></option>
        <?php endforeach; ?>
    </select>
    <input class="input" name="q" value="<?= e($f_arama) ?>" placeholder="Mesaj veya IP ara..." style="flex:1">
    <button class="btn btn-out btn-sm"><i class="fas fa-magnifying-glass"></i> Ara</button>
    <?php if ($f_tip || $f_arama): ?><a href="loglar.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
</form>

<?php if ($loglar): ?>
<div class="tbl">
<table>
    <thead>
        <tr>
            <th style="width:140px">Tip</th>
            <th>Mesaj</th>
            <th style="width:150px">Kullanıcı</th>
            <th style="width:120px">IP</th>
            <th style="width:160px">Tarih</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($loglar as $l):
            $cls = match($l['tip']){
                'login_ok'   => 'badge-ok',
                'login_fail' => 'badge-danger',
                'logout'     => 'badge-no',
                default      => 'badge-info',
            };
        ?>
        <tr>
            <td><span class="badge <?= $cls ?>"><?= e($l['tip']) ?></span></td>
            <td style="font-size:.92rem"><?= e($l['mesaj']) ?></td>
            <td><?= e($l['kullanici_ad'] ?: '—') ?></td>
            <td style="font-family:monospace;font-size:.82rem"><?= e($l['ip'] ?: '—') ?></td>
            <td style="font-size:.85rem"><?= tarih_tr($l['olusturma_tarihi'], true) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1): ?>
<div class="pager" style="margin-top:18px;display:flex;justify-content:center;gap:6px;flex-wrap:wrap">
    <?php if ($sayfa > 1): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$sayfa-1])) ?>" class="btn btn-out btn-sm"><i class="fas fa-chevron-left"></i></a>
    <?php endif; ?>
    <span style="padding:6px 12px;font-size:.92rem;color:var(--c-muted)">Sayfa <?= $sayfa ?> / <?= $toplam_sayfa ?></span>
    <?php if ($sayfa < $toplam_sayfa): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['sayfa'=>$sayfa+1])) ?>" class="btn btn-out btn-sm"><i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="tbl"><div class="empty"><i class="fas fa-file-lines" style="font-size:2rem;display:block;margin-bottom:10px"></i>Log kaydı bulunamadı.</div></div>
<?php endif; ?>

<!-- Temizleme Modalı -->
<div class="modal-bg" id="temizleModal">
    <div class="modal" style="max-width:480px">
        <h3 style="margin-bottom:14px">Eski Logları Temizle</h3>
        <p style="color:var(--c-muted);font-size:.92rem;margin-bottom:18px">Belirtilen günden daha eski log kayıtları kalıcı olarak silinecek. Bu işlem geri alınamaz.</p>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="temizle">
            <div class="field">
                <label>Şu kadar günden eski olanlar silinsin:</label>
                <select name="gun" class="input">
                    <option value="7">7 günden eski</option>
                    <option value="30" selected>30 günden eski</option>
                    <option value="90">90 günden eski</option>
                    <option value="180">180 günden eski</option>
                    <option value="365">1 yıldan eski</option>
                </select>
            </div>
            <div class="form-actions" style="margin-top:14px">
                <button type="submit" class="btn btn-danger"><i class="fas fa-broom"></i> Temizle</button>
                <button type="button" class="btn btn-out" data-close>İptal</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
