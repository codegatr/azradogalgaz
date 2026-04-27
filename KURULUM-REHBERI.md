# 🔧 Azra Doğalgaz — Kurulum Rehberi

Veritabanını **iki farklı yoldan** kurabilirsin. İkisi de aynı sonucu verir, hangisi sana kolaysa onu seç.

---

## ⚡ Yöntem 1 — phpMyAdmin ile import (TAVSİYE EDİLEN)

En hızlısı bu. Tahminen 1 dakika sürer.

1. **DirectAdmin** → "MySQL Yönetimi" → **Yeni Veritabanı Oluştur**
   - Veritabanı adı: `azradoga_web` (DirectAdmin önüne kullanıcı adını ekleyecek)
   - Kullanıcı adı: `azradoga_web`
   - Şifre: güçlü bir şifre seç, kenara kaydet

2. **DirectAdmin** → "phpMyAdmin" linkini aç → solda az önce oluşturduğun **veritabanına tıkla**

3. Üstteki **"İçe Aktar"** sekmesine geç → **"Dosya Seç"** ile `azradogalgaz.sql` dosyasını yükle → en alttaki **"İçe Aktar"** veya **"Başlat"** butonuna bas

4. "İçe aktarma başarıyla tamamlandı" mesajını gördüysen tablolar hazır. ✅

5. `config.php` dosyasını aç ve aşağıdaki dört satırı düzenle:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kullaniciadi_azradoga_web');
   define('DB_USER', 'kullaniciadi_azradoga_web');
   define('DB_PASS', 'AZ ÖNCE BELİRLEDİĞİN ŞİFRE');
   ```
   > ⚠️ DirectAdmin DB adlarına otomatik olarak hesap kullanıcı adını **ön ek** olarak koyar. Doğru tam adı phpMyAdmin'de tablonun üstünde gördüğün isimden kopyala.

6. Tarayıcıda `https://azradogalgaz.com/` adresini aç → siteyi görmelisin

7. **`kurulum.php` dosyasını sunucudan SİL** — kullanmadık ama dursun istemeyiz.

8. `https://azradogalgaz.com/admin/` ile giriş yap:
   - Kullanıcı: `admin@azradogalgaz.com`
   - Şifre: `Azra2026!`
   - **İlk girişten sonra mutlaka şifreyi değiştir.** *(Admin paneli Aşama 3'te geliyor.)*

---

## 🌐 Yöntem 2 — `kurulum.php` ile webden kur

Bu yol da çalışır, daha "akıllı" — DB ayarlarını tek tek kontrol eder, hata varsa açıkça söyler.

1. Yöntem 1'in **1 ve 5. adımlarını** yap (DB oluştur, `config.php` ayarla)

2. `https://azradogalgaz.com/kurulum.php` aç

3. Sayfa otomatik olarak:
   - DB bağlantısını kontrol eder
   - Bağlanamazsa hatayı net şekilde gösterir
   - Bağlanırsa tabloları oluşturur, demo verileri ekler, admini yaratır
   - Sonunda kilit dosyası oluşturur, ikinci kez çalışmasını engeller

4. **`kurulum.php` dosyasını sunucudan SİL** ❗ (Çok önemli, dursun istemiyoruz.)

5. `/admin/` ile giriş yap.

> 🔄 Yeniden kurulum gerekirse: `https://azradogalgaz.com/kurulum.php?yeniden=1` — kilidi açar, yeniden çalıştırır.

---

## 🚨 Sık Karşılaşılan Hatalar

### "kurulum.php 404 Not Found"
Eski `.htaccess` dosyası `kurulum.php`'yi yasaklıyordu. **Bu pakettte düzeltildi.** Yeni `.htaccess`'i yüklediğinden emin ol.

### "DB Bağlantı Hatası: Access denied"
- DB kullanıcısının yetkileri eksik. DirectAdmin'de kullanıcıyı veritabanına yetkilendirdiğinden emin ol.
- Şifrede özel karakter varsa `'` yerine `"` ile sar veya escape et.

### "Tablo zaten mevcut"
Endişelenme — `CREATE TABLE IF NOT EXISTS` olduğu için zarar vermez. Mevcut veriler korunur.

### "lockdown-install.js... SES Removing unpermitted intrinsics"
Bu **bizim siteyle ilgili değil**. Tarayıcındaki bir cüzdan eklentisi (MetaMask, Phantom, vb.) yazdırıyor. Yok say.

---

## 📋 Kurulum Sonrası Kontrol Listesi

- [ ] `kurulum.php` ve `azradogalgaz.sql` dosyalarını sunucudan SİL
- [ ] Ana sayfa düzgün açılıyor mu?
- [ ] `/kampanya/azra-dogalgaz-super-kombi-paketi` açılıyor mu?
- [ ] `/iletisim` formundan test mesajı gönder, phpMyAdmin'den `iletisim_mesajlari` tablosunda görüyor musun?
- [ ] Google Search Console'a siteyi ekle ve `sitemap.xml`'i gönder
- [ ] `assets/uploads/` klasörünün yazma izni var mı? (chmod 755)

Sorun olursa bana net hata mesajıyla yaz, hemen müdahale ederim.

— **CODEGA** · codega.com.tr
