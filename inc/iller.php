<?php
/**
 * Azra Doğalgaz — Türkiye 81 İl Veritabanı (v1.12.27)
 * 
 * Lokal SEO landing page'leri için il metadata'sı.
 * Pattern: /il/{slug}-dogalgaz-tesisati-firmasi
 *          /il/{slug}-kombi-servisi
 *          /il/{slug}-klima-montaji
 * 
 * Birincil hedef: İzmir + komşu Ege illeri (Manisa, Aydın, Muğla, Denizli)
 * İkincil: Türkiye genelinde organik trafik
 */

if (!function_exists('iller_listesi')) {
    function iller_listesi(): array
    {
        // [slug, ad, plaka, bolge, nufus_2024, dogalgaz_var, oncelik]
        // dogalgaz_var: il merkezinde doğalgaz altyapısı var mı (referans için, hizmet ima etmez)
        // oncelik: 1=birincil hedef (Ege), 2=büyük şehir, 3=orta, 4=düşük
        return [
            'adana'          => ['ad' => 'Adana',          'plaka' => '01', 'bolge' => 'Akdeniz',         'nufus' => 2274106, 'dogalgaz' => true,  'oncelik' => 2],
            'adiyaman'       => ['ad' => 'Adıyaman',       'plaka' => '02', 'bolge' => 'Güneydoğu Anadolu','nufus' => 635169,  'dogalgaz' => true,  'oncelik' => 4],
            'afyonkarahisar' => ['ad' => 'Afyonkarahisar', 'plaka' => '03', 'bolge' => 'Ege',             'nufus' => 747555,  'dogalgaz' => true,  'oncelik' => 2],
            'agri'           => ['ad' => 'Ağrı',           'plaka' => '04', 'bolge' => 'Doğu Anadolu',    'nufus' => 511238,  'dogalgaz' => true,  'oncelik' => 4],
            'aksaray'        => ['ad' => 'Aksaray',        'plaka' => '68', 'bolge' => 'İç Anadolu',      'nufus' => 433055,  'dogalgaz' => true,  'oncelik' => 4],
            'amasya'         => ['ad' => 'Amasya',         'plaka' => '05', 'bolge' => 'Karadeniz',       'nufus' => 339735,  'dogalgaz' => true,  'oncelik' => 4],
            'ankara'         => ['ad' => 'Ankara',         'plaka' => '06', 'bolge' => 'İç Anadolu',      'nufus' => 5803482, 'dogalgaz' => true,  'oncelik' => 2],
            'antalya'        => ['ad' => 'Antalya',        'plaka' => '07', 'bolge' => 'Akdeniz',         'nufus' => 2696249, 'dogalgaz' => true,  'oncelik' => 2],
            'ardahan'        => ['ad' => 'Ardahan',        'plaka' => '75', 'bolge' => 'Doğu Anadolu',    'nufus' => 92819,   'dogalgaz' => true,  'oncelik' => 4],
            'artvin'         => ['ad' => 'Artvin',         'plaka' => '08', 'bolge' => 'Karadeniz',       'nufus' => 169867,  'dogalgaz' => true,  'oncelik' => 4],
            'aydin'          => ['ad' => 'Aydın',          'plaka' => '09', 'bolge' => 'Ege',             'nufus' => 1148241, 'dogalgaz' => true,  'oncelik' => 1],
            'balikesir'      => ['ad' => 'Balıkesir',      'plaka' => '10', 'bolge' => 'Marmara',         'nufus' => 1268519, 'dogalgaz' => true,  'oncelik' => 2],
            'bartin'         => ['ad' => 'Bartın',         'plaka' => '74', 'bolge' => 'Karadeniz',       'nufus' => 207141,  'dogalgaz' => true,  'oncelik' => 4],
            'batman'         => ['ad' => 'Batman',         'plaka' => '72', 'bolge' => 'Güneydoğu Anadolu','nufus' => 642422,  'dogalgaz' => true,  'oncelik' => 4],
            'bayburt'        => ['ad' => 'Bayburt',        'plaka' => '69', 'bolge' => 'Karadeniz',       'nufus' => 86047,   'dogalgaz' => true,  'oncelik' => 4],
            'bilecik'        => ['ad' => 'Bilecik',        'plaka' => '11', 'bolge' => 'Marmara',         'nufus' => 230940,  'dogalgaz' => true,  'oncelik' => 4],
            'bingol'         => ['ad' => 'Bingöl',         'plaka' => '12', 'bolge' => 'Doğu Anadolu',    'nufus' => 286602,  'dogalgaz' => true,  'oncelik' => 4],
            'bitlis'         => ['ad' => 'Bitlis',         'plaka' => '13', 'bolge' => 'Doğu Anadolu',    'nufus' => 350926,  'dogalgaz' => true,  'oncelik' => 4],
            'bolu'           => ['ad' => 'Bolu',           'plaka' => '14', 'bolge' => 'Karadeniz',       'nufus' => 326734,  'dogalgaz' => true,  'oncelik' => 3],
            'burdur'         => ['ad' => 'Burdur',         'plaka' => '15', 'bolge' => 'Akdeniz',         'nufus' => 273716,  'dogalgaz' => true,  'oncelik' => 4],
            'bursa'          => ['ad' => 'Bursa',          'plaka' => '16', 'bolge' => 'Marmara',         'nufus' => 3214571, 'dogalgaz' => true,  'oncelik' => 2],
            'canakkale'      => ['ad' => 'Çanakkale',      'plaka' => '17', 'bolge' => 'Marmara',         'nufus' => 564420,  'dogalgaz' => true,  'oncelik' => 3],
            'cankiri'        => ['ad' => 'Çankırı',        'plaka' => '18', 'bolge' => 'İç Anadolu',      'nufus' => 199818,  'dogalgaz' => true,  'oncelik' => 4],
            'corum'          => ['ad' => 'Çorum',          'plaka' => '19', 'bolge' => 'Karadeniz',       'nufus' => 524388,  'dogalgaz' => true,  'oncelik' => 4],
            'denizli'        => ['ad' => 'Denizli',        'plaka' => '20', 'bolge' => 'Ege',             'nufus' => 1066867, 'dogalgaz' => true,  'oncelik' => 1],
            'diyarbakir'     => ['ad' => 'Diyarbakır',     'plaka' => '21', 'bolge' => 'Güneydoğu Anadolu','nufus' => 1818133, 'dogalgaz' => true,  'oncelik' => 3],
            'duzce'          => ['ad' => 'Düzce',          'plaka' => '81', 'bolge' => 'Karadeniz',       'nufus' => 410875,  'dogalgaz' => true,  'oncelik' => 4],
            'edirne'         => ['ad' => 'Edirne',         'plaka' => '22', 'bolge' => 'Marmara',         'nufus' => 414714,  'dogalgaz' => true,  'oncelik' => 4],
            'elazig'         => ['ad' => 'Elazığ',         'plaka' => '23', 'bolge' => 'Doğu Anadolu',    'nufus' => 596943,  'dogalgaz' => true,  'oncelik' => 4],
            'erzincan'       => ['ad' => 'Erzincan',       'plaka' => '24', 'bolge' => 'Doğu Anadolu',    'nufus' => 240881,  'dogalgaz' => true,  'oncelik' => 4],
            'erzurum'        => ['ad' => 'Erzurum',        'plaka' => '25', 'bolge' => 'Doğu Anadolu',    'nufus' => 743126,  'dogalgaz' => true,  'oncelik' => 3],
            'eskisehir'      => ['ad' => 'Eskişehir',      'plaka' => '26', 'bolge' => 'İç Anadolu',      'nufus' => 915418,  'dogalgaz' => true,  'oncelik' => 3],
            'gaziantep'      => ['ad' => 'Gaziantep',      'plaka' => '27', 'bolge' => 'Güneydoğu Anadolu','nufus' => 2154051, 'dogalgaz' => true,  'oncelik' => 2],
            'giresun'        => ['ad' => 'Giresun',        'plaka' => '28', 'bolge' => 'Karadeniz',       'nufus' => 451811,  'dogalgaz' => true,  'oncelik' => 4],
            'gumushane'      => ['ad' => 'Gümüşhane',      'plaka' => '29', 'bolge' => 'Karadeniz',       'nufus' => 144544,  'dogalgaz' => true,  'oncelik' => 4],
            'hakkari'        => ['ad' => 'Hakkari',        'plaka' => '30', 'bolge' => 'Doğu Anadolu',    'nufus' => 280991,  'dogalgaz' => false, 'oncelik' => 4],
            'hatay'          => ['ad' => 'Hatay',          'plaka' => '31', 'bolge' => 'Akdeniz',         'nufus' => 1544640, 'dogalgaz' => true,  'oncelik' => 3],
            'igdir'          => ['ad' => 'Iğdır',          'plaka' => '76', 'bolge' => 'Doğu Anadolu',    'nufus' => 203594,  'dogalgaz' => true,  'oncelik' => 4],
            'isparta'        => ['ad' => 'Isparta',        'plaka' => '32', 'bolge' => 'Akdeniz',         'nufus' => 444914,  'dogalgaz' => true,  'oncelik' => 4],
            'istanbul'       => ['ad' => 'İstanbul',       'plaka' => '34', 'bolge' => 'Marmara',         'nufus' => 15655924,'dogalgaz' => true,  'oncelik' => 2],
            'izmir'          => ['ad' => 'İzmir',          'plaka' => '35', 'bolge' => 'Ege',             'nufus' => 4462056, 'dogalgaz' => true,  'oncelik' => 1],
            'kahramanmaras'  => ['ad' => 'Kahramanmaraş',  'plaka' => '46', 'bolge' => 'Akdeniz',         'nufus' => 1116618, 'dogalgaz' => true,  'oncelik' => 3],
            'karabuk'        => ['ad' => 'Karabük',        'plaka' => '78', 'bolge' => 'Karadeniz',       'nufus' => 252058,  'dogalgaz' => true,  'oncelik' => 4],
            'karaman'        => ['ad' => 'Karaman',        'plaka' => '70', 'bolge' => 'İç Anadolu',      'nufus' => 256804,  'dogalgaz' => true,  'oncelik' => 4],
            'kars'           => ['ad' => 'Kars',           'plaka' => '36', 'bolge' => 'Doğu Anadolu',    'nufus' => 274829,  'dogalgaz' => true,  'oncelik' => 4],
            'kastamonu'      => ['ad' => 'Kastamonu',      'plaka' => '37', 'bolge' => 'Karadeniz',       'nufus' => 380366,  'dogalgaz' => true,  'oncelik' => 4],
            'kayseri'        => ['ad' => 'Kayseri',        'plaka' => '38', 'bolge' => 'İç Anadolu',      'nufus' => 1452660, 'dogalgaz' => true,  'oncelik' => 3],
            'kilis'          => ['ad' => 'Kilis',          'plaka' => '79', 'bolge' => 'Güneydoğu Anadolu','nufus' => 144139,  'dogalgaz' => true,  'oncelik' => 4],
            'kirikkale'      => ['ad' => 'Kırıkkale',      'plaka' => '71', 'bolge' => 'İç Anadolu',      'nufus' => 286372,  'dogalgaz' => true,  'oncelik' => 4],
            'kirklareli'     => ['ad' => 'Kırklareli',     'plaka' => '39', 'bolge' => 'Marmara',         'nufus' => 376945,  'dogalgaz' => true,  'oncelik' => 4],
            'kirsehir'       => ['ad' => 'Kırşehir',       'plaka' => '40', 'bolge' => 'İç Anadolu',      'nufus' => 246548,  'dogalgaz' => true,  'oncelik' => 4],
            'kocaeli'        => ['ad' => 'Kocaeli',        'plaka' => '41', 'bolge' => 'Marmara',         'nufus' => 2102907, 'dogalgaz' => true,  'oncelik' => 2],
            'konya'          => ['ad' => 'Konya',          'plaka' => '42', 'bolge' => 'İç Anadolu',      'nufus' => 2330024, 'dogalgaz' => true,  'oncelik' => 2],
            'kutahya'        => ['ad' => 'Kütahya',        'plaka' => '43', 'bolge' => 'Ege',             'nufus' => 580701,  'dogalgaz' => true,  'oncelik' => 2],
            'malatya'        => ['ad' => 'Malatya',        'plaka' => '44', 'bolge' => 'Doğu Anadolu',    'nufus' => 814520,  'dogalgaz' => true,  'oncelik' => 3],
            'manisa'         => ['ad' => 'Manisa',         'plaka' => '45', 'bolge' => 'Ege',             'nufus' => 1475716, 'dogalgaz' => true,  'oncelik' => 1],
            'mardin'         => ['ad' => 'Mardin',         'plaka' => '47', 'bolge' => 'Güneydoğu Anadolu','nufus' => 870374,  'dogalgaz' => true,  'oncelik' => 4],
            'mersin'         => ['ad' => 'Mersin',         'plaka' => '33', 'bolge' => 'Akdeniz',         'nufus' => 1916432, 'dogalgaz' => true,  'oncelik' => 3],
            'mugla'          => ['ad' => 'Muğla',          'plaka' => '48', 'bolge' => 'Ege',             'nufus' => 1066736, 'dogalgaz' => true,  'oncelik' => 1],
            'mus'            => ['ad' => 'Muş',            'plaka' => '49', 'bolge' => 'Doğu Anadolu',    'nufus' => 408728,  'dogalgaz' => false, 'oncelik' => 4],
            'nevsehir'       => ['ad' => 'Nevşehir',       'plaka' => '50', 'bolge' => 'İç Anadolu',      'nufus' => 309914,  'dogalgaz' => true,  'oncelik' => 4],
            'nigde'          => ['ad' => 'Niğde',          'plaka' => '51', 'bolge' => 'İç Anadolu',      'nufus' => 369305,  'dogalgaz' => true,  'oncelik' => 4],
            'ordu'           => ['ad' => 'Ordu',           'plaka' => '52', 'bolge' => 'Karadeniz',       'nufus' => 763190,  'dogalgaz' => true,  'oncelik' => 4],
            'osmaniye'       => ['ad' => 'Osmaniye',       'plaka' => '80', 'bolge' => 'Akdeniz',         'nufus' => 559405,  'dogalgaz' => true,  'oncelik' => 4],
            'rize'           => ['ad' => 'Rize',           'plaka' => '53', 'bolge' => 'Karadeniz',       'nufus' => 343212,  'dogalgaz' => true,  'oncelik' => 4],
            'sakarya'        => ['ad' => 'Sakarya',        'plaka' => '54', 'bolge' => 'Marmara',         'nufus' => 1098115, 'dogalgaz' => true,  'oncelik' => 3],
            'samsun'         => ['ad' => 'Samsun',         'plaka' => '55', 'bolge' => 'Karadeniz',       'nufus' => 1377546, 'dogalgaz' => true,  'oncelik' => 3],
            'sanliurfa'      => ['ad' => 'Şanlıurfa',      'plaka' => '63', 'bolge' => 'Güneydoğu Anadolu','nufus' => 2213964, 'dogalgaz' => true,  'oncelik' => 3],
            'siirt'          => ['ad' => 'Siirt',          'plaka' => '56', 'bolge' => 'Güneydoğu Anadolu','nufus' => 348508,  'dogalgaz' => true,  'oncelik' => 4],
            'sinop'          => ['ad' => 'Sinop',          'plaka' => '57', 'bolge' => 'Karadeniz',       'nufus' => 220799,  'dogalgaz' => true,  'oncelik' => 4],
            'sirnak'         => ['ad' => 'Şırnak',         'plaka' => '73', 'bolge' => 'Güneydoğu Anadolu','nufus' => 570745,  'dogalgaz' => true,  'oncelik' => 4],
            'sivas'          => ['ad' => 'Sivas',          'plaka' => '58', 'bolge' => 'İç Anadolu',      'nufus' => 644854,  'dogalgaz' => true,  'oncelik' => 4],
            'tekirdag'       => ['ad' => 'Tekirdağ',       'plaka' => '59', 'bolge' => 'Marmara',         'nufus' => 1167059, 'dogalgaz' => true,  'oncelik' => 3],
            'tokat'          => ['ad' => 'Tokat',          'plaka' => '60', 'bolge' => 'Karadeniz',       'nufus' => 596454,  'dogalgaz' => true,  'oncelik' => 4],
            'trabzon'        => ['ad' => 'Trabzon',        'plaka' => '61', 'bolge' => 'Karadeniz',       'nufus' => 824352,  'dogalgaz' => true,  'oncelik' => 3],
            'tunceli'        => ['ad' => 'Tunceli',        'plaka' => '62', 'bolge' => 'Doğu Anadolu',    'nufus' => 84660,   'dogalgaz' => true,  'oncelik' => 4],
            'usak'           => ['ad' => 'Uşak',           'plaka' => '64', 'bolge' => 'Ege',             'nufus' => 379141,  'dogalgaz' => true,  'oncelik' => 2],
            'van'            => ['ad' => 'Van',            'plaka' => '65', 'bolge' => 'Doğu Anadolu',    'nufus' => 1138155, 'dogalgaz' => true,  'oncelik' => 4],
            'yalova'         => ['ad' => 'Yalova',         'plaka' => '77', 'bolge' => 'Marmara',         'nufus' => 304005,  'dogalgaz' => true,  'oncelik' => 3],
            'yozgat'         => ['ad' => 'Yozgat',         'plaka' => '66', 'bolge' => 'İç Anadolu',      'nufus' => 414568,  'dogalgaz' => true,  'oncelik' => 4],
            'zonguldak'      => ['ad' => 'Zonguldak',      'plaka' => '67', 'bolge' => 'Karadeniz',       'nufus' => 587322,  'dogalgaz' => true,  'oncelik' => 4],
        ];
    }
}

