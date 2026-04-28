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
        $baslik = clean($_POST['baslik'] ?? '');
        $slug   = clean($_POST['slug'] ?? '');
        $kisa   = clean($_POST['kisa_aciklama'] ?? '');
        $icerik = (string)($_POST['icerik'] ?? '');
        $nakit  = $_POST['nakit_fiyat'] !== '' ? (float)str_replace([',','.'],['.',''],$_POST['nakit_fiyat']) : null;
        $kart   = $_POST['kart_fiyat']  !== '' ? (float)str_replace([',','.'],['.',''],$_POST['kart_fiyat'])  : null;
        $taksit = (int)($_POST['taksit_sayisi'] ?? 0);
        $bas    = clean($_POST['baslangic'] ?? '') ?: null;
        $bit    = clean($_POST['bitis'] ?? '') ?: null;
        $meta_b = clean($_POST['meta_baslik'] ?? '');
        $meta_a = clean($_POST['meta_aciklama'] ?? '');
        $aktif  = !empty($_POST['aktif']) ? 1 : 0;
        $gorsel_mevcut = clean($_POST['gorsel_mevcut'] ?? '');

        if ($baslik === '') {
            flash_set('err', 'Başlık zorunlu.');
        } else {
            if (!$slug) $slug = slugify($baslik);
            $cak = db_get("SELECT id FROM kampanyalar WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            $gorsel = $gorsel_mevcut;
            if (!empty($_FILES['gorsel']['name'])) {
                $yeni = resim_yukle($_FILES['gorsel'], 'kampanyalar');
                if ($yeni) $gorsel = $yeni;
            }

            if ($id) {
                db_run("UPDATE kampanyalar SET baslik=?, slug=?, kisa_aciklama=?, icerik=?, gorsel=?, nakit_fiyat=?, kart_fiyat=?, taksit_sayisi=?, baslangic=?, bitis=?, meta_baslik=?, meta_aciklama=?, aktif=? WHERE id=?",
                    [$baslik, $slug, $kisa, $icerik, $gorsel, $nakit, $kart, $taksit, $bas, $bit, $meta_b, $meta_a, $aktif, $id]);
                flash_set('ok', 'Kampanya güncellendi.');
            } else {
                db_run("INSERT INTO kampanyalar (baslik, slug, kisa_aciklama, icerik, gorsel, nakit_fiyat, kart_fiyat, taksit_sayisi, baslangic, bitis, meta_baslik, meta_aciklama, aktif) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$baslik, $slug, $kisa, $icerik, $gorsel, $nakit, $kart, $taksit, $bas, $bit, $meta_b, $meta_a, $aktif]);
                flash_set('ok', 'Kampanya eklendi.');
            }
            log_yaz('kampanya_kaydet', "ID: $id, baslik: $baslik", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/kampanyalar.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM kampanyalar WHERE id=?", [$id]);
        log_yaz('kampanya_sil', "ID: $id", (int)$_kul['id']);
        flash_set('ok', 'Kampanya silindi.');
        redirect(SITE_URL . '/admin/kampanyalar.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE kampanyalar SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/kampanyalar.php');
    }
}

$kampanyalar = db_all("SELECT * FROM kampanyalar ORDER BY id DESC");

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
        <p class="page-sub">Demirdöküm kombi paketi gibi özel fiyatlı kampanyaları buradan yönet.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Kampanya</a>
    <?php endif; ?>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Kampanyayı Düzenle' : 'Yeni Kampanya' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">
            <input type="hidden" name="gorsel_mevcut" value="<?= e($kayit['gorsel'] ?? '') ?>">

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

            <div class="form-row">
                <div class="field">
                    <label>Kısa Açıklama (kart önyüzü)</label>
                    <textarea class="textarea" name="kisa_aciklama" maxlength="400" rows="2"><?= e($kayit['kisa_aciklama'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="field">
                    <label>Peşin Fiyat (₺)</label>
                    <input class="input" name="nakit_fiyat" value="<?= $kayit['nakit_fiyat'] ?? '' ?>" placeholder="80000">
                </div>
                <div class="field">
                    <label>Kart Fiyatı (₺)</label>
                    <input class="input" name="kart_fiyat" value="<?= $kayit['kart_fiyat'] ?? '' ?>" placeholder="87000">
                </div>
                <div class="field">
                    <label>Taksit Sayısı</label>
                    <input type="number" class="input" name="taksit_sayisi" value="<?= (int)($kayit['taksit_sayisi'] ?? 0) ?>" placeholder="6">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Başlangıç Tarihi</label>
                    <input type="date" class="input" name="baslangic" value="<?= e($kayit['baslangic'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Bitiş Tarihi</label>
                    <input type="date" class="input" name="bitis" value="<?= e($kayit['bitis'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Detaylı İçerik (HTML)</label>
                    <textarea class="textarea" name="icerik" rows="12"><?= e($kayit['icerik'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Görsel</label>
                    <input type="file" name="gorsel" accept="image/*">
                    <?php if (!empty($kayit['gorsel'])): ?>
                        <div style="margin-top:10px"><img src="<?= UPLOAD_URL.'/'.e($kayit['gorsel']) ?>" style="max-width:240px;border-radius:8px;border:1px solid var(--c-line)"></div>
                    <?php endif; ?>
                </div>
            </div>

            <details>
                <summary style="cursor:pointer;font-weight:600;color:var(--c-muted);margin-bottom:14px">SEO Ayarları</summary>
                <div class="form-row">
                    <div class="field">
                        <label>Meta Başlık</label>
                        <input class="input" name="meta_baslik" value="<?= e($kayit['meta_baslik'] ?? '') ?>" maxlength="200">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Meta Açıklama</label>
                        <textarea class="textarea" name="meta_aciklama" maxlength="300" rows="2"><?= e($kayit['meta_aciklama'] ?? '') ?></textarea>
                    </div>
                </div>
            </details>

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= ($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Aktif</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> <?= $duzenle_id?'Güncelle':'Kaydet' ?></button>
                <a href="<?= SITE_URL ?>/admin/kampanyalar.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="padding:0">
        <table class="adm-table">
            <thead><tr><th width="60">Görsel</th><th>Başlık</th><th width="130">Peşin / Kart</th><th width="120">Tarih</th><th width="80">Durum</th><th width="160">İşlem</th></tr></thead>
            <tbody>
            <?php if (!$kampanyalar): ?>
                <tr><td colspan="6" class="empty">Henüz kampanya yok. <a href="?ekle=1">Ekle</a> veya <a href="<?= SITE_URL ?>/seed.php"><strong>/seed.php</strong></a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($kampanyalar as $k): ?>
                <tr>
                    <td><?php if ($k['gorsel']): ?><img src="<?= UPLOAD_URL.'/'.e($k['gorsel']) ?>" style="width:48px;height:36px;object-fit:cover;border-radius:4px"><?php else: ?>—<?php endif; ?></td>
                    <td>
                        <strong><?= e($k['baslik']) ?></strong>
                        <?php if ($k['kisa_aciklama']): ?>
                            <div style="font-size:.78rem;color:var(--c-muted);margin-top:2px"><?= e(mb_strimwidth($k['kisa_aciklama'], 0, 80, '…', 'UTF-8')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($k['nakit_fiyat']): ?>
                            <strong><?= tl((float)$k['nakit_fiyat']) ?></strong>
                            <?php if ($k['kart_fiyat']): ?><div style="font-size:.78rem;color:var(--c-muted)">/ <?= tl((float)$k['kart_fiyat']) ?> kart</div><?php endif; ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="font-size:.78rem">
                        <?php if ($k['baslangic']): ?><?= date('d.m.y', strtotime((string)$k['baslangic'])) ?><?php endif; ?>
                        <?php if ($k['bitis']): ?> → <?= date('d.m.y', strtotime((string)$k['bitis'])) ?><?php endif; ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="aktif_degistir">
                            <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                            <button class="badge badge-<?= $k['aktif']?'ok':'pas' ?>" type="submit"><?= $k['aktif']?'Aktif':'Pasif' ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="<?= SITE_URL ?>/kampanya/<?= e($k['slug']) ?>" target="_blank" class="btn btn-sm btn-out"><i class="fas fa-eye"></i></a>
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
