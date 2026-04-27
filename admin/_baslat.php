<?php
/**
 * Admin Bootstrap — Tüm admin sayfaları bu dosyayı dahil eder.
 * Yetkilendirme, ortak değişkenler ve yardımcılar burada hazırlanır.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

// Login sayfası ve giriş işleyicisi haricinde yetki zorunlu
$_admin_pubs = ['index.php', 'giris-yap.php', 'cikis.php'];
if (!in_array(basename($_SERVER['SCRIPT_NAME']), $_admin_pubs, true)) {
    admin_zorunlu();
}

// Aktif kullanıcı (giriş yapılmışsa)
$_kul = null;
if (admin_giris_var()) {
    $_kul = db_get("SELECT id, ad, eposta, rol FROM kullanicilar WHERE id=? AND aktif=1", [(int)$_SESSION['admin_id']]);
    if (!$_kul) {
        // hesap silinmiş veya pasifleştirilmiş
        session_destroy();
        redirect(SITE_URL . '/admin/');
    }
}

// Bayrak/mesaj sistemi (flash)
function flash_set(string $tip, string $msg): void {
    $_SESSION['_flash'][] = ['tip' => $tip, 'msg' => $msg];
}
function flash_pop(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

// Sayfa başlığı belirleme
function page_title(string $title): void {
    $GLOBALS['_admin_page_title'] = $title;
}

// Aktif menü öğesi
function nav_active(string $sayfa): string {
    return basename($_SERVER['SCRIPT_NAME']) === $sayfa ? 'active' : '';
}
