-- Migration 003: Kullanıcı Adı (Username)
-- Tarih: 2026-04-28
-- Açıklama: kullanicilar tablosuna kullanici_adi kolonu ekle, e-posta yerine veya yanı sıra giriş için.
-- Mevcut kullanıcılar için e-posta'nın @ öncesi otomatik atanır.

-- Kolonu ekle (yoksa)
ALTER TABLE kullanicilar ADD COLUMN IF NOT EXISTS kullanici_adi VARCHAR(60) DEFAULT NULL AFTER eposta;

-- UNIQUE index ekle (yoksa)
ALTER TABLE kullanicilar ADD UNIQUE INDEX IF NOT EXISTS idx_kul_kadi (kullanici_adi);

-- Mevcut NULL kayıtlar için: e-posta'nın @ öncesi kısmı + id (çakışma engelleme)
UPDATE kullanicilar
SET kullanici_adi = CONCAT(SUBSTRING_INDEX(eposta, '@', 1), '_', id)
WHERE kullanici_adi IS NULL OR kullanici_adi = '';
