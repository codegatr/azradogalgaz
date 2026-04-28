-- Migration 001: Cari & Muhasebe Modülleri
-- Tarih: 2026-04-28
-- Açıklama: Cariler, Cari Hareketleri, Faturalar, Fatura Kalemleri, Fişler, Fiş Kalemleri tabloları

CREATE TABLE IF NOT EXISTS cariler (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cari_kodu       VARCHAR(40) NOT NULL UNIQUE,
    unvan           VARCHAR(200) NOT NULL,
    tip             ENUM('bireysel','kurumsal') DEFAULT 'bireysel',
    tckn_vkn        VARCHAR(20) DEFAULT NULL,
    vergi_dairesi   VARCHAR(120) DEFAULT NULL,
    telefon         VARCHAR(40) DEFAULT NULL,
    telefon_2       VARCHAR(40) DEFAULT NULL,
    eposta          VARCHAR(150) DEFAULT NULL,
    il              VARCHAR(80) DEFAULT NULL,
    ilce            VARCHAR(80) DEFAULT NULL,
    adres           TEXT DEFAULT NULL,
    bakiye          DECIMAL(14,2) DEFAULT 0.00,
    notlar          TEXT DEFAULT NULL,
    aktif           TINYINT(1) DEFAULT 1,
    olusturma       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cari_unvan (unvan),
    INDEX idx_cari_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cari_hareketler (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cari_id         INT UNSIGNED NOT NULL,
    tarih           DATE NOT NULL,
    tip             ENUM('borc','alacak','tahsilat','odeme') NOT NULL,
    belge_tip       ENUM('manuel','fatura','fis','tahsilat','odeme') DEFAULT 'manuel',
    belge_id        INT UNSIGNED DEFAULT NULL,
    belge_no        VARCHAR(60) DEFAULT NULL,
    aciklama        VARCHAR(255) DEFAULT NULL,
    tutar           DECIMAL(14,2) NOT NULL,
    olusturan_id    INT UNSIGNED DEFAULT NULL,
    olusturma       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ch_cari (cari_id),
    INDEX idx_ch_tarih (tarih),
    INDEX idx_ch_belge (belge_tip, belge_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faturalar (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cari_id         INT UNSIGNED NOT NULL,
    fatura_no       VARCHAR(60) NOT NULL,
    tip             ENUM('satis','alis','iade_satis','iade_alis') DEFAULT 'satis',
    tarih           DATE NOT NULL,
    vade_tarihi     DATE DEFAULT NULL,
    ara_toplam      DECIMAL(14,2) DEFAULT 0.00,
    iskonto         DECIMAL(14,2) DEFAULT 0.00,
    kdv_toplam      DECIMAL(14,2) DEFAULT 0.00,
    genel_toplam    DECIMAL(14,2) DEFAULT 0.00,
    odeme_durumu    ENUM('odenmedi','kismi','odendi') DEFAULT 'odenmedi',
    odenen          DECIMAL(14,2) DEFAULT 0.00,
    notlar          TEXT DEFAULT NULL,
    olusturan_id    INT UNSIGNED DEFAULT NULL,
    olusturma       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fat_cari (cari_id),
    INDEX idx_fat_tarih (tarih),
    INDEX idx_fat_no (fatura_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fatura_kalemleri (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fatura_id       INT UNSIGNED NOT NULL,
    urun_id         INT UNSIGNED DEFAULT NULL,
    ad              VARCHAR(200) NOT NULL,
    miktar          DECIMAL(10,3) DEFAULT 1.000,
    birim           VARCHAR(20) DEFAULT 'adet',
    birim_fiyat     DECIMAL(14,2) DEFAULT 0.00,
    iskonto_yuzde   DECIMAL(5,2) DEFAULT 0.00,
    kdv_orani       DECIMAL(5,2) DEFAULT 20.00,
    toplam          DECIMAL(14,2) DEFAULT 0.00,
    INDEX idx_fk_fatura (fatura_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fisler (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cari_id         INT UNSIGNED DEFAULT NULL,
    fis_no          VARCHAR(60) NOT NULL,
    tip             ENUM('satis','tahsilat','odeme','gider','gelir') DEFAULT 'satis',
    tarih           DATE NOT NULL,
    aciklama        VARCHAR(255) DEFAULT NULL,
    ara_toplam      DECIMAL(14,2) DEFAULT 0.00,
    kdv_toplam      DECIMAL(14,2) DEFAULT 0.00,
    genel_toplam    DECIMAL(14,2) DEFAULT 0.00,
    odeme_yontemi   ENUM('nakit','kart','havale','cek','senet') DEFAULT 'nakit',
    olusturan_id    INT UNSIGNED DEFAULT NULL,
    olusturma       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fis_cari (cari_id),
    INDEX idx_fis_tarih (tarih),
    INDEX idx_fis_no (fis_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fis_kalemleri (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fis_id          INT UNSIGNED NOT NULL,
    urun_id         INT UNSIGNED DEFAULT NULL,
    ad              VARCHAR(200) NOT NULL,
    miktar          DECIMAL(10,3) DEFAULT 1.000,
    birim           VARCHAR(20) DEFAULT 'adet',
    birim_fiyat     DECIMAL(14,2) DEFAULT 0.00,
    kdv_orani       DECIMAL(5,2) DEFAULT 20.00,
    toplam          DECIMAL(14,2) DEFAULT 0.00,
    INDEX idx_fisk_fis (fis_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
