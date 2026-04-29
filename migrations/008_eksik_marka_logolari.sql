-- Migration 008: Eksik marka logoları
-- Tarih: 2026-04-29
-- Açıklama: Yunus'un admin'den sonradan eklediği 5 marka için (ECA, Mitsubishi Electric,
--           Mitsubishi Heavy, Arçelik, Beko) logo path'lerini set eder.
--           Sadece logo'su boş/NULL olanları günceller — kullanıcı tanımlı logo varsa dokunmaz.
-- Idempotent: birden fazla kez çalışsa zarar vermez.

UPDATE markalar SET logo='assets/img/markalar/eca.png'
  WHERE slug='eca' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/mitsubishi-electric.png'
  WHERE slug='mitsubishi-electric' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/mitsubishi-heavy.png'
  WHERE slug='mitsubishi-heavy' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/arcelik.png'
  WHERE slug='arcelik' AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/beko.png'
  WHERE slug='beko' AND (logo IS NULL OR logo='');

-- Olası slug varyasyonları (admin formundan farklı yazımlar)
UPDATE markalar SET logo='assets/img/markalar/eca.png'
  WHERE slug IN ('e-c-a','eca-kombi') AND (logo IS NULL OR logo='');

UPDATE markalar SET logo='assets/img/markalar/mitsubishi-heavy.png'
  WHERE slug IN ('mitsubishi-heavy-industries','mitsubishi-agir-sanayi') AND (logo IS NULL OR logo='');
