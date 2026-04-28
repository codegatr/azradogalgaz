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
        $ad     = clean($_POST['ad'] ?? '');
        $slug   = clean($_POST['slug'] ?? '');
        $ust_id = (int)($_POST['ust_id'] ?? 0) ?: null;
        $sira   = (int)($_POST['sira'] ?? 0);
        $aktif  = !empty($_POST['aktif']) ? 1 : 0;

        if ($id && $ust_id === $id) {
            flash_set('err', 'Kategori kendi üst kategorisi olamaz.');
            redirect(SITE_URL . '/admin/urun-kategorileri.php');
        }
        if ($ad === '') {
            flash_set('err', 'Kategori adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            $cak = db_get("SELECT id FROM urun_kategorileri WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100, 999);

            if ($id) {
                db_run("UPDATE urun_kategorileri SET ad=?, slug=?, ust_id=?, sira=?, aktif=? WHERE id=?",
                    [$ad, $slug, $ust_id, $sira, $aktif, $id]);
                flash_set('ok', 'Kategori güncellendi.');
            } else {
                db_run("INSERT INTO urun_kategorileri (ad, slug, ust_id, sira, aktif) VALUES (?,?,?,?,?)",
                    [$ad, $slug, $ust_id, $sira, $aktif]);
                $id = (int)db()->lastInsertId();
                flash_set('ok', 'Kategori eklendi.');
            }
            log_yaz('urun_kategori_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/urun-kategorileri.php');
    } elseif ($islem === 'sil' && $id) {
        $iliski = (int)db_get("SELECT COUNT(*) c FROM urunler WHERE kategori_id=?", [$id])['c'];
        $alt = (int)db_get("SELECT COUNT(*) c FROM urun_kategorileri WHERE ust_id=?", [$id])['c'];
        if ($iliski > 0) {
            flash_set('err', "Bu kategoriye bağlı $iliski ürün var. Önce ürünleri taşıyın.");
        } elseif ($alt > 0) {
            flash_set('err', "Bu kategoride $alt alt kategori var. Önce alt kategorileri silin.");
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

// Hiyerarşik liste
$kategoriler_raw = db_all("SELECT k.*,
    (SELECT COUNT(*) FROM urunler WHERE kategori_id=k.id) urun_sayisi,
    (SELECT ad FROM urun_kategorileri WHERE id=k.ust_id) ust_ad
    FROM urun_kategorileri k ORDER BY COALESCE(k.ust_id, k.id), k.sira ASC, k.id ASC");

// Üst kategorileri sırala (ana kategoriler önce, alt sonra)
$kategoriler = [];
foreach ($kategoriler_raw as $k) {
    if (!$k['ust_id']) {
        $kategoriler[] = $k;
        foreach ($kategoriler_raw as $a) {
            if ((int)$a['ust_id'] === (int)$k['id']) $kategoriler[] = $a;
        }
    }
}
// ust_id ataması olmayanları da ekle
foreach ($kategoriler_raw as $k) {
    $eklenmis = false;
    foreach ($kategoriler as $kk) if ((int)$kk['id'] === (int)$k['id']) { $eklenmis = true; break; }
    if (!$eklenmis) $kategoriler[] = $k;
}

$ana_kategoriler = array_filter($kategoriler_raw, fn($x) => !$x['ust_id']);

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
        <p class="page-sub">Kombiler, klimalar, panel radyatörler gibi ürün gruplarını yönet. Alt kategori desteği vardır.</p>
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
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="160" placeholder="kombiler">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Üst Kategori</label>
                    <select name="ust_id">
                        <option value="">— Ana kategori —</option>
                        <?php foreach ($ana_kategoriler as $a): if ($duzenle_id && (int)$a['id'] === $duzenle_id) continue; ?>
                            <option value="<?= (int)$a['id'] ?>" <?= (int)($kayit['ust_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>>
                                <?= e($a['ad']) ?>
                            </option>
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
                <a href="<?= SITE_URL ?>/admin/urun-kategorileri.php" class="btn btn-out">İptal</a>
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
    <th>Üst Kategori</th>
    <th style="width:100px">Ürün</th>
    <th style="width:80px">Sıra</th>
    <th style="width:100px">Durum</th>
    <th style="width:160px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$kategoriler): ?>
    <tr><td colspan="8" class="empty">Henüz kategori yok. Yukarıdan ekle.</td></tr>
<?php else: foreach ($kategoriler as $k): ?>
<tr>
    <td><?= (int)$k['id'] ?></td>
    <td><?php if ($k['ust_id']): ?><span style="color:var(--c-muted)">└──</span> <?php endif; ?><strong><?= e($k['ad']) ?></strong></td>
    <td><code><?= e($k['slug']) ?></code></td>
    <td><?= e($k['ust_ad'] ?? '—') ?></td>
    <td class="num"><?= (int)$k['urun_sayisi'] ?></td>
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