if (!function_exists('il_bul')) {
    function il_bul(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        $iller = iller_listesi();
        return $iller[$slug] ?? null;
    }
}

if (!function_exists('hizmetler_listesi')) {
    /**
     * Lokal landing page'lerde kullanılan hizmet kataloğu.
     * Her hizmet için: slug, ad, kisa_aciklama, anahtar_kelimeler.
     */
    function hizmetler_listesi(): array
    {
        return [
            'dogalgaz-tesisati' => [
                'ad' => 'Doğalgaz Tesisatı',
                'fiil' => 'doğalgaz tesisat firması',
                'aciklama' => 'Sertifikalı iç tesisat, proje çizimi, sızdırmazlık testi, gaz açma',
                'ikon' => 'fire',
                'fiyat_baslangic' => '25.000 ₺',
                'sure' => '7-10 iş günü',
            ],
            'kombi-montaji' => [
                'ad' => 'Kombi Montajı',
                'fiil' => 'kombi montaj firması',
                'aciklama' => 'Demirdöküm, Bosch, Vaillant yetkili. Yoğuşmalı kombi satış + montaj',
                'ikon' => 'fire-flame-curved',
                'fiyat_baslangic' => '27.000 ₺',
                'sure' => 'Aynı gün',
            ],
            'kombi-bakim-servis' => [
                'ad' => 'Kombi Bakım ve Servis',
                'fiil' => 'kombi bakım servis',
                'aciklama' => 'Yıllık bakım, arıza tespiti, yedek parça, garantili işçilik',
                'ikon' => 'screwdriver-wrench',
                'fiyat_baslangic' => '850 ₺',
                'sure' => '1-2 saat',
            ],
            'klima-montaji' => [
                'ad' => 'Klima Montajı',
                'fiil' => 'klima montaj firması',
                'aciklama' => 'Inverter klima, multi split, vrf sistem. Mitsubishi, Daikin, Bosch yetkili',
                'ikon' => 'snowflake',
                'fiyat_baslangic' => '15.000 ₺',
                'sure' => 'Aynı gün',
            ],
            'yerden-isitma' => [
                'ad' => 'Yerden Isıtma',
                'fiil' => 'yerden ısıtma uygulaması',
                'aciklama' => 'PEX-AL-PEX boru, kollektör sistemi, oda termostatları, 50 yıl garanti',
                'ikon' => 'temperature-arrow-up',
                'fiyat_baslangic' => '450 ₺/m²',
                'sure' => '3-5 iş günü',
            ],
            'sihhi-tesisat' => [
                'ad' => 'Sıhhi Tesisat',
                'fiil' => 'sıhhi tesisat firması',
                'aciklama' => 'Tıkanıklık açma, su kaçağı tespit, banyo-mutfak yenileme, paslanmaz boru',
                'ikon' => 'faucet',
                'fiyat_baslangic' => '500 ₺',
                'sure' => 'Aynı gün',
            ],
            'isi-pompasi' => [
                'ad' => 'Isı Pompası',
                'fiil' => 'ısı pompası kurulum',
                'aciklama' => 'Hava-su, hava-hava ısı pompası. A+++ verim, %60 enerji tasarrufu',
                'ikon' => 'arrows-rotate',
                'fiyat_baslangic' => '85.000 ₺',
                'sure' => '2-3 iş günü',
            ],
            'havalandirma' => [
                'ad' => 'Havalandırma Sistemi',
                'fiil' => 'havalandırma tesisatı',
                'aciklama' => 'Kanal sistemleri, mutfak davlumbazı, banyo aspiratörü, fan koyl',
                'ikon' => 'wind',
                'fiyat_baslangic' => '8.000 ₺',
                'sure' => '1-2 iş günü',
            ],
        ];
    }
}

if (!function_exists('hizmet_bul')) {
    function hizmet_bul(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        $hizmetler = hizmetler_listesi();
        return $hizmetler[$slug] ?? null;
    }
}
