<?php
require_once __DIR__ . '/_baslat.php';
page_title('Ürünler');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $kategori_id = (int)($_POST['kategori_id'] ?? 0) ?: null;
        $marka_id    = (int)($_POST['marka_id'] ?? 0) ?: null;
        $ad      = clean($_POST['ad'] ?? '');
        $slug    = clean($_POST['slug'] ?? '');
        $sku     = clean($_POST['sku'] ?? '') ?: null;
        $kisa    = clean($_POST['kisa_aciklama'] ?? '');
        $aciklama= (string)($_POST['aciklama'] ?? '');
        $ozellikler= (string)($_POST['ozellikler'] ?? '');
        $fiyat   = (float)str_replace([',','.'], ['.',''], $_POST['fiyat'] ?? '0');
        $indirim = (string)($_POST['indirimli_fiyat'] ?? '');
        $indirim_v = $indirim !== '' ? (float)str_replace([',','.'],['.',''],$indirim) : null;
        $kdv     = (int)($_POST['kdv_orani'] ?? 20);
        $stok    = (int)($_POST['stok'] ?? 0);
        $meta_b  = clean($_POST['meta_baslik'] ?? '');
        $meta_a  = clean($_POST['meta_aciklama'] ?? '');
        $one_cikan = !empty($_POST['one_cikan']) ? 1 : 0;
        $aktif   = !empty($_POST['aktif']) ? 1 : 0;
        $gorsel_mevcut = clean($_POST['gorsel_mevcut'] ?? '');
        $galeri_mevcut = (string)($_POST['galeri_mevcut'] ?? '[]');

        if ($ad === '') {
            flash_set('err', 'Ürün adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            $cak = db_get("SELECT id FROM urunler WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            $gorsel = $gorsel_mevcut;
            if (!empty($_FILES['gorsel']['name'])) {
                $yeni = resim_yukle($_FILES['gorsel'], 'urunler');
                if ($yeni) $gorsel = $yeni;
            }

            // Galeri (multiple files)
            $galeri = json_decode($galeri_mevcut, true) ?: [];
            if (!empty($_FILES['galeri']['name']) && is_array($_FILES['galeri']['name'])) {
                foreach ($_FILES['galeri']['name'] as $i => $isim) {
                    if (!$isim) continue;
                    $tek = [
                        'name' => $_FILES['galeri']['name'][$i],
                        'type' => $_FILES['galeri']['type'][$i],
                        'tmp_name' => $_FILES['galeri']['tmp_name'][$i],
                        'error' => $_FILES['galeri']['error'][$i],
                        'size' => $_FILES['galeri']['size'][$i],
                    ];
                    $yol = resim_yukle($tek, 'urunler');
                    if ($yol) $galeri[] = $yol;
                }
            }

            $galeri_json = json_encode($galeri, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($id) {
                db_run("UPDATE urunler SET kategori_id=?, marka_id=?, ad=?, slug=?, sku=?, kisa_aciklama=?, aciklama=?, ozellikler=?, fiyat=?, indirimli_fiyat=?, kdv_orani=?, stok=?, gorsel=?, galeri=?, meta_baslik=?, meta_aciklama=?, one_cikan=?, aktif=? WHERE id=?",
                    [$kategori_id, $marka_id, $ad, $slug, $sku, $kisa, $aciklama, $ozellikler, $fiyat, $indirim_v, $kdv, $stok, $gorsel, $galeri_json, $meta_b, $meta_a, $one_cikan, $aktif, $id]);
                flash_set('ok', 'Ürün güncellendi.');
            } else {
                db_run("INSERT INTO urunler (kategori_id, marka_id, ad, slug, sku, kisa_aciklama, aciklama, ozellikler, fiyat, indirimli_fiyat, kdv_orani, stok, gorsel, galeri, meta_baslik, meta_aciklama, one_cikan, aktif) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$kategori_id, $marka_id, $ad, $slug, $sku, $kisa, $aciklama, $ozellikler, $fiyat, $indirim_v, $kdv, $stok, $gorsel, $galeri_json, $meta_b, $meta_a, $one_cikan, $aktif]);
                flash_set('ok', 'Ürün eklendi.');
            }
            log_yaz('urun_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM urunler WHERE id=?", [$id]);
        log_yaz('urun_sil', "ID: $id", (int)$_kul['id']);
        flash_set('ok', 'Ürün silindi.');
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE urunler SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'one_cikan' && $id) {
        db_run("UPDATE urunler SET one_cikan = 1 - one_cikan WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'galeri_sil') {
        $urun_id = (int)($_POST['urun_id'] ?? 0);
        $resim   = clean($_POST['resim'] ?? '');
        if ($urun_id && $resim) {
            $u = db_get("SELECT galeri FROM urunler WHERE id=?", [$urun_id]);
            $g = json_decode($u['galeri'] ?? '[]', true) ?: [];
            $g = array_values(array_diff($g, [$resim]));
            db_run("UPDATE urunler SET galeri=? WHERE id=?",
                [json_encode($g, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $urun_id]);
            flash_set('ok', 'Galeri görseli silindi.');
        }
        redirect(SITE_URL . '/admin/urunler.php?duzenle=' . $urun_id);
    }
}

// Filtreler
$f_kat   = (int)($_GET['kategori'] ?? 0);
$f_marka = (int)($_GET['marka'] ?? 0);
$f_arama = clean($_GET['q'] ?? '');
$f_durum = $_GET['durum'] ?? '';

$where  = "1=1";
$params = [];
if ($f_kat)   { $where .= " AND u.kategori_id=?"; $params[] = $f_kat; }
if ($f_marka) { $where .= " AND u.marka_id=?";    $params[] = $f_marka; }
if ($f_arama) { $where .= " AND (u.ad LIKE ? OR u.sku LIKE ?)"; $params[] = "%$f_arama%"; $params[] = "%$f_arama%"; }
if ($f_durum === 'one_cikan') $where .= " AND u.one_cikan=1";
if ($f_durum === 'pasif') $where .= " AND u.aktif=0";

$urunler = db_all("SELECT u.*, k.ad kat_ad, m.ad marka_ad
    FROM urunler u
    LEFT JOIN urun_kategorileri k ON k.id=u.kategori_id
    LEFT JOIN markalar m ON m.id=u.marka_id
    WHERE $where ORDER BY u.one_cikan DESC, u.id DESC LIMIT 100", $params);

$kategoriler = db_all("SELECT id, ad FROM urun_kategorileri WHERE aktif=1 ORDER BY sira ASC, ad ASC");
$markalar    = db_all("SELECT id, ad FROM markalar WHERE aktif=1 ORDER BY ad ASC");

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM urunler WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/urunler.php'); }
}
$form_acik = $ekle || $duzenle_id;

$galeri_kayit = [];
if ($kayit && !empty($kayit['galeri'])) $galeri_kayit = json_decode($kayit['galeri'], true) ?: [];

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Ürünler</h1>
        <p class="page-sub">Kombi, klima, ısı pompası gibi ürünleri buradan yönet. Görsel ve galeri yükleyebilirsiniz.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Ürün</a>
    <?php endif; ?>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Ürünü Düzenle' : 'Yeni Ürün' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">
            <input type="hidden" name="gorsel_mevcut" value="<?= e($kayit['gorsel'] ?? '') ?>">
            <input type="hidden" name="galeri_mevcut" value='<?= e($kayit['galeri'] ?? '[]') ?>'>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Ürün Adı *</label>
                    <input class="input" name="ad" value="<?= e($kayit['ad'] ?? '') ?>" required maxlength="220">
                </div>
                <div class="field">
                    <label>Slug</label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="260">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Kategori</label>
                    <select name="kategori_id">
                        <option value="0">— Yok —</option>
                        <?php foreach ($kategoriler as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= ($kayit['kategori_id'] ?? 0) == $k['id'] ? 'selected':'' ?>><?= e($k['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Marka</label>
                    <select name="marka_id">
                        <option value="0">— Yok —</option>
                        <?php foreach ($markalar as $m): ?>
                            <option value="<?= (int)$m['id'] ?>" <?= ($kayit['marka_id'] ?? 0) == $m['id'] ? 'selected':'' ?>><?= e($m['ad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>SKU / Stok Kodu</label>
                    <input class="input" name="sku" value="<?= e($kayit['sku'] ?? '') ?>" maxlength="80" placeholder="DEMIRDOKUM-ADEMIX-2428P">
                </div>
                <div class="field">
                    <label>Stok</label>
                    <input type="number" class="input" name="stok" value="<?= (int)($kayit['stok'] ?? 0) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Kısa Açıklama (max 400)</label>
                    <textarea class="textarea" name="kisa_aciklama" maxlength="400" rows="2"><?= e($kayit['kisa_aciklama'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="field">
                    <label>Liste Fiyatı (₺)</label>
                    <input class="input" name="fiyat" value="<?= e($kayit['fiyat'] ?? '0') ?>" placeholder="32000">
                </div>
                <div class="field">
                    <label>İndirimli Fiyat (₺) <span class="opt">(opsiyonel)</span></label>
                    <input class="input" name="indirimli_fiyat" value="<?= e($kayit['indirimli_fiyat'] ?? '') ?>" placeholder="29500">
                </div>
                <div class="field">
                    <label>KDV (%)</label>
                    <input type="number" class="input" name="kdv_orani" value="<?= (int)($kayit['kdv_orani'] ?? 20) ?>" min="0" max="100">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Detaylı Açıklama (HTML)</label>
                    <textarea class="textarea" name="aciklama" rows="10"><?= e($kayit['aciklama'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Teknik Özellikler <span class="opt">(her satır: anahtar | değer)</span></label>
                    <textarea class="textarea" name="ozellikler" rows="6" placeholder="Kapasite | 24 kW
Enerji Sınıfı | A
Boyutlar | 626 × 400 × 270 mm
Ağırlık | 25.6 kg"><?= e($kayit['ozellikler'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Ana Görsel (PNG/JPG, max 8MB)</label>
                    <input type="file" name="gorsel" accept="image/*">
                    <?php if (!empty($kayit['gorsel'])): ?>
                        <div style="margin-top:10px"><img src="<?= UPLOAD_URL.'/'.e($kayit['gorsel']) ?>" style="max-width:200px;border-radius:8px;border:1px solid var(--c-line)"></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($duzenle_id && $galeri_kayit): ?>
            <div class="form-row">
                <div class="field">
                    <label>Mevcut Galeri (sil simgesine tıklayın)</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
                        <?php foreach ($galeri_kayit as $g): ?>
                            <div style="position:relative;width:100px;height:100px;border-radius:8px;overflow:hidden;border:1px solid var(--c-line)">
                                <img src="<?= UPLOAD_URL.'/'.e($g) ?>" style="width:100%;height:100%;object-fit:cover">
                                <form method="post" style="position:absolute;top:4px;right:4px">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="islem" value="galeri_sil">
                                    <input type="hidden" name="urun_id" value="<?= (int)$kayit['id'] ?>">
                                    <input type="hidden" name="resim" value="<?= e($g) ?>">
                                    <button type="submit" class="btn btn-sm btn-red" data-onay="Sil?" style="padding:4px 8px;font-size:.7rem"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="field">
                    <label>Galeriye Ekle <span class="opt">(birden fazla seçilebilir)</span></label>
                    <input type="file" name="galeri[]" accept="image/*" multiple>
                </div>
            </div>

            <details>
                <summary style="cursor:pointer;font-weight:600;color:var(--c-muted);margin-bottom:14px">SEO Ayarları (opsiyonel)</summary>
                <div class="form-row cols-2">
                    <div class="field">
                        <label>Meta Başlık</label>
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

            <div class="form-row" style="display:flex;gap:24px;flex-wrap:wrap">
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= ($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
                <label class="check">
                    <input type="checkbox" name="one_cikan" value="1" <?= ($kayit['one_cikan'] ?? 0) ? 'checked' : '' ?>>
                    <span>Öne çıkar (ana sayfada gösterilir)</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> <?= $duzenle_id?'Güncelle':'Kaydet' ?></button>
                <a href="<?= SITE_URL ?>/admin/urunler.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="padding:14px;margin-bottom:16px">
        <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
            <div style="flex:1;min-width:200px">
                <input class="input" name="q" value="<?= e($f_arama) ?>" placeholder="Ürün adı / SKU ara...">
            </div>
            <div>
                <select name="kategori">
                    <option value="0">Tüm Kategoriler</option>
                    <?php foreach ($kategoriler as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= $f_kat==$k['id']?'selected':'' ?>><?= e($k['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select name="marka">
                    <option value="0">Tüm Markalar</option>
                    <?php foreach ($markalar as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" <?= $f_marka==$m['id']?'selected':'' ?>><?= e($m['ad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select name="durum">
                    <option value="">Tüm Durumlar</option>
                    <option value="one_cikan" <?= $f_durum==='one_cikan'?'selected':'' ?>>Öne Çıkanlar</option>
                    <option value="pasif" <?= $f_durum==='pasif'?'selected':'' ?>>Pasif</option>
                </select>
            </div>
            <button class="btn btn-pri"><i class="fas fa-filter"></i></button>
            <?php if ($f_arama || $f_kat || $f_marka || $f_durum): ?><a href="<?= SITE_URL ?>/admin/urunler.php" class="btn btn-out">Temizle</a><?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding:0">
        <table class="adm-table">
            <thead><tr><th width="60">Görsel</th><th>Ürün</th><th>Marka</th><th>Kategori</th><th width="120">Fiyat</th><th width="60">Stok</th><th width="100">Durum</th><th width="160">İşlem</th></tr></thead>
            <tbody>
            <?php if (!$urunler): ?>
                <tr><td colspan="8" class="empty">Ürün bulunamadı. <a href="?ekle=1">Yeni ekle</a> veya <a href="<?= SITE_URL ?>/seed.php"><strong>/seed.php</strong></a> ile yükle.</td></tr>
            <?php endif; ?>
            <?php foreach ($urunler as $u):
                $f = (float)($u['indirimli_fiyat'] ?: $u['fiyat']);
            ?>
                <tr>
                    <td><?php if ($u['gorsel']): ?><img src="<?= UPLOAD_URL.'/'.e($u['gorsel']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:4px"><?php else: ?><span style="color:var(--c-muted)">—</span><?php endif; ?></td>
                    <td>
                        <strong><?= e($u['ad']) ?></strong>
                        <?php if ($u['one_cikan']): ?><span class="tag tag-orange" style="font-size:.65rem;margin-left:4px">⭐</span><?php endif; ?>
                        <?php if ($u['sku']): ?><div style="font-size:.72rem;color:var(--c-muted)"><?= e($u['sku']) ?></div><?php endif; ?>
                    </td>
                    <td><?= e($u['marka_ad'] ?? '—') ?></td>
                    <td><?= e($u['kat_ad'] ?? '—') ?></td>
                    <td>
                        <?php if ($u['indirimli_fiyat'] && $u['indirimli_fiyat'] < $u['fiyat']): ?>
                            <span style="text-decoration:line-through;color:var(--c-muted);font-size:.78rem"><?= tl((float)$u['fiyat']) ?></span><br>
                        <?php endif; ?>
                        <strong><?= tl($f) ?></strong>
                    </td>
                    <td><?= (int)$u['stok'] ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="aktif_degistir">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="badge badge-<?= $u['aktif']?'ok':'pas' ?>" type="submit"><?= $u['aktif']?'Aktif':'Pasif' ?></button>
                        </form>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="one_cikan">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="badge badge-<?= $u['one_cikan']?'ok':'pas' ?>" type="submit" title="Öne çıkan"><?= $u['one_cikan']?'⭐':'☆' ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="<?= SITE_URL ?>/urun/<?= e($u['slug']) ?>" target="_blank" class="btn btn-sm btn-out"><i class="fas fa-eye"></i></a>
                        <a href="?duzenle=<?= (int)$u['id'] ?>" class="btn btn-sm btn-out"><i class="fas fa-pen"></i></a>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="sil">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
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
