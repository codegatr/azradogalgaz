<?php
require_once __DIR__ . '/_baslat.php';
page_title('Projeler / Referanslar');

// =============================================================
// DÜZENLEME / EKLEME / SİLME
// =============================================================
$mod    = $_GET['mod'] ?? 'liste';   // liste | duzenle | yeni
$kayit_id = (int)($_GET['id'] ?? 0);

// Projeler tablosu yoksa oluştur (otomatik migration)
$projeler_var = false;
try {
    db()->query("SELECT 1 FROM projeler LIMIT 1");
    $projeler_var = true;
} catch (Throwable $e) {
    try {
        db()->query("CREATE TABLE IF NOT EXISTS `projeler` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(220) NOT NULL UNIQUE,
            `baslik` VARCHAR(220) NOT NULL,
            `kategori` VARCHAR(120) NULL,
            `ozet` VARCHAR(500) NULL,
            `icerik` MEDIUMTEXT NULL,
            `gorsel` VARCHAR(500) NULL,
            `galeri` MEDIUMTEXT NULL,
            `lokasyon` VARCHAR(160) NULL,
            `tarih` DATE NULL,
            `sira` INT DEFAULT 0,
            `aktif` TINYINT(1) DEFAULT 1,
            `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_aktif` (`aktif`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $projeler_var = true;
        log_yaz('migrate', 'projeler tablosu admin panel uzerinden olusturuldu', (int)$_kul['id']);
        flash_set('ok', 'Projeler tablosu otomatik oluşturuldu, artık proje ekleyebilirsiniz.');
    } catch (Throwable $e2) {
        flash_set('err', 'Tablo oluşturulamadı: ' . $e2->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu, lütfen tekrar deneyin.');
        redirect(SITE_URL . '/admin/projeler.php');
    }

    $islem = $_POST['islem'] ?? '';

    // SİL
    if ($islem === 'sil') {
        $sid = (int)($_POST['id'] ?? 0);
        if ($sid > 0) {
            db_run("DELETE FROM projeler WHERE id=?", [$sid]);
            log_yaz('proje_sil', "Proje silindi: ID $sid", (int)$_kul['id']);
            flash_set('ok', 'Proje silindi.');
        }
        redirect(SITE_URL . '/admin/projeler.php');
    }

    // EKLE veya GÜNCELLE
    if ($islem === 'kaydet') {
        $id      = (int)($_POST['id'] ?? 0);
        $baslik  = trim((string)($_POST['baslik'] ?? ''));
        $slug    = trim((string)($_POST['slug'] ?? ''));
        if (!$slug) $slug = slugify($baslik);
        $kat     = trim((string)($_POST['kategori'] ?? ''));
        $ozet    = trim((string)($_POST['ozet'] ?? ''));
        $icerik  = (string)($_POST['icerik'] ?? '');
        $gorsel_url_in = trim((string)($_POST['gorsel_url'] ?? ''));
        $lokasyon= trim((string)($_POST['lokasyon'] ?? ''));
        $tarih   = trim((string)($_POST['tarih'] ?? '')) ?: null;
        $sira    = (int)($_POST['sira'] ?? 0);
        $aktif   = !empty($_POST['aktif']) ? 1 : 0;

        if (!$baslik) {
            flash_set('err', 'Başlık zorunlu.');
            redirect($_SERVER['REQUEST_URI']);
        }

        // Görsel: dosya yüklendiyse onu, yoksa URL alanını kullan
        $mevcut_gorsel = $id ? (db_get("SELECT gorsel FROM projeler WHERE id=?", [$id])['gorsel'] ?? '') : '';
        $gorsel = $mevcut_gorsel;

        if (!empty($_FILES['gorsel']['name'])) {
            $yeni = resim_yukle($_FILES['gorsel'], 'projeler');
            if ($yeni) $gorsel = $yeni;
        } elseif ($gorsel_url_in) {
            $gorsel = $gorsel_url_in;  // URL'yi olduğu gibi kabul et (gorsel_url() helper destekler)
        }

        try {
            if ($id > 0) {
                db_run("UPDATE projeler SET slug=?, baslik=?, kategori=?, ozet=?, icerik=?, gorsel=?, lokasyon=?, tarih=?, sira=?, aktif=? WHERE id=?",
                    [$slug, $baslik, $kat, $ozet, $icerik, $gorsel, $lokasyon, $tarih, $sira, $aktif, $id]);
                log_yaz('proje_guncelle', "Proje güncellendi: $baslik", (int)$_kul['id']);
                flash_set('ok', 'Proje güncellendi.');
            } else {
                db_run("INSERT INTO projeler (slug, baslik, kategori, ozet, icerik, gorsel, lokasyon, tarih, sira, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$slug, $baslik, $kat, $ozet, $icerik, $gorsel, $lokasyon, $tarih, $sira, $aktif]);
                $id = (int)db()->lastInsertId();
                log_yaz('proje_ekle', "Yeni proje: $baslik", (int)$_kul['id']);
                flash_set('ok', 'Proje eklendi.');
            }
            redirect(SITE_URL . '/admin/projeler.php');
        } catch (PDOException $e) {
            $msg = (str_contains($e->getMessage(), 'Duplicate') ? 'Bu slug zaten kullanılıyor, farklı bir slug girin.' : 'Hata: ' . $e->getMessage());
            flash_set('err', $msg);
            redirect($_SERVER['REQUEST_URI']);
        }
    }
}

// Düzenleme verisi
$kayit = null;
if ($mod === 'duzenle' && $kayit_id) {
    $kayit = db_get("SELECT * FROM projeler WHERE id=?", [$kayit_id]);
    if (!$kayit) { flash_set('err', 'Proje bulunamadı.'); redirect(SITE_URL . '/admin/projeler.php'); }
}

// Liste için filtreler
$f_arama = trim((string)($_GET['q'] ?? ''));
$where = "1=1"; $params = [];
if ($f_arama) {
    $where .= " AND (baslik LIKE ? OR slug LIKE ? OR kategori LIKE ?)";
    $params[] = "%$f_arama%"; $params[] = "%$f_arama%"; $params[] = "%$f_arama%";
}

$projeler = db_all("SELECT id, baslik, slug, kategori, lokasyon, tarih, sira, aktif, gorsel
    FROM projeler WHERE $where ORDER BY sira ASC, id DESC", $params);

require_once __DIR__ . '/_header.php';
?>

<?php if ($mod === 'liste'): ?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Projeler / Referanslar</h1>
        <p class="page-sub">Tamamlanan iş referanslarınızı buradan yönetin. <strong><?= count($projeler) ?></strong> kayıt.</p>
    </div>
    <a href="?mod=yeni" class="btn btn-pri"><i class="fas fa-plus"></i> Yeni Proje</a>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<form method="get" class="card" style="margin-bottom:18px;display:flex;gap:8px;align-items:center">
    <input class="input" name="q" value="<?= e($f_arama) ?>" placeholder="Proje ara (başlık, slug, kategori)..." style="flex:1">
    <button class="btn btn-out btn-sm"><i class="fas fa-magnifying-glass"></i> Ara</button>
    <?php if ($f_arama): ?><a href="projeler.php" class="btn btn-out btn-sm">Temizle</a><?php endif; ?>
</form>

<?php if ($projeler): ?>
<div class="tbl">
<table>
    <thead>
        <tr>
            <th style="width:60px">Görsel</th>
            <th>Başlık</th>
            <th>Kategori</th>
            <th>Lokasyon</th>
            <th>Tarih</th>
            <th style="width:80px">Sıra</th>
            <th style="width:70px">Aktif</th>
            <th style="width:140px;text-align:right">İşlem</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projeler as $p): ?>
        <tr>
            <td>
                <?php if ($p['gorsel']): ?>
                <img src="<?= e(gorsel_url($p['gorsel'])) ?>" alt="" style="width:50px;height:50px;border-radius:6px;object-fit:cover">
                <?php else: ?>
                <div style="width:50px;height:50px;border-radius:6px;background:var(--c-bg-alt);display:flex;align-items:center;justify-content:center;color:var(--c-muted)"><i class="fas fa-image"></i></div>
                <?php endif; ?>
            </td>
            <td>
                <strong><?= e($p['baslik']) ?></strong>
                <div style="font-size:.78rem;color:var(--c-muted)">/proje/<?= e($p['slug']) ?></div>
            </td>
            <td><?= e($p['kategori'] ?: '—') ?></td>
            <td><?= e($p['lokasyon'] ?: '—') ?></td>
            <td><?= $p['tarih'] ? tarih_tr($p['tarih']) : '—' ?></td>
            <td><?= (int)$p['sira'] ?></td>
            <td><?php if ($p['aktif']): ?><span class="badge badge-ok">Aktif</span><?php else: ?><span class="badge badge-no">Pasif</span><?php endif; ?></td>
            <td style="text-align:right">
                <a href="?mod=duzenle&id=<?= (int)$p['id'] ?>" class="btn btn-out btn-sm"><i class="fas fa-pen"></i></a>
                <form method="post" style="display:inline" onsubmit="return confirm('Proje silinecek, emin misiniz?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="islem" value="sil">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php else: ?>
<div class="tbl"><div class="empty"><i class="fas fa-folder-open" style="font-size:2rem;display:block;margin-bottom:10px"></i>Henüz proje eklenmedi. <br><a href="?mod=yeni" class="btn btn-pri btn-sm" style="margin-top:10px"><i class="fas fa-plus"></i> İlk Projeyi Ekle</a></div></div>
<?php endif; ?>

<?php else: // FORM ?>

<div class="page-head">
    <div>
        <h1 class="page-h1"><?= $kayit ? 'Proje Düzenle' : 'Yeni Proje' ?></h1>
        <p class="page-sub"><?= $kayit ? e($kayit['baslik']) : 'Yeni iş referansı / proje ekle' ?></p>
    </div>
    <a href="projeler.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Listeye Dön</a>
</div>

<?php foreach (flash_pop() as $f): ?>
    <div class="alert alert-<?= $f['tip']==='ok'?'ok':'err' ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="islem" value="kaydet">
    <input type="hidden" name="id" value="<?= (int)($kayit['id'] ?? 0) ?>">

    <div class="form-row cols-2">
        <div class="card">
            <h3 style="margin-bottom:14px">Genel Bilgiler</h3>

            <div class="field">
                <label>Başlık <span class="req">*</span></label>
                <input class="input" name="baslik" value="<?= e($kayit['baslik'] ?? '') ?>" required maxlength="220" placeholder="Örn: Bornova Konut Sitesi — 48 Daire Doğalgaz Tesisat">
            </div>
            <div class="field">
                <label>Slug (URL)</label>
                <input class="input" name="slug" value="<?= e($kayit['slug'] ?? '') ?>" maxlength="220" placeholder="bornova-konut-sitesi (boş bırakırsanız başlıktan üretilir)">
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>Kategori</label>
                    <input class="input" name="kategori" value="<?= e($kayit['kategori'] ?? '') ?>" maxlength="120" placeholder="Doğalgaz Tesisatı / Kombi Montajı / Klima vs.">
                </div>
                <div class="field">
                    <label>Lokasyon</label>
                    <input class="input" name="lokasyon" value="<?= e($kayit['lokasyon'] ?? '') ?>" maxlength="160" placeholder="Bornova / İzmir">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>Proje Tarihi</label>
                    <input type="date" class="input" name="tarih" value="<?= e($kayit['tarih'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Sıralama</label>
                    <input type="number" class="input" name="sira" value="<?= (int)($kayit['sira'] ?? 0) ?>" placeholder="0">
                </div>
            </div>

            <div class="field">
                <label>Özet</label>
                <textarea class="textarea" name="ozet" rows="3" maxlength="500" placeholder="Projeyi özetleyen kısa metin..."><?= e($kayit['ozet'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label>İçerik (HTML)</label>
                <textarea class="textarea" name="icerik" rows="12" placeholder="<h3>Kapsam</h3><p>...</p><ul><li>...</li></ul>"><?= e($kayit['icerik'] ?? '') ?></textarea>
                <p class="help">HTML kullanılabilir. Başlıklar (h2, h3), liste (ul, li), paragraf (p), strong, em.</p>
            </div>
        </div>

        <div>
            <div class="card">
                <h3 style="margin-bottom:14px">Görsel</h3>

                <?php if (!empty($kayit['gorsel'])): ?>
                <div style="margin-bottom:14px">
                    <img src="<?= e(gorsel_url($kayit['gorsel'])) ?>" alt="" style="width:100%;border-radius:8px;border:1px solid var(--c-line)">
                    <p class="help" style="margin-top:6px">Mevcut görsel</p>
                </div>
                <?php endif; ?>

                <div class="field">
                    <label>Görsel Dosyası Yükle</label>
                    <input type="file" name="gorsel" accept="image/*" class="input">
                    <p class="help">JPG, PNG, WEBP (max 8 MB). Yüklenen dosya URL'inin yerine geçer.</p>
                </div>

                <div class="field">
                    <label>VEYA — Görsel URL</label>
                    <input class="input" type="url" name="gorsel_url" value="<?= e(preg_match('#^https?://#i', $kayit['gorsel'] ?? '') ? $kayit['gorsel'] : '') ?>" placeholder="https://images.unsplash.com/photo-...">
                    <p class="help">Uzak URL kullanmak isterseniz buraya yapıştırın (Unsplash, Pexels vb).</p>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom:14px">Yayın</h3>
                <label class="check">
                    <input type="checkbox" name="aktif" value="1" <?= !isset($kayit['aktif']) || $kayit['aktif'] ? 'checked' : '' ?>>
                    <span>Aktif (sitede görünsün)</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> Kaydet</button>
        <a href="projeler.php" class="btn btn-out">İptal</a>
    </div>
</form>

<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
