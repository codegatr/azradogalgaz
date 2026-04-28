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
        $kategori_id      = (int)($_POST['kategori_id'] ?? 0) ?: null;
        $marka_id         = (int)($_POST['marka_id'] ?? 0) ?: null;
        $ad               = clean($_POST['ad'] ?? '');
        $slug             = clean($_POST['slug'] ?? '');
        $sku              = clean($_POST['sku'] ?? '');
        $kisa_aciklama    = clean($_POST['kisa_aciklama'] ?? '');
        $aciklama         = trim($_POST['aciklama'] ?? '');
        $ozellikler       = trim($_POST['ozellikler'] ?? '');
        $fiyat            = (float)str_replace([',', ' '], ['.', ''], $_POST['fiyat'] ?? '0');
        $indirimli_fiyat  = $_POST['indirimli_fiyat'] !== '' ? (float)str_replace([',', ' '], ['.', ''], $_POST['indirimli_fiyat']) : null;
        $kdv_orani        = (int)($_POST['kdv_orani'] ?? 20);
        $stok             = (int)($_POST['stok'] ?? 0);
        $meta_baslik      = clean($_POST['meta_baslik'] ?? '');
        $meta_aciklama    = clean($_POST['meta_aciklama'] ?? '');
        $one_cikan        = !empty($_POST['one_cikan']) ? 1 : 0;
        $aktif            = !empty($_POST['aktif']) ? 1 : 0;

        if ($ad === '') {
            flash_set('err', 'Ürün adı zorunlu.');
        } else {
            if (!$slug) $slug = slugify($ad);
            $cak = db_get("SELECT id FROM urunler WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100, 999);

            // Görsel
            $gorsel = null;
            if (!empty($_FILES['gorsel']['name'])) {
                $gorsel = resim_yukle($_FILES['gorsel'], 'urunler');
                if (!$gorsel) flash_set('err', 'Ana görsel yüklenemedi.');
            }
            $gorsel_url_input = clean($_POST['gorsel_url'] ?? '');
            if (!$gorsel && $gorsel_url_input) $gorsel = $gorsel_url_input;
            if (!$gorsel && $id) {
                $eski = db_get("SELECT gorsel FROM urunler WHERE id=?", [$id]);
                $gorsel = $eski['gorsel'] ?? null;
            }
            if (!empty($_POST['gorsel_sil']) && $id) $gorsel = null;

            // Galeri (çoklu URL — virgül veya yeni satır ile ayrılmış)
            $galeri = clean($_POST['galeri'] ?? '');

            if ($id) {
                db_run("UPDATE urunler SET kategori_id=?, marka_id=?, ad=?, slug=?, sku=?, kisa_aciklama=?, aciklama=?, ozellikler=?,
                        fiyat=?, indirimli_fiyat=?, kdv_orani=?, stok=?, gorsel=?, galeri=?,
                        meta_baslik=?, meta_aciklama=?, one_cikan=?, aktif=?
                    WHERE id=?",
                    [$kategori_id, $marka_id, $ad, $slug, $sku, $kisa_aciklama, $aciklama, $ozellikler,
                     $fiyat, $indirimli_fiyat, $kdv_orani, $stok, $gorsel, $galeri,
                     $meta_baslik, $meta_aciklama, $one_cikan, $aktif, $id]);
                flash_set('ok', 'Ürün güncellendi.');
            } else {
                db_run("INSERT INTO urunler (kategori_id, marka_id, ad, slug, sku, kisa_aciklama, aciklama, ozellikler,
                        fiyat, indirimli_fiyat, kdv_orani, stok, gorsel, galeri,
                        meta_baslik, meta_aciklama, one_cikan, aktif)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$kategori_id, $marka_id, $ad, $slug, $sku, $kisa_aciklama, $aciklama, $ozellikler,
                     $fiyat, $indirimli_fiyat, $kdv_orani, $stok, $gorsel, $galeri,
                     $meta_baslik, $meta_aciklama, $one_cikan, $aktif]);
                $id = (int)db()->lastInsertId();
                flash_set('ok', 'Ürün eklendi.');
            }
            log_yaz('urun_kaydet', "ID: $id, ad: $ad", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM urunler WHERE id=?", [$id]);
        log_yaz('urun_sil', "ID: $id silindi", (int)$_kul['id']);
        flash_set('ok', 'Ürün silindi.');
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE urunler SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/urunler.php');
    } elseif ($islem === 'one_cikan_degistir' && $id) {
        db_run("UPDATE urunler SET one_cikan = 1 - one_cikan WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/urunler.php');
    }
}

