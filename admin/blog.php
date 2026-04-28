<?php
require_once __DIR__ . '/_baslat.php';
page_title('Blog Yazıları');

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
        $ozet   = clean($_POST['ozet'] ?? '');
        $icerik = (string)($_POST['icerik'] ?? '');
        $yazar  = clean($_POST['yazar'] ?? '') ?: 'Azra Doğalgaz';
        $etiketler = clean($_POST['etiketler'] ?? '');
        $meta_b = clean($_POST['meta_baslik'] ?? '');
        $meta_a = clean($_POST['meta_aciklama'] ?? '');
        $yayin  = clean($_POST['yayin_tarihi'] ?? '') ?: null;
        $aktif  = !empty($_POST['aktif']) ? 1 : 0;
        $gorsel_mevcut = clean($_POST['gorsel_mevcut'] ?? '');

        if ($baslik === '') {
            flash_set('err', 'Başlık zorunlu.');
        } else {
            if (!$slug) $slug = slugify($baslik);
            $cak = db_get("SELECT id FROM blog_yazilari WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100,999);

            $gorsel = $gorsel_mevcut;
            if (!empty($_FILES['gorsel']['name'])) {
                $yeni = resim_yukle($_FILES['gorsel'], 'blog');
                if ($yeni) $gorsel = $yeni;
            }

            if ($id) {
                db_run("UPDATE blog_yazilari SET baslik=?, slug=?, ozet=?, icerik=?, gorsel=?, yazar=?, meta_baslik=?, meta_aciklama=?, etiketler=?, yayin_tarihi=?, aktif=? WHERE id=?",
                    [$baslik, $slug, $ozet, $icerik, $gorsel, $yazar, $meta_b, $meta_a, $etiketler, $yayin, $aktif, $id]);
                flash_set('ok', 'Yazı güncellendi.');
            } else {
                db_run("INSERT INTO blog_yazilari (baslik, slug, ozet, icerik, gorsel, yazar, meta_baslik, meta_aciklama, etiketler, yayin_tarihi, aktif) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    [$baslik, $slug, $ozet, $icerik, $gorsel, $yazar, $meta_b, $meta_a, $etiketler, $yayin, $aktif]);
                flash_set('ok', 'Yazı eklendi.');
            }
            log_yaz('blog_kaydet', "ID: $id, baslik: $baslik", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/blog.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM blog_yazilari WHERE id=?", [$id]);
        log_yaz('blog_sil', "ID: $id", (int)$_kul['id']);
        flash_set('ok', 'Yazı silindi.');
        redirect(SITE_URL . '/admin/blog.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE blog_yazilari SET aktif = 1 - aktif WHERE id=?", [$id]);
        redirect(SITE_URL . '/admin/blog.php');
    }
}

$f_arama = clean($_GET['q'] ?? '');
$where = "1=1"; $params = [];
if ($f_arama) { $where .= " AND (baslik LIKE ? OR ozet LIKE ?)"; $params[]="%$f_arama%"; $params[]="%$f_arama%"; }

$yazilar = db_all("SELECT * FROM blog_yazilari WHERE $where
    ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC LIMIT 100", $params);

$duzenle_id = (int)($_GET['duzenle'] ?? 0);
$ekle = isset($_GET['ekle']);
$kayit = null;
if ($duzenle_id) {
    $kayit = db_get("SELECT * FROM blog_yazilari WHERE id=?", [$duzenle_id]);
    if (!$kayit) { flash_set('err','Kayıt bulunamadı.'); redirect(SITE_URL.'/admin/blog.php'); }
}
$form_acik = $ekle || $duzenle_id;

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Blog Yazıları</h1>
        <p class="page-sub">Doğalgaz, kombi, klima rehberlerini buradan ekle/düzenle.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Yazı</a>
    <?php endif; ?>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Yazıyı Düzenle' : 'Yeni Blog Yazısı' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="kaydet">
            <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">
            <input type="hidden" name="gorsel_mevcut" value="<?= e($kayit['gorsel'] ?? '') ?>">

            <div class="form-row">
                <div class="field">
                    <label>Başlık *</label>
                    <input class="input" name="baslik" value="<?= e($kayit['baslik'] ?? '') ?>" required maxlength="220">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Slug</label>
                    <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="260">
                </div>
                <div class="field">
                    <label>Yazar</label>
                    <input class="input" name="yazar" value="<?= e($kayit['yazar'] ?? 'Azra Doğalgaz') ?>" maxlength="120">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Özet (max 500 — listede görünür)</label>
                    <textarea class="textarea" name="ozet" maxlength="500" rows="2"><?= e($kayit['ozet'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>İçerik (HTML)</label>
                    <textarea class="textarea" name="icerik" rows="20"><?= e($kayit['icerik'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Etiketler <span class="opt">(virgülle ayırın)</span></label>
                    <input class="input" name="etiketler" value="<?= e($kayit['etiketler'] ?? '') ?>" maxlength="400" placeholder="kombi, doğalgaz, izmirgaz">
                </div>
                <div class="field">
                    <label>Yayın Tarihi <span class="opt">(boşsa şimdi)</span></label>
                    <input type="datetime-local" class="input" name="yayin_tarihi" value="<?= e($kayit['yayin_tarihi'] ? date('Y-m-d\TH:i', strtotime((string)$kayit['yayin_tarihi'])) : date('Y-m-d\TH:i')) ?>">
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
                    <div class="field"><label>Meta Başlık</label><input class="input" name="meta_baslik" value="<?= e($kayit['meta_baslik'] ?? '') ?>" maxlength="200"></div>
                </div>
                <div class="form-row">
                    <div class="field"><label>Meta Açıklama</label><textarea class="textarea" name="meta_aciklama" maxlength="300" rows="2"><?= e($kayit['meta_aciklama'] ?? '') ?></textarea></div>
                </div>
            </details>

            <div class="form-row">
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= ($kayit['aktif'] ?? 1) ? 'checked' : '' ?>>
                    <span>Yayında (sitede görünsün)</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> <?= $duzenle_id?'Güncelle':'Kaydet' ?></button>
                <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="padding:14px;margin-bottom:16px">
        <form method="get" style="display:flex;gap:10px">
            <input class="input" name="q" value="<?= e($f_arama) ?>" placeholder="Yazı ara..." style="flex:1">
            <button class="btn btn-pri"><i class="fas fa-search"></i></button>
            <?php if ($f_arama): ?><a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-out">Temizle</a><?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding:0">
        <table class="adm-table">
            <thead><tr><th width="60">Görsel</th><th>Başlık</th><th>Yazar</th><th width="120">Yayın</th><th width="80">Görüntül.</th><th width="80">Durum</th><th width="160">İşlem</th></tr></thead>
            <tbody>
            <?php if (!$yazilar): ?>
                <tr><td colspan="7" class="empty">Yazı yok. <a href="?ekle=1">Yeni yazı ekle</a> veya <a href="<?= SITE_URL ?>/seed.php"><strong>/seed.php</strong></a> ile yükle.</td></tr>
            <?php endif; ?>
            <?php foreach ($yazilar as $y): ?>
                <tr>
                    <td><?php if ($y['gorsel']): ?><img src="<?= UPLOAD_URL.'/'.e($y['gorsel']) ?>" style="width:48px;height:36px;object-fit:cover;border-radius:4px"><?php else: ?>—<?php endif; ?></td>
                    <td>
                        <strong><?= e($y['baslik']) ?></strong>
                        <?php if ($y['ozet']): ?>
                            <div style="font-size:.78rem;color:var(--c-muted);margin-top:2px"><?= e(mb_strimwidth($y['ozet'], 0, 100, '…', 'UTF-8')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($y['yazar']) ?></td>
                    <td style="font-size:.78rem"><?= $y['yayin_tarihi'] ? date('d.m.y', strtotime((string)$y['yayin_tarihi'])) : '—' ?></td>
                    <td><?= (int)$y['goruntulenme'] ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="aktif_degistir">
                            <input type="hidden" name="id" value="<?= (int)$y['id'] ?>">
                            <button class="badge badge-<?= $y['aktif']?'ok':'pas' ?>" type="submit"><?= $y['aktif']?'Yayın':'Taslak' ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="<?= SITE_URL ?>/blog/<?= e($y['slug']) ?>" target="_blank" class="btn btn-sm btn-out"><i class="fas fa-eye"></i></a>
                        <a href="?duzenle=<?= (int)$y['id'] ?>" class="btn btn-sm btn-out"><i class="fas fa-pen"></i></a>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="sil">
                            <input type="hidden" name="id" value="<?= (int)$y['id'] ?>">
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
