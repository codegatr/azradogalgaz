<?php
/**
 * Azra Doğalgaz — SEO Yardımcıları (v1.12.25)
 * 
 * Tüm sayfalar bu dosyayı kullanır:
 *   - canonical_url(): query string strip + slug normalize, tutarlı kanonik
 *   - schema_local_business(): firma genel LocalBusiness JSON-LD
 *   - schema_breadcrumb($path): site içi breadcrumb JSON-LD
 *   - schema_website(): site arama kutusu JSON-LD
 */
declare(strict_types=1);

if (!function_exists('canonical_url')) {
    /**
     * Geçerli sayfanın kanonik URL'sini döndürür.
     * - Query string'i sadece izin verilen parametreler için tutar (sayfalama, slug)
     * - Trailing slash kaldırır
     * - Her zaman https://azradogalgaz.com (config'deki SITE_URL) prefix
     */
    function canonical_url(?array $izinli_param = null): string
    {
        $izinli_param = $izinli_param ?? ['sayfa', 'slug', 'kategori'];

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');
        if ($path === '') $path = '/';

        // Query string'i temizle — sadece izinli parametreleri tut
        $qs = '';
        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $params);
            $temiz = [];
            foreach ($izinli_param as $k) {
                if (isset($params[$k]) && $params[$k] !== '') $temiz[$k] = $params[$k];
            }
            if (!empty($temiz)) {
                ksort($temiz);
                $qs = '?' . http_build_query($temiz);
            }
        }

        return SITE_URL . $path . $qs;
    }
}

if (!function_exists('schema_local_business')) {
    /**
     * Genel LocalBusiness / HVACBusiness Schema.org JSON-LD üretir.
     * Her sayfanın <head>'inde basılır → Google işletme bilgilerini standart formda alır.
     */
    function schema_local_business(): array
    {
        $tel = (string)ayar('firma_telefon_1', defined('FIRMA_TEL_1') ? FIRMA_TEL_1 : '');
        $tel2= (string)ayar('firma_telefon_2', defined('FIRMA_TEL_2') ? FIRMA_TEL_2 : '');

        return [
            '@context' => 'https://schema.org',
            '@type'    => 'HVACBusiness',
            '@id'      => SITE_URL . '#organization',
            'name'     => (string)ayar('firma_unvan', 'Azra Doğalgaz Tesisat'),
            'url'      => SITE_URL,
            'logo'     => SITE_URL . '/assets/img/logo.png',
            'image'    => SITE_URL . '/assets/img/og-default.jpg',
            'telephone'=> $tel,
            'email'    => (string)ayar('firma_eposta', 'info@azradogalgaz.com'),
            'priceRange' => '₺₺',
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => (string)ayar('firma_adres', 'Laleli Menderes Cd. No:392/C'),
                'addressLocality' => 'Buca',
                'addressRegion'   => 'İzmir',
                'postalCode'      => '35370',
                'addressCountry'  => 'TR',
            ],
            'areaServed' => [
                ['@type' => 'City', 'name' => 'İzmir'],
                ['@type' => 'AdministrativeArea', 'name' => 'İzmir İli'],
            ],
            'foundingDate'      => '2014',
            'numberOfEmployees' => ['@type' => 'QuantitativeValue', 'minValue' => 6, 'maxValue' => 10],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                    'opens'  => '09:00',
                    'closes' => '19:00',
                ],
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'Sunday',
                    'opens'  => '00:00',
                    'closes' => '00:00',
                    'description' => 'Acil durum 7/24 erişilebilir',
                ],
            ],
            'sameAs' => array_filter([
                (string)ayar('sosyal_facebook', ''),
                (string)ayar('sosyal_instagram', ''),
                (string)ayar('sosyal_twitter', ''),
                (string)ayar('sosyal_youtube', ''),
                (string)ayar('sosyal_linkedin', ''),
            ]),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $tel,
                'contactType' => 'customer service',
                'areaServed' => 'TR',
                'availableLanguage' => ['Turkish'],
            ],
        ];
    }
}

if (!function_exists('schema_website')) {
    /**
     * WebSite schema — site içi arama kutusu için Google Sitelinks Search Box destekler.
     */
    function schema_website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            '@id'      => SITE_URL . '#website',
            'url'      => SITE_URL,
            'name'     => (string)ayar('firma_unvan', 'Azra Doğalgaz'),
            'inLanguage' => 'tr-TR',
            'publisher'  => ['@id' => SITE_URL . '#organization'],
        ];
    }
}

if (!function_exists('schema_breadcrumb')) {
    /**
     * Breadcrumb JSON-LD üretir.
     * Kullanım: schema_breadcrumb([['Anasayfa','/'], ['Hizmetler','/hizmetler'], ['Kombi','/hizmet/kombi']])
     */
    function schema_breadcrumb(array $items): array
    {
        $list = [];
        foreach ($items as $i => $item) {
            [$name, $path] = $item;
            $list[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $name,
                'item'     => SITE_URL . $path,
            ];
        }
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }
}

if (!function_exists('schema_render')) {
    /**
     * Bir veya daha fazla JSON-LD bloğunu HTML'e basar.
     * <script type="application/ld+json">...</script>
     */
    function schema_render(array $bloklar): string
    {
        // Tek blok mu çoklu mu?
        $cikti = '';
        $coklu = isset($bloklar[0]) && is_array($bloklar[0]) && isset($bloklar[0]['@context']);
        $listeler = $coklu ? $bloklar : [$bloklar];

        foreach ($listeler as $b) {
            $json = json_encode($b, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $cikti .= '<script type="application/ld+json">' . $json . '</script>' . "\n";
        }
        return $cikti;
    }
}
