<?php
require_once __DIR__ . '/_baslat.php';
page_title('Markalar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $ad   = clean($_POST['ad'] ?? '');
        $slug = clean($_POST['slug'] ?? '');
        $aktif= !empty($_POST['aktif']) ? 1 : 0;
        $logo_mevcut = clean($_POST['logo_mevcut'] ?? '');

        if ($ad === '') {
            flash_set('err', 'Marka adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            $cak = db_get("SELECT id FROM markalar WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            $logo = $logo_mevcut;
            if (!empty($_FILES['logo']['name'])) {
                $yeni = resim_yukle($_FILES['logo'], 'markalar');
                if ($yeni) $logo = $yeni;
            }

            if ($id) {
                db_run("UPDATE markalar SET ad=?, slug=?, logo=?, aktif=? WHERE id=?",
                    [$ad, $slug, $logo, $aktif, $id]);
                flash_set('ok', 'Marka güncellendi.');
            } else {
                db_run("INSERT INTO markalar (ad, slug, logo, aktif) VALUES (?,?,?,?)",
                    [$ad, $slug, $logo, $aktif]);
                flash_set('ok', 'Marka eklendi.');
            }
            log_yaz('marka_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/markalar.php');
    } elseif ($islem === 'sil' && $id) {
        $iliski = (int)(db_get("SELECT COUNT(*) c FROM urunler WHERE marka_id=?", [$id])['c'] ?? 0);
        if ($iliski > 0) {
            flash_set('err', "Bu markaya bağlı $iliski ürün var. Önce ürünleri taşıyın.");
        } else {
            db_run("DELETE FROM markalar WHERE id=?", [$id]);
            log_yaz('marka_sil', "ID: $id silindi", (int)$_kul['id']);
            flash_set('ok', 'Marka silindi.');
        }
        redirect(SITE_URL . '/admin/markalar.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE markalar SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/markalar.php');
    }
}

$markalar = db_all("SELECT m.*, (SELECT COUNT(*) FROM urunler WHERE marka_id=m.id) urun_sayisi
    FROM markalar m ORDER BY m.ad ASC");

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM markalar WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/markalar.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Markalar</h1>
        <p class="page-sub">Demirdöküm, Bosch, Daikin gibi ürün üreticilerini buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Marka</a>
    <?php endif; ?>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Markayı Düzenle' : 'Yeni Marka' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">
            <input type="hidden" name="logo_mevcut" value="<?= e($kayit['logo'] ?? '') ?>">

            <div class="form-row cols-2">
                <div class="field">
                    <label>Marka Adı *</label>
                    <input class="input" name="ad" value="<?= e($kayit['ad'] ?? '') ?>" required maxlength="80">
                </div>
                <div class="field">
                    <label>Slug <span class="opt">(boş bırakılırsa otomatik)</span></label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="120" placeholder="demirdokum">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Logo (PNG/JPG, max 8MB)</label>
                    <input type="file" name="logo" accept="image/*">
                    <?php if (!empty($kayit['logo'])): ?>
                        <div style="margin-top:10px">
                            <img src="<?= UPLOAD_URL . '/' . e($kayit['logo']) ?>" style="max-width:120px;max-height:60px;background:#fff;padding:6px;border:1px solid var(--c-line);border-radius:6px">
                            <p class="help">Mevcut logo. Yeni dosya yüklerseniz değiştirilir.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= ($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif (sitede görünsün)</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> <?= $duzenle_id?'Güncelle':'Kaydet' ?></button>
                <a href="<?= SITE_URL ?>/admin/markalar.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="padding:0">
        <table class="adm-table">
            <thead><tr><th width="60">Logo</th><th>Marka</th><th>Slug</th><th width="80">Ürün</th><th width="80">Durum</th><th width="160">İşlem</th></tr></thead>
            <tbody>
            <?php if (!$markalar): ?>
                <tr><td colspan="6" class="empty">Henüz marka eklenmemiş. <a href="?ekle=1">İlk markayı ekleyin</a> veya <a href="<?= SITE_URL ?>/seed.php"><strong>/seed.php</strong></a> aracını çalıştırın.</td></tr>
            <?php endif; ?>
            <?php foreach ($markalar as $m): ?>
                <tr>
                    <td><?php if ($m['logo']): ?><img src="<?= UPLOAD_URL.'/'.e($m['logo']) ?>" style="max-width:48px;max-height:32px;background:#fff;padding:3px;border-radius:4px"><?php else: ?><span style="color:var(--c-muted)">—</span><?php endif; ?></td>
                    <td><strong><?= e($m['ad']) ?></strong></td>
                    <td><code><?= e($m['slug']) ?></code></td>
                    <td><?= (int)$m['urun_sayisi'] ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="aktif_degistir">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button class="badge badge-<?= $m['aktif']?'ok':'pas' ?>" type="submit"><?= $m['aktif']?'Aktif':'Pasif' ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="?duzenle=<?= (int)$m['id'] ?>" class="btn btn-sm btn-out"><i class="fas fa-pen"></i></a>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="sil">
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button class="btn btn-sm btn-red" type="submit" data-onay="<?= e($m['ad']) ?> markasini silmek istediginize emin misiniz?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
