-- Migration 004: SMTP & Bakım Bildirim Ayarları
-- Tarih: 2026-04-28
-- Açıklama: ayarlar tablosuna SMTP yapılandırması ve bakım bildirim varsayılanları

INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES
    ('smtp_host', ''),
    ('smtp_port', '587'),
    ('smtp_user', ''),
    ('smtp_sifre', ''),
    ('smtp_secure', 'tls'),
    ('smtp_gonderen_eposta', ''),
    ('smtp_gonderen_ad', ''),
    ('bakim_bildirim_aktif', '1'),
    ('bakim_bildirim_gun', '15'),
    ('cron_anahtar', SUBSTRING(MD5(RAND()) FROM 1 FOR 24));

-- bakim_bildirim_log tablosu (tekrar göndermeyi engellemek için)
CREATE TABLE IF NOT EXISTS bakim_bildirim_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bakim_id        INT UNSIGNED NOT NULL,
    eposta          VARCHAR(150) NOT NULL,
    konu            VARCHAR(255) DEFAULT NULL,
    sonuc           ENUM('basarili','hata') DEFAULT 'basarili',
    hata_mesaji     TEXT DEFAULT NULL,
    gonderim        DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bbl_bakim (bakim_id),
    INDEX idx_bbl_tarih (gonderim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
