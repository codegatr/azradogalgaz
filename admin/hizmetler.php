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
        $kategori_id   = (int)($_POST['kategori_id'] ?? 0) ?: null;
        $baslik        = clean($_POST['baslik'] ?? '');
        $slug          = clean($_POST['slug'] ?? '');
        $kisa_aciklama = clean($_POST['kisa_aciklama'] ?? '');
        $icerik        = trim($_POST['icerik'] ?? '');
        $meta_baslik   = clean($_POST['meta_baslik'] ?? '');
        $meta_aciklama = clean($_POST['meta_aciklama'] ?? '');
        $sira          = (int)($_POST['sira'] ?? 0);
        $aktif         = !empty($_POST['aktif']) ? 1 : 0;

        if ($baslik === '') {
            flash_set('err', 'Başlık zorunlu.');
        } else {
            if (!$slug) $slug = slugify($baslik);
            $cak = db_get("SELECT id FROM hizmetler WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100, 999);

            // Görsel yönetimi
            $gorsel = null;
            if (!empty($_FILES['gorsel']['name'])) {
                $gorsel = resim_yukle($_FILES['gorsel'], 'hizmetler');
                if (!$gorsel) flash_set('err', 'Görsel yüklenemedi (sadece JPG/PNG/WEBP, en fazla 8MB).');
            }
            $gorsel_url_input = clean($_POST['gorsel_url'] ?? '');
            if (!$gorsel && $gorsel_url_input) $gorsel = $gorsel_url_input;
            if (!$gorsel && $id) {
                $eski = db_get("SELECT gorsel FROM hizmetler WHERE id=?", [$id]);
                $gorsel = $eski['gorsel'] ?? null;
            }
            if (!empty($_POST['gorsel_sil']) && $id) $gorsel = null;

            if ($id) {
                db_run("UPDATE hizmetler SET kategori_id=?, baslik=?, slug=?, kisa_aciklama=?, icerik=?, gorsel=?, meta_baslik=?, meta_aciklama=?, sira=?, aktif=? WHERE id=?",
                    [$kategori_id, $baslik, $slug, $kisa_aciklama, $icerik, $gorsel, $meta_baslik, $meta_aciklama, $sira, $aktif, $id]);
                flash_set('ok', 'Hizmet güncellendi.');
            } else {
                db_run("INSERT INTO hizmetler (kategori_id, baslik, slug, kisa_aciklama, icerik, gorsel, meta_baslik, meta_aciklama, sira, aktif)
                    VALUES (?,?,?,?,?,?,?,?,?,?)",
                    [$kategori_id, $baslik, $slug, $kisa_aciklama, $icerik, $gorsel, $meta_baslik, $meta_aciklama, $sira, $aktif]);
                $id = (int)db()->lastInsertId();
                flash_set('ok', 'Hizmet eklendi.');
            }
            log_yaz('hizmet_kaydet', "ID: $id, baslik: $baslik", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/hizmetler.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM hizmetler WHERE id=?", [$id]);
        log_yaz('hizmet_sil', "ID: $id silindi", (int)$_kul['id']);
        flash_set('ok', 'Hizmet silindi.');
        redirect(SITE_URL . '/admin/hizmetler.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE hizmetler SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/hizmetler.php');
    }
}

// Filtreler
$filter_kat = (int)($_GET['kat'] ?? 0);
$arama = clean($_GET['q'] ?? '');
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 20;
$ofset = ($sayfa - 1) * $limit;

$where = "1=1"; $params = [];
if ($filter_kat) { $where .= " AND h.kategori_id=?"; $params[] = $filter_kat; }
if ($arama)     { $where .= " AND (h.baslik LIKE ? OR h.kisa_aciklama LIKE ?)"; $w="%$arama%"; $params[]=$w; $params[]=$w; }

$toplam = (int)db_get("SELECT COUNT(*) c FROM hizmetler h WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$hizmetler = db_all("SELECT h.*, k.ad AS kategori_ad
    FROM hizmetler h
    LEFT JOIN hizmet_kategorileri k ON h.kategori_id=k.id
    WHERE $where
    ORDER BY h.sira ASC, h.id DESC
    LIMIT $limit OFFSET $ofset", $params);

$kategoriler = db_all("SELECT id, ad FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira, ad");

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
        <p class="page-sub">Doğalgaz tesisatı, kombi montajı, klima servisi gibi hizmetleri buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Hizmet</a>
    <?php endif; ?>
</div>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Hizmeti Düzenle' : 'Yeni Hizmet' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

            <div class="form-row cols-2">
                <div class="field">
                    <label>Başlık *</label>
                    <input class="input" name="baslik" value="<?= e($kayit['baslik'] ?? '') ?>" required maxlength="180">
                </div>
                <div class="field">
                    <label>Slug <span class="opt">(boş = otomatik)</span></label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="220">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Kategori</label>
                    <select name="kategori_id">
                        <option value="">— Kategorisiz —</option>
                        <?php foreach ($kategoriler as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= (int)($kayit['kategori_id'] ?? 0) === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= e($k['ad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Sıralama</label>
                    <input class="input" type="number" name="sira" value="<?= (int)($kayit['sira'] ?? 0) ?>">
                </div>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>Kısa Açıklama <span class="opt">(kart üstünde, max 300 karakter)</span></label>
                <textarea class="input" name="kisa_aciklama" rows="2" maxlength="300"><?= e($kayit['kisa_aciklama'] ?? '') ?></textarea>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>İçerik (Detay sayfası — HTML kabul)</label>
                <textarea class="input" name="icerik" rows="10"><?= e($kayit['icerik'] ?? '') ?></textarea>
                <small style="color:var(--c-muted)">HTML kullanabilirsin: &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;a href&gt;</small>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Görsel (Dosya Yükle)</label>
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
                        <img src="<?= e(gorsel_url($kayit['gorsel'])) ?>" style="max-height:100px;max-width:200px;border-radius:6px;object-fit:cover">
                        <label class="check"><input type="checkbox" name="gorsel_sil" value="1"> <span>Görseli kaldır</span></label>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <details style="margin:14px 0;padding:14px;background:var(--c-bg);border-radius:8px">
                <summary style="cursor:pointer;font-weight:700">SEO Ayarları (isteğe bağlı)</summary>
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

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" <?= !isset($kayit) || (int)($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/admin/hizmetler.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="filters">
        <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Hizmet ara…" class="input">
        <select name="kat">
            <option value="">Tüm kategoriler</option>
            <?php foreach ($kategoriler as $k): ?>
                <option value="<?= (int)$k['id'] ?>" <?= $filter_kat === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['ad']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i> Filtrele</button>
        <?php if ($filter_kat || $arama): ?>
            <a href="<?= SITE_URL ?>/admin/hizmetler.php" class="btn btn-out btn-sm">Temizle</a>
        <?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:60px">#</th>
    <th style="width:80px">Görsel</th>
    <th>Hizmet</th>
    <th>Kategori</th>
    <th>Slug</th>
    <th style="width:80px">Sıra</th>
    <th style="width:90px">Durum</th>
    <th style="width:160px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$hizmetler): ?>
    <tr><td colspan="8" class="empty">Hizmet bulunamadı.</td></tr>
<?php else: foreach ($hizmetler as $h): ?>
<tr>
    <td><?= (int)$h['id'] ?></td>
    <td>
        <?php if ($h['gorsel']): ?>
            <img src="<?= e(gorsel_url($h['gorsel'])) ?>" style="width:60px;height:42px;object-fit:cover;border-radius:6px">
        <?php else: ?><span style="color:var(--c-muted);font-size:.78rem">—</span><?php endif; ?>
    </td>
    <td>
        <strong><?= e($h['baslik']) ?></strong>
        <?php if ($h['kisa_aciklama']): ?>
            <br><small style="color:var(--c-muted)"><?= e(mb_substr($h['kisa_aciklama'], 0, 80)) ?><?= mb_strlen($h['kisa_aciklama']) > 80 ? '…' : '' ?></small>
        <?php endif; ?>
    </td>
    <td><?= e($h['kategori_ad'] ?? '—') ?></td>
    <td><code style="font-size:.78rem"><?= e($h['slug']) ?></code></td>
    <td class="num"><?= (int)$h['sira'] ?></td>
    <td>
        <?php if ((int)$h['aktif']): ?>
            <span class="badge badge-ok">Aktif</span>
        <?php else: ?>
            <span class="badge badge-no">Pasif</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions">
            <a href="<?= SITE_URL ?>/hizmet/<?= e($h['slug']) ?>" target="_blank" class="btn btn-out btn-sm" title="Sitede Gör"><i class="fas fa-eye"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="aktif_degistir">
                <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                <button class="btn btn-out btn-sm" title="Aktif/Pasif"><i class="fas fa-toggle-on"></i></button>
            </form>
            <a href="?duzenle=<?= (int)$h['id'] ?>" class="btn btn-blue btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="sil">
                <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                <button class="btn btn-danger btn-sm" data-onay="Hizmet silinsin mi?" title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/hizmetler.php?' . http_build_query(array_filter(['q'=>$arama, 'kat'=>$filter_kat]));
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
