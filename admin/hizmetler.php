<?php
require_once __DIR__ . '/_baslat.php';
page_title('Hizmetler');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $kategori_id = (int)($_POST['kategori_id'] ?? 0) ?: null;
        $baslik = clean($_POST['baslik'] ?? '');
        $slug   = clean($_POST['slug'] ?? '');
        $kisa   = clean($_POST['kisa_aciklama'] ?? '');
        $icerik = (string)($_POST['icerik'] ?? '');
        $meta_b = clean($_POST['meta_baslik'] ?? '');
        $meta_a = clean($_POST['meta_aciklama'] ?? '');
        $sira   = (int)($_POST['sira'] ?? 0);
        $aktif  = !empty($_POST['aktif']) ? 1 : 0;
        $gorsel_mevcut = clean($_POST['gorsel_mevcut'] ?? '');

        if ($baslik === '') {
            flash_set('err', 'Başlık zorunlu.');
        } else {
            if (!$slug) $slug = slugify($baslik);
            $cak = db_get("SELECT id FROM hizmetler WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            $gorsel = $gorsel_mevcut;
            if (!empty($_FILES['gorsel']['name'])) {
                $yeni = resim_yukle($_FILES['gorsel'], 'hizmetler');
                if ($yeni) $gorsel = $yeni;
            }

            if ($id) {
                db_run("UPDATE hizmetler SET kategori_id=?, baslik=?, slug=?, kisa_aciklama=?, icerik=?, gorsel=?, meta_baslik=?, meta_aciklama=?, sira=?, aktif=? WHERE id=?",
                    [$kategori_id, $baslik, $slug, $kisa, $icerik, $gorsel, $meta_b, $meta_a, $sira, $aktif, $id]);
                flash_set('ok', 'Hizmet güncellendi.');
            } else {
                db_run("INSERT INTO hizmetler (kategori_id, baslik, slug, kisa_aciklama, icerik, gorsel, meta_baslik, meta_aciklama, sira, aktif) VALUES (?,?,?,?,?,?,?,?,?,?)",
                    [$kategori_id, $baslik, $slug, $kisa, $icerik, $gorsel, $meta_b, $meta_a, $sira, $aktif]);
                flash_set('ok', 'Hizmet eklendi.');
            }
            log_yaz('hizmet_kaydet', "ID: $id, baslik: $baslik", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/hizmetler.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM hizmetler WHERE id=?", [$id]);
        log_yaz('hizmet_sil', "ID: $id", (int)$_kul['id']);
        flash_set('ok', 'Hizmet silindi.');
        redirect(SITE_URL . '/admin/hizmetler.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE hizmetler SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/hizmetler.php');
    }
}

// Filtreler
$f_kat   = (int)($_GET['kategori'] ?? 0);
$f_arama = clean($_GET['q'] ?? '');

$where  = "1=1";
$params = [];
if ($f_kat)   { $where .= " AND h.kategori_id=?"; $params[] = $f_kat; }
if ($f_arama) { $where .= " AND (h.baslik LIKE ? OR h.kisa_aciklama LIKE ?)"; $params[] = "%$f_arama%"; $params[] = "%$f_arama%"; }

$hizmetler = db_all("SELECT h.*, k.ad kat_ad
    FROM hizmetler h LEFT JOIN hizmet_kategorileri k ON k.id=h.kategori_id
    WHERE $where ORDER BY h.sira ASC, h.id DESC", $params);

$kategoriler = db_all("SELECT id, ad FROM hizmet_kategorileri ORDER BY sira ASC, ad ASC");

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM hizmetler WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/hizmetler.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Hizmetler</h1>
        <p class="page-sub">Doğalgaz tesisat, kombi montajı, klima gibi hizmetlerinizi buradan ekle/düzenle.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Hizmet</a>
    <?php endif; ?>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Hizmeti Düzenle' : 'Yeni Hizmet' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">
            <input type="hidden" name="gorsel_mevcut" value="<?= e($kayit['gorsel'] ?? '') ?>">

            <div class="form-row cols-2">
                <div class="field">
                    <label>Başlık *</label>
                    <input class="input" name="baslik" value="<?= e($kayit['baslik'] ?? '') ?>" required maxlength="180">
                </div>
                <div class="field">
                    <label>Kategori</label>
                    <select name="kategori_id">
                        <option value="0">— Yok —</option>
                        <?php foreach ($kategoriler as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= ($kayit['kategori_id'] ?? 0) == $k['id'] ? 'selected':'' ?>><?= e($k['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Slug</label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="220" placeholder="izmirgaz-onayli-tesisat">
                </div>
                <div class="field">
                    <label>Sıra</label>
                    <input type="number" class="input" name="sira" value="<?= (int)($kayit['sira'] ?? 0) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Kısa Açıklama <span class="opt">(kart önyüzü, max 300)</span></label>
                    <textarea class="textarea" name="kisa_aciklama" maxlength="300" rows="2"><?= e($kayit['kisa_aciklama'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Detaylı İçerik <span class="opt">(HTML destekler)</span></label>
                    <textarea class="textarea" name="icerik" rows="14" placeholder="<h2>...</h2><p>...</p><ul><li>...</li></ul>"><?= e($kayit['icerik'] ?? '') ?></textarea>
                    <p class="help">HTML etiketleri kullanabilirsiniz: &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;/&lt;li&gt;, &lt;ol&gt;, &lt;a href=""&gt;, &lt;table&gt;, &lt;blockquote&gt;.</p>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Görsel (PNG/JPG, max 8MB)</label>
                    <input type="file" name="gorsel" accept="image/*">
                    <?php if (!empty($kayit['gorsel'])): ?>
                        <div style="margin-top:10px">
                            <img src="<?= UPLOAD_URL.'/'.e($kayit['gorsel']) ?>" style="max-width:240px;max-height:160px;border-radius:8px;border:1px solid var(--c-line)">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <details>
                <summary style="cursor:pointer;font-weight:600;color:var(--c-muted);margin-bottom:14px">SEO Ayarları (opsiyonel)</summary>
                <div class="form-row">
                    <div class="field">
                        <label>Meta Başlık <span class="opt">(boşsa: hizmet başlığı)</span></label>
                        <input class="input" name="meta_baslik" value="<?= e($kayit['meta_baslik'] ?? '') ?>" maxlength="200">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Meta Açıklama (max 300)</label>
                        <textarea class="textarea" name="meta_aciklama" maxlength="300" rows="2"><?= e($kayit['meta_aciklama'] ?? '') ?></textarea>
                    </div>
                </div>
            </details>

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= ($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif (sitede görünsün)</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> <?= $duzenle_id?'Güncelle':'Kaydet' ?></button>
                <a href="<?= SITE_URL ?>/admin/hizmetler.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="padding:14px;margin-bottom:16px">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap">
            <input class="input" name="q" value="<?= e($f_arama) ?>" placeholder="Hizmet ara..." style="flex:1;min-width:200px">
            <select name="kategori" style="min-width:180px">
                <option value="0">Tüm Kategoriler</option>
                <?php foreach ($kategoriler as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= $f_kat==$k['id']?'selected':'' ?>><?= e($k['ad']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-pri"><i class="fas fa-filter"></i> Filtrele</button>
            <?php if ($f_arama || $f_kat): ?><a href="<?= SITE_URL ?>/admin/hizmetler.php" class="btn btn-out">Temizle</a><?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding:0">
        <table class="adm-table">
            <thead><tr><th width="60">Görsel</th><th>Başlık</th><th>Kategori</th><th>Slug</th><th width="60">Sıra</th><th width="80">Durum</th><th width="160">İşlem</th></tr></thead>
            <tbody>
            <?php if (!$hizmetler): ?>
                <tr><td colspan="7" class="empty">Hizmet bulunamadı. <a href="?ekle=1">Yeni hizmet ekle</a> veya <a href="<?= SITE_URL ?>/seed.php"><strong>/seed.php</strong></a> ile yükle.</td></tr>
            <?php endif; ?>
            <?php foreach ($hizmetler as $h): ?>
                <tr>
                    <td><?php if ($h['gorsel']): ?><img src="<?= UPLOAD_URL.'/'.e($h['gorsel']) ?>" style="width:48px;height:36px;object-fit:cover;border-radius:4px"><?php else: ?><span style="color:var(--c-muted)">—</span><?php endif; ?></td>
                    <td>
                        <strong><?= e($h['baslik']) ?></strong>
                        <?php if ($h['kisa_aciklama']): ?>
                            <div style="font-size:.78rem;color:var(--c-muted);margin-top:2px"><?= e(mb_strimwidth($h['kisa_aciklama'], 0, 80, '…', 'UTF-8')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($h['kat_ad'] ?? '—') ?></td>
                    <td><code style="font-size:.78rem"><?= e($h['slug']) ?></code></td>
                    <td><?= (int)$h['sira'] ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="aktif_degistir">
                            <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                            <button class="badge badge-<?= $h['aktif']?'ok':'pas' ?>" type="submit"><?= $h['aktif']?'Aktif':'Pasif' ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="<?= SITE_URL ?>/hizmet/<?= e($h['slug']) ?>" target="_blank" class="btn btn-sm btn-out" title="Sitedeki sayfayı aç"><i class="fas fa-eye"></i></a>
                        <a href="?duzenle=<?= (int)$h['id'] ?>" class="btn btn-sm btn-out"><i class="fas fa-pen"></i></a>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="sil">
                            <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
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
