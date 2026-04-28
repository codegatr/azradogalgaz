<?php
require_once __DIR__ . '/_baslat.php';
page_title('KVKK / Gizlilik Metinleri');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $izinli = ['kvkk_metni', 'gizlilik_metni'];
    $stmt = db()->prepare("INSERT INTO ayarlar (anahtar, deger) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE deger = VALUES(deger)");
    foreach ($izinli as $k) {
        $v = (string)($_POST[$k] ?? '');
        $stmt->execute([$k, $v]);
    }
    log_yaz('sayfa_guncelle', 'KVKK/Gizlilik metinleri güncellendi.', (int)$_kul['id']);
    flash_set('ok', 'Metinler güncellendi.');
    redirect(SITE_URL . '/admin/sayfalar.php' . (isset($_POST['_tab']) ? '?tab=' . urlencode($_POST['_tab']) : ''));
}

$ayarlar = db_all("SELECT anahtar, deger FROM ayarlar WHERE anahtar IN ('kvkk_metni','gizlilik_metni')");
$a = [];
foreach ($ayarlar as $row) $a[$row['anahtar']] = (string)$row['deger'];

$aktif_tab = $_GET['tab'] ?? 'kvkk';

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">KVKK / Gizlilik Metinleri</h1>
        <p class="page-sub">Sitenin alt linklerinde gösterilen yasal metinleri buradan düzenleyin. HTML kullanılabilir.</p>
    </div>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<div class="alert alert-info">
    <i class="fas fa-circle-info"></i>
    <div>
        Bu alanları boş bırakırsanız, sitedeki <code>/kvkk</code> ve <code>/gizlilik</code> sayfalarında <strong>varsayılan içerik</strong> gösterilir. Burada yazdığınız metin varsayılanın yerine geçer.
    </div>
</div>

<form method="post" data-tabs>
    <?= csrf_field() ?>
    <input type="hidden" name="_tab" id="_tab" value="<?= e($aktif_tab) ?>">

    <div class="tabs-h">
        <div class="t <?= $aktif_tab==='kvkk'?'active':'' ?>" data-tab="kvkk" onclick="document.getElementById('_tab').value='kvkk'">KVKK Aydınlatma Metni</div>
        <div class="t <?= $aktif_tab==='gizlilik'?'active':'' ?>" data-tab="gizlilik" onclick="document.getElementById('_tab').value='gizlilik'">Gizlilik Politikası</div>
    </div>

    <div class="tab-body <?= $aktif_tab==='kvkk'?'active':'' ?>" data-tab="kvkk">
        <div class="card">
            <div class="form-row">
                <div class="field">
                    <label>KVKK Aydınlatma Metni (HTML)</label>
                    <textarea class="textarea" name="kvkk_metni" rows="22"><?= e($a['kvkk_metni'] ?? '') ?></textarea>
                    <p class="help">Boş bırakılırsa sitede varsayılan KVKK metni gösterilir. <a href="<?= SITE_URL ?>/kvkk" target="_blank">Önizleme</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-body <?= $aktif_tab==='gizlilik'?'active':'' ?>" data-tab="gizlilik">
        <div class="card">
            <div class="form-row">
                <div class="field">
                    <label>Gizlilik Politikası (HTML)</label>
                    <textarea class="textarea" name="gizlilik_metni" rows="22"><?= e($a['gizlilik_metni'] ?? '') ?></textarea>
                    <p class="help">Boş bırakılırsa sitede varsayılan Gizlilik metni gösterilir. <a href="<?= SITE_URL ?>/gizlilik" target="_blank">Önizleme</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
    </div>
</form>

<?php require_once __DIR__ . '/_footer.php'; ?>
