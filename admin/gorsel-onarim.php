<?php
require_once __DIR__ . '/_baslat.php';
page_title('Görsel Onarım');

/* ============================================================
   GÖRSEL ONARIM ARACI
   Bozuk source.unsplash.com URL'lerini placehold.co ile değiştirir
   ============================================================ */

// Marka/turunun rengine göre placeholder rengi (orange tema)
$RENK_TEMA = 'F97316';   // turuncu
$RENK_YAZI = 'FFFFFF';

function placeholder_olustur(string $metin, int $w = 800, int $h = 600, string $bg = 'F97316', string $fg = 'FFFFFF'): string {
    // Türkçe karakterleri placeholder servisine uyumlu hale getir
    $tr = ['ç','Ç','ğ','Ğ','ı','İ','ö','Ö','ş','Ş','ü','Ü'];
    $en = ['c','C','g','G','i','I','o','O','s','S','u','U'];
    $metin = str_replace($tr, $en, $metin);
    // Çok uzunsa kısalt
    if (mb_strlen($metin, 'UTF-8') > 40) $metin = mb_substr($metin, 0, 38, 'UTF-8') . '..';
    $metin = preg_replace('/\s+/', ' ', trim($metin)) ?? '';
    return "https://placehold.co/{$w}x{$h}/{$bg}/{$fg}?text=" . urlencode($metin);
}

function bozuk_mu(?string $url): bool {
    if (!$url) return false;
    return str_contains($url, 'source.unsplash.com');
}

/* ========== AKSİYONLAR ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'tek_onar') {
        $tablo = $_POST['tablo'] ?? '';
        $id    = (int)($_POST['id'] ?? 0);
        $yeni  = trim($_POST['yeni'] ?? '');
        if (in_array($tablo, ['urunler','hizmetler','blog_yazilari','projeler','kampanyalar'], true) && $id && $yeni) {
            db_run("UPDATE `$tablo` SET gorsel=? WHERE id=?", [$yeni, $id]);
            log_yaz('gorsel_onar', "Tek onar: $tablo #$id", (int)$_kul['id']);
            flash_set('ok', 'Görsel güncellendi.');
        }
        redirect(SITE_URL . '/admin/gorsel-onarim.php');
    }

    if ($islem === 'toplu_onar') {
        $sayac = 0;
        // urunler
        $rows = db_all("SELECT id, ad, slug FROM urunler WHERE gorsel LIKE '%source.unsplash.com%'");
        foreach ($rows as $r) {
            $url = placeholder_olustur($r['ad']);
            db_run("UPDATE urunler SET gorsel=? WHERE id=?", [$url, $r['id']]);
            $sayac++;
        }
        // hizmetler
        $rows = db_all("SELECT id, baslik FROM hizmetler WHERE gorsel LIKE '%source.unsplash.com%'");
        foreach ($rows as $r) {
            $url = placeholder_olustur($r['baslik']);
            db_run("UPDATE hizmetler SET gorsel=? WHERE id=?", [$url, $r['id']]);
            $sayac++;
        }
        // blog_yazilari
        $rows = db_all("SELECT id, baslik FROM blog_yazilari WHERE gorsel LIKE '%source.unsplash.com%'");
        foreach ($rows as $r) {
            $url = placeholder_olustur($r['baslik']);
            db_run("UPDATE blog_yazilari SET gorsel=? WHERE id=?", [$url, $r['id']]);
            $sayac++;
        }
        // projeler
        try {
            $rows = db_all("SELECT id, baslik FROM projeler WHERE gorsel LIKE '%source.unsplash.com%'");
            foreach ($rows as $r) {
                $url = placeholder_olustur($r['baslik']);
                db_run("UPDATE projeler SET gorsel=? WHERE id=?", [$url, $r['id']]);
                $sayac++;
            }
        } catch (Throwable $e) {}
        // kampanyalar
        $rows = db_all("SELECT id, baslik FROM kampanyalar WHERE gorsel LIKE '%source.unsplash.com%'");
        foreach ($rows as $r) {
            $url = placeholder_olustur($r['baslik']);
            db_run("UPDATE kampanyalar SET gorsel=? WHERE id=?", [$url, $r['id']]);
            $sayac++;
        }

        log_yaz('gorsel_toplu_onar', "Toplu onar: $sayac kayıt", (int)$_kul['id']);
        flash_set('ok', "$sayac bozuk görsel onarıldı (placehold.co placeholder ile değiştirildi).");
        redirect(SITE_URL . '/admin/gorsel-onarim.php');
    }

    if ($islem === 'galeri_temizle') {
        // urunler.galeri JSON içeriğindeki bozuk URL'leri de temizle
        $rows = db_all("SELECT id, ad, galeri FROM urunler WHERE galeri LIKE '%source.unsplash.com%'");
        $sayac = 0;
        foreach ($rows as $r) {
            $g = json_decode((string)$r['galeri'], true);
            if (!is_array($g)) continue;
            $yeni_g = [];
            foreach ($g as $u) {
                if (str_contains((string)$u, 'source.unsplash.com')) continue;
                $yeni_g[] = $u;
            }
            db_run("UPDATE urunler SET galeri=? WHERE id=?", [json_encode($yeni_g, JSON_UNESCAPED_UNICODE), $r['id']]);
            $sayac++;
        }
        flash_set('ok', "$sayac üründen bozuk galeri görselleri temizlendi.");
        redirect(SITE_URL . '/admin/gorsel-onarim.php');
    }
    redirect($_SERVER['REQUEST_URI']);
}

/* ========== TARAMA ========== */
$bozuk_urunler   = db_all("SELECT id, ad, gorsel FROM urunler WHERE gorsel LIKE '%source.unsplash.com%' ORDER BY id");
$bozuk_hizmetler = db_all("SELECT id, baslik AS ad, gorsel FROM hizmetler WHERE gorsel LIKE '%source.unsplash.com%' ORDER BY id");
$bozuk_blog      = db_all("SELECT id, baslik AS ad, gorsel FROM blog_yazilari WHERE gorsel LIKE '%source.unsplash.com%' ORDER BY id");
$bozuk_kampanya  = db_all("SELECT id, baslik AS ad, gorsel FROM kampanyalar WHERE gorsel LIKE '%source.unsplash.com%' ORDER BY id");
$bozuk_proje     = [];
try { $bozuk_proje = db_all("SELECT id, baslik AS ad, gorsel FROM projeler WHERE gorsel LIKE '%source.unsplash.com%' ORDER BY id"); } catch (Throwable $e) {}
$bozuk_galeri    = (int)(db_get("SELECT COUNT(*) c FROM urunler WHERE galeri LIKE '%source.unsplash.com%'")['c'] ?? 0);

