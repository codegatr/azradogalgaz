-- Migration 010: SEO altyapısı - IndexNow + sosyal medya ayarları
-- Tarih: 2026-04-29
-- Açıklama: v1.12.25 SEO uyumluluğu için ayar tablosuna eklenen key'ler.
--           IndexNow anahtarı boş başlar — ilk kullanımda otomatik üretilir.
--           Sosyal medya linkleri Schema.org sameAs için kullanılır.
-- Idempotent: INSERT IGNORE.

INSERT IGNORE INTO ayarlar (anahtar, deger) VALUES
    ('indexnow_anahtar', ''),
    ('sosyal_facebook',  ''),
    ('sosyal_instagram', ''),
    ('sosyal_twitter',   ''),
    ('sosyal_youtube',   ''),
    ('sosyal_linkedin',  '');
