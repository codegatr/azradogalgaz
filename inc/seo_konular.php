<?php
/**
 * Azra Doğalgaz — SEO Konu Kütüphanesi (v1.12.28)
 *
 * 4 Pillar (ana konu) + 30+ alt konu (cluster). Her konu için:
 *   - slug, baslik, h1
 *   - meta_aciklama (155 karakter)
 *   - anahtar_kelimeler[] — sayfaya doğal entegre edilecek 8-15 keyword
 *   - icerik_paragraflari[] — gerçek bilgi içeren paragraflar (long-form)
 *   - sss[] — FAQ schema için soru-cevap çiftleri
 *   - ilgili_konular[] — internal linking için
 *   - schema_tip — Article | HowTo | FAQPage
 *
 * Bu dosya keyword stuff yapmaz — her konu kendi cluster'ına ait
 * organik içerik içerir. Google "topical authority" sinyali alır.
 */

if (!function_exists('seo_konular')) {
    function seo_konular(): array
    {
        return [

            // ═════════════════════════════════════════════════════════
            // PILLAR 1 — DOĞALGAZ
            // ═════════════════════════════════════════════════════════
            'dogalgaz-tesisati-nedir' => [
                'pillar' => 'dogalgaz',
                'baslik' => 'Doğalgaz Tesisatı Nedir? A\'dan Z\'ye Süreç ve Mevzuat Rehberi',
                'h1' => 'Doğalgaz Tesisatı Nedir, Nasıl Kurulur?',
                'meta_aciklama' => 'Doğalgaz tesisatı nedir, hangi aşamalardan oluşur, kaç günde tamamlanır? İzmir\'de doğalgaz tesisat sürecinin tüm detayları, fiyatlar ve mevzuat.',
                'anahtar_kelimeler' => [
                    'doğalgaz tesisatı', 'doğalgaz tesisat firması', 'doğalgaz tesisatı nasıl yapılır',
                    'doğalgaz tesisat fiyatları', 'doğalgaz iç tesisat', 'doğalgaz tesisat onay',
                    'doğalgaz proje çizimi', 'sızdırmazlık testi', 'gaz açma',
                    'çelik boru tesisat', 'paslanmaz boru doğalgaz', 'doğalgaz mevzuat',
                ],
                'icerik' => [
                    'Doğalgaz tesisatı, dağıtım şirketinden alınan doğalgazın güvenli şekilde mutfak, banyo ve kombi gibi son kullanım noktalarına ulaştırılmasını sağlayan boru sistemidir. Yapı içinde tesisat döşeme, sayaç odası fitingleri, vana sistemleri, sızdırmazlık testleri ve dağıtım şirketi onayı süreçlerini kapsar. İzmir gibi gelişmiş doğalgaz altyapısına sahip büyükşehirlerde her yıl on binlerce konut doğalgaza geçmektedir.',

                    'Tesisatın güvenli ve mevzuata uygun kurulması zorunludur. EPDK ve TS EN 15266 standartları ile dağıtım şirketinin teknik şartnameleri belirleyici çerçeveyi oluşturur. Bu nedenle tesisat işleri, dağıtım şirketi tarafından yetkilendirilmiş Sertifikalı İç Tesisat Firmaları tarafından gerçekleştirilmek zorundadır. Yetkisiz uygulamalar gaz açılışı reddiyle sonuçlanır.',

                    'Tipik bir doğalgaz tesisat süreci 7 ila 10 iş günü sürer. Süreç şu aşamalardan oluşur: ücretsiz keşif ve yazılı teklif (1 gün), proje çizimi ve müşteri onayı (1-2 gün), dağıtım şirketi proje onayı (2-5 iş günü), tesisat döşeme uygulaması (1-2 gün), basınç altında sızdırmazlık testi, dağıtım şirketi teknik kontrolü ve gaz arzı, son olarak Doğalgaz Uygunluk Belgesi tanzimi.',

                    'Boru malzemesi seçimi tesisatın ömrünü doğrudan etkiler. Görünür alanlarda paslanmaz çelik (AISI 304/316) veya alev kaynaklı çelik boru, gömme tesisatlarda çok katmanlı PE-AL-PE veya bakır boru tercih edilir. Tüm fitingler ve vanalar TSE veya CE sertifikalı olmalıdır. Azra Doğalgaz olarak tüm uygulamalarımızda yalnızca sertifikalı malzeme kullanır, 2 yıl işçilik garantisi sunarız.',

                    'Doğalgaz tesisatı maliyeti; daire büyüklüğü, kombi konumu, kullanılacak nokta sayısı (mutfak, banyo, salon) ve boru hattı uzunluğuna göre değişir. 100 m² standart bir daire için 2026 itibarıyla ortalama maliyet 25.000 TL ile 45.000 TL arasındadır. Apartman ölçeğinde toplu projelerde daire başı maliyet 15-20% düşebilir.',
                ],
                'sss' => [
                    ['Doğalgaz tesisatı kaç günde biter?', 'Standart bir konut tesisatı, dağıtım şirketi onay süresi dahil 7-10 iş günü içinde tamamlanır. Apartman bazlı toplu projelerde süre 15-25 iş gününe çıkabilir.'],
                    ['Doğalgaz tesisatı ne kadar tutar?', 'Konut büyüklüğüne ve kombi konumuna göre değişmekle birlikte 100 m² standart daire için 2026 yılında 25.000-45.000 TL aralığındadır. Yazılı keşif sonrası net teklif sunarız.'],
                    ['Doğalgaz tesisatını herkes yapabilir mi?', 'Hayır. Yalnızca dağıtım şirketi tarafından yetkilendirilmiş Sertifikalı İç Tesisat Firmaları yetkilidir. Yetkisiz uygulama gaz açılışı reddedilmesine yol açar.'],
                    ['Hangi boru kullanılır?', 'Görünür alanlarda paslanmaz çelik veya alev kaynaklı çelik, gömme tesisatlarda PE-AL-PE veya bakır boru. Tüm malzemeler TSE veya CE sertifikalı olmalıdır.'],
                    ['Sızdırmazlık testi nedir?', 'Tesisatın gazı tutup tutmadığını kontrol eden basınç testidir. Boru hattı 100 mbar basınç altında en az 10 dakika kayıp vermemelidir. Test geçilmeden gaz açılmaz.'],
                ],
                'ilgili_konular' => ['kombi-secimi-rehberi', 'dogalgaz-donusum-fiyat', 'sizdirmazlik-testi-nedir', 'apartman-toplu-dogalgaz'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 8,
            ],

            'kombi-secimi-rehberi' => [
                'pillar' => 'kombi',
                'baslik' => 'Doğru Kombi Nasıl Seçilir? Yoğuşmalı, Hermetik, Bacalı Karşılaştırma',
                'h1' => 'Kombi Seçim Rehberi: 2026 Tüm Kriterler',
                'meta_aciklama' => 'Kombi nasıl seçilir? Yoğuşmalı vs hermetik vs bacalı, kaç kW lazım, hangi marka iyi? Daire m²\'sine göre kapasite, fiyat, verim karşılaştırması.',
                'anahtar_kelimeler' => [
                    'kombi seçimi', 'kombi nasıl seçilir', 'yoğuşmalı kombi', 'hermetik kombi',
                    'bacalı kombi', 'kombi kw hesaplama', 'kaç kw kombi', 'en iyi kombi markası',
                    'kombi karşılaştırma', 'demirdöküm kombi', 'bosch kombi', 'vaillant kombi',
                    'baymak kombi', 'kombi a sınıfı', 'kombi enerji verimliliği',
                ],
                'icerik' => [
                    'Doğru kombi seçimi; konut büyüklüğüne, ısıtma ihtiyacına, sıcak su debisine ve enerji verimliliği beklentisine göre belirlenir. Yanlış kapasiteli kombi hem yüksek fatura hem de konfor kaybı anlamına gelir. Bu rehberde 2026 itibarıyla piyasadaki kombi tipleri, kapasite hesaplama yöntemleri ve önde gelen markaların karşılaştırması yer almaktadır.',

                    'Kombi tipleri arasında en yaygın üçlü ayrım yoğuşmalı, hermetik ve bacalı kombilerdir. Yoğuşmalı kombiler, baca gazındaki su buharını yoğuşturarak ekstra ısı geri kazanımı sağlar. A sınıfı verim oranıyla %107\'ye varan termal verimliliğe ulaşır ve aynı kullanımda standart kombiye göre %30\'a varan tasarruf sunar. Hermetik kombiler, kapalı yanma odalı olup yanma havasını dış cepheden çeker; iç mekanda hava kalitesini bozmaz. Bacalı kombiler ise eski tip olup 2018 sonrası yeni montajlarda mevzuat gereği tercih edilmemektedir.',

                    'Kapasite hesaplaması için pratik bir kural vardır: yapı yalıtım durumuna göre m² başına 80-130 W ısı kaybı varsayılır. 100 m² standart yalıtımlı bir daire için yaklaşık 10-13 kW ısıtma gücü yeterlidir. Sıcak su anlık üretimi de hesaba katıldığında 24 kW kombi tipik konutlar için ideal seçenektir. 150 m² üzeri büyük daireler veya yüksek tavanlı konutlarda 28 kW veya 33 kW modeller önerilir.',

                    'Marka seçiminde ön plana çıkan firmalar Demirdöküm Ademix serisi, Bosch Condens, Vaillant ecoTEC, Baymak ve Buderus Logamax\'tır. Her markanın kendine özgü avantajları vardır: Demirdöküm Türkiye\'de en yaygın yetkili servis ağına sahiptir, Bosch yüksek verimlilik ve sessiz çalışma, Vaillant uzun ömür konusunda öne çıkar, Baymak ise fiyat-performans dengesi sunar. Azra Doğalgaz olarak Demirdöküm yetkili bayisi olmakla birlikte tüm markaların montajını gerçekleştiriyoruz.',

                    '2026 itibarıyla A sınıfı yoğuşmalı kombi fiyatları 24 kW segment için 27.000-38.000 TL arasındadır. Montaj dahil tipik anahtar teslim fiyatı 30.000-42.000 TL\'dir. Garanti süresi marka bazlı 2-5 yıl arası değişir; ek olarak işçilik garantimiz tüm uygulamalarda 2 yıldır. ErP A sınıfı etiketi olan modelleri tercih etmenizi öneririz; uzun vadede kendini amorti eder.',
                ],
                'sss' => [
                    ['100 m² daireye kaç kW kombi yeter?', 'Standart yalıtımlı 100 m² daire için 10-13 kW ısıtma gücü yeterlidir. Sıcak su anlık üretimi de düşünüldüğünde 24 kW kombi ideal seçenektir.'],
                    ['Yoğuşmalı kombi tasarruf eder mi?', 'Evet. A sınıfı yoğuşmalı kombi, baca gazını yoğuşturarak %30\'a varan yakıt tasarrufu sağlar. İlk yatırım 5-7 yıl içinde kendini amorti eder.'],
                    ['En iyi kombi markası hangisi?', 'Demirdöküm yaygın servis ağı, Bosch yüksek verim, Vaillant uzun ömür, Baymak fiyat-performans avantajı sunar. İhtiyaca göre değişir.'],
                    ['Kombi kaç yıl çalışır?', 'Yoğuşmalı bir kombinin ortalama ömrü 12-15 yıldır. Düzenli yıllık bakım ile bu süre 18-20 yıla kadar uzayabilir.'],
                    ['Kombi fiyatları 2026', 'A sınıfı 24 kW yoğuşmalı kombi fiyatları 27.000-38.000 TL aralığındadır. Montaj dahil 30.000-42.000 TL.'],
                ],
                'ilgili_konular' => ['kombi-bakimi-nasil-yapilir', 'dogalgaz-tesisati-nedir', 'kombi-arizalari-cozumler'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 10,
            ],

            'kombi-bakimi-nasil-yapilir' => [
                'pillar' => 'kombi',
                'baslik' => 'Kombi Bakımı Nasıl Yapılır? Yıllık Bakım Süreci ve Önemi',
                'h1' => 'Kombi Bakımı: Yapılması Gerekenler ve Sıklığı',
                'meta_aciklama' => 'Kombi bakımı neden önemli, ne sıklıkla yapılır, fiyatı ne kadar? Yıllık bakım sürecinin tüm adımları, kontrol edilen parçalar ve bakım sonrası performans.',
                'anahtar_kelimeler' => [
                    'kombi bakımı', 'kombi bakım fiyatı', 'kombi yıllık bakım', 'kombi servis',
                    'kombi temizliği', 'kombi eşanjör temizliği', 'kombi periyodik bakım',
                    'demirdöküm kombi servis', 'bosch kombi servis', 'kombi arıza',
                ],
                'icerik' => [
                    'Kombi bakımı; cihazın güvenli, verimli ve uzun ömürlü çalışmasını sağlayan periyodik bir kontrol ve temizlik sürecidir. Üretici firmalar yıllık bakımı garanti şartı olarak belirlemekte, doğalgaz mevzuatı da güvenlik gereği yıllık bakımı zorunlu tutmaktadır. Düzenli bakım yapılmayan kombilerde verim düşmesi, yakıt tüketimi artışı, sık arıza ve en kötüsü baca gazı sızıntısı gibi tehlikeli durumlar yaşanır.',

                    'Yıllık standart bakım süreci 60-90 dakika sürer ve şu adımları içerir: ön kontrol ve hata kodu okunması, ana eşanjör temizliği, brülör (alev jiklörlü) sökümü ve karbon temizliği, tutuşturma elektrotu ve iyon kontrolü, baca gazı analizi (CO, CO2 ve O2 ölçümü), gaz basınç ayarları, su basıncı ve genleşme tankı kontrolü, sirkülasyon pompası testi, üç yollu vana fonksiyon testi ve son olarak baca emniyet sistemleri kontrolü.',

                    'Bakım sonrası kombide şu performans iyileşmeleri ölçülebilir: yakıt tüketiminde %5-15 azalma, ses seviyesinde gözle görülür düşüş, ısınma hızının artması ve sıcak su debisinin yükselmesi. Aynı zamanda baca gazı emisyonları yönetmelik sınırlarına çekilir, iç ortam hava kalitesi korunur. İhmal edilen kombiler her yıl ortalama %2-3 verim kaybına uğrar.',

                    '2026 itibarıyla İzmir bölgesinde kombi yıllık bakım fiyatları 850-1500 TL arasındadır. Fiyat farkı; kombi marka/modeli, eşanjör tipi (paslanmaz veya alüminyum), ek arıza tespiti gerekip gerekmediği gibi faktörlere bağlıdır. Demirdöküm yetkili bayisi olan Azra Doğalgaz olarak bakım sonrası 6 aylık işçilik garantisi sunmaktayız.',

                    'Bakım için ideal zaman, ısıtma sezonu öncesi yani Eylül-Ekim aylarıdır. Bu dönemde kombiyi sezona hazır hale getirmek hem konfor hem de güvenlik açısından kritiktir. Kombi 5 yaşını aştıysa bakım sıklığını yılda iki keze çıkarmanızı öneririz; ana eşanjörde kireçlenme ve karbon birikimi hızlanır.',
                ],
                'sss' => [
                    ['Kombi bakımı ne sıklıkla yapılmalı?', 'Yılda en az bir kez, ısıtma sezonu öncesi (Eylül-Ekim) yapılmalıdır. 5 yaş üstü kombiler için yılda iki kez önerilir.'],
                    ['Kombi bakımı kaç para?', '2026 İzmir fiyatları 850-1500 TL aralığındadır. Kombi modeli ve ek arıza durumuna göre değişir.'],
                    ['Bakımsız kombi tehlikeli mi?', 'Evet. Bakımsız kombide karbonmonoksit sızıntısı, baca gazı geri tepmesi ve yangın riski artar. Yıllık bakım hayati önem taşır.'],
                    ['Kombi bakımı ne kadar sürer?', 'Standart bakım 60-90 dakika sürer. Ek arıza tespiti veya parça değişimi varsa süre uzayabilir.'],
                    ['Garantim devam eder mi?', 'Evet. Yetkili servis tarafından yapılan bakım, üretici garantisini sürdürür. Aksine yetkisiz müdahale garantiyi düşürür.'],
                ],
                'ilgili_konular' => ['kombi-secimi-rehberi', 'kombi-arizalari-cozumler'],
                'schema_tip' => 'HowTo',
                'okuma_dakika' => 7,
            ],

            'klima-btu-hesaplama' => [
                'pillar' => 'klima',
                'baslik' => 'Klima BTU Hesaplama: Kaç Metrekareye Kaç BTU?',
                'h1' => 'Klima BTU Nedir, Nasıl Hesaplanır?',
                'meta_aciklama' => 'Klima BTU hesaplama formülü, m² başına kaç BTU yeterli, oda tipine göre hesap. 9000, 12000, 18000 ve 24000 BTU klimaların kapsama alanları.',
                'anahtar_kelimeler' => [
                    'klima btu hesaplama', 'kaç btu klima', 'btu nedir', 'klima boyutlandırma',
                    'inverter klima', 'salon klima btu', 'yatak odası klima', '12000 btu klima',
                    '18000 btu klima', '24000 btu klima', 'multi split klima',
                    'klima fiyatları 2026', 'mitsubishi klima', 'daikin klima',
                ],
                'icerik' => [
                    'BTU (British Thermal Unit) klimaların soğutma ve ısıtma kapasitesini ifade eden uluslararası birimdir. 1 BTU yaklaşık 1055 jouldür ve klima kapasitesi BTU/saat olarak verilir. Doğru BTU seçimi konforu doğrudan etkiler: yetersiz BTU klimanın sürekli çalışıp soğutamamasına, fazla BTU ise nem ayarsızlığına ve yüksek elektrik tüketimine yol açar.',

                    'BTU hesaplamasının temel formülü: oda alanı (m²) × oda yüksekliği (m) × 250 = ihtiyaç duyulan BTU. Bu temel hesaba ek olarak; güneye bakan duvar veya büyük pencere varlığı (+%20), oda içinde elektronik cihaz yoğunluğu (+%10), kalabalık oda kullanımı (kişi başına +500 BTU), mutfaklarda yemek ısı yükü (+%30) gibi düzeltme faktörleri eklenir.',

                    'Pratik bir referans tablo olarak; 20 m²\'ye kadar oda 9000 BTU, 25-35 m² için 12000 BTU, 35-50 m² için 18000 BTU, 50-70 m² için 24000 BTU klima yeterlidir. Salon-mutfak birleşik plan açık alanlarda bir üst kapasite tercih edilmelidir. Apartman dairesinde her odaya ayrı klima gerekiyorsa multi split sistem önerilir; tek dış ünite ile 2-5 iç ünite çalıştırılabilir.',

                    'Inverter teknolojisi 2026 itibarıyla standart hale gelmiştir. Inverter klima kompresör hızını ihtiyaca göre ayarlayarak %30-50 elektrik tasarrufu sağlar. A++ ve A+++ enerji sınıfı modeller uzun vadede yüksek başlangıç maliyetini fazlasıyla amorti eder. Mitsubishi Electric, Daikin, Bosch, Toshiba ve LG önde gelen inverter klima üreticileridir.',

                    'Montaj kalitesi klimanın performansını doğrudan etkiler. Yanlış vakumlanmış sistemde kompresör 1-2 yıl içinde arızalanır. Bakır boru bağlantıları, izolasyon kalınlığı, kondens drenaj eğimi ve dış ünite yerleşimi titizlikle planlanmalıdır. Azra Doğalgaz olarak tüm klima montajlarımızda dijital vakum cihazıyla uzun süreli kaçak testi yapar, 2 yıl montaj garantisi sunarız.',
                ],
                'sss' => [
                    ['25 m² odaya kaç BTU klima yeterli?', '25 m² standart oda için 12000 BTU klima ideal kapasitedir. Güneşe bakan büyük pencere varsa 18000 BTU önerilir.'],
                    ['Inverter klima ne demek?', 'Inverter teknolojisi kompresör hızını ihtiyaca göre ayarlayarak sürekli açık-kapalı yerine değişken hızda çalışır. %30-50 elektrik tasarrufu sağlar.'],
                    ['Klima montajı kaç para?', '12000 BTU split klima montajı dahil 2026 fiyatı 18.000-26.000 TL aralığındadır. Marka ve A+++ enerji sınıfına göre değişir.'],
                    ['Multi split klima nedir?', 'Tek dış üniteye 2 ila 5 farklı iç ünitenin bağlandığı sistem. Her oda bağımsız çalışır, dış cephe estetiği korunur.'],
                    ['En iyi klima markası?', 'Mitsubishi Electric ve Daikin Japon teknolojisi ile uzun ömür sunar. Bosch, LG ve Toshiba güçlü alternatiflerdir.'],
                ],
                'ilgili_konular' => ['klima-bakimi-onemi', 'inverter-klima-avantajlari'],
                'schema_tip' => 'HowTo',
                'okuma_dakika' => 9,
            ],

            'yerden-isitma-rehberi' => [
                'pillar' => 'tesisat',
                'baslik' => 'Yerden Isıtma Sistemi: Maliyet, Verim ve Kurulum Süreci',
                'h1' => 'Yerden Isıtma: Tüm Detaylar 2026',
                'meta_aciklama' => 'Yerden ısıtma nedir, nasıl çalışır, m² fiyatı ne kadar? PEX-AL-PEX boru, kollektör sistemi, oda termostatı ve enerji verimliliği avantajları.',
                'anahtar_kelimeler' => [
                    'yerden ısıtma', 'yerden ısıtma fiyatı', 'yerden ısıtma m² fiyat',
                    'pex al pex boru', 'kollektör sistemi', 'oda termostatı', 'yerden ısıtma kombi',
                    'yerden ısıtma elektrik tüketimi', 'yerden ısıtma vs radyatör', 'yerden ısıtma villa',
                    'yerden ısıtma uygulaması', 'şap betonu yerden ısıtma',
                ],
                'icerik' => [
                    'Yerden ısıtma; ısıtılmış suyun, döşeme altında belirli bir düzende dolaştırılan plastik borular vasıtasıyla iç mekanı dipten yukarıya ısıtmasını sağlayan sistemdir. Geleneksel radyatör ısıtmasına kıyasla %15-30 enerji tasarrufu, daha homojen ısı dağılımı ve dekoratif esneklik (radyatör görünümü olmaması) sunar. Modern yapılarda standart hale gelmiş bir konfor sistemidir.',

                    'Sistem çalışma prensibi şudur: kombi veya ısı pompasından gelen 35-45°C\'lik su, kollektör adı verilen dağıtım noktasından her odaya giden devrelere yönlendirilir. Plastik borular (genellikle PEX-AL-PEX çok katmanlı) döşeme altında 10-20 cm aralıkla yılan biçiminde yerleştirilir. Üzerine şap betonu dökülür ve son olarak parke, seramik veya laminat kaplama yapılır.',

                    'Boru malzemesi seçiminde PEX-AL-PEX (çapraz bağlı polietilen + alüminyum) en yaygın tercihtir; oksijen geçirimsizlik, esneklik ve uzun ömür (50 yıl garanti) sunar. PEX-A ve PE-RT alternatifleridir. Borular 16x2 mm veya 17x2 mm çapındadır. Her oda için ayrı kollektör devresi açılır; oda termostatları ile sıcaklıklar bağımsız ayarlanır.',

                    '2026 itibarıyla yerden ısıtma m² maliyeti İzmir bölgesinde 450-700 TL arasındadır. Bu fiyata; PEX-AL-PEX boru, izolasyon plakası (XPS), ısıtma kollektörü, oda termostatları, otomatik hava tahliye, dengeleme vanaları ve işçilik dahildir. Şap dökümü ayrı bütçelendirilir (yaklaşık 250-350 TL/m²). 100 m² daire için anahtar teslim toplam maliyet 70.000-105.000 TL aralığındadır.',

                    'Yerden ısıtma için ideal ısı kaynağı yoğuşmalı kombi veya hava-su ısı pompasıdır. Düşük sıcaklıkta (35-45°C) su sirkülasyonu yoğuşmalı kombiler için optimum çalışma koşuludur, böylece %107\'ye varan termal verim elde edilir. Hava-su ısı pompalarıyla birleştirildiğinde COP 4.0\'a varan verim katsayısı yakalanır; her 1 kW elektrikle 4 kW ısı üretilir.',
                ],
                'sss' => [
                    ['Yerden ısıtma m² fiyatı ne kadar?', '2026 İzmir fiyatları 450-700 TL/m² aralığındadır. PEX-AL-PEX boru, izolasyon, kollektör ve işçilik dahildir.'],
                    ['Yerden ısıtma mı radyatör mü?', 'Yerden ısıtma %15-30 daha verimlidir, homojen ısı dağılımı ve dekoratif esneklik sunar. İlk yatırım maliyeti yüksektir.'],
                    ['Yerden ısıtma elektrik harcar mı?', 'Hayır, yerden ısıtma elektrikle çalışmaz. Kombi veya ısı pompasından gelen sıcak su sirkülasyonuyla çalışır.'],
                    ['Hangi boru kullanılır?', 'PEX-AL-PEX (çok katmanlı) en yaygın tercihtir. 50 yıl garantili, oksijen geçirimsiz, esnektir.'],
                    ['Yerden ısıtma kaç yıl ömürlü?', 'Kaliteli PEX-AL-PEX boru sistemleri 50 yıl garantilidir. Kollektör ve termostatlar 15-20 yılda yenilenebilir.'],
                ],
                'ilgili_konular' => ['kombi-secimi-rehberi', 'isi-pompasi-nedir'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 8,
            ],

            'isi-pompasi-nedir' => [
                'pillar' => 'tesisat',
                'baslik' => 'Isı Pompası: Çalışma Prensibi, Tipleri ve Verimliliği',
                'h1' => 'Isı Pompası Nedir, Nasıl Çalışır?',
                'meta_aciklama' => 'Isı pompası nedir, nasıl çalışır, COP değeri ne demek? Hava-su ve hava-hava ısı pompaları, yerden ısıtmayla entegrasyon, yıllık tasarruf hesabı.',
                'anahtar_kelimeler' => [
                    'ısı pompası', 'ısı pompası nedir', 'hava su ısı pompası', 'hava hava ısı pompası',
                    'cop değeri', 'ısı pompası fiyat', 'ısı pompası verim', 'a+++ ısı pompası',
                    'mitsubishi ısı pompası', 'daikin altherma', 'ısı pompası yerden ısıtma',
                    'yenilenebilir enerji ısıtma', 'ısı pompası elektrik tüketimi',
                ],
                'icerik' => [
                    'Isı pompası; dış ortamdaki düşük sıcaklıklı havadan veya topraktan ısı alarak iç mekanı ısıtan, yazın ise ters yönde çalışarak soğutan elektrikli cihazdır. Klimalardan farkı, çok daha geniş hava sıcaklığı aralığında verimli çalışacak şekilde tasarlanmasıdır. Avrupa\'da 2030 sonrası fosil yakıt yasakları nedeniyle hızla yaygınlaşan, geleceğin ısıtma teknolojisi olarak kabul edilen sistemdir.',

                    'Çalışma prensibi termodinamik döngüye dayanır. Soğutucu akışkan, dış ünitede ortam havasından ısı çekerek buharlaşır. Kompresör buharı sıkıştırarak sıcaklığını yükseltir. İç ünite veya su tankı içinde yoğuşan akışkan ısıyı bırakır. Ekspansiyon valfı basıncı düşürür ve döngü tekrarlanır. Dış sıcaklık -20°C\'ye kadar verimli çalışan modeller mevcuttur (özel düşük sıcaklık serileri).',

                    'COP (Coefficient of Performance) verim katsayısıdır. 1 kW elektrik ile kaç kW ısı üretildiğini gösterir. Modern A+++ sınıfı hava-su ısı pompalarında COP değeri 3.5-4.5 arasındadır. Yani 1 kW elektrikle 3.5-4.5 kW ısı üretilir. Bu doğalgaz kombiye kıyasla %50-70 daha az enerji tüketimi anlamına gelir. Yıllık verim olarak SCOP (Seasonal COP) değerine bakılır; 4.0 üzeri çok iyidir.',

                    'İki ana tip vardır: hava-su ve hava-hava ısı pompaları. Hava-su tipi, ısıtılan suyu yerden ısıtma sistemine veya radyatöre gönderir; sıcak kullanım suyu da üretir. Yerden ısıtmayla entegre edildiğinde mükemmel uyum sergiler. Hava-hava (multi split klima benzeri) tipi ise içeride doğrudan hava ısıtır; yaz aylarında klima olarak da çalışır. Konut tipine göre uygun olan seçilir.',

                    '2026 itibarıyla 8-12 kW kapasiteli hava-su ısı pompası montaj dahil 85.000-180.000 TL aralığındadır. İlk yatırım yüksek olsa da yıllık enerji tasarrufu (doğalgaza göre 40.000-60.000 TL/yıl) düşünüldüğünde 4-6 yılda kendini amorti eder. KVKK Yenilenebilir Enerji teşvikleri ile vergi avantajı da sağlanmaktadır. Mitsubishi Ecodan, Daikin Altherma, Bosch Compress ve Vaillant aroTHERM önde gelen markalardır.',
                ],
                'sss' => [
                    ['Isı pompası elektrik faturasını arttırır mı?', 'COP 4.0 değerinde her 1 kW elektrikle 4 kW ısı üretildiği için doğalgaza göre %50-70 daha az enerji tüketir. Toplam fatura düşer.'],
                    ['Isı pompası hangi sıcaklıkta çalışır?', 'Modern modeller -20°C\'ye kadar verimli çalışır. Türkiye iklim koşullarında performans kaybı çok düşüktür.'],
                    ['Hava-su mu, hava-hava mı?', 'Yerden ısıtma veya radyatörlü konutta hava-su, klima tarzı kullanımda hava-hava. İhtiyaca göre seçilir.'],
                    ['Isı pompası fiyatı?', '8-12 kW hava-su ısı pompası montaj dahil 85.000-180.000 TL aralığındadır. KVKK teşvikleri ile düşürülebilir.'],
                    ['Kaç yılda kendini amorti eder?', 'Yıllık enerji tasarrufu nedeniyle 4-6 yılda yatırım geri döner. Sistem ömrü 15-20 yıldır.'],
                ],
                'ilgili_konular' => ['yerden-isitma-rehberi', 'kombi-secimi-rehberi'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 9,
            ],

            'sizdirmazlik-testi-nedir' => [
                'pillar' => 'dogalgaz',
                'baslik' => 'Sızdırmazlık Testi Nedir, Doğalgaz Tesisatında Neden Zorunlu?',
                'h1' => 'Doğalgaz Sızdırmazlık Testi Süreci',
                'meta_aciklama' => 'Sızdırmazlık testi nedir, kaç bar basınçta yapılır, ne kadar sürer? Test geçmezse ne olur, hangi cihazlar kullanılır?',
                'anahtar_kelimeler' => [
                    'sızdırmazlık testi', 'doğalgaz sızdırmazlık', 'basınç testi doğalgaz',
                    'gaz testi', 'tesisat testi', 'kaçak testi', 'gaz sızıntısı tespit',
                ],
                'icerik' => [
                    'Sızdırmazlık testi; yeni döşenen veya değiştirilen doğalgaz tesisatının basınç altında gaz kaybı yapıp yapmadığını ölçen zorunlu bir kontrol prosedürüdür. EPDK ve dağıtım şirketi mevzuatı gereği gaz açılışından önce mutlaka yapılması gereken son kalite kontrol adımıdır. Test geçilmeden gaz arzı kesinlikle yapılmaz.',

                    'Test prosedürü standartlaşmıştır: tesisat hattı kapatılır, manometre veya dijital basınç ölçer takılır. Hava veya azot ile 100 mbar (1000 mmH2O) basınca getirilir. En az 10 dakika gözlem süresi boyunca basınç düşüşü kayıt edilir. Düşüş 0.1 mbar\'dan az olmalıdır; aksi durumda test başarısız sayılır ve kaçak noktası tespit edilir.',

                    'Kaçak tespiti için sabunlu su yöntemi en yaygın olanıdır; tüm bağlantı noktalarına sabunlu su sürülür ve kabarcık oluşumu gözlenir. Modern uygulamalarda hassas elektronik gaz kaçak detektörleri kullanılır; ppm seviyesinde sızıntıları tespit eder. Kaçak bulunursa bağlantı sökülür, conta yenilenir veya boru değiştirilir, test tekrarlanır.',

                    'Test başarılı olduktan sonra tesisat raporu hazırlanır ve dağıtım şirketinin teknik personeline kontrol için bildirim yapılır. Şirket ekibi yerinde inceleme yaparak kendi onay testlerini gerçekleştirir; her şey usulüne uygunsa Doğalgaz Uygunluk Belgesi tanzim edilir ve gaz arzı sağlanır. Tüm süreç ortalama 1-3 iş günü içinde tamamlanır.',
                ],
                'sss' => [
                    ['Sızdırmazlık testi kaç bar?', 'Standart test 100 mbar (1000 mmH2O) basınçta yapılır. En az 10 dakika gözlem yapılır.'],
                    ['Test geçilmezse ne olur?', 'Kaçak noktası bulunup giderilir, test tekrarlanır. Tüm tesisat geçene kadar gaz açılmaz.'],
                    ['Test ne kadar sürer?', 'Hazırlık dahil 30-60 dakikalık bir prosedürdür. Tesisat boyutuna göre değişir.'],
                ],
                'ilgili_konular' => ['dogalgaz-tesisati-nedir', 'apartman-toplu-dogalgaz'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 5,
            ],

            'dogalgaz-donusum-fiyat' => [
                'pillar' => 'dogalgaz',
                'baslik' => 'Doğalgaz Dönüşüm Maliyeti 2026: Soba ve Kaloriferden Doğalgaza',
                'h1' => 'Doğalgaz Dönüşümü: Tüm Maliyetler 2026',
                'meta_aciklama' => 'Sobadan, kalorifederden, LPG\'den doğalgaza dönüşüm maliyeti ne kadar? 2026 İzmir fiyatları, tasarruf hesabı ve dönüşüm süreci.',
                'anahtar_kelimeler' => [
                    'doğalgaz dönüşüm', 'doğalgaz dönüşüm fiyat', 'sobadan doğalgaza',
                    'kaloriferden doğalgaza', 'lpg doğalgaza geçiş', 'doğalgaz tasarruf',
                    'doğalgaz toplu dönüşüm', 'apartman doğalgaz dönüşüm',
                ],
                'icerik' => [
                    'Doğalgaz dönüşümü; mevcut soba, kat kaloriferi, kömür sistemi, LPG\'li tüplü sistem veya fuel-oil kazanın doğalgaz altyapısına geçirilmesi sürecidir. Yıllık ortalama %35 yakıt tasarrufu, daha temiz hava, otomatik kontrol, 24 saat sıcak su ve istenen odanın bireysel ısıtılabilmesi gibi avantajlar sunar. İzmir merkez ve çevre ilçelerde dönüşüm yıllarca devam etmektedir.',

                    'Tipik bir 100 m² daire için 2026 itibarıyla anahtar teslim dönüşüm maliyeti şöyledir: doğalgaz iç tesisatı (proje + döşeme + sızdırmazlık + gaz açma) 25.000-45.000 TL, A sınıfı yoğuşmalı 24 kW kombi (montaj dahil) 30.000-42.000 TL, eski sistemin sökülmesi ve baca dönüşümü 5.000-12.000 TL. Toplam 70.000-120.000 TL aralığında bütçelendirilir.',

                    'Tasarruf hesabı dönüşüm yatırımının kendini amorti süresini gösterir. Ortalama 100 m² dairede yıllık ısıtma ve sıcak su gideri sobada 25.000-35.000 TL, kat kaloriferinde 30.000-40.000 TL\'dir. Doğalgazda aynı tüketim 18.000-25.000 TL\'ye düşer. Yıllık tasarruf 8.000-15.000 TL aralığındadır; dönüşüm yatırımı 6-9 yılda kendini amorti eder ve sonrasında saf tasarruf başlar.',

                    'Apartman ölçeğindeki toplu dönüşümler ek avantajlar sunar. Kat kaloriferli (merkezi sistem) bir apartmanda toplu yenileme; her daireye bireysel kombi (kullanım esnekliği), %15-20 toplu fiyat avantajı, hatalı eski tesisatın güvenli sökülmesi, tek koordinatör avantajı sağlar. Yöneticiler için karar metni desteği ve tüm süreç boyunca tek temsilci hizmeti sunulur.',
                ],
                'sss' => [
                    ['Sobadan doğalgaza geçiş kaç para?', '100 m² daire için anahtar teslim toplam 70.000-120.000 TL aralığındadır. Tesisat + kombi + söküm dahil.'],
                    ['Kaç yılda kendini amorti eder?', 'Yıllık ortalama 8.000-15.000 TL tasarruf ile 6-9 yılda yatırım geri döner.'],
                    ['Apartman toplu dönüşüm avantajı?', '%15-20 fiyat indirimi, tek koordinatör, hızlı süreç. Kat malikleri kararı sonrası hızla başlanır.'],
                    ['LPG\'den doğalgaza geçiş?', 'LPG sistem sökümü, doğalgaz tesisatı kurulumu ve kombi montajı ile gerçekleşir. Süre 7-10 iş günü.'],
                ],
                'ilgili_konular' => ['dogalgaz-tesisati-nedir', 'apartman-toplu-dogalgaz', 'kombi-secimi-rehberi'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 7,
            ],

            'apartman-toplu-dogalgaz' => [
                'pillar' => 'dogalgaz',
                'baslik' => 'Apartman Toplu Doğalgaz Tesisat: Yönetici ve Kat Malikleri Rehberi',
                'h1' => 'Apartman Doğalgaz Yenileme Süreci',
                'meta_aciklama' => 'Apartman doğalgaz toplu projesi nasıl yapılır, kat malikleri kararı, maliyet paylaşımı, süreç adımları ve toplu fiyat avantajı.',
                'anahtar_kelimeler' => [
                    'apartman doğalgaz', 'toplu doğalgaz tesisat', 'apartman doğalgaz dönüşüm',
                    'kat kaloriferi doğalgaza', 'apartman yöneticisi doğalgaz', 'site doğalgaz tesisat',
                    'kolon hattı doğalgaz', 'apartman karar defteri doğalgaz',
                ],
                'icerik' => [
                    'Apartman toplu doğalgaz tesisatı; çok daireli binalarda merkezi kalorifer sisteminin sökülerek her daireye bireysel kombi ve doğalgaz tesisatı kurulması veya hiç doğalgaz olmayan bir binanın baştan tesisatlanmasıdır. Eski merkezi sistemler ortak yakıt tüketimi, tek termostat, sınırlı esneklik gibi sorunlar barındırırken bireysel sistemler kat maliklerine tam kontrol sunar.',

                    'Süreç kat malikleri kurulu kararı ile başlar. Kanunen tüm kat maliklerinin oybirliği gerekmez; salt çoğunluk kararı yeterlidir (Kat Mülkiyeti Kanunu m.42 ve 44). Karar defterine işlenen karar metninde tesisat firması seçimi, maliyet paylaşımı yöntemi (eşit pay veya alan oranlı) ve uygulama takvimi netleştirilir. Yönetici, sürecin tek koordinatörü olarak çalışır.',

                    'Tipik 8 daireli bir apartman projesinde maliyet kalemleri şöyledir: kolon hattı (ana boru sistemi) 35.000-55.000 TL, daire içi tesisatlar (her biri 25.000-35.000 TL × 8 = 200.000-280.000 TL), her daireye 24 kW yoğuşmalı kombi (30.000-40.000 TL × 8 = 240.000-320.000 TL), eski sistem söküm ve baca dönüşümü 25.000-50.000 TL. Toplam apartman bütçesi 500.000-705.000 TL aralığında olabilir; daire başı 62.000-88.000 TL.',

                    'Toplu projelerin avantajı pürüzsüz koordinasyondur. Tek bir tesisatçı tüm kat maliklerini çözüm ortağı olarak alır, paralel olarak çalışan ekiplerle uygulama hızlanır. Toplu malzeme alımı sayesinde %15-20 fiyat indirimi sağlanır. Yöneticilik süresi boyunca yapılacak en pürüzsüz işlerden biri toplu doğalgaz dönüşümüdür; ortak alanlar tek seferde düzenlenir, daireler bireysel olarak kontrol edilir.',
                ],
                'sss' => [
                    ['Apartman doğalgaz dönüşümü için hangi karar gerekli?', 'Kat malikleri kurulu salt çoğunluk kararı yeterlidir. Karar defterine işlenmesi şarttır.'],
                    ['Toplu projede daire başı maliyet?', '8 daireli apartman projesinde daire başı 62.000-88.000 TL. Tesisat + kombi + söküm dahil.'],
                    ['Süreç ne kadar sürer?', 'Apartman ölçeğinde 15-25 iş günü. Daireler kademeli olarak teslim edilir.'],
                    ['Yöneticilik için ne yapmam gerek?', 'Karar metni hazırlama, malik bilgilendirme ve tek koordinatör olarak iletişim. Tesisat firması destek sağlar.'],
                ],
                'ilgili_konular' => ['dogalgaz-tesisati-nedir', 'dogalgaz-donusum-fiyat'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 8,
            ],

            'kombi-arizalari-cozumler' => [
                'pillar' => 'kombi',
                'baslik' => 'Kombi Arıza Kodları ve Çözümleri: En Sık Karşılaşılan Sorunlar',
                'h1' => 'Kombi Arızaları: Hata Kodu Tablosu ve Çözümler',
                'meta_aciklama' => 'Kombi yanmıyor, su basıncı düşük, hata kodu çıkıyor? En sık kombi arızaları ve evde uygulanabilen ilk müdahaleler.',
                'anahtar_kelimeler' => [
                    'kombi arızaları', 'kombi hata kodu', 'kombi yanmıyor', 'kombi su basıncı düşük',
                    'kombi alev sönüyor', 'kombi sıcak su gelmiyor', 'kombi gürültü yapıyor',
                    'demirdöküm kombi hata', 'bosch kombi hata', 'vaillant kombi arıza',
                ],
                'icerik' => [
                    'Kombi arızaları çoğu zaman basit nedenlerden kaynaklanır ve doğru tespitle hızla giderilir. Bu rehberde en sık karşılaşılan 8 arıza durumu ve ilk müdahale önerileri yer almaktadır. Önemli not: kombi gaz hattıyla bağlantılı bir cihazdır; herhangi bir gaz kokusu durumunda kombiye dokunmayın, gaz vanasını kapatın ve 187 hattını arayın.',

                    'Su basıncı düşük arızası en yaygın olanıdır (genellikle 0.5 bar altı). Manometre kırmızı bölgede gösterir, kombi çalışmaz veya peteklere gitmez. Çözüm: kombinin alt kısmındaki dolum vanasından (genellikle metal hortum) su eklenir, manometre 1-1.5 bar arasına geldiğinde vana kapatılır. Sık tekrarlanıyorsa genleşme tankı veya bir sızıntı kontrol edilmelidir.',

                    'Alev sönmesi (E1, F1, A1 gibi hata kodları) gaz akışı veya tutuşturma sistemi kaynaklıdır. Önce gaz vanasının açık olduğu kontrol edilir. Kombi reset düğmesine 1 kez basılır; tekrar denenir. Sürekli yanmıyorsa elektrod kirlenmesi, gaz valfi arızası veya ana kart sorunu olabilir; servis çağrısı gereklidir.',

                    'Sıcak su gelmemesi durumunda; kombi reset edilir, su musluğunun yeterli debide aktığı kontrol edilir (1 lt/dk altı debilerde sensör tetiklenmez), sıcak su ayar düğmesi orta-yüksek konuma getirilir. Sorun devam ederse plaka tipi eşanjör kireçlenmesi (sert su bölgelerinde 4-6 yılda olağan) veya akış sensörü arızası olabilir.',

                    'Anormal gürültü; pompada hava kalması, peteklerde dolaşmayan hava cepleri veya kireçlenme kaynaklıdır. Peteklerin hava tahliye vanaları açılarak hava boşaltılır. Sirkülasyon pompası ses yapıyorsa servis çağrılmalıdır. Tıkırtı ve cızırtı seslerinde eşanjör temizliği gerekebilir; bu yıllık bakımın bir parçasıdır.',
                ],
                'sss' => [
                    ['Kombi su basıncı nasıl arttırılır?', 'Alt kısımdaki dolum vanasından su eklenir, manometre 1-1.5 bar arasına gelince vana kapatılır.'],
                    ['Kombi hata kodu E1 ne demek?', 'Genelde alev sönmesi veya tutuşturma sorunudur. Reset denenir, devam ederse servis çağrılmalıdır.'],
                    ['Kombi gürültü yaparsa?', 'Genelde hava cebi veya kireçlenme. Petek hava tahliye vanaları açılır, sürerse servis gerekir.'],
                    ['Kombide gaz kokusu varsa ne yapmalı?', 'Gaz vanasını kapat, kombiye dokunma, ortamı havalandır, 187 hattını ara. Kesinlikle elektrik açma/kapatma yapma.'],
                ],
                'ilgili_konular' => ['kombi-bakimi-nasil-yapilir', 'kombi-secimi-rehberi'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 8,
            ],

            'klima-bakimi-onemi' => [
                'pillar' => 'klima',
                'baslik' => 'Klima Bakımı Neden Önemli? Yıllık Servis Süreci',
                'h1' => 'Klima Bakım Rehberi: Süreç ve Maliyet',
                'meta_aciklama' => 'Klima bakımı neden gerekli, kaç yılda bir yapılır, fiyatı ne kadar? Filtre temizliği, gaz kontrolü, dış ünite bakımı süreci.',
                'anahtar_kelimeler' => [
                    'klima bakımı', 'klima bakım fiyatı', 'klima filtre temizliği', 'klima gaz dolumu',
                    'klima servis', 'klima dış ünite bakım', 'inverter klima bakım',
                    'mitsubishi klima servis', 'daikin klima servis',
                ],
                'icerik' => [
                    'Klima bakımı; cihazın soğutma performansını koruması, elektrik tüketimini düşük tutması ve uzun ömürlü çalışması için yıllık olarak yapılması gereken kontrol ve temizlik prosedürüdür. İhmal edilen klimalar; kompresör arızası, gaz kaçağı, küflü hava ve %20\'ye varan elektrik tüketim artışı gibi sorunlar yaşar. İdeal bakım zamanı yaz sezonu öncesi (Mayıs) veya sonrasıdır.',

                    'Standart yıllık bakım 60-90 dakika sürer ve şu adımları içerir: iç ünite filtre sökümü, alkali bazlı temizlik kimyasalı ile ön panel temizliği, evaporatör (iç ünite radyatörü) yüksek basınçlı su ile temizlenmesi, kondens drenaj kanalı tahliyesi, dış ünite kondenser temizliği, soğutucu gaz basıncı ölçümü, sızıntı kontrolü, kompresör akım ölçümü ve son olarak performans testi.',

                    'Bakım yapılmamış bir klimada şu belirtiler görülür: aniden artan elektrik faturası (filtreler tıkalı, kompresör daha çok yorulur), iç üniteden kötü koku (mantar, küf birikimi), iç üniteden su damlaması (drenaj tıkanıklığı), soğutmada azalma (gaz kaybı veya kirli kondenser), dış ünitede aşırı ses (yağ kaybı veya kompresör problemi). Bu belirtilerde acil bakım gereklidir.',

                    '2026 itibarıyla klima bakım fiyatları İzmir bölgesinde 600-1200 TL aralığındadır. Fiyat farkı; klima tipine (split, salon tipi, multi split), iç ünite sayısına ve bakım kapsamına bağlıdır. Multi split sistemler için her iç ünite ayrı ücretlendirilir (fiyat 50-65% civarında dış ünite bakımına ek olarak). Mitsubishi Electric ve Daikin gibi premium markalar için yetkili servis fiyatları biraz daha yüksek olabilir.',
                ],
                'sss' => [
                    ['Klima bakımı kaç para?', '2026 İzmir fiyatları 600-1200 TL aralığındadır. Multi split sistemlerde iç ünite başına ek ücret.'],
                    ['Klima ne sıklıkla bakım ister?', 'Yılda en az bir kez, ideal olarak yaz sezonu öncesi. Yoğun kullanımlı veya tozlu ortamlarda 6 ayda bir.'],
                    ['Filtre kendim temizleyebilir miyim?', 'Evet. İç ünite filtreleri çıkarılıp musluk altında yıkanabilir. Yılda 2-3 kez yapılması önerilir.'],
                    ['Gaz dolumu lazım mı?', 'Sadece gaz kaçağı tespit edildiğinde. Modern klimalar fabrikada kapalı sistem olarak gönderilir; rutin gaz takviyesi gerekmez.'],
                ],
                'ilgili_konular' => ['klima-btu-hesaplama', 'inverter-klima-avantajlari'],
                'schema_tip' => 'HowTo',
                'okuma_dakika' => 6,
            ],

            'inverter-klima-avantajlari' => [
                'pillar' => 'klima',
                'baslik' => 'Inverter Klima Nedir, Standart Klimadan Farkı?',
                'h1' => 'Inverter Klima: Çalışma Prensibi ve Tasarrufu',
                'meta_aciklama' => 'Inverter klima ne demek, normal klimadan farkı nedir, gerçekten %50 elektrik tasarrufu eder mi? Teknik detaylar ve karşılaştırma.',
                'anahtar_kelimeler' => [
                    'inverter klima', 'inverter klima avantajları', 'inverter klima nedir',
                    'inverter klima tasarrufu', 'a+++ inverter klima', 'inverter vs standart klima',
                    'dc inverter', 'değişken hızlı kompresör',
                ],
                'icerik' => [
                    'Inverter klima; kompresör hızını ihtiyaca göre sürekli ayarlayan, sabit hızlı standart klimalara kıyasla çok daha verimli çalışan modern klima teknolojisidir. 2026 itibarıyla kalitelı klimaların standart özelliği haline gelmiştir; A++ ve A+++ enerji sınıfı klimaların tamamı inverter teknolojisi kullanır.',

                    'Standart klimalar (sabit hızlı kompresörlü) açık-kapalı (on-off) prensibiyle çalışır. Hedef sıcaklığa ulaşıldığında kompresör tamamen kapanır, sıcaklık 1-2°C yükselince yeniden devreye girer. Bu döngü saatte 6-10 kez tekrarlanır. Her devreye girişte yüksek başlangıç akımı çekilir, sıcaklık dalgalı seyreder, ses iniş çıkışları olur.',

                    'Inverter klima ise frekans değiştirici (inverter modülü) sayesinde kompresörün dönüş hızını sürekli ayarlar. Hedef sıcaklığa ulaşıldıktan sonra kompresör düşük hızda çalışmaya devam ederek sıcaklığı sabit tutar. Hiç durmaz, sürekli ince ayar yapar. Bu sayede ortalama %30-50 elektrik tasarrufu, daha sessiz çalışma, daha hızlı soğutma ve sabit konfor sıcaklığı elde edilir.',

                    'A+++ enerji sınıfı bir inverter klima ile A enerji sınıfı standart klima karşılaştırılırsa; yıllık 1000 saat kullanımda inverter 350-400 kWh, standart klima 700-800 kWh elektrik tüketir. 2026 elektrik tarifelerinde yıllık tasarruf 2.500-4.000 TL arasında olabilir; klima ömrü 10-15 yıl düşünüldüğünde toplam tasarruf 25.000-50.000 TL\'ye ulaşır.',

                    'Önde gelen inverter klima üreticileri Mitsubishi Electric Hyper Heat, Daikin Perfera, Bosch Climate, Toshiba Seiya, LG Dual Cool, Vestel ve Arçelik\'tir. 2026 itibarıyla 12000 BTU A+++ inverter klima fiyatları 18.000-35.000 TL aralığındadır. Premium markalar (Mitsubishi, Daikin) ortalama 5.000-8.000 TL daha yüksek olsa da uzun vadede yatırımı amorti eder.',
                ],
                'sss' => [
                    ['Inverter klima gerçekten tasarruf eder mi?', 'Evet. A+++ inverter klimalar standart A sınıfına göre %30-50 az elektrik tüketir. Yıllık 2.500-4.000 TL tasarruf mümkün.'],
                    ['Inverter klima daha mı pahalı?', 'Başlangıçta 5.000-8.000 TL daha yüksek olabilir. Ancak elektrik tasarrufu nedeniyle 2-3 yılda farkı kapatır.'],
                    ['Inverter klima daha sessiz mi?', 'Evet. Sürekli düşük hızda çalıştığı için iç ünite gürültüsü 19-22 dB seviyesindedir; standart klima 30-35 dB.'],
                    ['Inverter klima bakım gerektirir mi?', 'Evet, yıllık bakım gerekir. Standart klimalarla aynı bakım prosedürü uygulanır.'],
                ],
                'ilgili_konular' => ['klima-btu-hesaplama', 'klima-bakimi-onemi'],
                'schema_tip' => 'Article',
                'okuma_dakika' => 7,
            ],
        ];
    }
}

if (!function_exists('seo_konu_bul')) {
    function seo_konu_bul(string $slug): ?array
    {
        $konular = seo_konular();
        return $konular[$slug] ?? null;
    }
}

if (!function_exists('seo_pillar_konular')) {
    /**
     * Bir pillar'a ait tüm konuları döndür.
     */
    function seo_pillar_konular(string $pillar): array
    {
        return array_filter(seo_konular(), fn($k) => ($k['pillar'] ?? '') === $pillar);
    }
}
