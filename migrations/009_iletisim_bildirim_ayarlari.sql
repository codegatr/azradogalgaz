-- Migration 009: İletişim formu bildirim ayarları
-- Tarih: 2026-04-29
-- Açıklama: api/iletisim-gonder.php SMTP class kullanarak yöneticiye HTML bildirim
--           gönderir + müşteriye opsiyonel teşekkür maili gönderir.
--           Bu migration default ayar değerlerini DB'ye ekler.
-- Idempotent: birden fazla kez çalışsa zarar vermez (INSERT IGNORE).

-- (1) Bildirim alıcı e-postası — boş ise firma_eposta'ya fallback eder.
--     Yunus farklı yönetici adresi kullanmak isterse admin'den buraya yazar.
INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES
    ('iletisim_bildirim_eposta', '');

-- (2) Müşteriye otomatik teşekkür e-postası gönderilsin mi (1=evet, 0=hayır).
--     Default açık. Müşteri e-posta verdiğinde tetiklenir.
INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES
    ('iletisim_musteri_onay', '1');