// Filtreler
$filter_kat   = (int)($_GET['kat'] ?? 0);
$filter_marka = (int)($_GET['marka'] ?? 0);
$arama        = clean($_GET['q'] ?? '');
$sayfa        = max(1, (int)($_GET['sayfa'] ?? 1));
$limit        = 20;
$ofset        = ($sayfa - 1) * $limit;

$where = "1=1"; $params = [];
if ($filter_kat)   { $where .= " AND u.kategori_id=?"; $params[] = $filter_kat; }
if ($filter_marka) { $where .= " AND u.marka_id=?"; $params[] = $filter_marka; }
if ($arama)        { $where .= " AND (u.ad LIKE ? OR u.sku LIKE ? OR u.kisa_aciklama LIKE ?)"; $w="%$arama%"; $params[]=$w; $params[]=$w; $params[]=$w; }

$toplam = (int)db_get("SELECT COUNT(*) c FROM urunler u WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$urunler = db_all("SELECT u.*, k.ad AS kategori_ad, m.ad AS marka_ad
    FROM urunler u
    LEFT JOIN urun_kategorileri k ON u.kategori_id=k.id
    LEFT JOIN markalar m ON u.marka_id=m.id
    WHERE $where
    ORDER BY u.id DESC
    LIMIT $limit OFFSET $ofset", $params);

$kategoriler = db_all("SELECT id, ad, ust_id FROM urun_kategorileri WHERE aktif=1 ORDER BY COALESCE(ust_id, id), sira, ad");
$markalar    = db_all("SELECT id, ad FROM markalar WHERE aktif=1 ORDER BY ad");

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM urunler WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/urunler.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Ürünler</h1>
        <p class="page-sub">Demirdöküm Ademix kombiler, klimalar, tesisat ürünlerini buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Ürün</a>
    <?php endif; ?>
</div>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Ürünü Düzenle' : 'Yeni Ürün' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

            <div class="form-row cols-2">
                <div class="field">
                    <label>Ürün Adı *</label>
                    <input class="input" name="ad" value="<?= e($kayit['ad'] ?? '') ?>" required maxlength="220">
                </div>
                <div class="field">
                    <label>Slug <span class="opt">(boş = otomatik)</span></label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="260">
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="field">
                    <label>Kategori</label>
                    <select name="kategori_id">
                        <option value="">— Seçiniz —</option>
                        <?php foreach ($kategoriler as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= (int)($kayit['kategori_id'] ?? 0) === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= $k['ust_id'] ? '└ ' : '' ?><?= e($k['ad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Marka</label>
                    <select name="marka_id">
                        <option value="">— Seçiniz —</option>
                        <?php foreach ($markalar as $m): ?>
                            <option value="<?= (int)$m['id'] ?>" <?= (int)($kayit['marka_id'] ?? 0) === (int)$m['id'] ? 'selected' : '' ?>>
                                <?= e($m['ad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>SKU / Stok Kodu</label>
                    <input class="input" name="sku" value="<?= e($kayit['sku'] ?? '') ?>" maxlength="80">
                </div>
            </div>

            <div class="form-row cols-4">
                <div class="field">
                    <label>Fiyat (₺) *</label>
                    <input class="input" type="number" step="0.01" name="fiyat" value="<?= e((string)($kayit['fiyat'] ?? '0')) ?>" required>
                </div>
                <div class="field">
                    <label>İndirimli Fiyat <span class="opt">(boş = indirim yok)</span></label>
                    <input class="input" type="number" step="0.01" name="indirimli_fiyat" value="<?= e((string)($kayit['indirimli_fiyat'] ?? '')) ?>">
                </div>
                <div class="field">
                    <label>KDV Oranı (%)</label>
                    <select name="kdv_orani">
                        <?php foreach ([1,8,10,18,20] as $kv): ?>
                            <option value="<?= $kv ?>" <?= (int)($kayit['kdv_orani'] ?? 20) === $kv ? 'selected' : '' ?>>%<?= $kv ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Stok Adedi</label>
                    <input class="input" type="number" name="stok" value="<?= (int)($kayit['stok'] ?? 0) ?>">
                </div>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>Kısa Açıklama <span class="opt">(liste/kart üstü, max 400)</span></label>
                <textarea class="input" name="kisa_aciklama" rows="2" maxlength="400"><?= e($kayit['kisa_aciklama'] ?? '') ?></textarea>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>Detaylı Açıklama (HTML)</label>
                <textarea class="input" name="aciklama" rows="8"><?= e($kayit['aciklama'] ?? '') ?></textarea>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>Teknik Özellikler <span class="opt">(her satırda "Etiket: Değer" formatında)</span></label>
                <textarea class="input" name="ozellikler" rows="6" placeholder="Güç: 24 kW&#10;Verimlilik: A sınıfı&#10;Garanti: 2 yıl"><?= e($kayit['ozellikler'] ?? '') ?></textarea>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Ana Görsel (Dosya Yükle)</label>
                    <input class="input" type="file" name="gorsel" accept="image/*">
                </div>
                <div class="field">
                    <label>veya Görsel URL</label>
                    <input class="input" name="gorsel_url" value="<?= e($kayit['gorsel'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>

            <?php if (!empty($kayit['gorsel'])): ?>
            <div class="form-row">
                <div class="field">
                    <label>Mevcut Görsel</label>
                    <div style="display:flex;gap:14px;align-items:center;background:var(--c-bg);padding:12px;border-radius:8px">
                        <img src="<?= e(gorsel_url($kayit['gorsel'])) ?>" style="max-height:120px;max-width:200px;border-radius:6px;object-fit:cover">
                        <label class="check"><input type="checkbox" name="gorsel_sil" value="1"> <span>Görseli kaldır</span></label>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="field" style="margin-bottom:14px">
                <label>Galeri Görselleri <span class="opt">(her satıra bir URL, isteğe bağlı)</span></label>
                <textarea class="input" name="galeri" rows="3" placeholder="https://...resim1.jpg&#10;https://...resim2.jpg"><?= e($kayit['galeri'] ?? '') ?></textarea>
            </div>

            <details style="margin:14px 0;padding:14px;background:var(--c-bg);border-radius:8px">
                <summary style="cursor:pointer;font-weight:700">SEO Ayarları</summary>
                <div style="margin-top:14px">
                    <div class="field" style="margin-bottom:12px">
                        <label>Meta Başlık</label>
                        <input class="input" name="meta_baslik" value="<?= e($kayit['meta_baslik'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="field">
                        <label>Meta Açıklama</label>
                        <textarea class="input" name="meta_aciklama" rows="2" maxlength="300"><?= e($kayit['meta_aciklama'] ?? '') ?></textarea>
                    </div>
                </div>
            </details>

            <div class="form-row cols-2">
                <label class="check">
                    <input type="checkbox" name="aktif" <?= !isset($kayit) || (int)($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif (sitede görünür)</span>
                </label>
                <label class="check">
                    <input type="checkbox" name="one_cikan" <?= isset($kayit) && (int)($kayit['one_cikan'] ?? 0) ? 'checked' : '' ?>>
                    <span>Anasayfada öne çıkar</span>
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/admin/urunler.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="filters">
        <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Ürün, SKU ara…" class="input">
        <select name="kat">
            <option value="">Tüm kategoriler</option>
            <?php foreach ($kategoriler as $k): ?>
                <option value="<?= (int)$k['id'] ?>" <?= $filter_kat === (int)$k['id'] ? 'selected' : '' ?>>
                    <?= $k['ust_id'] ? '└ ' : '' ?><?= e($k['ad']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="marka">
            <option value="">Tüm markalar</option>
            <?php foreach ($markalar as $m): ?>
                <option value="<?= (int)$m['id'] ?>" <?= $filter_marka === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['ad']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i> Filtrele</button>
        <?php if ($filter_kat || $filter_marka || $arama): ?>
            <a href="<?= SITE_URL ?>/admin/urunler.php" class="btn btn-out btn-sm">Temizle</a>
        <?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> ürün</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:60px">#</th>
    <th style="width:80px">Görsel</th>
    <th>Ürün</th>
    <th>Kategori / Marka</th>
    <th>SKU</th>
    <th style="width:140px">Fiyat</th>
    <th style="width:80px">Stok</th>
    <th style="width:90px">Durum</th>
    <th style="width:180px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$urunler): ?>
    <tr><td colspan="9" class="empty">Ürün bulunamadı.</td></tr>
<?php else: foreach ($urunler as $u): ?>
<tr>
    <td><?= (int)$u['id'] ?></td>
    <td>
        <?php if ($u['gorsel']): ?>
            <img src="<?= e(gorsel_url($u['gorsel'])) ?>" style="width:60px;height:42px;object-fit:cover;border-radius:6px">
        <?php else: ?><span style="color:var(--c-muted);font-size:.78rem">—</span><?php endif; ?>
    </td>
    <td>
        <strong><?= e($u['ad']) ?></strong>
        <?php if ((int)$u['one_cikan']): ?> <span class="badge badge-warn">Öne Çıkan</span><?php endif; ?>
        <br><small style="color:var(--c-muted)"><code><?= e($u['slug']) ?></code></small>
    </td>
    <td>
        <?= e($u['kategori_ad'] ?? '—') ?>
        <?php if ($u['marka_ad']): ?><br><small style="color:var(--c-muted)"><?= e($u['marka_ad']) ?></small><?php endif; ?>
    </td>
    <td><small><code><?= e($u['sku'] ?: '—') ?></code></small></td>
    <td class="num">
        <?php if ($u['indirimli_fiyat'] !== null && (float)$u['indirimli_fiyat'] > 0): ?>
            <strong style="color:var(--c-orange)"><?= tl((float)$u['indirimli_fiyat']) ?></strong>
            <br><s style="color:var(--c-muted);font-size:.8rem"><?= tl((float)$u['fiyat']) ?></s>
        <?php else: ?>
            <strong><?= tl((float)$u['fiyat']) ?></strong>
        <?php endif; ?>
    </td>
    <td class="num"><?= (int)$u['stok'] ?></td>
    <td>
        <?php if ((int)$u['aktif']): ?>
            <span class="badge badge-ok">Aktif</span>
        <?php else: ?>
            <span class="badge badge-no">Pasif</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions">
            <a href="<?= SITE_URL ?>/urun/<?= e($u['slug']) ?>" target="_blank" class="btn btn-out btn-sm" title="Sitede Gör"><i class="fas fa-eye"></i></a>
            <form method="post" style="display:inline" title="Öne çıkan değiştir">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="one_cikan_degistir">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-out btn-sm <?= (int)$u['one_cikan'] ? 'btn-warn-active' : '' ?>" title="Öne Çıkan"><i class="fas fa-star" <?= (int)$u['one_cikan'] ? 'style="color:#f59e0b"' : '' ?>></i></button>
            </form>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="aktif_degistir">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-out btn-sm" title="Aktif/Pasif"><i class="fas fa-toggle-on"></i></button>
            </form>
            <a href="?duzenle=<?= (int)$u['id'] ?>" class="btn btn-blue btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="sil">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-danger btn-sm" data-onay="Ürün silinsin mi? Geri alınamaz." title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/urunler.php?' . http_build_query(array_filter(['q'=>$arama, 'kat'=>$filter_kat, 'marka'=>$filter_marka]));
    $base .= ($base[strlen($base)-1] === '?') ? '' : '&';
?>
<nav class="pager">
    <?php if ($sayfa > 1): ?><a href="<?= $base ?>sayfa=<?= $sayfa-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($p=1;$p<=$toplam_sayfa;$p++): ?>
        <a href="<?= $base ?>sayfa=<?= $p ?>" class="<?= $p===$sayfa?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($sayfa < $toplam_sayfa): ?><a href="<?= $base ?>sayfa=<?= $sayfa+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
