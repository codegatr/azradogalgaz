-- Migration 002: Bakım Hatırlatıcıları
-- Tarih: 2026-04-28
-- Açıklama: Kombi/klima/kazan vb. periyodik bakım hatırlatma sistemi

CREATE TABLE IF NOT EXISTS bakim_hatirlaticilari (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cari_id                 INT UNSIGNED DEFAULT NULL,
    musteri_ad              VARCHAR(200) DEFAULT NULL,
    telefon                 VARCHAR(40) DEFAULT NULL,
    eposta                  VARCHAR(150) DEFAULT NULL,
    adres                   TEXT DEFAULT NULL,
    urun_tipi               ENUM('kombi','klima','kazan','sofben','termosifon','diger') DEFAULT 'kombi',
    marka                   VARCHAR(80) DEFAULT NULL,
    model                   VARCHAR(120) DEFAULT NULL,
    seri_no                 VARCHAR(80) DEFAULT NULL,
    kurulum_tarihi          DATE DEFAULT NULL,
    son_bakim_tarihi        DATE DEFAULT NULL,
    sonraki_bakim_tarihi    DATE DEFAULT NULL,
    periyot_ay              INT UNSIGNED DEFAULT 12,
    durum                   ENUM('aktif','pasif','tamamlandi') DEFAULT 'aktif',
    bildirim_gonderildi     TINYINT(1) DEFAULT 0,
    son_bildirim_tarihi     DATETIME DEFAULT NULL,
    notlar                  TEXT DEFAULT NULL,
    olusturma               DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bh_cari (cari_id),
    INDEX idx_bh_durum (durum),
    INDEX idx_bh_sonraki (sonraki_bakim_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
