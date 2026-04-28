<?php
require_once __DIR__ . '/_baslat.php';
page_title('Blog Yönetimi');

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
        $ozet           = clean($_POST['ozet'] ?? '');
        $icerik         = trim($_POST['icerik'] ?? '');
        $yazar          = clean($_POST['yazar'] ?? '') ?: 'Azra Doğalgaz';
        $etiketler      = clean($_POST['etiketler'] ?? '');
        $yayin_tarihi   = $_POST['yayin_tarihi'] ?: null;
        $meta_baslik    = clean($_POST['meta_baslik'] ?? '');
        $meta_aciklama  = clean($_POST['meta_aciklama'] ?? '');
        $aktif          = !empty($_POST['aktif']) ? 1 : 0;

        if ($baslik === '') {
            flash_set('err', 'Başlık zorunlu.');
        } else {
            if (!$slug) $slug = slugify($baslik);
            $cak = db_get("SELECT id FROM blog_yazilari WHERE slug=? AND id<>?", [$slug, $id]);
            if ($cak) $slug = $slug . '-' . random_int(100, 999);

            // Yayın tarihi formatı
            if ($yayin_tarihi) {
                $yayin_tarihi = date('Y-m-d H:i:s', strtotime($yayin_tarihi));
            } elseif ($aktif) {
                $yayin_tarihi = date('Y-m-d H:i:s'); // aktifse şimdiki zamanı yayın tarihi yap
            }

            // Meta açıklama otomatik
            if (!$meta_aciklama && $ozet) $meta_aciklama = mb_substr($ozet, 0, 297, 'UTF-8');
            if (!$meta_aciklama && $icerik) $meta_aciklama = meta_aciklama($icerik, 297);
            if (!$meta_baslik) $meta_baslik = mb_substr($baslik, 0, 197, 'UTF-8');

            $gorsel = null;
            if (!empty($_FILES['gorsel']['name'])) {
                $gorsel = resim_yukle($_FILES['gorsel'], 'blog');
                if (!$gorsel) flash_set('err', 'Görsel yüklenemedi.');
            }
            $gorsel_url_input = clean($_POST['gorsel_url'] ?? '');
            if (!$gorsel && $gorsel_url_input) $gorsel = $gorsel_url_input;
            if (!$gorsel && $id) {
                $eski = db_get("SELECT gorsel FROM blog_yazilari WHERE id=?", [$id]);
                $gorsel = $eski['gorsel'] ?? null;
            }
            if (!empty($_POST['gorsel_sil']) && $id) $gorsel = null;

            if ($id) {
                db_run("UPDATE blog_yazilari SET baslik=?, slug=?, ozet=?, icerik=?, gorsel=?, yazar=?,
                        etiketler=?, yayin_tarihi=?, meta_baslik=?, meta_aciklama=?, aktif=?
                    WHERE id=?",
                    [$baslik, $slug, $ozet, $icerik, $gorsel, $yazar, $etiketler, $yayin_tarihi,
                     $meta_baslik, $meta_aciklama, $aktif, $id]);
                flash_set('ok', 'Yazı güncellendi.');
            } else {
                db_run("INSERT INTO blog_yazilari (baslik, slug, ozet, icerik, gorsel, yazar,
                        etiketler, yayin_tarihi, meta_baslik, meta_aciklama, aktif)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    [$baslik, $slug, $ozet, $icerik, $gorsel, $yazar, $etiketler, $yayin_tarihi,
                     $meta_baslik, $meta_aciklama, $aktif]);
                $id = (int)db()->lastInsertId();
                flash_set('ok', 'Yazı eklendi.');
            }
            log_yaz('blog_kaydet', "ID: $id, baslik: $baslik", (int)$_kul['id']);
        }
        redirect(SITE_URL . '/admin/blog.php');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM blog_yazilari WHERE id=?", [$id]);
        log_yaz('blog_sil', "ID: $id silindi", (int)$_kul['id']);
        flash_set('ok', 'Yazı silindi.');
        redirect(SITE_URL . '/admin/blog.php');
    } elseif ($islem === 'aktif_degistir' && $id) {
        db_run("UPDATE blog_yazilari SET aktif = 1 - aktif WHERE id=?", [$id]);
        // İlk kez yayınlanıyorsa yayın tarihi ata
        db_run("UPDATE blog_yazilari SET yayin_tarihi=NOW() WHERE id=? AND aktif=1 AND yayin_tarihi IS NULL", [$id]);
        redirect(SITE_URL . '/admin/blog.php');
    }
}

$arama = clean($_GET['q'] ?? '');
$durum = $_GET['durum'] ?? '';
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 20;
$ofset = ($sayfa - 1) * $limit;

$where = "1=1"; $params = [];
if ($arama) { $where .= " AND (baslik LIKE ? OR ozet LIKE ? OR etiketler LIKE ?)"; $w="%$arama%"; $params[]=$w; $params[]=$w; $params[]=$w; }
if ($durum === 'aktif') $where .= " AND aktif=1";
elseif ($durum === 'taslak') $where .= " AND aktif=0";

