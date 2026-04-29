-- Migration 005: Yasal Metinler (KVKK, Gizlilik, Çerez, Mesafeli, İade)
-- Tarih: 2026-04-29
-- Açıklama: ayarlar tablosuna 5 hukuki metin eklenir.
-- Mevcut değer varsa korunur (INSERT IGNORE) — admin metinleri özelleştirebilir.
-- Eğer admin sıfırlamak isterse SET deger='' yapıp /sayfalar.php üstünden yeniden düzenleyebilir.

-- kvkk_metni
INSERT INTO ayarlar (anahtar, deger) VALUES ('kvkk_metni', '<h2>1. Veri Sorumlusunun Kimliği</h2>
<p>İşbu Aydınlatma Metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, veri sorumlusu sıfatıyla <strong>Azra Doğalgaz Tesisat</strong> ("Şirket") tarafından hazırlanmıştır.</p>
<ul>
  <li><strong>Adres:</strong> Laleli Menderes Caddesi No:392/C 35370 / Buca - İzmir</li>
  <li><strong>Telefon:</strong> 0546 790 78 77 — 0546 820 60 80</li>
  <li><strong>E-posta:</strong> info@azradogalgaz.com</li>
  <li><strong>Web:</strong> www.azradogalgaz.com</li>
</ul>

<h2>2. İşlenen Kişisel Veriler</h2>
<p>Şirketimiz tarafından, hizmet sunumu kapsamında aşağıdaki kişisel veri kategorileri işlenmektedir:</p>
<ul>
  <li><strong>Kimlik bilgileri:</strong> Ad, soyad, T.C. kimlik numarası (sözleşme zorunluluğu hâlinde)</li>
  <li><strong>İletişim bilgileri:</strong> Telefon numarası, e-posta adresi, ikamet/teslimat adresi</li>
  <li><strong>Müşteri işlem bilgileri:</strong> Hizmet talep tarihi, teklif içeriği, sözleşme detayları, fatura ve ödeme bilgileri</li>
  <li><strong>Tesisat bilgileri:</strong> Mülk adresi, kombi/klima marka-model bilgisi, abone numarası, İzmirgaz tesisat numarası</li>
  <li><strong>Pazarlama bilgileri:</strong> Onay verdiğiniz takdirde kampanya ve duyuru bilgilendirmeleri</li>
  <li><strong>İşlem güvenliği bilgileri:</strong> IP adresi, tarayıcı bilgisi, çerez kayıtları</li>
</ul>

<h2>3. Kişisel Verilerin İşlenme Amaçları</h2>
<p>Toplanan kişisel verileriniz, KVKK madde 5 ve 6''da yer alan kişisel veri işleme şartları çerçevesinde aşağıdaki amaçlarla işlenmektedir:</p>
<ul>
  <li>Hizmet talebinizin değerlendirilmesi ve teklif sunulması</li>
  <li>Doğalgaz tesisat projelerinin İzmirgaz sistemine yüklenmesi ve onay süreçlerinin yürütülmesi</li>
  <li>Sözleşmenin kurulması, ifası ve sona ermesi süreçleri</li>
  <li>Ürün satışı, kurulum, bakım ve servis hizmetlerinin sunulması</li>
  <li>Fatura, irsaliye ve diğer mali belgelerin düzenlenmesi</li>
  <li>Yasal yükümlülüklerin yerine getirilmesi (Vergi Usul Kanunu, Tüketicinin Korunması Hakkında Kanun, vb.)</li>
  <li>Müşteri ilişkileri yönetimi ve şikayet/talep süreçlerinin takibi</li>
  <li>Hizmet kalitesinin geliştirilmesi</li>
  <li>Açık rızanız bulunduğu hâlde pazarlama ve kampanya bildirimleri</li>
</ul>

<h2>4. Kişisel Verilerin Toplanma Yöntemi ve Hukuki Sebebi</h2>
<p>Kişisel verileriniz; web sitemiz üzerinden iletişim ve teklif formları, telefon görüşmeleri, e-posta yazışmaları, yüz yüze görüşmeler, sözleşme süreçleri ve resmi makamlarca düzenlenen belgeler aracılığıyla toplanır. Hukuki sebepleri:</p>
<ul>
  <li>Sözleşmenin kurulması ve ifası için zorunlu olması (KVKK m.5/2-c)</li>
  <li>Hukuki yükümlülüğün yerine getirilmesi (KVKK m.5/2-ç)</li>
  <li>Meşru menfaatler için veri işlenmesinin zorunlu olması (KVKK m.5/2-f)</li>
  <li>Açık rıza alınması (pazarlama ve elektronik ileti onayı için — KVKK m.5/1)</li>
</ul>

<h2>5. Kişisel Verilerin Aktarılması</h2>
<p>Kişisel verileriniz; yasal düzenlemelerin öngördüğü ölçüde aşağıdaki taraflarla paylaşılabilir:</p>
<ul>
  <li><strong>İzmirgaz A.Ş.</strong> — tesisat onay ve abonelik süreçleri</li>
  <li><strong>Yetkili kamu kurum ve kuruluşları</strong> — yasal talepler doğrultusunda</li>
  <li><strong>Üretici ve tedarikçi firmalar</strong> — Demirdöküm gibi cihaz garanti süreçleri için</li>
  <li><strong>Mali müşavir, hukuk müşaviri ve denetim hizmeti aldığımız üçüncü taraflar</strong></li>
  <li><strong>Kargo/lojistik firmaları</strong> — ürün sevkiyatı için</li>
  <li><strong>Bankalar ve ödeme kuruluşları</strong> — ödeme işlemleri için</li>
</ul>
<p>Verileriniz yurt dışına aktarılmamaktadır.</p>

<h2>6. KVKK Madde 11 Kapsamındaki Haklarınız</h2>
<p>Kişisel veri sahibi olarak aşağıdaki haklara sahipsiniz:</p>
<ul>
  <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
  <li>İşlenmişse buna ilişkin bilgi talep etme</li>
  <li>Kişisel verilerin işlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
  <li>Yurt içinde veya yurt dışında kişisel verilerin aktarıldığı üçüncü kişileri bilme</li>
  <li>Kişisel verilerin eksik veya yanlış işlenmiş olması hâlinde bunların düzeltilmesini isteme</li>
  <li>KVKK madde 7 uyarınca kişisel verilerin silinmesini veya yok edilmesini isteme</li>
  <li>Düzeltme, silme ve yok etme işlemlerinin verilerin aktarıldığı üçüncü kişilere bildirilmesini isteme</li>
  <li>İşlenen verilerin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi suretiyle aleyhinize bir sonucun ortaya çıkmasına itiraz etme</li>
  <li>Kişisel verilerinizin kanuna aykırı olarak işlenmesi sebebiyle zarara uğramanız hâlinde zararın giderilmesini talep etme</li>
</ul>

<h2>7. Başvuru Yöntemi</h2>
<p>Yukarıda belirtilen haklarınızı kullanmak için <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a> adresine yazılı başvuruda bulunabilir veya Şirket adresine elden teslim edebilirsiniz. Başvurunuzda kimlik bilgilerinizin yer alması gereklidir. Talebiniz, KVKK madde 13 gereği en geç 30 gün içinde sonuçlandırılacaktır.</p>

<p style="margin-top:30px;color:#64748b;font-size:.9rem"><em>Son güncelleme: 29.04.2026</em></p>')
  ON DUPLICATE KEY UPDATE deger = IF(deger='' OR deger IS NULL, VALUES(deger), deger);

-- gizlilik_metni
INSERT INTO ayarlar (anahtar, deger) VALUES ('gizlilik_metni', '<h2>Giriş</h2>
<p>Azra Doğalgaz Tesisat ("Şirket", "biz", "bizim") olarak, web sitemizi (www.azradogalgaz.com) ziyaret eden kullanıcılarımızın bilgi gizliliğine saygı duyuyor ve aşağıdaki ilkeler doğrultusunda hareket ediyoruz. İşbu Gizlilik Politikası, bilgi toplama, kullanım, paylaşım ve koruma uygulamalarımızı açıklar.</p>

<h2>1. Toplanan Bilgiler</h2>
<p>Aşağıdaki bilgileri çeşitli yollarla toplayabiliriz:</p>
<ul>
  <li><strong>Doğrudan sağladığınız bilgiler:</strong> İletişim formu, teklif talebi, keşif başvurusu veya bakım talebi doldururken verdiğiniz ad, soyad, telefon, e-posta, adres ve mesaj içerikleri</li>
  <li><strong>Otomatik toplanan bilgiler:</strong> IP adresi, tarayıcı türü, işletim sistemi, ziyaret tarihi/saati, görüntülenen sayfalar, yönlendiren site (referrer)</li>
  <li><strong>Çerez bilgileri:</strong> Tarayıcınıza yerleştirilen çerezlerle elde edilen oturum, tercih ve analiz verileri (detay için <a href="/cerez">Çerez Politikası</a>)</li>
</ul>

<h2>2. Bilgilerin Kullanım Amaçları</h2>
<p>Toplanan bilgileri yalnızca aşağıdaki amaçlarla kullanırız:</p>
<ul>
  <li>Talebinize yanıt verme ve teklif sunma</li>
  <li>Hizmet sözleşmelerinin kurulması ve uygulanması</li>
  <li>Sipariş, kurulum, bakım ve servis süreçlerinin yürütülmesi</li>
  <li>Yasal yükümlülüklerin yerine getirilmesi (vergi, fatura, garanti, vb.)</li>
  <li>Müşteri memnuniyetinin ölçülmesi ve hizmet kalitesinin iyileştirilmesi</li>
  <li>Açık rızanız varsa kampanya ve duyuru bildirimleri</li>
  <li>Sitemizin teknik altyapısının ve güvenliğinin sağlanması</li>
</ul>

<h2>3. Bilgilerin Paylaşılması</h2>
<p>Kişisel bilgileriniz <strong>üçüncü taraflara satılmaz, kiralanmaz veya pazarlama amacıyla paylaşılmaz</strong>. Bilgileriniz aşağıdaki sınırlı durumlarda paylaşılabilir:</p>
<ul>
  <li>Hizmetin sunumu için zorunlu olan iş ortakları (İzmirgaz, üretici firmalar, kargo/lojistik, ödeme kuruluşları)</li>
  <li>Mali müşavir, hukuk danışmanı, denetçi gibi sır saklama yükümlülüğü olan profesyoneller</li>
  <li>Mahkeme kararı, savcılık talebi veya kanun gereği bildirim zorunluluğu doğduğunda yetkili kamu kurumları</li>
</ul>

<h2>4. Bilgi Güvenliği</h2>
<p>Toplanan bilgilerin güvenliği için endüstri standardı önlemler alıyoruz:</p>
<ul>
  <li>Site genelinde SSL/TLS ile şifreli iletişim (HTTPS)</li>
  <li>Veritabanına şifre korumalı ve sınırlı erişim</li>
  <li>Yönetici paneline yetkili kullanıcı ve güçlü parola politikası</li>
  <li>Düzenli yedekleme ve sistem izleme</li>
  <li>Güvenlik açıklarına karşı sürekli güncelleme</li>
</ul>
<p>Bununla birlikte, internet üzerinden hiçbir aktarım yöntemi %100 güvenli olmadığından, mutlak güvenlik garantisi verilemez.</p>

<h2>5. Bilgi Saklama Süresi</h2>
<p>Kişisel bilgileriniz, ilgili yasal saklama sürelerine ve hizmetin gereği olan süreye uygun şekilde saklanır:</p>
<ul>
  <li><strong>Müşteri kayıtları:</strong> Vergi Usul Kanunu gereği 5 yıl</li>
  <li><strong>Sözleşme ve fatura kayıtları:</strong> Türk Ticaret Kanunu gereği 10 yıl</li>
  <li><strong>İletişim formu mesajları:</strong> Talep sonuçlandıktan sonra en fazla 2 yıl</li>
  <li><strong>Pazarlama izinleri:</strong> İzin geri alınana kadar</li>
  <li><strong>Sunucu logları:</strong> Güvenlik amacıyla en fazla 1 yıl</li>
</ul>

<h2>6. Üçüncü Taraf Hizmetler</h2>
<p>Sitemizde aşağıdaki üçüncü taraf servisler kullanılabilir:</p>
<ul>
  <li><strong>Google Analytics</strong> — anonim ziyaretçi istatistikleri için</li>
  <li><strong>Google Maps</strong> — adres ve harita gösterimi için</li>
  <li><strong>Google Fonts</strong> — yazı tipi yükleme için</li>
  <li><strong>Sosyal medya bağlantıları</strong> — Facebook, Instagram, YouTube</li>
</ul>
<p>Bu servislere ait gizlilik politikaları geçerlidir; servisi kullanmadan önce ilgili politikaları incelemenizi öneririz.</p>

<h2>7. Çocukların Gizliliği</h2>
<p>Sitemiz 18 yaş altı kişilere yönelik değildir. Bilerek 18 yaş altı kişilerden kişisel veri toplamayız. Çocuğunuzun bilgilerini paylaştığını fark ederseniz lütfen bizimle iletişime geçin; söz konusu bilgileri sileceğiz.</p>

<h2>8. Bilgilerinizi Düzenleme veya Silme</h2>
<p>Toplanan kişisel bilgilerinizin görüntülenmesi, düzeltilmesi veya silinmesi için <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a> adresine yazılı talebinizi iletebilirsiniz. KVKK madde 11 kapsamındaki haklarınızın detayı için <a href="/kvkk">KVKK Aydınlatma Metni</a> sayfasını inceleyebilirsiniz.</p>

<h2>9. Politika Güncellemeleri</h2>
<p>İşbu Gizlilik Politikası, mevzuattaki değişiklikler veya hizmet kapsamımızdaki gelişmelere bağlı olarak güncellenebilir. Önemli değişiklikler bu sayfada yayımlanır ve gerekli görüldüğünde e-posta ile bildirilir. Sitemizi kullanmaya devam etmeniz, güncel politikayı kabul ettiğiniz anlamına gelir.</p>

<h2>10. İletişim</h2>
<p>Gizlilik uygulamalarımızla ilgili her türlü soru, talep veya şikayetiniz için:</p>
<ul>
  <li><strong>E-posta:</strong> <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a></li>
  <li><strong>Telefon:</strong> 0546 790 78 77</li>
  <li><strong>Adres:</strong> Laleli Menderes Caddesi No:392/C 35370 / Buca - İzmir</li>
</ul>

<p style="margin-top:30px;color:#64748b;font-size:.9rem"><em>Son güncelleme: 29.04.2026</em></p>')
  ON DUPLICATE KEY UPDATE deger = IF(deger='' OR deger IS NULL, VALUES(deger), deger);

-- cerez_metni
INSERT INTO ayarlar (anahtar, deger) VALUES ('cerez_metni', '<h2>Çerez (Cookie) Nedir?</h2>
<p>Çerezler, ziyaret ettiğiniz web siteleri tarafından tarayıcınıza yerleştirilen ve bilgisayarınızda/mobil cihazınızda saklanan küçük metin dosyalarıdır. Çerezler, sitelerin sizi tanımasına, tercihlerinizi hatırlamasına ve daha kişiselleştirilmiş bir deneyim sunmasına olanak tanır.</p>

<h2>Sitemizde Kullanılan Çerez Türleri</h2>

<h3>1. Zorunlu Çerezler</h3>
<p>Sitenin temel işlevlerinin çalışması için zorunludur. Bu çerezleri devre dışı bırakmak siteyi kullanılmaz hâle getirir. Onay gerektirmezler.</p>
<ul>
  <li><strong>AZRASID</strong> — oturum çerezi (form gönderimi, admin girişi)</li>
  <li><strong>csrf_token</strong> — güvenlik amacıyla form sahteciliği önleme</li>
</ul>

<h3>2. Performans ve Analiz Çerezleri</h3>
<p>Ziyaretçilerin siteyi nasıl kullandığını anonim olarak analiz etmek için kullanılır. Sayfa görüntüleme sayıları, popüler içerikler ve ziyaret süresi gibi metrikler toplanır.</p>
<ul>
  <li><strong>Google Analytics çerezleri</strong> (_ga, _gid, _gat) — site trafik analizi için</li>
</ul>

<h3>3. İşlevsellik Çerezleri</h3>
<p>Tercihlerinizi hatırlamak ve gelişmiş özellikler sunmak için kullanılır.</p>
<ul>
  <li>Form alanlarında girdiğiniz son değerlerin hatırlanması (tarayıcı tarafından yönetilir)</li>
  <li>Tema/dil tercihleriniz (kullanıldığı takdirde)</li>
</ul>

<h3>4. Üçüncü Taraf Çerezler</h3>
<p>Site üzerinde yer alan üçüncü taraf hizmetleri (Google Maps, sosyal medya butonları) kendi çerezlerini yerleştirebilir. Bu çerezler ilgili sağlayıcının gizlilik politikasına tabidir.</p>

<h2>Çerezlerin Saklanma Süresi</h2>
<table style="width:100%;border-collapse:collapse;margin:14px 0">
  <thead><tr style="background:#f1f5f9"><th style="padding:10px;text-align:left;border:1px solid #e2e8f0">Çerez Tipi</th><th style="padding:10px;text-align:left;border:1px solid #e2e8f0">Süre</th></tr></thead>
  <tbody>
    <tr><td style="padding:10px;border:1px solid #e2e8f0">Oturum çerezleri</td><td style="padding:10px;border:1px solid #e2e8f0">Tarayıcı kapanınca silinir</td></tr>
    <tr><td style="padding:10px;border:1px solid #e2e8f0">Google Analytics _ga</td><td style="padding:10px;border:1px solid #e2e8f0">2 yıl</td></tr>
    <tr><td style="padding:10px;border:1px solid #e2e8f0">Google Analytics _gid</td><td style="padding:10px;border:1px solid #e2e8f0">24 saat</td></tr>
    <tr><td style="padding:10px;border:1px solid #e2e8f0">Tercih çerezleri</td><td style="padding:10px;border:1px solid #e2e8f0">1 yıl</td></tr>
  </tbody>
</table>

<h2>Çerezleri Kontrol Etme ve Devre Dışı Bırakma</h2>
<p>Çerez tercihlerinizi tarayıcı ayarlarınızdan istediğiniz zaman değiştirebilirsiniz. Yaygın tarayıcılar için çerez yönetim sayfaları:</p>
<ul>
  <li><strong>Google Chrome:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve diğer site verileri</li>
  <li><strong>Mozilla Firefox:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve Site Verileri</li>
  <li><strong>Safari:</strong> Tercihler → Gizlilik → Çerezleri Yönet</li>
  <li><strong>Microsoft Edge:</strong> Ayarlar → Gizlilik, Arama ve Hizmetler → Çerezleri Görüntüle</li>
</ul>
<p>Çerezleri devre dışı bırakmanın bazı site özelliklerinin çalışmamasına neden olabileceğini unutmayın (örneğin, iletişim formu gönderimi).</p>

<h2>Reklam Çerezleri</h2>
<p>Sitemizde <strong>reklam amaçlı çerez kullanılmamaktadır</strong>. Üçüncü taraf reklam ağlarına dahil değiliz, davranışsal reklam veya yeniden hedefleme çerezi yerleştirilmez.</p>

<h2>Çerez Politikası Güncellemeleri</h2>
<p>Bu Çerez Politikası, mevzuat veya hizmet değişikliklerine bağlı olarak güncellenebilir. Önemli değişiklikler sayfanın üst kısmında duyurulur. Sitemizi kullanmaya devam etmeniz, güncel politikayı kabul ettiğiniz anlamına gelir.</p>

<h2>İletişim</h2>
<p>Çerez kullanımıyla ilgili sorularınız için <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a> adresine yazabilirsiniz.</p>

<p style="margin-top:30px;color:#64748b;font-size:.9rem"><em>Son güncelleme: 29.04.2026</em></p>')
  ON DUPLICATE KEY UPDATE deger = IF(deger='' OR deger IS NULL, VALUES(deger), deger);

-- mesafeli_metni
INSERT INTO ayarlar (anahtar, deger) VALUES ('mesafeli_metni', '<p style="background:#fef3c7;border-left:3px solid #f59e0b;padding:12px 16px;border-radius:6px;margin-bottom:18px">
<strong>Not:</strong> İşbu Mesafeli Satış Sözleşmesi, yalnızca web sitemiz aracılığıyla gerçekleştirilen <strong>ürün satışları</strong> için geçerlidir. Yerinde keşif, kurulum ve servis hizmetlerimiz tüketicinin korunması mevzuatı çerçevesinde ayrı sözleşme şartlarına tabidir.
</p>

<h2>1. Taraflar</h2>

<h3>SATICI</h3>
<ul>
  <li><strong>Unvan:</strong> Azra Doğalgaz Tesisat</li>
  <li><strong>Adres:</strong> Laleli Menderes Caddesi No:392/C 35370 / Buca - İzmir</li>
  <li><strong>Telefon:</strong> 0546 790 78 77 — 0546 820 60 80</li>
  <li><strong>E-posta:</strong> info@azradogalgaz.com</li>
</ul>

<h3>ALICI (TÜKETİCİ)</h3>
<p>Sipariş formunda yer alan ad, soyad, T.C. kimlik numarası, adres, telefon ve e-posta bilgileri kapsamında belirtilen kişi/kurum.</p>

<h2>2. Sözleşmenin Konusu</h2>
<p>İşbu Sözleşmenin konusu; ALICI''nın SATICI''ya ait <a href="/">www.azradogalgaz.com</a> internet sitesinden elektronik ortamda siparişini yaptığı, sözleşmede nitelikleri ve satış fiyatı belirtilen ürünün satışı ve teslimi ile ilgili olarak <strong>6502 sayılı Tüketicinin Korunması Hakkında Kanun</strong> ve <strong>Mesafeli Sözleşmeler Yönetmeliği</strong> hükümleri gereğince tarafların hak ve yükümlülüklerinin saptanmasıdır.</p>

<h2>3. Sözleşme Konusu Ürün Bilgileri</h2>
<p>Ürünün cinsi, türü, miktarı, marka/modeli, satış bedeli, ödeme şekli, teslim alacak kişi, teslimat adresi, fatura bilgileri ile kargo ücreti — sipariş onay sayfasında ve ALICI''ya gönderilen sipariş onay e-postasında ayrıntılı olarak belirtilir. Ürün fiyatlarına KDV dahildir.</p>

<h2>4. Genel Hükümler</h2>
<ul>
  <li>ALICI, sipariş vermeden önce sözleşmenin temel niteliklerini, vergiler dahil toplam fiyatı, ödeme ve teslimat bilgilerini, cayma hakkını ve şikayet başvuru yollarını okuyup anladığını kabul eder.</li>
  <li>SATICI, sipariş edilen ürünü en geç 30 gün içerisinde teslim etmekle yükümlüdür. Teslimat süresi olağanüstü durumlar (mücbir sebep, tedarik zinciri kesintisi) hâlinde uzatılabilir; ALICI bilgilendirilir.</li>
  <li>Sözleşme konusu ürünün teslimi için ALICI''nın siparişi tamamlamış ve ödemeyi gerçekleştirmiş olması zorunludur.</li>
  <li>Ödeme alınmamış siparişler, SATICI tarafından önceden bildirimde bulunulmaksızın iptal edilebilir.</li>
</ul>

<h2>5. Cayma Hakkı</h2>
<p>ALICI, sözleşmenin imzalandığı veya ürünün teslim alındığı tarihten itibaren <strong>14 (on dört) gün</strong> içerisinde herhangi bir gerekçe göstermeksizin ve cezai şart ödemeksizin sözleşmeden cayma hakkına sahiptir.</p>
<p>Cayma hakkını kullanmak için süresi içinde SATICI''ya aşağıdaki yollardan biriyle yazılı bildirim yapmak gerekir:</p>
<ul>
  <li>E-posta: <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a></li>
  <li>İadeli taahhütlü posta veya kargo: SATICI adresine</li>
</ul>
<p>Cayma bildiriminin SATICI''ya ulaşmasını takiben 10 gün içinde ürün bedeli, ödendiği yöntemle ALICI''ya iade edilir. ALICI, cayma süresi içinde ürünü olağan kullanım gereği oluşan değişiklikler dışında özenli kullanmakla yükümlüdür.</p>

<h2>6. Cayma Hakkının Kullanılamayacağı Hâller</h2>
<p>Mesafeli Sözleşmeler Yönetmeliği madde 15 uyarınca aşağıdaki hâllerde cayma hakkı kullanılamaz:</p>
<ul>
  <li>Tüketicinin istekleri veya açıkça onun kişisel ihtiyaçları doğrultusunda hazırlanan, niteliği itibarıyla geri gönderilmeye elverişli olmayan ve çabuk bozulma tehlikesi olan veya son kullanma tarihi geçme ihtimali olan ürünler</li>
  <li>Tesliminden sonra ambalaj, bant, mühür, paket gibi koruyucu unsurları açılmış olan ürünlerden iadesi sağlık ve hijyen açısından uygun olmayanlar</li>
  <li>Tesliminden sonra başka ürünlerle karışan ve doğası gereği ayrıştırılması mümkün olmayan ürünler</li>
  <li>Tesliminden sonra ALICI tarafından ambalajı açılmış (kurulum başlatılmış) kombi, klima ve benzeri cihazlar — ancak ürün arızalı veya kusurlu olarak teslim edildiyse ALICI''nın ayıplı mal hükümlerinden faydalanma hakkı saklıdır.</li>
</ul>

<h2>7. Teslimat ve Kargo</h2>
<ul>
  <li>Ürünler, sipariş onayını takip eden 1-3 iş günü içinde anlaşmalı kargo firmasıyla gönderilir. Bölgeye göre teslimat süresi 1-7 iş gününü bulabilir.</li>
  <li>Kargo ücreti sipariş özetinde ayrıca belirtilir. Belirli tutar üzeri siparişlerde kargo ücretsiz olabilir; bu durumda kampanya şartları sipariş sayfasında belirtilir.</li>
  <li>ALICI, ürünü teslim aldığında dış paketin hasarsız olduğunu kontrol etmek; hasar varsa kargo görevlisi huzurunda tutanak tutturmakla yükümlüdür. Hasarlı kargo nedeniyle iade veya değişim, tutanak ile birlikte talep edilebilir.</li>
</ul>

<h2>8. Garanti ve Ayıplı Mal</h2>
<p>Tüm ürünler üretici garantisi ile satışa sunulmaktadır. Garanti süresi, koşulları ve garanti belgesi ürünle birlikte teslim edilir. ALICI, 6502 sayılı Tüketicinin Korunması Hakkında Kanun kapsamındaki ayıplı mal hükümlerinden faydalanma hakkına sahiptir.</p>

<h2>9. Uyuşmazlıkların Çözümü</h2>
<p>Tüketici, satın aldığı ürünle ilgili şikayet ve itirazlarını, yıllık olarak Ticaret Bakanlığı tarafından belirlenen parasal sınırlar dahilinde, mal veya hizmeti satın aldığı veya ikametgâhının bulunduğu yerdeki <strong>Tüketici Hakem Heyetine</strong> veya <strong>Tüketici Mahkemesine</strong> başvurabilir.</p>

<h2>10. Kabul</h2>
<p>ALICI, internet sitesinde sipariş tamamlama sürecinde işbu Mesafeli Satış Sözleşmesi''ni okuyup onayladığını, sözleşmenin tüm hükümlerine uygun davranmayı kabul ve taahhüt ettiğini beyan eder. Sözleşme, sipariş onayının ALICI''ya elektronik ortamda iletilmesi ile yürürlüğe girer.</p>

<p style="margin-top:30px;color:#64748b;font-size:.9rem"><em>Son güncelleme: 29.04.2026</em></p>')
  ON DUPLICATE KEY UPDATE deger = IF(deger='' OR deger IS NULL, VALUES(deger), deger);

-- iade_metni
INSERT INTO ayarlar (anahtar, deger) VALUES ('iade_metni', '<h2>Genel İlkeler</h2>
<p>Azra Doğalgaz Tesisat olarak müşteri memnuniyetini ön planda tutuyor, 6502 sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği çerçevesinde adil ve şeffaf bir iade-değişim politikası uyguluyoruz.</p>

<h2>1. Cayma Hakkı (14 Gün)</h2>
<p>Web sitemiz üzerinden satın aldığınız ürünleri, teslim aldığınız tarihten itibaren <strong>14 gün</strong> içinde herhangi bir gerekçe göstermeksizin iade edebilirsiniz. Bu süre içinde:</p>
<ul>
  <li>Ürün ambalajının açılmamış, kullanılmamış ve bozulmamış olması gerekir</li>
  <li>Tüm aksesuar, kullanım kılavuzu ve garanti belgesinin eksiksiz olması gerekir</li>
  <li>Faturanın ürünle birlikte iade edilmesi gerekir</li>
</ul>

<h2>2. Cayma Hakkının Kullanılamayacağı Ürünler</h2>
<p>Mesafeli Sözleşmeler Yönetmeliği madde 15 uyarınca aşağıdaki ürünlerde cayma hakkı kullanılamaz:</p>
<ul>
  <li>Kuruluma başlanmış kombi, klima ve doğalgaz cihazları (ambalajı açılıp tesisata bağlanmış)</li>
  <li>Müşteri özel istekleri doğrultusunda hazırlanmış (özel boy, özel renk, özel konfigürasyon) ürünler</li>
  <li>Hijyen ve sağlık açısından iadesi uygun olmayan, ambalajı açılmış ürünler</li>
  <li>Niteliği gereği iade edilmeye elverişsiz veya çabuk bozulma tehlikesi olan ürünler</li>
</ul>
<p><strong>Önemli:</strong> Cihaz arızalı veya kusurlu teslim edildiyse, kurulum yapılmış olsa dahi ayıplı mal hükümleri çerçevesinde değişim/iade talep edebilirsiniz.</p>

<h2>3. İade Süreci — Adım Adım</h2>
<ol>
  <li><strong>Talep oluşturun:</strong> İade talebinizi <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a> adresine veya 0546 790 78 77 numarasına bildirin. Sipariş numaranızı, iade nedeninizi ve fatura bilgilerinizi belirtin.</li>
  <li><strong>Onay alın:</strong> Talebinizi 2 iş günü içinde değerlendirip iade onay numarası ve gönderim talimatlarını içeren bir e-posta göndereceğiz.</li>
  <li><strong>Ürünü gönderin:</strong> Ürünü orijinal ambalajında, tüm aksesuarları ve faturasıyla birlikte adresimize anlaşmalı kargo firmamızla gönderin.</li>
  <li><strong>İnceleme:</strong> Ürün tarafımıza ulaştıktan sonra 3 iş günü içinde inceleme yapılır.</li>
  <li><strong>İade işlemi:</strong> İncelemenin uygun bulunması durumunda ödeme bedeli, sipariş anında kullandığınız ödeme yöntemine 7 iş günü içinde iade edilir.</li>
</ol>

<h2>4. İade Adresi</h2>
<p style="background:#f1f5f9;padding:14px 18px;border-radius:8px">
<strong>Azra Doğalgaz Tesisat</strong><br>
Laleli Menderes Caddesi No:392/C<br>
35370 / Buca - İzmir<br>
Tel: 0546 790 78 77
</p>
<p>Lütfen kargo gönderiminden önce iade onay numaranızı aldığınızdan emin olun. Onay numarası olmadan gönderilen kargolar teslim alınmayabilir.</p>

<h2>5. Kargo Bedeli</h2>
<ul>
  <li><strong>Cayma hakkı kullanımında:</strong> Kargo bedeli ALICI tarafından karşılanır.</li>
  <li><strong>Ürün arızalı veya kusurlu ise:</strong> Kargo bedeli SATICI (Şirketimiz) tarafından karşılanır. Bu durumda anlaşmalı kargo firmamız üzerinden ücretsiz gönderim sağlanır.</li>
  <li><strong>Yanlış ürün gönderildi ise:</strong> Kargo bedeli SATICI tarafından karşılanır.</li>
</ul>

<h2>6. Değişim</h2>
<p>İade yerine ürün değişimi tercih ediyorsanız, iade talebinizi oluştururken belirtebilirsiniz. Değişim talepleri:</p>
<ul>
  <li>Aynı ürünün farklı varyantı ile yapılabilir (renk, kapasite, vb.)</li>
  <li>Stok durumuna bağlı olarak gerçekleştirilir</li>
  <li>Fiyat farkı varsa fark tahsil edilir veya iade edilir</li>
</ul>

<h2>7. Garanti Kapsamındaki Servis Talepleri</h2>
<p>Üretici garantisi kapsamında bulunan ürünler için <strong>iade değil servis</strong> uygulanır. Demirdöküm yetkili servis olarak kombi, klima ve doğalgaz cihazlarınızda:</p>
<ul>
  <li>Yerinde teknik servis ücretsizdir (garanti süresi içinde)</li>
  <li>Parça değişimi gerekiyorsa orijinal yedek parça ile yapılır</li>
  <li>Servis kayıtları sistemimizde tutulur</li>
</ul>
<p>Servis talebi için: 0546 790 78 77 numaramızdan ulaşabilirsiniz.</p>

<h2>8. İade Edilemeyen Bedeller</h2>
<p>Aşağıdaki bedeller iade kapsamı dışındadır:</p>
<ul>
  <li>Kurulum hizmet bedeli (kurulum tamamlandıysa)</li>
  <li>Yerinde keşif ücreti (keşif yapıldıysa)</li>
  <li>Ürün üzerinde değişiklik/montaj yapılan kalemler</li>
</ul>

<h2>9. Tüketici Hakları ve Başvuru Yolları</h2>
<p>İade veya değişim taleplerinizle ilgili çözüme ulaşılamadığı durumlarda:</p>
<ul>
  <li>İkamet ettiğiniz yerin <strong>Tüketici Hakem Heyetine</strong> başvurabilirsiniz</li>
  <li>Parasal sınırı aşan durumlarda <strong>Tüketici Mahkemesine</strong> başvurabilirsiniz</li>
  <li>Online şikayet için: <a href="https://tuketici.ticaret.gov.tr" target="_blank" rel="noopener">tuketici.ticaret.gov.tr</a></li>
</ul>

<h2>10. İletişim</h2>
<p>İade ve değişim talepleri için:</p>
<ul>
  <li><strong>E-posta:</strong> <a href="mailto:info@azradogalgaz.com">info@azradogalgaz.com</a></li>
  <li><strong>Telefon:</strong> 0546 790 78 77 — 0546 820 60 80</li>
  <li><strong>Çalışma saatleri:</strong> Pazartesi-Cumartesi 08:00-20:00</li>
</ul>

<p style="margin-top:30px;color:#64748b;font-size:.9rem"><em>Son güncelleme: 29.04.2026</em></p>')
  ON DUPLICATE KEY UPDATE deger = IF(deger='' OR deger IS NULL, VALUES(deger), deger);

