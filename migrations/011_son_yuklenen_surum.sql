-- Migration 011: Son yüklenen sürüm fallback (v1.12.26)
-- Açıklama: manifest.json bozulduğunda veya yazılamadığında "Yüklü sürüm: v0.0.0"
--           gösterimini önlemek için ayarlar tablosuna fallback değer.
-- Idempotent: INSERT IGNORE.

INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES
    ('son_yuklenen_surum', '1.12.26'),
    ('son_yukleme_tarihi', NOW());

-- Eğer eski boş kayıt varsa şimdi v1.12.26'ya getir (önceki migration'lardan kalmış olabilir)
UPDATE ayarlar SET deger='1.12.26' WHERE anahtar='son_yuklenen_surum' AND (deger='' OR deger='0.0.0');