$toplam = (int)db_get("SELECT COUNT(*) c FROM blog_yazilari WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$yazilar = db_all("SELECT * FROM blog_yazilari WHERE $where
    ORDER BY COALESCE(yayin_tarihi, olusturma_tarihi) DESC
    LIMIT $limit OFFSET $ofset", $params);

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
        <h1 class="page-h1">Blog Yönetimi</h1>
        <p class="page-sub">Doğalgaz, kombi, klima konularında uzman yazılarını yönet. Google'da "kombi", "izmir doğalgaz" sorgularında bu yazılarla yükselirsin.</p>
    </div>
    <?php if (!$form_acik): ?>
        <a href="?ekle=1" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Yazı</a>
    <?php endif; ?>
</div>

<?php if ($form_acik): ?>
    <div class="card">
        <h3><?= $duzenle_id ? 'Yazıyı Düzenle' : 'Yeni Blog Yazısı' ?></h3>
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
                <label>Özet (max 500)</label>
                <textarea class="input" name="ozet" rows="2" maxlength="500"><?= e($kayit['ozet'] ?? '') ?></textarea>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label>İçerik (HTML) *</label>
                <textarea class="input" name="icerik" rows="14" required><?= e($kayit['icerik'] ?? '') ?></textarea>
                <small style="color:var(--c-muted)">HTML kullan: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt;. SEO için H2 başlıklarla bölümle.</small>
            </div>

            <div class="form-row cols-3">
                <div class="field">
                    <label>Yazar</label>
                    <input class="input" name="yazar" value="<?= e($kayit['yazar'] ?? 'Azra Doğalgaz') ?>" maxlength="120">
                </div>
                <div class="field">
                    <label>Etiketler <span class="opt">(virgülle ayır)</span></label>
                    <input class="input" name="etiketler" value="<?= e($kayit['etiketler'] ?? '') ?>" placeholder="kombi, doğalgaz, izmir">
                </div>
                <div class="field">
                    <label>Yayın Tarihi</label>
                    <input class="input" type="datetime-local" name="yayin_tarihi"
                        value="<?= e($kayit['yayin_tarihi'] ? date('Y-m-d\TH:i', strtotime($kayit['yayin_tarihi'])) : '') ?>">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Kapak Görseli (Dosya Yükle)</label>
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
                <summary style="cursor:pointer;font-weight:700">SEO Ayarları (boş = otomatik üretilir)</summary>
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
                    <input type="checkbox" name="aktif" <?= !isset($kayit) || (int)($kayit['aktif'] ?? 0) ? 'checked' : '' ?>>
                    <span>Yayında (kaldırırsan taslak olur)</span>
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="filters">
        <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Başlık, etiket ara…" class="input">
        <select name="durum">
            <option value="">Tüm yazılar</option>
            <option value="aktif"  <?= $durum==='aktif' ?'selected':'' ?>>Yayında</option>
            <option value="taslak" <?= $durum==='taslak'?'selected':'' ?>>Taslak</option>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i> Filtrele</button>
        <?php if ($arama || $durum): ?><a href="<?= SITE_URL ?>/admin/blog.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> yazı</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:60px">#</th>
    <th style="width:80px">Görsel</th>
    <th>Başlık</th>
    <th style="width:130px">Yayın Tarihi</th>
    <th style="width:90px">Görüntüleme</th>
    <th style="width:100px">Durum</th>
    <th style="width:160px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$yazilar): ?>
    <tr><td colspan="7" class="empty">Yazı bulunamadı.</td></tr>
<?php else: foreach ($yazilar as $y): ?>
<tr>
    <td><?= (int)$y['id'] ?></td>
    <td>
        <?php if ($y['gorsel']): ?>
            <img src="<?= e(gorsel_url($y['gorsel'])) ?>" style="width:60px;height:42px;object-fit:cover;border-radius:6px">
        <?php else: ?><span style="color:var(--c-muted);font-size:.78rem">—</span><?php endif; ?>
    </td>
    <td>
        <strong><?= e($y['baslik']) ?></strong>
        <br><small style="color:var(--c-muted)"><code><?= e($y['slug']) ?></code><?php if ($y['etiketler']): ?> · <?= e($y['etiketler']) ?><?php endif; ?></small>
    </td>
    <td><?= $y['yayin_tarihi'] ? tarih_tr($y['yayin_tarihi']) : '<small style="color:var(--c-muted)">—</small>' ?></td>
    <td class="num"><?= (int)$y['goruntulenme'] ?></td>
    <td>
        <?php if ((int)$y['aktif']): ?>
            <span class="badge badge-ok">Yayında</span>
        <?php else: ?>
            <span class="badge badge-no">Taslak</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions">
            <a href="<?= SITE_URL ?>/blog/<?= e($y['slug']) ?>" target="_blank" class="btn btn-out btn-sm" title="Sitede Gör"><i class="fas fa-eye"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="aktif_degistir">
                <input type="hidden" name="id" value="<?= (int)$y['id'] ?>">
                <button class="btn btn-out btn-sm" title="Yayında/Taslak"><i class="fas fa-toggle-on"></i></button>
            </form>
            <a href="?duzenle=<?= (int)$y['id'] ?>" class="btn btn-blue btn-sm" title="Düzenle"><i class="fas fa-pen"></i></a>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="sil">
                <input type="hidden" name="id" value="<?= (int)$y['id'] ?>">
                <button class="btn btn-danger btn-sm" data-onay="Yazı silinsin mi? Geri alınamaz." title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/blog.php?' . http_build_query(array_filter(['q'=>$arama, 'durum'=>$durum]));
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
