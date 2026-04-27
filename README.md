# Azra Doğalgaz Web Sistemi

İzmir merkezli Demirdöküm yetkili tesisat firması Azra Doğalgaz için PHP 8.1+ tabanlı kurumsal web sitesi + ERP arka planı.

## ⚙️ Aşama 1 — Temel Altyapı (Bu Pakette)

### 📁 Dosya Yapısı
```
azradogalgaz/
├── config.php           ← DB ayarları + global fonksiyonlar
├── kurulum.php          ← Veritabanı kurulum scripti
├── manifest.json        ← Sürüm bilgisi (GitHub güncelleme için)
├── index.php            ← Ana sayfa
├── 404.php
├── sitemap.php          ← Dinamik XML sitemap
├── robots.txt
├── .htaccess
├── inc/
│   ├── header.php       ← Tüm sayfalarda çağırılan başlık + nav
│   ├── footer.php       ← Footer + alt mobil bar + JS
│   └── functions.php    ← Yardımcı fonksiyonlar
└── assets/
    ├── css/style.css
    ├── js/, img/, uploads/
    └── ...
```

### 🚀 Kurulum Adımları

1. **Hosting'e yükle.** Tüm dosyaları DirectAdmin `public_html`'e yükle.

2. **Veritabanı oluştur.** DirectAdmin > MySQL Yönetimi'nden yeni bir veritabanı + kullanıcı oluştur.

3. **`config.php`'yi düzenle:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kullaniciadi_azra');     // DirectAdmin'de oluşturduğun isim
   define('DB_USER', 'kullaniciadi_azra');
   define('DB_PASS', 'GUCLU_SIFRENI_BURAYA_YAZ');
   ```

4. **Kurulum scriptini çalıştır.**
   `https://azradogalgaz.com/kurulum.php?token=azra-2026-kurulum-XXXXXXXX` adresine git
   (Token, `config.php` içindeki DB_NAME'in MD5'inin ilk 8 hanesi ile türer — `kurulum.php` zaten doğru token'ı sayfada gösterir.)

5. **Yönetici girişi:**
   - URL: `/admin/` (Aşama 3'te eklenecek)
   - Kullanıcı: `admin@azradogalgaz.com`
   - Şifre: `Azra2026!` (ilk girişte değiştir)

6. **`kurulum.php` dosyasını mutlaka SİL** veya `.htaccess` ile engelle.

### ✅ Aşama 1'de Tamamlananlar

- [x] Veritabanı şeması (18 tablo): kurumsal + ERP (cari, fatura, fiş, stok)
- [x] Tam SEO altyapısı (canonical, OG, Twitter, JSON-LD HVACBusiness, FAQPage, Product schema)
- [x] Dinamik XML sitemap
- [x] robots.txt
- [x] Temiz URL yapısı (`/hizmet/...`, `/urun/...`, `/kampanya/...`, `/blog/...`)
- [x] HTTPS yönlendirmesi, gzip, cache, güvenlik başlıkları
- [x] Marka kimliğine uygun frontend (broşür renk paleti)
- [x] Sticky header + alt mobil bar (Yunus'un imza navigasyonu)
- [x] Sabit WhatsApp CTA
- [x] Ana sayfa: Hero + Kampanya kartı + Hizmetler + Neden Biz + Öne Çıkan Ürünler + CTA
- [x] CSRF altyapısı, PDO prepared statements, password_hash
- [x] Tüm Türkçe karakterler için utf8mb4_unicode_ci
- [x] Mobil-öncelikli responsive tasarım

### 🔜 Sonraki Aşamalar

- **Aşama 2:** Frontend sayfaları (hizmetler, ürünler, kampanyalar, blog, hakkımızda, iletişim)
- **Aşama 3:** Admin panel + içerik yönetimi
- **Aşama 4:** ERP modülleri (cari, fatura, fiş, stok, raporlar)
- **Aşama 5:** GitHub güncelleme sistemi + nihai optimizasyon

---

**CODEGA — codega.com.tr**
