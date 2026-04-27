<?php
/**
 * Veritabanı Migration Aracı — v1.4.2
 * Yeni hizmet kategorileri ekler, "projeler" tablosunu oluşturur.
 *
 * Kullanım: Tarayıcıda /migrate.php açılır, admin oturumu zorunlu.
 * Çalıştıktan sonra kendisini "kilit" dosyası ile bir daha çalıştırılamaz hale getirir.
 */
require_once __DIR__ . '/config.php';

// Sadece admin
if (!admin_giris_var()) {
    header('Location: ' . SITE_URL . '/admin/giris-yap.php?donus=' . urlencode('/migrate.php'));
    exit;
}

$kilit = __DIR__ . '/.migrate-1.4.2.lock';
$mesajlar = [];
$hatalar  = [];

if (file_exists($kilit) && empty($_GET['zorla'])) {
    $mesajlar[] = '⚠️ Bu migration daha önce çalıştırılmış. Tekrar çalıştırmak için URL\'ye <code>?zorla=1</code> ekleyin (mevcut veriler korunur, sadece eksikler eklenir).';
}

if (!empty($_POST['calistir'])) {
    csrf_check();

    // 1) Yeni hizmet kategorilerini ekle (varsa atla)
    $yeni_hizmetler = [
        ['yerden-isitma',     'Yerden Isıtma', 'Yerden ısıtma sistemi tasarım ve montajı', 50],
        ['havalandirma',      'Havalandırma Tesisatı', 'Mekanik havalandırma, davlumbaz, ısı geri kazanım', 60],
        ['sihhi-tesisat',     'Sıhhi Tesisat', 'Su tesisatı, banyo - WC, drenaj sistemleri', 70],
        ['yangin-tesisati',   'Yangın Tesisatı', 'Yangın algılama, sprinkler, hidrant sistemleri', 80],
        ['isi-pompasi',       'Isı Pompası', 'Hava kaynaklı ısı pompası satış ve montaj', 90],
    ];
    foreach ($yeni_hizmetler as [$slug, $ad, $aciklama, $sira]) {
        $var = db_get("SELECT id FROM hizmet_kategorileri WHERE slug=?", [$slug]);
        if ($var) {
            $mesajlar[] = "✓ <strong>$ad</strong> kategorisi zaten mevcut, atlandı.";
        } else {
            try {
                db_run("INSERT INTO hizmet_kategorileri (slug, ad, aciklama, sira, aktif, olusturma_tarihi) VALUES (?, ?, ?, ?, 1, NOW())",
                    [$slug, $ad, $aciklama, $sira]);
                $mesajlar[] = "✅ <strong>$ad</strong> kategorisi eklendi.";
            } catch (Throwable $e) {
                $hatalar[] = "❌ <strong>$ad</strong> eklenemedi: " . $e->getMessage();
            }
        }
    }

    // 2) projeler tablosu oluştur
    try {
        db_run("CREATE TABLE IF NOT EXISTS projeler (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) UNIQUE NOT NULL,
            baslik VARCHAR(200) NOT NULL,
            kategori VARCHAR(100) DEFAULT NULL,
            ozet TEXT DEFAULT NULL,
            icerik LONGTEXT DEFAULT NULL,
            gorsel VARCHAR(255) DEFAULT NULL,
            galeri TEXT DEFAULT NULL,
            lokasyon VARCHAR(120) DEFAULT NULL,
            tarih DATE DEFAULT NULL,
            sira INT DEFAULT 0,
            aktif TINYINT(1) DEFAULT 1,
            seo_baslik VARCHAR(200) DEFAULT NULL,
            seo_aciklama TEXT DEFAULT NULL,
            olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_kategori (kategori),
            INDEX idx_aktif (aktif)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $mesajlar[] = "✅ <strong>projeler</strong> tablosu oluşturuldu (zaten varsa korundu).";
    } catch (Throwable $e) {
        $hatalar[] = "❌ projeler tablosu hatası: " . $e->getMessage();
    }

    // 3) Kilit dosyası oluştur
    @file_put_contents($kilit, "v1.4.2 migration tamamlandı: " . date('Y-m-d H:i:s'));
    $mesajlar[] = "🔒 Migration tamamlandı, kilit dosyası oluşturuldu.";
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Migration — v1.4.2 — Azra Doğalgaz</title>
<style>
    body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 40px 20px; }
    .box { max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 12px rgba(15,23,42,.05); }
    h1 { font-size: 1.6rem; margin-bottom: 8px; }
    .lead { color: #64748b; margin-bottom: 28px; }
    h2 { font-size: 1.1rem; margin: 24px 0 10px; }
    .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 12px; font-size: .92rem; line-height: 1.6; }
    .alert-ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-info { background: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; }
    .alert-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    code { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: .9em; }
    .btn { display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #ff6b00, #f97316); color: #fff; border-radius: 50px; font-weight: 600; text-decoration: none; border: 0; cursor: pointer; font-size: 1rem; }
    .btn-out { background: #fff; color: #0f172a; border: 1px solid #e2e8f0; padding: 12px 24px; }
    ul { padding-left: 22px; margin: 14px 0; }
    li { margin-bottom: 6px; font-size: .92rem; }
</style>
</head>
<body>
<div class="box">
    <h1>🛠️ Veritabanı Migration — v1.4.2</h1>
    <p class="lead">Aşama 6 Paket 2: Yeni hizmet kategorileri ve projeler tablosu eklenir.</p>

    <?php foreach ($mesajlar as $m): ?>
        <div class="alert alert-ok"><?= $m ?></div>
    <?php endforeach; ?>
    <?php foreach ($hatalar as $h): ?>
        <div class="alert alert-err"><?= $h ?></div>
    <?php endforeach; ?>

    <?php if (empty($_POST['calistir'])): ?>
        <h2>Yapılacaklar</h2>
        <div class="alert alert-info">
            Bu migration aşağıdakileri uygular:
            <ul>
                <li><strong>Yerden Isıtma</strong> hizmet kategorisi ekler</li>
                <li><strong>Havalandırma Tesisatı</strong> kategorisi ekler</li>
                <li><strong>Sıhhi Tesisat</strong> kategorisi ekler</li>
                <li><strong>Yangın Tesisatı</strong> kategorisi ekler</li>
                <li><strong>Isı Pompası</strong> kategorisi ekler</li>
                <li><strong>projeler</strong> tablosunu oluşturur (admin panelinden proje ekleyebilirsiniz)</li>
            </ul>
            Mevcut kategoriler ve veriler <strong>korunur</strong>, sadece eksikler eklenir.
        </div>

        <?php if (file_exists($kilit) && empty($_GET['zorla'])): ?>
            <div class="alert alert-warn">
                ⚠️ Bu migration daha önce çalıştırılmış. Yine de çalıştırmak isterseniz, mevcut kayıtlar korunur:
                <br><br>
                <a href="?zorla=1" class="btn btn-out">Zorla Çalıştır</a>
                <a href="<?= SITE_URL ?>/admin/" class="btn btn-out">Admin Paneline Dön</a>
            </div>
        <?php else: ?>
            <form method="post" style="margin-top:20px">
                <?= csrf_field() ?>
                <input type="hidden" name="calistir" value="1">
                <button class="btn" type="submit">▶️ Migration'u Çalıştır</button>
                <a href="<?= SITE_URL ?>/admin/" class="btn btn-out" style="margin-left:8px">İptal</a>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info" style="margin-top:24px">
            ✅ Migration tamamlandı.<br><br>
            <a href="<?= SITE_URL ?>/admin/" class="btn">Admin Paneline Dön</a>
            <a href="<?= SITE_URL ?>/" class="btn btn-out" style="margin-left:8px">Siteyi Görüntüle</a>
        </div>
    <?php endif; ?>

    <hr style="margin:32px 0;border:0;border-top:1px solid #e2e8f0">
    <p style="font-size:.82rem;color:#94a3b8">
        <strong>Güvenlik notu:</strong> Bu dosya bir kez çalıştıktan sonra <code>.migrate-1.4.2.lock</code> dosyası oluşturur. İsterseniz manuel olarak silebilirsiniz: <code>rm .migrate-1.4.2.lock</code>
    </p>
</div>
</body>
</html>
