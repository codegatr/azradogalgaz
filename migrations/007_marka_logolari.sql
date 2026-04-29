-- Migration 007: Marka Logoları
-- Tarih: 2026-04-29
-- Açıklama: 7 yetkili marka için logo path'leri DB'ye yazılır (assets/img/markalar/[slug].png).
-- Sadece logo NULL veya boş olan kayıtlar güncellenir — admin manuel logo yüklediyse korunur.

UPDATE markalar SET logo='assets/img/markalar/demirdokum.png'
    WHERE slug='demirdokum' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/bosch.png'
    WHERE slug='bosch' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/vaillant.png'
    WHERE slug='vaillant' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/baymak.png'
    WHERE slug='baymak' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/buderus.png'
    WHERE slug='buderus' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/mitsubishi.png'
    WHERE slug='mitsubishi' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/daikin.png'
    WHERE slug='daikin' AND (logo IS NULL OR logo='');
