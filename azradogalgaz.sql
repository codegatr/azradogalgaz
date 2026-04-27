-- ============================================================
-- Azra Doğalgaz — Veritabanı Şeması ve Demo Verileri
-- ============================================================
-- Sürüm     : 1.1.0
-- Tarih     : 2026-04-27
-- PHP       : 8.1+
-- MySQL     : 5.7+ / MariaDB 10.3+
-- Karakter  : utf8mb4_unicode_ci
--
-- KULLANIM (phpMyAdmin):
--   1) DirectAdmin → MySQL Yönetimi'nden veritabanı oluştur (örn: kullanici_azra)
--   2) phpMyAdmin'i aç, oluşturduğun veritabanını seç
--   3) "İçe Aktar" sekmesine git
--   4) Bu dosyayı (azradogalgaz.sql) yükle ve "Başlat"a bas
--   5) config.php içindeki DB ayarlarını gir
--   6) https://azradogalgaz.com/admin/ ile giriş yap
--      Kullanıcı: admin@azradogalgaz.com
--      Şifre   : Azra2026!  (ilk girişte değiştir!)
--
-- NOT: Bu dosya istediğin zaman güvenlidir, içinde DB seçimi yoktur.
--      Hangi DB'yi seçersen oraya yüklenir.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

/* ============================================================
   T A B L O L A R
   ============================================================ */

-- ----------- AYARLAR -----------
CREATE TABLE IF NOT EXISTS `ayarlar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `anahtar` VARCHAR(80) NOT NULL UNIQUE,
    `deger` MEDIUMTEXT NULL,
    `guncelleme_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- KULLANICILAR -----------
CREATE TABLE IF NOT EXISTS `kullanicilar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad` VARCHAR(80) NOT NULL,
    `eposta` VARCHAR(160) NOT NULL UNIQUE,
    `sifre` VARCHAR(255) NOT NULL,
    `rol` ENUM('admin','editor','muhasebe') DEFAULT 'admin',
    `aktif` TINYINT(1) DEFAULT 1,
    `son_giris` DATETIME NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- HİZMET KATEGORİLERİ -----------
CREATE TABLE IF NOT EXISTS `hizmet_kategorileri` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(160) NOT NULL UNIQUE,
    `ikon` VARCHAR(60) NULL,
    `sira` INT DEFAULT 0,
    `aktif` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- HİZMETLER -----------
