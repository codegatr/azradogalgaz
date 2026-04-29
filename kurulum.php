<?php
/**
 * Azra Doğalgaz — Veritabanı Kurulum (v1.1.1)
 *
 * KULLANIM:
 *   1) config.php içindeki DB_HOST, DB_NAME, DB_USER, DB_PASS doldur.
 *   2) Bu dosyayı tarayıcıda aç:  https://azradogalgaz.com/kurulum.php
 *   3) Kurulum tamamlandı mesajını gördükten sonra
 *      DOSYAYI MUTLAKA SİL:  /kurulum.php
 *
 * Bir kez kurulduktan sonra "kilit dosyası" oluşur ve bu sayfa
 * tekrar tetiklenemez. Yeniden kurmak istersen:
 *   ?yeniden=1   parametresiyle çağırabilirsin.
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$KILIT = UPLOAD_DIR . '/.kurulum-tamam';

// Yeniden kurulum istendi mi?
if (isset($_GET['yeniden']) && $_GET['yeniden'] === '1' && file_exists($KILIT)) {
    @unlink($KILIT);
}

// Daha önce kuruldu mu?
$daha_once_kuruldu = file_exists($KILIT);

$logs = [];
$ok   = function(string $m) use (&$logs) { $logs[] = ['ok',  $m]; };
$err  = function(string $m) use (&$logs) { $logs[] = ['err', $m]; };
$tum_basarili = true;

// Db bağlantı testi
$db_baglandi = false;
$db_hata = '';
try {
    db()->query("SELECT 1");
    $db_baglandi = true;
} catch (Throwable $e) {
    $db_hata = $e->getMessage();
}

// Kurulum yap?
$kurulumu_calistir = !$daha_once_kuruldu && $db_baglandi;

if ($kurulumu_calistir) {
    try {
        $pdo = db();
        $tablolar = [

        "CREATE TABLE IF NOT EXISTS ayarlar (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            anahtar VARCHAR(80) NOT NULL UNIQUE,
            deger MEDIUMTEXT NULL,
            guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS kullanicilar (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ad VARCHAR(80) NOT NULL,
            eposta VARCHAR(160) NOT NULL UNIQUE,
            sifre VARCHAR(255) NOT NULL,
            rol ENUM('admin','editor','muhasebe') DEFAULT 'admin',
            aktif TINYINT(1) DEFAULT 1,
            son_giris DATETIME NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS hizmet_kategorileri (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ad VARCHAR(120) NOT NULL,
            slug VARCHAR(160) NOT NULL UNIQUE,
            ikon VARCHAR(60) NULL,
            sira INT DEFAULT 0,
            aktif TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS hizmetler (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kategori_id INT UNSIGNED NULL,
            baslik VARCHAR(180) NOT NULL,
            slug VARCHAR(220) NOT NULL UNIQUE,
            kisa_aciklama VARCHAR(300) NULL,
            icerik MEDIUMTEXT NULL,
            gorsel VARCHAR(255) NULL,
            meta_baslik VARCHAR(200) NULL,
            meta_aciklama VARCHAR(300) NULL,
            sira INT DEFAULT 0,
            aktif TINYINT(1) DEFAULT 1,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_kategori (kategori_id),
            INDEX idx_aktif (aktif)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS markalar (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ad VARCHAR(80) NOT NULL,
            slug VARCHAR(120) NOT NULL UNIQUE,
            logo VARCHAR(255) NULL,
            aktif TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS urun_kategorileri (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ad VARCHAR(120) NOT NULL,
            slug VARCHAR(160) NOT NULL UNIQUE,
            ust_id INT UNSIGNED NULL,
            sira INT DEFAULT 0,
            aktif TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS urunler (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kategori_id INT UNSIGNED NULL,
            marka_id INT UNSIGNED NULL,
            ad VARCHAR(220) NOT NULL,
            slug VARCHAR(260) NOT NULL UNIQUE,
            sku VARCHAR(80) NULL UNIQUE,
            kisa_aciklama VARCHAR(400) NULL,
            aciklama MEDIUMTEXT NULL,
            ozellikler MEDIUMTEXT NULL,
            fiyat DECIMAL(12,2) DEFAULT 0,
            indirimli_fiyat DECIMAL(12,2) NULL,
            kdv_orani TINYINT DEFAULT 20,
            stok INT DEFAULT 0,
            gorsel VARCHAR(255) NULL,
            galeri MEDIUMTEXT NULL,
            meta_baslik VARCHAR(200) NULL,
            meta_aciklama VARCHAR(300) NULL,
            one_cikan TINYINT(1) DEFAULT 0,
            aktif TINYINT(1) DEFAULT 1,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_kategori (kategori_id),
            INDEX idx_marka (marka_id),
            INDEX idx_aktif (aktif),
            INDEX idx_one (one_cikan)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS kampanyalar (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            baslik VARCHAR(220) NOT NULL,
            slug VARCHAR(260) NOT NULL UNIQUE,
            kisa_aciklama VARCHAR(400) NULL,
            icerik MEDIUMTEXT NULL,
            gorsel VARCHAR(255) NULL,
            nakit_fiyat DECIMAL(12,2) NULL,
            kart_fiyat DECIMAL(12,2) NULL,
            taksit_sayisi INT DEFAULT 0,
            baslangic DATE NULL,
            bitis DATE NULL,
            meta_baslik VARCHAR(200) NULL,
            meta_aciklama VARCHAR(300) NULL,
            aktif TINYINT(1) DEFAULT 1,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS blog_yazilari (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            baslik VARCHAR(220) NOT NULL,
            slug VARCHAR(260) NOT NULL UNIQUE,
            ozet VARCHAR(500) NULL,
            icerik MEDIUMTEXT NULL,
            gorsel VARCHAR(255) NULL,
            yazar VARCHAR(120) DEFAULT 'Azra Doğalgaz',
            meta_baslik VARCHAR(200) NULL,
            meta_aciklama VARCHAR(300) NULL,
            etiketler VARCHAR(400) NULL,
            goruntulenme INT UNSIGNED DEFAULT 0,
            aktif TINYINT(1) DEFAULT 1,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            yayin_tarihi DATETIME NULL,
            INDEX idx_aktif (aktif),
            INDEX idx_yayin (yayin_tarihi)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS iletisim_mesajlari (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ad_soyad VARCHAR(160) NOT NULL,
            eposta VARCHAR(160) NULL,
            telefon VARCHAR(40) NULL,
            konu VARCHAR(200) NULL,
            mesaj TEXT NOT NULL,
            ip VARCHAR(45) NULL,
            durum ENUM('yeni','okundu','arandi','kapali') DEFAULT 'yeni',
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_durum (durum)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS cariler (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cari_kodu VARCHAR(40) NOT NULL UNIQUE,
            unvan VARCHAR(220) NOT NULL,
            tip ENUM('bireysel','kurumsal') DEFAULT 'bireysel',
            tckn_vkn VARCHAR(20) NULL,
            vergi_dairesi VARCHAR(120) NULL,
            telefon VARCHAR(40) NULL,
            telefon_2 VARCHAR(40) NULL,
            eposta VARCHAR(160) NULL,
            il VARCHAR(60) NULL,
            ilce VARCHAR(80) NULL,
            adres TEXT NULL,
            bakiye DECIMAL(14,2) DEFAULT 0,
            notlar TEXT NULL,
            aktif TINYINT(1) DEFAULT 1,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_unvan (unvan),
            INDEX idx_tel (telefon),
            INDEX idx_aktif (aktif)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS cari_hareketler (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cari_id INT UNSIGNED NOT NULL,
            tarih DATE NOT NULL,
            tip ENUM('borc','alacak','tahsilat','odeme') NOT NULL,
            belge_tip ENUM('manuel','fatura','fis','tahsilat','odeme','iade') DEFAULT 'manuel',
            belge_id INT UNSIGNED NULL,
            belge_no VARCHAR(60) NULL,
            aciklama VARCHAR(300) NULL,
            tutar DECIMAL(14,2) NOT NULL,
            olusturan_id INT UNSIGNED NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cari (cari_id),
            INDEX idx_tarih (tarih),
            INDEX idx_belge (belge_tip, belge_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS faturalar (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cari_id INT UNSIGNED NOT NULL,
            fatura_no VARCHAR(40) NOT NULL UNIQUE,
            tip ENUM('satis','alis','iade_satis','iade_alis') DEFAULT 'satis',
            tarih DATE NOT NULL,
            vade_tarihi DATE NULL,
            ara_toplam DECIMAL(14,2) DEFAULT 0,
            iskonto DECIMAL(14,2) DEFAULT 0,
            kdv_toplam DECIMAL(14,2) DEFAULT 0,
            genel_toplam DECIMAL(14,2) DEFAULT 0,
            odeme_durumu ENUM('odenmedi','kismi','odendi') DEFAULT 'odenmedi',
            odenen DECIMAL(14,2) DEFAULT 0,
            notlar TEXT NULL,
            olusturan_id INT UNSIGNED NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_cari (cari_id),
            INDEX idx_tarih (tarih),
            INDEX idx_odeme (odeme_durumu)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS fatura_kalemleri (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fatura_id INT UNSIGNED NOT NULL,
            urun_id INT UNSIGNED NULL,
            ad VARCHAR(220) NOT NULL,
            miktar DECIMAL(12,3) DEFAULT 1,
            birim VARCHAR(20) DEFAULT 'Adet',
            birim_fiyat DECIMAL(14,2) DEFAULT 0,
            iskonto_yuzde DECIMAL(5,2) DEFAULT 0,
            kdv_orani TINYINT DEFAULT 20,
            toplam DECIMAL(14,2) DEFAULT 0,
            INDEX idx_fatura (fatura_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS fisler (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cari_id INT UNSIGNED NULL,
            fis_no VARCHAR(40) NOT NULL UNIQUE,
            tip ENUM('satis','tahsilat','odeme','gider','gelir') DEFAULT 'satis',
            tarih DATE NOT NULL,
            aciklama VARCHAR(300) NULL,
            ara_toplam DECIMAL(14,2) DEFAULT 0,
            kdv_toplam DECIMAL(14,2) DEFAULT 0,
            genel_toplam DECIMAL(14,2) DEFAULT 0,
            odeme_yontemi ENUM('nakit','kart','havale','cek','senet') DEFAULT 'nakit',
            notlar TEXT NULL,
            olusturan_id INT UNSIGNED NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cari (cari_id),
            INDEX idx_tarih (tarih),
            INDEX idx_tip (tip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS fis_kalemleri (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fis_id INT UNSIGNED NOT NULL,
            urun_id INT UNSIGNED NULL,
            ad VARCHAR(220) NOT NULL,
            miktar DECIMAL(12,3) DEFAULT 1,
            birim VARCHAR(20) DEFAULT 'Adet',
            birim_fiyat DECIMAL(14,2) DEFAULT 0,
            kdv_orani TINYINT DEFAULT 20,
            toplam DECIMAL(14,2) DEFAULT 0,
            INDEX idx_fis (fis_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS stok_hareketleri (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            urun_id INT UNSIGNED NOT NULL,
            tarih DATE NOT NULL,
            tip ENUM('giris','cikis','sayim','iade') NOT NULL,
            miktar DECIMAL(12,3) NOT NULL,
            belge_tip VARCHAR(40) NULL,
            belge_id INT UNSIGNED NULL,
            aciklama VARCHAR(300) NULL,
            olusturan_id INT UNSIGNED NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_urun (urun_id),
            INDEX idx_tarih (tarih)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS log_kayitlari (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kullanici_id INT UNSIGNED NULL,
            tip VARCHAR(40) NOT NULL,
            mesaj VARCHAR(500) NOT NULL,
            ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tip (tip),
            INDEX idx_tarih (olusturma_tarihi)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS guncelleme_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            eski_surum VARCHAR(20) NULL,
            yeni_surum VARCHAR(20) NULL,
            durum ENUM('basarili','hata') DEFAULT 'basarili',
            detay TEXT NULL,
            olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        ];

        foreach ($tablolar as $sql) $pdo->exec($sql);
        $ok('Tablolar oluşturuldu (' . count($tablolar) . ' adet).');

        // Varsayılan ayarlar
        $varsayilanlar = [
            'site_baslik'        => 'Azra Doğalgaz — Konforlu Yaşam, Güvenli Gelecek',
            'site_aciklama'      => SITE_DESC,
            'site_anahtar_kelime'=> SITE_KEYWORDS,
            'firma_unvan'        => 'Azra Doğalgaz Tesisat',
            'firma_telefon_1'    => FIRMA_TEL_1,
            'firma_telefon_2'    => FIRMA_TEL_2,
            'firma_eposta'       => FIRMA_EMAIL,
            'firma_adres'        => 'İzmir, Türkiye',
            'firma_calisma_saatleri' => 'Pzt-Cmt 08:00-20:00',
            'sosyal_facebook'    => '',
            'sosyal_instagram'   => '',
            'sosyal_youtube'     => '',
            'sosyal_x'           => '',
            'whatsapp_numara'    => '905467907877',
            'google_analytics'   => '',
            'google_search_console_meta' => '',
            'harita_iframe'      => '',
            'aktif_kampanya_id'  => '',
            'kvkk_metni'         => '',
            'gizlilik_metni'     => '',
            'github_repo'        => 'codegatr/azradogalgaz',
            'github_token'       => '',
            'guncel_surum'       => '1.1.0',
        ];
        $st = $pdo->prepare("INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES (?,?)");
        foreach ($varsayilanlar as $k => $v) $st->execute([$k, $v]);
        $ok('Varsayılan ayarlar yüklendi.');

        // Demo admin
        $eposta = 'admin@azradogalgaz.com';
        $sifre  = 'Azra2026!';
        $kontrol = $pdo->prepare("SELECT id FROM kullanicilar WHERE eposta=?");
        $kontrol->execute([$eposta]);
        if (!$kontrol->fetch()) {
            $pdo->prepare("INSERT INTO kullanicilar (ad,eposta,sifre,rol) VALUES (?,?,?,?)")
                ->execute(['Sistem Yöneticisi', $eposta, password_hash($sifre, PASSWORD_DEFAULT), 'admin']);
            $ok("Yönetici hesabı: <strong>$eposta</strong> / <code>$sifre</code> (ilk girişte değiştir!)");
        } else {
            $ok('Yönetici hesabı zaten mevcut, atlandı.');
        }

        // Demo kategoriler
        $kategoriler = [
            ['Doğalgaz Tesisatı','dogalgaz-tesisati','flame'],
            ['Klima Montajı',    'klima-montaji',    'snowflake'],
            ['Tesisat Hizmetleri','tesisat-hizmetleri','wrench'],
            ['Kombi Servisi',    'kombi-servisi',    'tools'],
        ];
        $st = $pdo->prepare("INSERT IGNORE INTO hizmet_kategorileri (ad,slug,ikon,sira) VALUES (?,?,?,?)");
        foreach ($kategoriler as $i=>$k) { $st->execute([$k[0],$k[1],$k[2], $i+1]); }
        $ok('Hizmet kategorileri yüklendi.');

        // Demo markalar
        $markalar = [
            ['Demirdöküm','demirdokum'],
            ['Bosch','bosch'],
            ['Vaillant','vaillant'],
            ['Baymak','baymak'],
            ['Buderus','buderus'],
            ['Mitsubishi','mitsubishi'],
            ['Daikin','daikin'],
        ];
        $st = $pdo->prepare("INSERT IGNORE INTO markalar (ad,slug) VALUES (?,?)");
        foreach ($markalar as $m) { $st->execute($m); }
        $ok('Markalar yüklendi.');

        // Demo kampanya
        $st = $pdo->prepare("INSERT IGNORE INTO kampanyalar
            (baslik,slug,kisa_aciklama,icerik,nakit_fiyat,kart_fiyat,taksit_sayisi,meta_baslik,meta_aciklama,aktif)
            VALUES (?,?,?,?,?,?,?,?,?,1)");
        $st->execute([
            'Azra Doğalgaz Süper Kombi Paketi — Demirdöküm Ademix 24 kW',
            'azra-dogalgaz-super-kombi-paketi',
            'Demirdöküm Ademix 24 kW tam yoğuşmalı kombi, 5 metre termopan petek, kombi dolabı, 50x100 havlupan, siyah boru ve proje dahil mevzuata uygun tesisat hizmeti.',
            '<h2>Paket İçeriği</h2><ul>'
            .'<li>Demirdöküm Ademix 24 kW Tam Yoğuşmalı Kombi</li>'
            .'<li>5 Metre Termopan Petek</li>'
            .'<li>Kombi Dolabı</li>'
            .'<li>50x100 Havlupan</li>'
            .'<li>Siyah Boru + Proje Dahil</li>'
            .'<li>Doğalgaz Tesisat Hizmetleri</li>'
            .'</ul><p>Konforlu yaşam ve güvenli gelecek için Azra Doğalgaz garantisi.</p>',
            80000.00, 87000.00, 6,
            'İzmir Kombi Paketi 80.000 TL — Demirdöküm Ademix 24 kW | Azra Doğalgaz',
            'İzmir\'de Demirdöküm Ademix 24 kW tam yoğuşmalı kombi paketi 80.000 TL nakit. 5 m petek, kombi dolabı, havlupan, siyah boru, proje ve mevzuata uygun tesisat dahil.',
        ]);
        $ok('Demo kampanya yüklendi.');

        // Kilit dosyası
        if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
        @file_put_contents($KILIT, "Kurulum tamamlandı: " . date('Y-m-d H:i:s') . "\nSürüm: 1.1.0\n");
        $ok('🎉 Kurulum tamamlandı. <strong>Şimdi kurulum.php dosyasını sunucundan SİL!</strong>');

    } catch (Throwable $e) {
        $tum_basarili = false;
        $err('HATA: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Azra Doğalgaz — Kurulum</title>
<style>
:root{--bg:#0a1024;--card:#131f3e;--line:rgba(255,255,255,.08);--text:#e8ecf3;--muted:#9aa3b8;--orange:#ff8c00;--green:#00cc66;--red:#ff3b3b}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:30px 16px;line-height:1.6}
.wrap{max-width:780px;margin:0 auto}
.card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:30px;margin-bottom:18px}
h1{font-size:2rem;margin-bottom:6px}
h1 span{background:linear-gradient(135deg,#ff8c00,#ff5500);-webkit-background-clip:text;background-clip:text;color:transparent}
.sub{color:var(--muted);margin-bottom:20px}
.log{background:#0a0f1c;padding:12px 16px;border-left:4px solid var(--green);margin:8px 0;border-radius:8px;font-size:.95rem}
.log.err{border-color:var(--red);color:#ff8b8b}
.box{background:#0a0f1c;padding:16px;border-radius:10px;border:1px solid var(--line);margin:14px 0}
code{background:#1f2937;padding:3px 8px;border-radius:6px;font-family:ui-monospace,monospace;color:#ffd479}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:50px;font-weight:700;text-decoration:none;margin:6px 6px 0 0;transition:.2s}
.btn-pri{background:linear-gradient(135deg,#ff8c00,#ff5500);color:#fff}
.btn-out{background:transparent;color:var(--text);border:2px solid rgba(255,255,255,.18)}
.btn:hover{transform:translateY(-2px)}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:18px;border:1px solid}
.alert-ok{background:rgba(0,204,102,.12);border-color:rgba(0,204,102,.3);color:var(--green)}
.alert-err{background:rgba(255,59,59,.12);border-color:rgba(255,59,59,.3);color:#ff8b8b}
.alert-warn{background:rgba(255,140,0,.12);border-color:rgba(255,140,0,.3);color:#ffb04d}
table{width:100%;margin-top:12px;font-size:.92rem}
table td{padding:6px 0;border-bottom:1px dashed rgba(255,255,255,.08)}
table td:first-child{color:var(--muted);width:140px}
.danger{color:#ff8b8b;font-weight:600}
</style>
</head>
<body>
<div class="wrap">

<div class="card">
    <h1>🔥 <span>Azra Doğalgaz</span> Kurulum</h1>
    <p class="sub">Veritabanı kurulumu / migrasyon</p>

    <?php if (!$db_baglandi): ?>
        <div class="alert alert-err">
            <strong>❌ Veritabanına bağlanılamadı.</strong><br>
            Lütfen <code>config.php</code> içindeki <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code> değerlerini kontrol et.
        </div>
        <div class="box">
            <strong>Mevcut DB ayarları:</strong>
            <table>
                <tr><td>DB_HOST</td><td><code><?= e(DB_HOST) ?></code></td></tr>
                <tr><td>DB_NAME</td><td><code><?= e(DB_NAME) ?></code></td></tr>
                <tr><td>DB_USER</td><td><code><?= e(DB_USER) ?></code></td></tr>
                <tr><td>DB_PASS</td><td><code><?= str_repeat('•', max(4, strlen(DB_PASS))) ?></code></td></tr>
            </table>
            <p style="margin-top:12px;color:var(--muted);font-size:.88rem">Hata mesajı: <code><?= e($db_hata) ?></code></p>
        </div>
        <div class="alert alert-warn" style="margin-top:18px">
            <strong>İpucu:</strong> DirectAdmin → MySQL Yönetimi'nden veritabanını ve kullanıcısını oluşturduğundan, kullanıcıya tam yetki verdiğinden emin ol. Düzelttikten sonra bu sayfayı yenile.
        </div>

    <?php elseif ($daha_once_kuruldu): ?>
        <div class="alert alert-ok">
            <strong>✅ Kurulum daha önce tamamlanmış.</strong><br>
            Sistem şu anda kurulumun yeniden çalıştırılmasına izin vermiyor.
        </div>
        <div class="alert alert-warn">
            <strong>⚠️ Güvenlik uyarısı:</strong> <span class="danger">Hâlâ <code>kurulum.php</code> dosyasını silmediysen şimdi sil.</span> Bu sayfanın sunucuda durması gereksiz risktir.
        </div>
        <div style="margin-top:14px">
            <a href="/" class="btn btn-pri">→ Ana Sayfa</a>
            <a href="/admin/" class="btn btn-out">→ Yönetim Paneli</a>
            <a href="?yeniden=1" class="btn btn-out" onclick="return confirm('Kilit kaldırılsın ve kurulum tekrar çalışsın mı? (Var olan tablolara dokunulmaz, eksikler eklenir.)')">🔄 Yeniden Çalıştır</a>
        </div>

    <?php else: ?>
        <?php foreach ($logs as [$tip,$msg]): ?>
            <div class="log <?= $tip==='err'?'err':'' ?>"><?= $msg ?></div>
        <?php endforeach; ?>

        <?php if ($tum_basarili): ?>
            <div class="alert alert-warn" style="margin-top:18px">
                <strong>⚠️ ÖNEMLİ:</strong> <span class="danger">Şimdi FTP veya DirectAdmin Dosya Yöneticisi ile <code>kurulum.php</code> dosyasını sun­ucudan SİL.</span>
            </div>
            <div style="margin-top:14px">
                <a href="/" class="btn btn-pri">→ Ana Sayfa</a>
                <a href="/admin/" class="btn btn-out">→ Yönetim Paneli</a>
            </div>
        <?php else: ?>
            <div class="alert alert-err" style="margin-top:18px">
                <strong>Kurulum sırasında hata oluştu.</strong> Yukarıdaki mesajları kontrol et. Düzelttikten sonra sayfayı yenile.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</div>
</body>
</html>
