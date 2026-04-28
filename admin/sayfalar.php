<?php
require_once __DIR__ . '/_baslat.php';
page_title('Statik Sayfalar (KVKK / Gizlilik)');

// Yönetilen sayfa anahtarları
$sayfalar = [
    'kvkk_metni'      => ['baslik'=>'KVKK Aydınlatma Metni',     'sayfa'=>'kvkk',     'aciklama'=>'Kişisel verilerin işlenmesi aydınlatma metni'],
    'gizlilik_metni'  => ['baslik'=>'Gizlilik Politikası',       'sayfa'=>'gizlilik', 'aciklama'=>'Site ziyaretçi gizlilik bilgileri'],
    'cerez_metni'     => ['baslik'=>'Çerez Politikası',          'sayfa'=>'cerez',    'aciklama'=>'Çerez kullanım bildirgesi'],
    'mesafeli_metni'  => ['baslik'=>'Mesafeli Satış Sözleşmesi', 'sayfa'=>'mesafeli', 'aciklama'=>'Online ürün/hizmet satışı için sözleşme'],
    'iade_metni'      => ['baslik'=>'İade Politikası',           'sayfa'=>'iade',     'aciklama'=>'İade ve değişim koşulları'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem  = $_POST['islem'] ?? '';
    $anahtar = $_POST['anahtar'] ?? '';

    if ($islem === 'kaydet' && isset($sayfalar[$anahtar])) {
        $icerik = trim($_POST['icerik'] ?? '');
        // ayarlar tablosunda upsert
        $var = db_get("SELECT id FROM ayarlar WHERE anahtar=?", [$anahtar]);
        if ($var) {
            db_run("UPDATE ayarlar SET deger=? WHERE anahtar=?", [$icerik, $anahtar]);
        } else {
            db_run("INSERT INTO ayarlar (anahtar, deger) VALUES (?,?)", [$anahtar, $icerik]);
        }
        log_yaz('sayfa_kaydet', "Anahtar: $anahtar", (int)$_kul['id']);
        flash_set('ok', $sayfalar[$anahtar]['baslik'] . ' güncellendi.');
        redirect(SITE_URL . '/admin/sayfalar.php?duzenle=' . urlencode($anahtar));
    }
}

// Mevcut anahtarın seçilmesi
$secili_anahtar = $_GET['duzenle'] ?? '';
if ($secili_anahtar && !isset($sayfalar[$secili_anahtar])) $secili_anahtar = '';

$icerikler = [];
foreach ($sayfalar as $a => $bilgi) {
    $r = db_get("SELECT deger FROM ayarlar WHERE anahtar=?", [$a]);
    $icerikler[$a] = $r['deger'] ?? '';
}

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Statik Sayfalar</h1>
        <p class="page-sub">KVKK, Gizlilik, Çerez Politikası gibi yasal metinleri buradan düzenle.</p>
    </div>
</div>

<?php if (!$secili_anahtar): ?>
    <!-- Sayfa listesi -->
    <div class="form-row cols-2">
    <?php foreach ($sayfalar as $a => $bilgi):
        $dolu = !empty($icerikler[$a]);
        $karakter = mb_strlen(strip_tags($icerikler[$a]), 'UTF-8');
    ?>
        <div class="card" style="position:relative;<?= $dolu ? '' : 'border:2px dashed #fcd34d;background:#fffbeb' ?>">
            <div style="display:flex;justify-content:space-between;align-items:start;gap:12px">
                <div>
                    <h3 style="margin:0 0 4px"><?= e($bilgi['baslik']) ?>
                        <?php if (!$dolu): ?><span class="badge badge-warn" style="margin-left:6px">EKSİK</span><?php endif; ?>
                    </h3>
                    <p style="color:var(--c-muted);margin:0 0 10px;font-size:.86rem"><?= e($bilgi['aciklama']) ?></p>
                    <div style="font-size:.8rem;color:var(--c-muted)">
                        <code>/<?= e($bilgi['sayfa']) ?></code>
                        <?php if ($dolu): ?>
                            · <?= number_format($karakter, 0, ',', '.') ?> karakter
                        <?php endif; ?>
                    </div>
                </div>
                <i class="fas fa-file-lines" style="color:var(--c-orange);font-size:1.6rem;opacity:.5"></i>
            </div>
            <div style="margin-top:14px;display:flex;gap:8px">
                <a href="?duzenle=<?= e($a) ?>" class="btn btn-pri btn-sm"><i class="fas fa-pen"></i> Düzenle</a>
                <a href="<?= SITE_URL ?>/<?= e($bilgi['sayfa']) ?>" target="_blank" class="btn btn-out btn-sm"><i class="fas fa-eye"></i> Sitede Gör</a>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <div class="card" style="margin-top:14px;background:var(--c-bg)">
        <h3 style="margin-top:0"><i class="fas fa-circle-info"></i> Bilgi</h3>
        <p style="color:var(--c-text-2);margin-bottom:0">
            Bu metinler <code>ayarlar</code> tablosundaki anahtarlarda saklanır:
            <code>kvkk_metni</code>, <code>gizlilik_metni</code>, <code>cerez_metni</code>, vb.
            HTML kullanabilirsin (örnek: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;a href&gt;).
        </p>
    </div>

<?php else:
    // Düzenleme formu
    $bilgi = $sayfalar[$secili_anahtar];
    $mevcut = $icerikler[$secili_anahtar];
?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
            <div>
                <h3 style="margin:0"><?= e($bilgi['baslik']) ?></h3>
                <small style="color:var(--c-muted)"><?= e($bilgi['aciklama']) ?> · URL: <code>/<?= e($bilgi['sayfa']) ?></code></small>
            </div>
            <a href="<?= SITE_URL ?>/admin/sayfalar.php" class="btn btn-out btn-sm"><i class="fas fa-arrow-left"></i> Sayfa Listesi</a>
        </div>

        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="islem"   value="kaydet">
            <input type="hidden" name="anahtar" value="<?= e($secili_anahtar) ?>">

            <div class="field" style="margin-bottom:14px">
                <label>İçerik (HTML)</label>
                <textarea class="input" name="icerik" rows="22" style="font-family:Consolas,monospace;font-size:.9rem"><?= e($mevcut) ?></textarea>
                <small style="color:var(--c-muted)">
                    Kullanılabilir HTML: &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a href&gt;.
                    Bölümlemek için &lt;h2&gt; başlıklar kullan.
                </small>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Kaydet</button>
                <a href="<?= SITE_URL ?>/<?= e($bilgi['sayfa']) ?>" target="_blank" class="btn btn-out"><i class="fas fa-external-link-alt"></i> Önizle</a>
                <a href="<?= SITE_URL ?>/admin/sayfalar.php" class="btn btn-out">İptal</a>
            </div>
        </form>
    </div>

    <?php if (!$mevcut): ?>
    <details style="margin-top:14px">
        <summary style="cursor:pointer;font-weight:700;color:var(--c-orange)">
            <i class="fas fa-magic-wand-sparkles"></i> Örnek metin şablonu (kopyalayıp kullanabilirsin)
        </summary>
        <div style="margin-top:12px;padding:14px;background:var(--c-bg);border-radius:8px;font-size:.85rem;line-height:1.7">
        <?php if ($secili_anahtar === 'kvkk_metni'): ?>
            <pre style="white-space:pre-wrap"><h2>1. Veri Sorumlusu</h2>
<p>Azra Doğalgaz olarak ("Şirket") 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") kapsamında veri sorumlusu sıfatıyla hareket etmekteyiz.</p>

<h2>2. İşlenen Kişisel Veriler</h2>
<ul>
  <li><strong>Kimlik Bilgileri:</strong> Ad, soyad</li>
  <li><strong>İletişim Bilgileri:</strong> Telefon, e-posta, adres</li>
  <li><strong>Müşteri İşlem Bilgileri:</strong> Hizmet/ürün satın alma, ödeme</li>
</ul>

<h2>3. Verilerin İşlenme Amaçları</h2>
<p>Kişisel verileriniz; hizmet sunumu, sözleşme yönetimi, yasal yükümlülüklerin yerine getirilmesi, müşteri ilişkileri yönetimi amaçlarıyla işlenir.</p>

<h2>4. Haklarınız</h2>
<p>KVKK madde 11 kapsamında verilerinize erişim, düzeltme, silme, işlemeye itiraz hakkına sahipsiniz. Başvurularınızı info@azradogalgaz.com e-posta adresine iletebilirsiniz.</p></pre>
        <?php elseif ($secili_anahtar === 'gizlilik_metni'): ?>
            <pre style="white-space:pre-wrap"><h2>Gizlilik Politikası</h2>
<p>Azra Doğalgaz olarak kişisel verilerinizin gizliliğine saygı duyuyor ve bu doğrultuda hareket ediyoruz.</p>

<h2>1. Toplanan Bilgiler</h2>
<p>Site üzerinden iletişim formu, üyelik veya sipariş aşamasında ad, e-posta, telefon, adres bilgilerinizi alıyoruz.</p>

<h2>2. Bilgilerin Kullanımı</h2>
<p>Bilgileriniz yalnızca size hizmet sunmak, yasal yükümlülükleri yerine getirmek ve sizinle iletişim kurmak için kullanılır.</p>

<h2>3. Üçüncü Taraflarla Paylaşım</h2>
<p>Verileriniz, açık rıza olmaksızın hiçbir üçüncü tarafla paylaşılmaz. Yasal mercilerin talebi olması durumunda bilgi paylaşımı yapılabilir.</p></pre>
        <?php elseif ($secili_anahtar === 'cerez_metni'): ?>
            <pre style="white-space:pre-wrap"><h2>Çerez Politikası</h2>
<p>Web sitemiz, kullanıcı deneyimini iyileştirmek için çerezler kullanır.</p>

<h2>Kullanılan Çerez Tipleri</h2>
<ul>
  <li><strong>Zorunlu Çerezler:</strong> Site işlevselliği için gerekli</li>
  <li><strong>Performans Çerezleri:</strong> Site kullanımını analiz</li>
  <li><strong>İşlevsellik Çerezleri:</strong> Tercihlerinizi hatırlamak</li>
</ul>

<h2>Çerezleri Devre Dışı Bırakma</h2>
<p>Tarayıcı ayarlarınızdan çerezleri devre dışı bırakabilirsiniz. Ancak bu durumda sitenin bazı işlevleri çalışmayabilir.</p></pre>
        <?php endif; ?>
        </div>
    </details>
    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
