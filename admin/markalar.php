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
        $ad    = clean($_POST['ad'] ?? '');
        $slug  = clean($_POST['slug'] ?? '');
        $aktif = !empty($_POST['aktif']) ? 1 : 0;

        if ($ad === '') {
            flash_set('err', 'Marka adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            $cak = db_get("SELECT id FROM markalar WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100, 999);

            // Logo yükleme
            $logo = null;
            if (!empty($_FILES['logo']['name'])) {
                $logo = resim_yukle($_FILES['logo'], 'markalar');
                if (!$logo) flash_set('err', 'Logo yüklenemedi (sadece JPG/PNG/WEBP, en fazla 8MB).');
            }
            // Eski logoyu koru, kullanıcı kaldırmadıysa
            $logo_url_input = clean($_POST['logo_url'] ?? '');
            if (!$logo && $logo_url_input) $logo = $logo_url_input;
            if (!$logo && $id) {
                $eski = db_get("SELECT logo FROM markalar WHERE id=?", [$id]);
                $logo = $eski['logo'] ?? null;
            }
            if (!empty($_POST['logo_sil']) && $id) $logo = null;

            if ($id) {
                db_run("UPDATE markalar SET ad=?, slug=?, logo=?, aktif=? WHERE id=?",
                    [$ad, $slug, $logo, $aktif, $id]);
                flash_set('ok', 'Marka güncellendi.');
            } else {
                db_run("INSERT INTO markalar (ad, slug, logo, aktif) VALUES (?,?,?,?)",
                    [$ad, $slug, $logo, $aktif]);
                $id = (int)db()->lastInsertId();
                flash_set('ok', 'Marka eklendi.');
            }
            log_yaz('marka_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/markalar.php');
    } elseif ($islem === 'sil' && $id) {
        $iliski = (int)db_get("SELECT COUNT(*) c FROM urunler WHERE marka_id=?", [$id])['c'];
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
        <p class="page-sub">Demirdöküm, Bosch, Vaillant gibi markaları buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Marka</a>
    <?php endif; ?>
</div>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Markayı Düzenle' : 'Yeni Marka' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

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

            <div class="form-row cols-2">
                <div class="field">
                    <label>Logo (Dosya Yükle)</label>
                    <input class="input" type="file" name="logo" accept="image/*">
                    <small style="color:var(--c-muted)">JPG/PNG/WEBP, max 8 MB. Şeffaf PNG önerilir.</small>
                </div>
                <div class="field">
                    <label>veya Logo URL</label>
                    <input class="input" name="logo_url" value="<?= e($kayit['logo'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>

            <?php if (!empty($kayit['logo'])): ?>
            <div class="form-row">
                <div class="field">
                    <label>Mevcut Logo</label>
                    <div style="display:flex;gap:14px;align-items:center;background:var(--c-bg);padding:12px;border-radius:8px">
                        <img src="<?= e(gorsel_url($kayit['logo'])) ?>" style="max-height:60px;max-width:200px;object-fit:contain">
                        <label class="check"><input type="checkbox" name="logo_sil" value="1"> <span>Logoyu kaldır</span></label>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" <?= !isset($kayit) || (int)($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/admin/markalar.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:60px">#</th>
    <th style="width:90px">Logo</th>
    <th>Marka</th>
    <th>Slug</th>
    <th style="width:100px">Ürün</th>
    <th style="width:100px">Durum</th>
    <th style="width:160px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$markalar): ?>
    <tr><td colspan="7" class="empty">Henüz marka yok. Yukarıdan ekle.</td></tr>
<?php else: foreach ($markalar as $m): ?>
<tr>
    <td><?= (int)$m['id'] ?></td>
    <td>
        <?php if ($m['logo']): ?>
            <img src="<?= e(gorsel_url($m['logo'])) ?>" style="max-height:36px;max-width:80px;object-fit:contain">
        <?php else: ?>
            <span style="color:var(--c-muted);font-size:.78rem">—</span>
        <?php endif; ?>
    </td>
    <td><strong><?= e($m['ad']) ?></strong></td>
    <td><code><?= e($m['slug']) ?></code></td>
    <td class="num"><?= (int)$m['urun_sayisi'] ?></td>
    <td>
        <?php if ((int)$m['aktif']): ?>
            <span class="badge badge-ok">Aktif</span>
        <?php else: ?>
            <span class="badge badge-no">Pasif</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions">
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="aktif_degistir">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button class="btn btn-out btn-sm" title="Aktif/Pasif"><i class="fas fa-toggle-on"></i></button>
            </form>
            <a href="?duzenle=<?= (int)$m['id'] ?>" class="btn btn-blue btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="sil">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button class="btn btn-danger btn-sm" data-onay="Marka silinsin mi?" title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
