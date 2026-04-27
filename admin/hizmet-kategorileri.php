<?php
require_once __DIR__ . '/_baslat.php';
page_title('Hizmet Kategorileri');

// Aksiyonlar
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
        $ikon = clean($_POST['ikon'] ?? '');
        $sira = (int)($_POST['sira'] ?? 0);
        $aktif= !empty($_POST['aktif']) ? 1 : 0;

        if ($ad === '') {
            flash_set('err', 'Kategori adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            // Slug çakışma kontrolü
            $cak = db_get("SELECT id FROM hizmet_kategorileri WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            if ($id) {
                db_run("UPDATE hizmet_kategorileri SET ad=?, slug=?, ikon=?, sira=?, aktif=? WHERE id=?",
                    [$ad, $slug, $ikon, $sira, $aktif, $id]);
                flash_set('ok', 'Kategori güncellendi.');
            } else {
                db_run("INSERT INTO hizmet_kategorileri (ad, slug, ikon, sira, aktif) VALUES (?,?,?,?,?)",
                    [$ad, $slug, $ikon, $sira, $aktif]);
                flash_set('ok', 'Kategori eklendi.');
            }
            log_yaz('hizmet_kategori_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/hizmet-kategorileri.php');
    } elseif ($islem === 'sil' && $id) {
        // İlişkili hizmet var mı?
        $iliski = (int)db_get("SELECT COUNT(*) c FROM hizmetler WHERE kategori_id=?", [$id])['c'];
        if ($iliski > 0) {
            flash_set('err', "Bu kategoriye bağlı $iliski hizmet var. Önce hizmetleri taşıyın veya silin.");
        } else {
            db_run("DELETE FROM hizmet_kategorileri WHERE id=?", [$id]);
            log_yaz('hizmet_kategori_sil', "ID: $id silindi", (int)$_kul['id']);
            flash_set('ok', 'Kategori silindi.');
        }
        redirect(SITE_URL . '/admin/hizmet-kategorileri.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE hizmet_kategorileri SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/hizmet-kategorileri.php');
    }
}

// Listele
$kategoriler = db_all("SELECT k.*, (SELECT COUNT(*) FROM hizmetler WHERE kategori_id=k.id) hizmet_sayisi
    FROM hizmet_kategorileri k ORDER BY k.sira ASC, k.id ASC");

// Düzenleme veya yeni kayıt formu
$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM hizmet_kategorileri WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/hizmet-kategorileri.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Hizmet Kategorileri</h1>
        <p class="page-sub">Doğalgaz, klima, tesisat gibi ana hizmet gruplarını buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Kategori</a>
    <?php endif; ?>
</div>

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
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="160" placeholder="dogalgaz-tesisati">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>İkon</label>
                    <select name="ikon">
                        <?php
                        $ikonlar = ['flame'=>'Alev (doğalgaz)','snowflake'=>'Kar tanesi (klima)','wrench'=>'İngiliz anahtarı (tesisat)','tools'=>'Araçlar (kombi)'];
                        $sec = $kayit['ikon'] ?? '';
                        foreach ($ikonlar as $k=>$v): ?>
                            <option value="<?= e($k) ?>" <?= $sec===$k?'selected':'' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Sıralama</label>
                    <input class="input" type="number" name="sira" value="<?= (int)($kayit['sira'] ?? 0) ?>">
                </div>
            </div>
            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" <?= !isset($kayit) || (int)($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
            </div>
            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/admin/hizmet-kategorileri.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:60px">#</th>
    <th>Kategori</th>
    <th>Slug</th>
    <th style="width:100px">Hizmet</th>
    <th style="width:80px">Sıra</th>
    <th style="width:100px">Durum</th>
    <th style="width:160px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$kategoriler): ?>
    <tr><td colspan="7" class="empty">Henüz kategori yok. Yukarıdan ekle.</td></tr>
<?php else: foreach ($kategoriler as $k): ?>
<tr>
    <td><?= (int)$k['id'] ?></td>
    <td><strong><?= e($k['ad']) ?></strong> <?php if ($k['ikon']): ?><span style="color:var(--c-muted);font-size:.78rem">(<?= e($k['ikon']) ?>)</span><?php endif; ?></td>
    <td><code><?= e($k['slug']) ?></code></td>
    <td class="num"><?= (int)$k['hizmet_sayisi'] ?></td>
    <td class="num"><?= (int)$k['sira'] ?></td>
    <td>
        <?php if ((int)$k['aktif']): ?>
            <span class="badge badge-ok">Aktif</span>
        <?php else: ?>
            <span class="badge badge-no">Pasif</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions">
            <a href="<?= SITE_URL ?>/kategori/<?= e($k['slug']) ?>" target="_blank" class="btn btn-out btn-sm" title="Sitede Gör"><i class="fas fa-eye"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="aktif_degistir">
                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                <button class="btn btn-out btn-sm" title="Aktif/Pasif"><i class="fas fa-toggle-on"></i></button>
            </form>
            <a href="?duzenle=<?= (int)$k['id'] ?>" class="btn btn-blue btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="sil">
                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                <button class="btn btn-danger btn-sm" data-onay="Kategori silinsin mi?" title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
