-- Migration 006: Teklif Sistemi
-- Tarih: 2026-04-29
-- Açıklama: Müşterilere teklif sunmak için 3 yeni tablo + ayarlar.
-- teklifler: ana teklif kayıtları (cariye opsiyonel bağlanır, snapshot tutar)
-- teklif_kalemleri: tekliflerin satır kalemleri
-- teklif_log: teklif olay logu (oluşturuldu, gönderildi, görüntülendi, kabul, red)

CREATE TABLE IF NOT EXISTS teklifler (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teklif_no       VARCHAR(40) NOT NULL UNIQUE,
    cari_id         INT UNSIGNED DEFAULT NULL,           -- mevcut cariye bağlandıysa
    musteri_ad      VARCHAR(200) NOT NULL,                -- snapshot (cari değişse de teklifte kalır)
    musteri_telefon VARCHAR(40) DEFAULT NULL,
    musteri_eposta  VARCHAR(150) DEFAULT NULL,
    musteri_adres   TEXT DEFAULT NULL,
    konu            VARCHAR(255) NOT NULL,
    teklif_tarihi   DATE NOT NULL,
    gecerlilik_tarihi DATE NOT NULL,
    durum           ENUM('taslak','gonderildi','goruntulendi','kabul','red','iptal','faturalandi') DEFAULT 'taslak',
    para_birimi     VARCHAR(5) DEFAULT 'TL',
    ara_toplam      DECIMAL(14,2) DEFAULT 0.00,
    iskonto_tutar   DECIMAL(14,2) DEFAULT 0.00,
    kdv_toplam      DECIMAL(14,2) DEFAULT 0.00,
    genel_toplam    DECIMAL(14,2) DEFAULT 0.00,
    notlar          TEXT DEFAULT NULL,                    -- müşteriye görünür notlar
    sartlar         TEXT DEFAULT NULL,                    -- ödeme/teslim şartları
    public_token    VARCHAR(64) DEFAULT NULL UNIQUE,      -- müşteri public link için
    olusturan_id    INT UNSIGNED DEFAULT NULL,
    olusturma       DATETIME DEFAULT CURRENT_TIMESTAMP,
    guncelleme      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tek_cari (cari_id),
    INDEX idx_tek_durum (durum),
    INDEX idx_tek_tarih (teklif_tarihi),
    INDEX idx_tek_token (public_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teklif_kalemleri (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teklif_id       INT UNSIGNED NOT NULL,
    sira            INT DEFAULT 0,
    urun_id         INT UNSIGNED DEFAULT NULL,
    aciklama        VARCHAR(500) NOT NULL,
    miktar          DECIMAL(10,3) DEFAULT 1.000,
    birim           VARCHAR(20) DEFAULT 'Adet',
    birim_fiyat     DECIMAL(14,2) DEFAULT 0.00,
    iskonto_yuzde   DECIMAL(5,2) DEFAULT 0.00,
    kdv_orani       DECIMAL(5,2) DEFAULT 20.00,
    toplam          DECIMAL(14,2) DEFAULT 0.00,
    INDEX idx_tk_teklif (teklif_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teklif_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teklif_id       INT UNSIGNED NOT NULL,
    olay            VARCHAR(50) NOT NULL,
    aciklama        VARCHAR(500) DEFAULT NULL,
    ip              VARCHAR(45) DEFAULT NULL,
    user_agent      VARCHAR(255) DEFAULT NULL,
    olusturma       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tlog_teklif (teklif_id),
    INDEX idx_tlog_olay (olay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan ayarlar
INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES
    ('teklif_varsayilan_gecerlilik_gun', '15'),
    ('teklif_varsayilan_kdv', '20'),
    ('teklif_varsayilan_sartlar', 'Bu teklif düzenlendiği tarihten itibaren 15 gün geçerlidir.\nKurulum sonrası ödeme: %50 kapora, %50 kurulum sonrası.\nTeklif fiyatlarına KDV dahildir.\nFiyatlar TL bazlıdır, döviz kuru oynaması durumunda revize edilebilir.');
