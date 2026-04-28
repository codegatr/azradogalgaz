<?php
/**
 * Azra Doğalgaz — Yardımcı Fonksiyonlar
 */
declare(strict_types=1);

// ============================================================
// GÜVENLİK / TEMİZLEME
// ============================================================
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clean(?string $s): string {
    return trim(strip_tags((string)$s));
}

function redirect(string $url, int $code = 302): never {
    header('Location: ' . $url, true, $code);
    exit;
}

// ============================================================
// SLUG (Türkçe karakter destekli)
// ============================================================
function slugify(string $text): string {
    $tr = ['ç','Ç','ğ','Ğ','ı','İ','ö','Ö','ş','Ş','ü','Ü'];
    $en = ['c','c','g','g','i','i','o','o','s','s','u','u'];
    $text = str_replace($tr, $en, $text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s\-]/', '', $text) ?? '';
    $text = preg_replace('/[\s\-]+/', '-', $text) ?? '';
    return trim($text, '-');
}

// ============================================================
// AYARLAR (cached)
// ============================================================
function ayar(string $key, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        try {
            $rows = db()->query("SELECT anahtar, deger FROM ayarlar")->fetchAll();
            $cache = [];
            foreach ($rows as $r) $cache[$r['anahtar']] = $r['deger'];
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

// ============================================================
// PARA / TARİH BİÇİMLENDİRME
// ============================================================
function tl(float $n): string {
    return number_format($n, 2, ',', '.') . ' ₺';
}

function tarih_tr(?string $datetime, bool $saatli = false): string {
    if (!$datetime) return '-';
    try {
        $dt = new DateTime($datetime);
        return $dt->format($saatli ? 'd.m.Y H:i' : 'd.m.Y');
    } catch (Throwable $e) {
        return '-';
    }
}

// ============================================================
// META ETİKETLERİ
// ============================================================
function set_meta(array $meta): void {
    $GLOBALS['_meta'] = array_merge($GLOBALS['_meta'] ?? [], $meta);
}
function get_meta(string $key, string $default = ''): string {
    return (string)($GLOBALS['_meta'][$key] ?? $default);
}

// ============================================================
// JSON-LD Schema.org
// ============================================================
function schema_org(array $schema): string {
    return '<script type="application/ld+json">'
        . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>';
}

// ============================================================
// LocalBusiness Schema (her sayfada otomatik basılır)
// ============================================================
function local_business_schema(): string {
    return schema_org([
        '@context'  => 'https://schema.org',
        '@type'     => 'HVACBusiness',
        'name'      => 'Azra Doğalgaz',
        'image'     => SITE_URL . '/assets/img/logo.png',
        'url'       => SITE_URL,
        'telephone' => [FIRMA_TEL_1, FIRMA_TEL_2],
        'email'     => FIRMA_EMAIL,
        'priceRange'=> '₺₺',
        'description' => SITE_DESC,
        'address'   => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'İzmir',
            'addressRegion'   => 'İzmir',
            'addressCountry'  => 'TR',
        ],
        'areaServed' => [
            ['@type' => 'City', 'name' => 'İzmir'],
        ],
        'openingHoursSpecification' => [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
            'opens'     => '08:00',
            'closes'    => '20:00',
        ],
        'sameAs' => array_filter([
            ayar('sosyal_facebook'),
            ayar('sosyal_instagram'),
            ayar('sosyal_youtube'),
            ayar('sosyal_x'),
        ]),
    ]);
}

// ============================================================
// LOG (admin işlem geçmişi için)
// ============================================================
function log_yaz(string $tip, string $mesaj, ?int $kullanici_id = null): void {
    try {
        $stmt = db()->prepare("INSERT INTO log_kayitlari (tip, mesaj, kullanici_id, ip, user_agent) VALUES (?,?,?,?,?)");
        $stmt->execute([
            $tip,
            $mesaj,
            $kullanici_id,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
        ]);
    } catch (Throwable $e) { /* sessizce yut */ }
}

// ============================================================
// RESİM YÜKLEME
// ============================================================
/**
 * Görsel URL — yüklenmiş dosya yolu OR uzak URL (https://...) destekler.
 * gorsel kolonu http(s):// ile başlıyorsa direkt kullan, değilse UPLOAD_URL prefix ekle.
 */
function gorsel_url(?string $g, string $varsayilan = ''): string {
    $g = trim((string)$g);
    if ($g === '') return $varsayilan;
    if (preg_match('#^https?://#i', $g)) return $g;
    return UPLOAD_URL . '/' . ltrim($g, '/');
}

function resim_yukle(array $file, string $klasor = 'genel'): ?string {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $izinli = ['image/jpeg','image/png','image/webp','image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $izinli, true)) return null;
    if ($file['size'] > 8 * 1024 * 1024) return null;
    $ext = match($mime){
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };
    $hedef_klasor = UPLOAD_DIR . '/' . preg_replace('/[^a-z0-9\-]/i', '', $klasor);
    if (!is_dir($hedef_klasor)) mkdir($hedef_klasor, 0755, true);
    $isim = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $tam_yol = $hedef_klasor . '/' . $isim;
    if (move_uploaded_file($file['tmp_name'], $tam_yol)) {
        return $klasor . '/' . $isim;
    }
    return null;
}

// ============================================================
// ADMIN OTURUM
// ============================================================
function admin_giris_var(): bool {
    return !empty($_SESSION['admin_id']);
}
function admin_zorunlu(): void {
    if (!admin_giris_var()) {
        redirect(SITE_URL . '/admin/');
    }
}

// ============================================================
// KISA YOL: DB yardımcıları
// ============================================================
function db_get(string $sql, array $params = []): ?array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $r = $stmt->fetch();
    return $r === false ? null : $r;
}
function db_all(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function db_run(string $sql, array $params = []): int {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

// ============================================================
// META AÇIKLAMA: HTML stringden temiz açıklama üret
// ============================================================
function meta_aciklama(string $html, int $uzunluk = 160): string {
    $t = trim(strip_tags($html));
    $t = preg_replace('/\s+/', ' ', $t) ?? '';
    if (mb_strlen($t, 'UTF-8') <= $uzunluk) return $t;
    return mb_substr($t, 0, $uzunluk - 1, 'UTF-8') . '…';
}
