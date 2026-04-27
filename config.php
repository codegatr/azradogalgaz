<?php
/**
 * Azra Doğalgaz — Yapılandırma
 * KENDI HOSTING BILGILERINIZI GIRIN
 */
declare(strict_types=1);

// ============= VERİTABANI =============
define('DB_HOST', 'localhost');
define('DB_NAME', 'KENDI_DB_ADINIZ');
define('DB_USER', 'KENDI_DB_KULLANICINIZ');
define('DB_PASS', 'KENDI_DB_SIFRENIZ');
define('DB_CHARSET', 'utf8mb4');

// ============= SİTE =============
define('SITE_URL',     'https://azradogalgaz.com');
define('SITE_TITLE',   'Azra Doğalgaz — Konforlu Yaşam, Güvenli Gelecek');
define('SITE_DESC',    'İzmir\'de Demirdöküm Ademix kombi, klima ve doğalgaz tesisat hizmetleri. İzmirgaz uyumlu, garantili kurulum, 7/24 teknik destek.');
define('SITE_KEYWORDS','azra doğalgaz, izmir kombi, demirdöküm ademix, izmirgaz uyumlu tesisat, kombi montaj izmir, klima montaj, doğalgaz tesisat');

// ============= FİRMA =============
define('FIRMA_TEL_1',  '0546 790 78 77');
define('FIRMA_TEL_2',  '0546 820 60 80');
define('FIRMA_EMAIL',  'info@azradogalgaz.com');
define('FIRMA_WHATSAPP','905467907877');

// ============= GÜVENLİK =============
define('CSRF_KEY', 'azra-2026-degistir-bu-anahtari-kendi-sifrenizle');
define('SESSION_NAME', 'azra_panel');

// ============= UPLOAD =============
define('UPLOAD_DIR',  __DIR__ . '/assets/uploads');
define('UPLOAD_URL',  SITE_URL . '/assets/uploads');
define('MAX_UPLOAD_MB', 8);

// ============= ZAMAN DİLİMİ =============
date_default_timezone_set('Europe/Istanbul');
mb_internal_encoding('UTF-8');

// ============= OTURUM =============
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// ============= PDO =============
$_pdo = null;
function db(): PDO {
    global $_pdo;
    if ($_pdo) return $_pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $_pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci",
    ]);
    return $_pdo;
}

// ============= CSRF =============
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}
function csrf_check(?string $t): bool {
    return !empty($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

// ============= YETKİ =============
function admin_giris_var(): bool {
    return !empty($_SESSION['admin_id']);
}
function admin_zorunlu(): void {
    if (!admin_giris_var()) {
        $donus = SITE_URL . '/admin/?bilgi=' . urlencode('Lütfen giriş yapın.');
        header('Location: ' . $donus);
        exit;
    }
}

// ============= ORTAK YARDIMCILAR =============
require_once __DIR__ . '/inc/functions.php';
