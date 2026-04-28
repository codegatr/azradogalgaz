<?php
require_once __DIR__ . '/_baslat.php';
page_title('Ürün Kategorileri');

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
        $ust  = (int)($_POST['ust_id'] ?? 0) ?: null;
        $sira = (int)($_POST['sira'] ?? 0);
        $aktif= !empty($_POST['aktif']) ? 1 : 0;

        if ($ad === '') {
            flash_set('err', 'Kategori adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            $cak = db_get("SELECT id FROM urun_kategorileri WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            if ($id) {
                db_run("UPDATE urun_kategorileri SET ad=?, slug=?, ust_id=?, sira=?, aktif=? WHERE id=?",
                    [$ad, $slug, $ust, $sira, $aktif, $id]);
                flash_set('ok', 'Kategori güncellendi.');
            } else {
                db_run("INSERT INTO urun_kategorileri (ad, slug, ust_id, sira, aktif) VALUES (?,?,?,?,?)",
                    [$ad, $slug, $ust, $sira, $aktif]);
                flash_set('ok', 'Kategori eklendi.');
            }
            log_yaz('urun_kategori_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/urun-kategorileri.php');
    } elseif ($islem === 'sil' && $id) {
        $iliski = (int)(db_get("SELECT COUNT(*) c FROM urunler WHERE kategori_id=?", [$id])['c'] ?? 0);
        if ($iliski > 0) {
            flash_set('err', "Bu kategoriye bağlı $iliski ürün var.");
        } else {
            db_run("DELETE FROM urun_kategorileri WHERE id=?", [$id]);
            log_yaz('urun_kategori_sil', "ID: $id silindi", (int)$_kul['id']);
            flash_set('ok', 'Kategori silindi.');
        }
        redirect(SITE_URL . '/admin/urun-kategorileri.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE urun_kategorileri SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/urun-kategorileri.php');
    }
}

$kategoriler = db_all("SELECT k.*,
    (SELECT ad FROM urun_kategorileri WHERE id=k.ust_id) ust_ad,
    (SELECT COUNT(*) FROM urunler WHERE kategori_id=k.id) urun_sayisi
    FROM urun_kategorileri k ORDER BY k.sira ASC, k.id ASC");

$tum_kat = db_all("SELECT id, ad FROM urun_kategorileri ORDER BY ad ASC");

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM urun_kategorileri WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/urun-kategorileri.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Ürün Kategorileri</h1>
        <p class="page-sub">Yoğuşmalı kombi, inverter klima gibi ürün gruplarını buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Kategori</a>
    <?php endif; ?>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Kategoriyi Düzenle' : 'Yeni Kategori' ?></h3>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

            <div class="form-row cols-2">
                <div class="field">
                    <label>Kategori Adı *</label>
                    <input class="input" name="ad" value="<?= e($kayit['ad'] ?? '') ?>" required maxlength="120">
                </div>
                <div class="field">
                    <label>Slug <span class="opt">(boş bırakılırsa otomatik)</span></label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="160" placeholder="yogusmali-kombi">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Üst Kategori <span class="opt">(opsiyonel — alt kategori olarak)</span></label>
                    <select name="ust_id">
                        <option value="0">— Yok (ana kategori) —</option>
                        <?php foreach ($tum_kat as $u):
                            if ($u['id'] == ($kayit['id'] ?? 0)) continue; ?>
                            <option value="<?= (int)$u['id'] ?>" <?= ($kayit['ust_id'] ?? 0) == $u['id'] ? 'selected':'' ?>><?= e($u['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Sıra <span class="opt">(küçük olan üstte)</span></label>
                    <input type="number" class="input" name="sira" value="<?= (int)($kayit['sira'] ?? 0) ?>">
                </div>
            </div>

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= ($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> <?= $duzenle_id?'Güncelle':'Kaydet' ?></button>
                <a href="<?= SITE_URL ?>/admin/urun-kategorileri.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="padding:0">
        <table class="adm-table">
            <thead><tr><th>Kategori</th><th>Slug</th><th>Üst</th><th width="60">Sıra</th><th width="80">Ürün</th><th width="80">Durum</th><th width="160">İşlem</th></tr></thead>
            <tbody>
            <?php if (!$kategoriler): ?>
                <tr><td colspan="7" class="empty">Henüz kategori yok. <a href="?ekle=1">Ekle</a> ya da <a href="<?= SITE_URL ?>/seed.php"><strong>/seed.php</strong></a> ile otomatik yükle.</td></tr>
            <?php endif; ?>
            <?php foreach ($kategoriler as $k): ?>
                <tr>
                    <td><strong><?= e($k['ad']) ?></strong></td>
                    <td><code><?= e($k['slug']) ?></code></td>
                    <td><?= e($k['ust_ad'] ?? '—') ?></td>
                    <td><?= (int)$k['sira'] ?></td>
                    <td><?= (int)$k['urun_sayisi'] ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="aktif_degistir">
                            <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                            <button class="badge badge-<?= $k['aktif']?'ok':'pas' ?>" type="submit"><?= $k['aktif']?'Aktif':'Pasif' ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="?duzenle=<?= (int)$k['id'] ?>" class="btn btn-sm btn-out"><i class="fas fa-pen"></i></a>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="sil">
                            <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                            <button class="btn btn-sm btn-red" type="submit" data-onay="Silinsin mi?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