$toplam_bozuk = count($bozuk_urunler) + count($bozuk_hizmetler) + count($bozuk_blog) + count($bozuk_kampanya) + count($bozuk_proje);

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Görsel Onarım</h1>
        <p class="page-sub">Bozuk <code>source.unsplash.com</code> URL'lerini onar (Unsplash bu servisi 2024'te kapattı, tüm bu görseller 404).</p>
    </div>
    <a href="<?= SITE_URL ?>/admin/panel.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Panele Dön</a>
</div>

<?php if ($toplam_bozuk === 0 && $bozuk_galeri === 0): ?>

<div class="alert alert-ok">
    <i class="fas fa-check-circle"></i> <strong>Hiç bozuk görsel yok.</strong> Tüm görsel URL'leri sağlam.
</div>

<?php else: ?>

<div class="alert alert-warn">
    <i class="fas fa-triangle-exclamation"></i>
    Toplam <strong><?= $toplam_bozuk ?> bozuk görsel</strong> bulundu<?= $bozuk_galeri ? " (+ $bozuk_galeri üründe bozuk galeri kayıtları)" : '' ?>.
    Bunları toplu olarak onarmak için aşağıdaki butona basın — placeholder.co tabanlı, marka rengiyle, ürün adının yazılı olduğu görseller atanacak.
    Sonra istediğin ürüne girip kendi gerçek görselini yükleyebilirsin.
</div>

