<?php
require_once __DIR__ . '/../config.php';
if (admin_giris_var()) {
    log_yaz('logout', 'Çıkış yapıldı', (int)$_SESSION['admin_id']);
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
redirect(SITE_URL . '/admin/?bilgi=' . urlencode('Çıkış yapıldı.'));
