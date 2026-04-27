<?php
require_once __DIR__ . '/_baslat.php';

$donus = SITE_URL . '/admin/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($donus);
}

// CSRF
if (!csrf_check($_POST['csrf'] ?? null)) {
    redirect($donus . '?hata=' . urlencode('Oturum süresi doldu, sayfayı yenileyin.'));
}

$eposta = clean($_POST['eposta'] ?? '');
$sifre  = (string)($_POST['sifre'] ?? '');
$hatirla = !empty($_POST['hatirla']);
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if ($eposta === '' || $sifre === '' || !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
    redirect($donus . '?hata=' . urlencode('Geçersiz bilgi.') . '&e=' . urlencode($eposta));
}

// Brute force koruma: aynı IP'den son 15 dakikada 5+ başarısız varsa reddet
try {
    $say = (int)db_get(
        "SELECT COUNT(*) c FROM log_kayitlari WHERE tip='login_fail' AND ip=? AND olusturma_tarihi > NOW() - INTERVAL 15 MINUTE",
        [$ip]
    )['c'];
    if ($say >= 5) {
        redirect($donus . '?hata=' . urlencode('Çok fazla başarısız deneme. 15 dakika sonra tekrar deneyin.') . '&e=' . urlencode($eposta));
    }
} catch (Throwable $e) { /* devam */ }

// Kullanıcıyı bul
$kul = db_get("SELECT id, ad, sifre, rol, aktif FROM kullanicilar WHERE eposta=?", [$eposta]);
if (!$kul || !(int)$kul['aktif'] || !password_verify($sifre, (string)$kul['sifre'])) {
    log_yaz('login_fail', "Başarısız giriş: $eposta", null);
    redirect($donus . '?hata=' . urlencode('E-posta veya şifre hatalı.') . '&e=' . urlencode($eposta));
}

// Şifre yeniden hash gerekiyorsa güncelle (algoritma değişimi vb.)
if (password_needs_rehash((string)$kul['sifre'], PASSWORD_DEFAULT)) {
    db_run("UPDATE kullanicilar SET sifre=? WHERE id=?", [password_hash($sifre, PASSWORD_DEFAULT), (int)$kul['id']]);
}

// Oturum aç (session fixation'a karşı yenile)
session_regenerate_id(true);
$_SESSION['admin_id']   = (int)$kul['id'];
$_SESSION['admin_ad']   = $kul['ad'];
$_SESSION['admin_rol']  = $kul['rol'];
$_SESSION['admin_login_at'] = time();

// "Beni hatırla" — basit yaklaşım: PHPSESSID ömrünü uzat
if ($hatirla) {
    $omur = 30 * 24 * 60 * 60;
    setcookie(session_name(), session_id(), [
        'expires' => time() + $omur,
        'path'    => '/',
        'secure'  => isset($_SERVER['HTTPS']),
        'httponly'=> true,
        'samesite'=> 'Lax',
    ]);
}

// Son giriş tarihi
db_run("UPDATE kullanicilar SET son_giris=NOW() WHERE id=?", [(int)$kul['id']]);
log_yaz('login_ok', 'Giriş yapıldı: ' . $eposta, (int)$kul['id']);

redirect(SITE_URL . '/admin/panel.php');
