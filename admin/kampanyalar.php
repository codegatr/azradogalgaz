<?php
require_once __DIR__ . '/_baslat.php';
page_title('Kampanyalar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'kaydet') {
        $baslik         = clean($_POST['baslik'] ?? '');
        $slug           = clean($_POST['slug'] ?? '');
        $kisa_aciklama  = clean($_POST['kisa_aciklama'] ?? '');
        $icerik         = trim($_POST['icerik'] ?? '');
        $nakit_fiyat    = $_POST['nakit_fiyat'] !== '' ? (float)str_replace([',', ' '], ['.', ''], $_POST['nakit_fiyat']) : null;
        $kart_fiyat     = $_POST['kart_fiyat']  !== '' ? (float)str_replace([',', ' '], ['.', ''], $_POST['kart_fiyat'])  : null;
        $taksit_sayisi  = (int)($_POST['taksit_sayisi'] ?? 0);
        $baslangic      = $_POST['baslangic'] ?: null;
        $bitis          = $_POST['bitis'] ?: null;
        $meta_baslik    = clean($_POST['meta_baslik'] ?? '');
        $meta_aciklama  = clean($_POST['meta_aciklama'] ?? '');
        $aktif          = !empty($_POST['aktif']) ? 1 : 0;

        if ($baslik === '') {
            flash_set('err', 'Başlık zorunlu.');
        } else {
            if (!$slug) $slug = slugify($baslik);
            $cak = db_get("SELECT id FROM kampanyalar WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100, 999);

            $gorsel = null;
            if (!empty($_FILES['gorsel']['name'])) {
                $gorsel = resim_yukle($_FILES['gorsel'], 'kampanyalar');
                if (!$gorsel) flash_set('err', 'Görsel yüklenemedi.');
            }
            $gorsel_url_input = clean($_POST['gorsel_url'] ?? '');
            if (!$gorsel && $gorsel_url_input) $gorsel = $gorsel_url_input;
            if (!$gorsel && $id) {
                $eski = db_get("SELECT gorsel FROM kampanyalar WHERE id=?", [$id]);
                $gorsel = $eski['gorsel'] ?? null;
            }
            if (!empty($_POST['gorsel_sil']) && $id) $gorsel = null;

            if ($id) {
                db_run("UPDATE kampanyalar SET baslik=?, slug=?, kisa_aciklama=?, icerik=?, gorsel=?,
                        nakit_fiyat=?, kart_fiyat=?, taksit_sayisi=?, baslangic=?, bitis=?,
                        meta_baslik=?, meta_aciklama=?, aktif=?
                    WHERE id=?",
                    [$baslik, $slug, $kisa_aciklama, $icerik, $gorsel,
                     $nakit_fiyat, $kart_fiyat, $taksit_sayisi, $baslangic, $bitis,
                     $meta_baslik, $meta_aciklama, $aktif, $id]);
                flash_set('ok', 'Kampanya güncellendi.');
            } else {
                db_run("INSERT INTO kampanyalar (baslik, slug, kisa_aciklama, icerik, gorsel,
                        nakit_fiyat, kart_fiyat, taksit_sayisi, baslangic, bitis,
                        meta_baslik, meta_aciklama, aktif)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$baslik, $slug, $kisa_aciklama, $icerik, $gorsel,
                     $nakit_fiyat, $kart_fiyat, $taksit_sayisi, $baslangic, $bitis,
                     $meta_baslik, $meta_aciklama, $aktif]);
                $id = (int)db()->lastInsertId();
                flash_set('ok', 'Kampanya eklendi.');
            }
            log_yaz('kampanya_kaydet', "ID: $id, baslik: $baslik", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/kampanyalar.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM kampanyalar WHERE id=?", [$id]);
        log_yaz('kampanya_sil', "ID: $id silindi", (int)$_kul['id']);
        flash_set('ok', 'Kampanya silindi.');
        redirect(SITE_URL . '/admin/kampanyalar.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE kampanyalar SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/kampanyalar.php');
    } elseif ($islem === 'aktif_kampanya_yap' && $id) {
        // Anasayfada gösterilecek kampanyayı işaretle (ayarlar tablosu)
        db_run("INSERT INTO ayarlar (anahtar, deger) VALUES ('aktif_kampanya_id', ?)
                ON DUPLICATE KEY UPDATE deger=VALUES(deger)", [(string)$id]);
        flash_set('ok', 'Anasayfa kampanyası ayarlandı.');
        redirect(SITE_URL . '/admin/kampanyalar.php');
    }
}

$arama = clean($_GET['q'] ?? '');
$where = "1=1"; $params = [];
if ($arama) { $where .= " AND (baslik LIKE ? OR kisa_aciklama LIKE ?)"; $w="%$arama%"; $params[]=$w; $params[]=$w; }

$kampanyalar = db_all("SELECT * FROM kampanyalar WHERE $where ORDER BY id DESC", $params);
$aktif_kampanya_id = (int)(db_get("SELECT deger FROM ayarlar WHERE anahtar='aktif_kampanya_id'")['deger'] ?? 0);

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM kampanyalar WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/kampanyalar.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Kampanyalar</h1>
        <p class="page-sub">Anasayfada gösterilen Demirdöküm Ademix paket kampanyalarını ve indirimleri yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Kampanya</a>
    <?php endif; ?>
</div>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Kampanyayı Düzenle' : 'Yeni Kampanya' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

            <div class="form-row cols-2">
                <div class="field">
                    <label>Başlık *</label>
                    <input class="input" name="baslik" value="<?= e($kayit['baslik'] ?? '') ?>" required maxlength="220">
                </div>
                <div class="field">
                    <label>Slug</label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="260">
                </div>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>Kısa Açıklama (max 400)</label>
                <textarea class="input" name="kisa_aciklama" rows="2" maxlength="400"><?= e($kayit['kisa_aciklama'] ?? '') ?></textarea>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>Detay İçerik (HTML)</label>
                <textarea class="input" name="icerik" rows="8"><?= e($kayit['icerik'] ?? '') ?></textarea>
            </div>

            <div class="form-row cols-3">
                <div class="field">
                    <label>Nakit Fiyat (₺)</label>
                    <input class="input" type="number" step="0.01" name="nakit_fiyat" value="<?= e((string)($kayit['nakit_fiyat'] ?? '')) ?>" placeholder="80000">
                </div>
                <div class="field">
                    <label>Kart Fiyat (₺)</label>
                    <input class="input" type="number" step="0.01" name="kart_fiyat" value="<?= e((string)($kayit['kart_fiyat'] ?? '')) ?>" placeholder="87000">
                </div>
                <div class="field">
                    <label>Taksit Sayısı</label>
                    <input class="input" type="number" name="taksit_sayisi" value="<?= (int)($kayit['taksit_sayisi'] ?? 6) ?>">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Başlangıç Tarihi</label>
                    <input class="input" type="date" name="baslangic" value="<?= e($kayit['baslangic'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Bitiş Tarihi</label>
                    <input class="input" type="date" name="bitis" value="<?= e($kayit['bitis'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Görsel (Dosya Yükle)</label>
                    <input class="input" type="file" name="gorsel" accept="image/*">
                </div>
                <div class="field">
                    <label>veya Görsel URL</label>
                    <input class="input" name="gorsel_url" value="<?= e($kayit['gorsel'] ?? '') ?>">
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

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" <?= !isset($kayit) || (int)($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/admin/kampanyalar.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="filters">
        <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Kampanya ara…" class="input">
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i> Filtrele</button>
        <?php if ($arama): ?><a href="<?= SITE_URL ?>/admin/kampanyalar.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= count($kampanyalar) ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:60px">#</th>
    <th style="width:80px">Görsel</th>
    <th>Kampanya</th>
    <th style="width:160px">Fiyat</th>
    <th style="width:140px">Tarih</th>
    <th style="width:90px">Durum</th>
    <th style="width:200px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$kampanyalar): ?>
    <tr><td colspan="7" class="empty">Kampanya yok.</td></tr>
<?php else: foreach ($kampanyalar as $k): ?>
<tr>
    <td><?= (int)$k['id'] ?></td>
    <td>
        <?php if ($k['gorsel']): ?>
            <img src="<?= e(gorsel_url($k['gorsel'])) ?>" style="width:60px;height:42px;object-fit:cover;border-radius:6px">
        <?php else: ?><span style="color:var(--c-muted);font-size:.78rem">—</span><?php endif; ?>
    </td>
    <td>
        <strong><?= e($k['baslik']) ?></strong>
        <?php if ((int)$k['id'] === $aktif_kampanya_id): ?>
            <span class="badge badge-warn" style="margin-left:6px"><i class="fas fa-house"></i> Anasayfada</span>
        <?php endif; ?>
        <br><small style="color:var(--c-muted)"><code><?= e($k['slug']) ?></code></small>
    </td>
    <td class="num">
        <?php if ($k['nakit_fiyat']): ?><strong style="color:var(--c-orange)"><?= tl((float)$k['nakit_fiyat']) ?></strong> <small>nakit</small><br><?php endif; ?>
        <?php if ($k['kart_fiyat']): ?><small><?= tl((float)$k['kart_fiyat']) ?> <?= (int)$k['taksit_sayisi'] ? '/'.$k['taksit_sayisi'].' taksit' : '' ?></small><?php endif; ?>
    </td>
    <td>
        <?php if ($k['baslangic']): ?><small><?= tarih_tr($k['baslangic']) ?></small><br><?php endif; ?>
        <?php if ($k['bitis']): ?><small style="color:var(--c-muted)">→ <?= tarih_tr($k['bitis']) ?></small><?php endif; ?>
    </td>
    <td>
        <?php if ((int)$k['aktif']): ?>
            <span class="badge badge-ok">Aktif</span>
        <?php else: ?>
            <span class="badge badge-no">Pasif</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions">
            <a href="<?= SITE_URL ?>/kampanya/<?= e($k['slug']) ?>" target="_blank" class="btn btn-out btn-sm" title="Sitede Gör"><i class="fas fa-eye"></i></a>
            <?php if ((int)$k['id'] !== $aktif_kampanya_id): ?>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="aktif_kampanya_yap">
                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                <button class="btn btn-out btn-sm" title="Anasayfada göster" data-onay="Anasayfa kampanyası bu olsun mu?"><i class="fas fa-house"></i></button>
            </form>
            <?php endif; ?>
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
                <button class="btn btn-danger btn-sm" data-onay="Kampanya silinsin mi?" title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
