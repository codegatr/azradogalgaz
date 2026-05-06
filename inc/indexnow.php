<?php
/**
 * Azra Doğalgaz — IndexNow Protokolü (v1.12.25)
 *
 * IndexNow, Bing ve Yandex tarafından desteklenen "URL değişti, hemen tarayın"
 * bildirim protokolüdür. Site içeriği değiştiğinde (yeni hizmet, blog yazısı,
 * kampanya, ürün eklendiğinde) bu sınıf çağrılır → arama motorları anında
 * haberdar olur, indeksleme süresi günlerden saatlere düşer.
 *
 * Spec: https://www.indexnow.org/documentation
 */
declare(strict_types=1);

class IndexNow
{
    public const ENDPOINT_BING   = 'https://www.bing.com/indexnow';
    public const ENDPOINT_YANDEX = 'https://yandex.com/indexnow';
    public const ENDPOINT_NAVER  = 'https://searchadvisor.naver.com/indexnow';
    public const ENDPOINT_SEZNAM = 'https://search.seznam.cz/indexnow';

    /**
     * Anahtar dosyası mevcut değilse oluştur.
     * Anahtar formatı: 32 karakter hex (16 byte)
     * Dosya: /{ANAHTAR}.txt → içerik anahtarın kendisi
     */
    public static function anahtar_al(): string
    {
        $anahtar = (string)ayar('indexnow_anahtar', '');
        if ($anahtar !== '' && preg_match('/^[a-f0-9]{32}$/', $anahtar)) {
            return $anahtar;
        }
        // Yeni anahtar oluştur
        $anahtar = bin2hex(random_bytes(16));
        try {
            db_run(
                "INSERT INTO ayarlar (anahtar, deger) VALUES ('indexnow_anahtar', ?)
                 ON DUPLICATE KEY UPDATE deger=VALUES(deger)",
                [$anahtar]
            );
        } catch (Throwable $e) {
            error_log('IndexNow anahtar kayıt hata: ' . $e->getMessage());
        }
        return $anahtar;
    }

    /**
     * Doğrulama dosyasını oluştur (eğer yoksa).
     * Web kökünde {ANAHTAR}.txt olarak içeriğinde anahtarı barındırır.
     */
    public static function dogrulama_dosyasi_yaz(string $anahtar): bool
    {
        $kok = realpath(__DIR__ . '/..');
        if (!$kok) return false;
        $yol = $kok . '/' . $anahtar . '.txt';
        if (file_exists($yol)) return true;
        return @file_put_contents($yol, $anahtar) !== false;
    }

    /**
     * Tek URL gönder.
     * Dönüş: ['ok' => bool, 'kod' => int, 'cevap' => string, 'gateway' => string]
     */
    public static function bildir_tek(string $url, string $endpoint = self::ENDPOINT_BING): array
    {
        $anahtar = self::anahtar_al();
        self::dogrulama_dosyasi_yaz($anahtar);

        $host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'azradogalgaz.com';
        $tam = $endpoint . '?' . http_build_query([
            'url' => $url,
            'key' => $anahtar,
        ]);

        return self::http_get($tam);
    }

    /**
     * Çoklu URL gönder (POST + JSON body).
     * Tek seferde 10.000 URL'e kadar destekler.
     */
    public static function bildir_coklu(array $urls, string $endpoint = self::ENDPOINT_BING): array
    {
        if (empty($urls)) return ['ok' => false, 'kod' => 0, 'cevap' => 'URL listesi boş'];

        $anahtar = self::anahtar_al();
        self::dogrulama_dosyasi_yaz($anahtar);

        $host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'azradogalgaz.com';
        $body = json_encode([
            'host'        => $host,
            'key'         => $anahtar,
            'keyLocation' => SITE_URL . '/' . $anahtar . '.txt',
            'urlList'     => array_values(array_slice($urls, 0, 10000)),
        ], JSON_UNESCAPED_SLASHES);

        return self::http_post($endpoint, $body);
    }

    /**
     * Hem Bing hem Yandex'e bildirim — tek call.
     * (IndexNow ağı node'lar arası senkronize ama her node ayrı 200 dönüyor.)
     */
    public static function bildir_tum(array $urls): array
    {
        $sonuclar = [];
        foreach ([self::ENDPOINT_BING, self::ENDPOINT_YANDEX] as $endpoint) {
            $sonuclar[parse_url($endpoint, PHP_URL_HOST)] =
                count($urls) === 1 ? self::bildir_tek($urls[0], $endpoint)
                                    : self::bildir_coklu($urls, $endpoint);
        }
        return $sonuclar;
    }

    private static function http_get(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'AzraDogalgaz-IndexNow/1.0',
        ]);
        $cevap = curl_exec($ch);
        $kod   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hata  = curl_error($ch);
        curl_close($ch);
        return [
            'ok'    => $kod >= 200 && $kod < 300,
            'kod'   => $kod,
            'cevap' => is_string($cevap) ? $cevap : '',
            'hata'  => $hata,
        ];
    }

    private static function http_post(string $url, string $body): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_USERAGENT      => 'AzraDogalgaz-IndexNow/1.0',
        ]);
        $cevap = curl_exec($ch);
        $kod   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hata  = curl_error($ch);
        curl_close($ch);
        return [
            'ok'    => $kod >= 200 && $kod < 300,
            'kod'   => $kod,
            'cevap' => is_string($cevap) ? $cevap : '',
            'hata'  => $hata,
        ];
    }
}