CREATE TABLE IF NOT EXISTS `hizmetler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kategori_id` INT UNSIGNED NULL,
    `baslik` VARCHAR(180) NOT NULL,
    `slug` VARCHAR(220) NOT NULL UNIQUE,
    `kisa_aciklama` VARCHAR(300) NULL,
    `icerik` MEDIUMTEXT NULL,
    `gorsel` VARCHAR(255) NULL,
    `meta_baslik` VARCHAR(200) NULL,
    `meta_aciklama` VARCHAR(300) NULL,
    `sira` INT DEFAULT 0,
    `aktif` TINYINT(1) DEFAULT 1,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `guncelleme_tarihi` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_kategori` (`kategori_id`),
    INDEX `idx_aktif` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- MARKALAR -----------
CREATE TABLE IF NOT EXISTS `markalar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad` VARCHAR(80) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `logo` VARCHAR(255) NULL,
    `aktif` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- ÜRÜN KATEGORİLERİ -----------
CREATE TABLE IF NOT EXISTS `urun_kategorileri` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(160) NOT NULL UNIQUE,
    `ust_id` INT UNSIGNED NULL,
    `sira` INT DEFAULT 0,
    `aktif` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- ÜRÜNLER -----------
CREATE TABLE IF NOT EXISTS `urunler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kategori_id` INT UNSIGNED NULL,
    `marka_id` INT UNSIGNED NULL,
    `ad` VARCHAR(220) NOT NULL,
    `slug` VARCHAR(260) NOT NULL UNIQUE,
    `sku` VARCHAR(80) NULL UNIQUE,
    `kisa_aciklama` VARCHAR(400) NULL,
    `aciklama` MEDIUMTEXT NULL,
    `ozellikler` MEDIUMTEXT NULL,
    `fiyat` DECIMAL(12,2) DEFAULT 0,
    `indirimli_fiyat` DECIMAL(12,2) NULL,
    `kdv_orani` TINYINT DEFAULT 20,
    `stok` INT DEFAULT 0,
    `gorsel` VARCHAR(255) NULL,
    `galeri` MEDIUMTEXT NULL,
    `meta_baslik` VARCHAR(200) NULL,
    `meta_aciklama` VARCHAR(300) NULL,
    `one_cikan` TINYINT(1) DEFAULT 0,
    `aktif` TINYINT(1) DEFAULT 1,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `guncelleme_tarihi` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_kategori` (`kategori_id`),
    INDEX `idx_marka` (`marka_id`),
    INDEX `idx_aktif` (`aktif`),
    INDEX `idx_one` (`one_cikan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- KAMPANYALAR -----------
CREATE TABLE IF NOT EXISTS `kampanyalar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `baslik` VARCHAR(220) NOT NULL,
    `slug` VARCHAR(260) NOT NULL UNIQUE,
    `kisa_aciklama` VARCHAR(400) NULL,
    `icerik` MEDIUMTEXT NULL,
    `gorsel` VARCHAR(255) NULL,
    `nakit_fiyat` DECIMAL(12,2) NULL,
    `kart_fiyat` DECIMAL(12,2) NULL,
    `taksit_sayisi` INT DEFAULT 0,
    `baslangic` DATE NULL,
    `bitis` DATE NULL,
    `meta_baslik` VARCHAR(200) NULL,
    `meta_aciklama` VARCHAR(300) NULL,
    `aktif` TINYINT(1) DEFAULT 1,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- BLOG -----------
CREATE TABLE IF NOT EXISTS `blog_yazilari` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `baslik` VARCHAR(220) NOT NULL,
    `slug` VARCHAR(260) NOT NULL UNIQUE,
    `ozet` VARCHAR(500) NULL,
    `icerik` MEDIUMTEXT NULL,
    `gorsel` VARCHAR(255) NULL,
    `yazar` VARCHAR(120) DEFAULT 'Azra Doğalgaz',
    `meta_baslik` VARCHAR(200) NULL,
    `meta_aciklama` VARCHAR(300) NULL,
    `etiketler` VARCHAR(400) NULL,
    `goruntulenme` INT UNSIGNED DEFAULT 0,
    `aktif` TINYINT(1) DEFAULT 1,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `yayin_tarihi` DATETIME NULL,
    INDEX `idx_aktif` (`aktif`),
    INDEX `idx_yayin` (`yayin_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- İLETİŞİM -----------
CREATE TABLE IF NOT EXISTS `iletisim_mesajlari` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad_soyad` VARCHAR(160) NOT NULL,
    `eposta` VARCHAR(160) NULL,
    `telefon` VARCHAR(40) NULL,
    `konu` VARCHAR(200) NULL,
    `mesaj` TEXT NOT NULL,
    `ip` VARCHAR(45) NULL,
    `durum` ENUM('yeni','okundu','arandi','kapali') DEFAULT 'yeni',
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_durum` (`durum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- CARİLER (ERP) -----------
CREATE TABLE IF NOT EXISTS `cariler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cari_kodu` VARCHAR(40) NOT NULL UNIQUE,
    `unvan` VARCHAR(220) NOT NULL,
    `tip` ENUM('bireysel','kurumsal') DEFAULT 'bireysel',
    `tckn_vkn` VARCHAR(20) NULL,
    `vergi_dairesi` VARCHAR(120) NULL,
    `telefon` VARCHAR(40) NULL,
    `telefon_2` VARCHAR(40) NULL,
    `eposta` VARCHAR(160) NULL,
    `il` VARCHAR(60) NULL,
    `ilce` VARCHAR(80) NULL,
    `adres` TEXT NULL,
    `bakiye` DECIMAL(14,2) DEFAULT 0,
    `notlar` TEXT NULL,
    `aktif` TINYINT(1) DEFAULT 1,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `guncelleme_tarihi` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_unvan` (`unvan`),
    INDEX `idx_tel` (`telefon`),
    INDEX `idx_aktif` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cari_hareketler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cari_id` INT UNSIGNED NOT NULL,
    `tarih` DATE NOT NULL,
    `tip` ENUM('borc','alacak','tahsilat','odeme') NOT NULL,
    `belge_tip` ENUM('manuel','fatura','fis','tahsilat','odeme','iade') DEFAULT 'manuel',
    `belge_id` INT UNSIGNED NULL,
    `belge_no` VARCHAR(60) NULL,
    `aciklama` VARCHAR(300) NULL,
    `tutar` DECIMAL(14,2) NOT NULL,
    `olusturan_id` INT UNSIGNED NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cari` (`cari_id`),
    INDEX `idx_tarih` (`tarih`),
    INDEX `idx_belge` (`belge_tip`, `belge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- FATURALAR (ERP) -----------
CREATE TABLE IF NOT EXISTS `faturalar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cari_id` INT UNSIGNED NOT NULL,
    `fatura_no` VARCHAR(40) NOT NULL UNIQUE,
    `tip` ENUM('satis','alis','iade_satis','iade_alis') DEFAULT 'satis',
    `tarih` DATE NOT NULL,
    `vade_tarihi` DATE NULL,
    `ara_toplam` DECIMAL(14,2) DEFAULT 0,
    `iskonto` DECIMAL(14,2) DEFAULT 0,
    `kdv_toplam` DECIMAL(14,2) DEFAULT 0,
    `genel_toplam` DECIMAL(14,2) DEFAULT 0,
    `odeme_durumu` ENUM('odenmedi','kismi','odendi') DEFAULT 'odenmedi',
    `odenen` DECIMAL(14,2) DEFAULT 0,
    `notlar` TEXT NULL,
    `olusturan_id` INT UNSIGNED NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `guncelleme_tarihi` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cari` (`cari_id`),
    INDEX `idx_tarih` (`tarih`),
    INDEX `idx_odeme` (`odeme_durumu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fatura_kalemleri` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fatura_id` INT UNSIGNED NOT NULL,
    `urun_id` INT UNSIGNED NULL,
    `ad` VARCHAR(220) NOT NULL,
    `miktar` DECIMAL(12,3) DEFAULT 1,
    `birim` VARCHAR(20) DEFAULT 'Adet',
    `birim_fiyat` DECIMAL(14,2) DEFAULT 0,
    `iskonto_yuzde` DECIMAL(5,2) DEFAULT 0,
    `kdv_orani` TINYINT DEFAULT 20,
    `toplam` DECIMAL(14,2) DEFAULT 0,
    INDEX `idx_fatura` (`fatura_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- FİŞLER (ERP) -----------
CREATE TABLE IF NOT EXISTS `fisler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cari_id` INT UNSIGNED NULL,
    `fis_no` VARCHAR(40) NOT NULL UNIQUE,
    `tip` ENUM('satis','tahsilat','odeme','gider','gelir') DEFAULT 'satis',
    `tarih` DATE NOT NULL,
    `aciklama` VARCHAR(300) NULL,
    `ara_toplam` DECIMAL(14,2) DEFAULT 0,
    `kdv_toplam` DECIMAL(14,2) DEFAULT 0,
    `genel_toplam` DECIMAL(14,2) DEFAULT 0,
    `odeme_yontemi` ENUM('nakit','kart','havale','cek','senet') DEFAULT 'nakit',
    `notlar` TEXT NULL,
    `olusturan_id` INT UNSIGNED NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cari` (`cari_id`),
    INDEX `idx_tarih` (`tarih`),
    INDEX `idx_tip` (`tip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fis_kalemleri` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fis_id` INT UNSIGNED NOT NULL,
    `urun_id` INT UNSIGNED NULL,
    `ad` VARCHAR(220) NOT NULL,
    `miktar` DECIMAL(12,3) DEFAULT 1,
    `birim` VARCHAR(20) DEFAULT 'Adet',
    `birim_fiyat` DECIMAL(14,2) DEFAULT 0,
    `kdv_orani` TINYINT DEFAULT 20,
    `toplam` DECIMAL(14,2) DEFAULT 0,
    INDEX `idx_fis` (`fis_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- STOK (ERP) -----------
CREATE TABLE IF NOT EXISTS `stok_hareketleri` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `urun_id` INT UNSIGNED NOT NULL,
    `tarih` DATE NOT NULL,
    `tip` ENUM('giris','cikis','sayim','iade') NOT NULL,
    `miktar` DECIMAL(12,3) NOT NULL,
    `belge_tip` VARCHAR(40) NULL,
    `belge_id` INT UNSIGNED NULL,
    `aciklama` VARCHAR(300) NULL,
    `olusturan_id` INT UNSIGNED NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_urun` (`urun_id`),
    INDEX `idx_tarih` (`tarih`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- LOG -----------
CREATE TABLE IF NOT EXISTS `log_kayitlari` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kullanici_id` INT UNSIGNED NULL,
    `tip` VARCHAR(40) NOT NULL,
    `mesaj` VARCHAR(500) NOT NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tip` (`tip`),
    INDEX `idx_tarih` (`olusturma_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guncelleme_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `eski_surum` VARCHAR(20) NULL,
    `yeni_surum` VARCHAR(20) NULL,
    `durum` ENUM('basarili','hata') DEFAULT 'basarili',
    `detay` TEXT NULL,
    `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/* ============================================================
   V A R S A Y I L A N    V E R İ L E R
   ============================================================ */

-- ----------- AYARLAR -----------
INSERT INTO `ayarlar` (`anahtar`, `deger`) VALUES
('site_baslik', 'Azra Doğalgaz — Konforlu Yaşam, Güvenli Gelecek'),
('site_aciklama', 'İzmir''de Demirdöküm Ademix kombi, klima ve doğalgaz tesisat hizmetleri. İzmirgaz uyumlu, garantili kurulum, 7/24 teknik destek.'),
('site_anahtar_kelime', 'azra doğalgaz, izmir kombi, demirdöküm ademix, izmirgaz uyumlu tesisat, kombi montaj izmir, klima montaj, doğalgaz tesisat'),
('firma_unvan', 'Azra Doğalgaz Tesisat'),
('firma_telefon_1', '0546 790 78 77'),
('firma_telefon_2', '0546 820 60 80'),
('firma_eposta', 'info@azradogalgaz.com'),
('firma_adres', 'İzmir, Türkiye'),
('firma_calisma_saatleri', 'Pzt-Cmt 08:00-20:00'),
('sosyal_facebook', ''),
('sosyal_instagram', ''),
('sosyal_youtube', ''),
('sosyal_x', ''),
('whatsapp_numara', '905467907877'),
('google_analytics', ''),
('google_search_console_meta', ''),
('harita_iframe', ''),
('aktif_kampanya_id', ''),
('kvkk_metni', ''),
('gizlilik_metni', ''),
('github_repo', 'codegatr/azradogalgaz'),
('github_token', ''),
('guncel_surum', '1.1.0');

-- ----------- KULLANICI: admin@azradogalgaz.com / Azra2026! -----------
-- Şifre bcrypt ile hashlenmiştir; ilk girişten sonra mutlaka değiştir.
INSERT INTO `kullanicilar` (`ad`, `eposta`, `sifre`, `rol`, `aktif`) VALUES
('Sistem Yöneticisi', 'admin@azradogalgaz.com', '$2y$10$WyBTlEK79QxPplRcC7BDuuFdrKUvfLOTOMAOgJjwMhgpJu4zGmoPy', 'admin', 1);

-- ----------- HİZMET KATEGORİLERİ -----------
INSERT INTO `hizmet_kategorileri` (`ad`, `slug`, `ikon`, `sira`, `aktif`) VALUES
('Doğalgaz Tesisatı', 'dogalgaz-tesisati', 'flame', 1, 1),
('Klima Montajı', 'klima-montaji', 'snowflake', 2, 1),
('Tesisat Hizmetleri', 'tesisat-hizmetleri', 'wrench', 3, 1),
('Kombi Servisi', 'kombi-servisi', 'tools', 4, 1);

-- ----------- MARKALAR -----------
INSERT INTO `markalar` (`ad`, `slug`, `aktif`) VALUES
('Demirdöküm', 'demirdokum', 1),
('Bosch', 'bosch', 1),
('Vaillant', 'vaillant', 1),
('Baymak', 'baymak', 1),
('Buderus', 'buderus', 1),
('Mitsubishi', 'mitsubishi', 1),
('Daikin', 'daikin', 1);

-- ----------- DEMO KAMPANYA -----------
INSERT INTO `kampanyalar`
(`baslik`, `slug`, `kisa_aciklama`, `icerik`, `nakit_fiyat`, `kart_fiyat`, `taksit_sayisi`, `meta_baslik`, `meta_aciklama`, `aktif`)
VALUES
(
'Azra Doğalgaz Süper Kombi Paketi — Demirdöküm Ademix 24 kW',
'azra-dogalgaz-super-kombi-paketi',
'Demirdöküm Ademix 24 kW tam yoğuşmalı kombi, 5 metre termopan petek, kombi dolabı, 50x100 havlupan, siyah boru ve proje dahil İzmirgaz uyumlu tesisat hizmeti.',
'<h2>Paket İçeriği</h2><ul><li>Demirdöküm Ademix 24 kW Tam Yoğuşmalı Kombi</li><li>5 Metre Termopan Petek</li><li>Kombi Dolabı</li><li>50x100 Havlupan</li><li>Siyah Boru + Proje Dahil</li><li>İzmirgaz Uyumlu Tesisat Hizmetleri</li></ul><p>Konforlu yaşam ve güvenli gelecek için Azra Doğalgaz garantisi.</p>',
80000.00,
87000.00,
6,
'İzmir Kombi Paketi 80.000 TL — Demirdöküm Ademix 24 kW | Azra Doğalgaz',
'İzmir''de Demirdöküm Ademix 24 kW tam yoğuşmalı kombi paketi 80.000 TL nakit. 5 m petek, kombi dolabı, havlupan, siyah boru, proje ve İzmirgaz uyumlu tesisat dahil.',
1
);

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- KURULUM TAMAMLANDI ✅
-- Sonraki adımlar:
--   1) config.php içindeki DB ayarlarını gir
--   2) /admin/ ile giriş yap (admin@azradogalgaz.com / Azra2026!)
--   3) İlk girişten sonra şifreyi değiştir!
-- ============================================================