<div class="card">
    <h3>Toplu Onar</h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="toplu_onar">
        <p style="color:var(--c-muted);margin-bottom:14px">Bu işlem tüm <code>source.unsplash.com</code> URL'lerini turuncu renkli, ürün/hizmet/yazı adının yazılı olduğu placehold.co görseliyle değiştirir. Geri alınamaz.</p>
        <div class="form-actions">
            <button class="btn btn-pri" data-onay="Tüm bozuk görseller (toplam <?= $toplam_bozuk ?> adet) onarılsın mı?"><i class="fas fa-wrench"></i> Hepsini Onar (<?= $toplam_bozuk ?>)</button>
            <?php if ($bozuk_galeri > 0): ?>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="galeri_temizle">
                <button class="btn btn-out" data-onay="Galeri içindeki bozuk URL'ler kaldırılsın mı?"><i class="fas fa-broom"></i> Galerileri Temizle</button>
            </form>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
$gruplar = [
    ['Ürünler',     $bozuk_urunler,   'urunler',       'urunler.php'],
    ['Hizmetler',   $bozuk_hizmetler, 'hizmetler',     'hizmetler.php'],
    ['Blog Yazıları', $bozuk_blog,    'blog_yazilari', 'blog.php'],
    ['Kampanyalar', $bozuk_kampanya,  'kampanyalar',   'kampanyalar.php'],
    ['Projeler',    $bozuk_proje,     'projeler',      'projeler.php'],
];
foreach ($gruplar as [$baslik, $rows, $tablo, $modul]):
    if (!$rows) continue;
?>
<div class="card">
    <h3><?= e($baslik) ?> (<?= count($rows) ?>)</h3>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th style="width:40px">#</th><th>Ad</th><th style="width:280px">Mevcut URL</th><th style="width:180px">Yeni Tek URL</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r):
            $tahmini_yeni = "https://placehold.co/800x600/F97316/FFFFFF?text=" . urlencode(preg_replace('/\s+/', '+', $r['ad']));
        ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><strong><?= e($r['ad']) ?></strong></td>
                <td><small style="color:var(--c-muted);word-break:break-all"><?= e(mb_substr((string)$r['gorsel'], 0, 80)) ?>…</small></td>
                <td>
                    <form method="post" style="display:flex;gap:4px">
                        <?= csrf_field() ?>
                        <input type="hidden" name="islem" value="tek_onar">
                        <input type="hidden" name="tablo" value="<?= e($tablo) ?>">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <input class="input" type="url" name="yeni" placeholder="Manuel URL ya da boş = otomatik" style="font-size:.78rem">
                        <button class="btn btn-out btn-sm" title="Bu satırı manuel URL ile değiştir"><i class="fas fa-pen"></i></button>
                    </form>
                </td>
                <td><a href="<?= SITE_URL ?>/admin/<?= e($modul) ?>?duzenle=<?= $r['id'] ?>" class="btn btn-blue btn-sm" title="Modülde aç"><i class="fas fa-external-link-alt"></i></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<div class="card">
    <h3><i class="fas fa-info-circle" style="color:var(--c-blue)"></i> Bilgi</h3>
    <p style="color:var(--c-muted);font-size:.92rem;line-height:1.7">
        <strong>Neden bozuk?</strong> Eski seed verisinde Unsplash'in <code>source.unsplash.com/featured/...</code> rastgele resim API'si kullanılmıştı. Unsplash bu servisi 2024 ortasında kapattı, tüm istekler 404 dönüyor.<br><br>
        <strong>Çözüm:</strong> Toplu onar butonu, ürün adının yazılı olduğu marka renkli placeholder görseller atar (placehold.co — ücretsiz, atıf gerektirmez, kararlı). Sonra zamanın oldukça <strong>her ürüne tek tek girip "Görsel" kutusuna gerçek ürün fotoğrafının URL'sini yapıştır</strong> ya da kendi sunucundan dosya yükle.<br><br>
        <strong>Kaynak öneri:</strong> Marka resmi web sitelerinden (Bosch, Daikin, Mitsubishi, Demirdöküm) ürün katalog görsellerini sağ tık → "Resim adresini kopyala" ile alıp yapıştırabilirsin (telif konusunu gözeterek).
    </p>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
