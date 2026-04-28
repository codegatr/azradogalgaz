<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php'; // migrations otomatik

$donus = SITE_URL . '/admin/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($donus);
}

// CSRF
if (!csrf_check($_POST['csrf'] ?? null)) {
    redirect($donus . '?hata=' . urlencode('Oturum süresi doldu, sayfayı yenileyin.'));
}

// "kimlik" alanı: e-posta veya kullanıcı adı kabul edilir
// Geriye uyumluluk: eski formdaki "eposta" alanı da çalışır
$kimlik = clean($_POST['kimlik'] ?? $_POST['eposta'] ?? '');
$sifre  = (string)($_POST['sifre'] ?? '');
$hatirla = !empty($_POST['hatirla']);
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if ($kimlik === '' || $sifre === '') {
    redirect($donus . '?hata=' . urlencode('Kullanıcı adı / e-posta ve şifre zorunlu.') . '&e=' . urlencode($kimlik));
}

// Brute force koruma: aynı IP'den son 15 dakikada 5+ başarısız varsa reddet
try {
    $say = (int)db_get(
        "SELECT COUNT(*) c FROM log_kayitlari WHERE tip='login_fail' AND ip=? AND olusturma_tarihi > NOW() - INTERVAL 15 MINUTE",
        [$ip]
    )['c'];
    if ($say >= 5) {
        redirect($donus . '?hata=' . urlencode('Çok fazla başarısız deneme. 15 dakika sonra tekrar deneyin.') . '&e=' . urlencode($kimlik));
    }
} catch (Throwable $e) { /* devam */ }

// Kimlik formatına göre arama yap: '@' içeriyorsa e-posta, değilse kullanıcı adı
$kul = null;
$is_email = (bool)filter_var($kimlik, FILTER_VALIDATE_EMAIL);

try {
    if ($is_email) {
        $kul = db_get("SELECT id, ad, sifre, rol, aktif, eposta, kullanici_adi FROM kullanicilar WHERE eposta=?", [$kimlik]);
    } else {
        // Önce kullanıcı adı olarak ara (kolon varsa)
        try {
            $kul = db_get("SELECT id, ad, sifre, rol, aktif, eposta, kullanici_adi FROM kullanicilar WHERE kullanici_adi=?", [$kimlik]);
        } catch (Throwable $e) {
            // kullanici_adi kolonu yoksa fallback: kimliği e-posta'ymış gibi dene
            $kul = null;
        }
        // Hâlâ bulunmadıysa e-posta olarak da bir kez dene (örn. "ad@domain" yerine "ad" yazılmış olabilir, atlasın)
        if (!$kul) {
            $kul = db_get("SELECT id, ad, sifre, rol, aktif, eposta, kullanici_adi FROM kullanicilar WHERE eposta=?", [$kimlik]);
        }
    }
} catch (Throwable $e) {
    // kullanici_adi kolonu yoksa eski şemayla devam
    $kul = db_get("SELECT id, ad, sifre, rol, aktif, eposta FROM kullanicilar WHERE eposta=?", [$kimlik]);
}

if (!$kul || !(int)$kul['aktif'] || !password_verify($sifre, (string)$kul['sifre'])) {
    log_yaz('login_fail', "Başarısız giriş: $kimlik (IP: $ip)", null);
    redirect($donus . '?hata=' . urlencode('Kullanıcı adı / e-posta veya şifre hatalı.') . '&e=' . urlencode($kimlik));
}

// Şifre yeniden hash gerekiyorsa güncelle
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
$gosterilen = $kul['kullanici_adi'] ?? $kul['eposta'] ?? $kimlik;
log_yaz('login_ok', 'Giriş yapıldı: ' . $gosterilen, (int)$kul['id']);

redirect(SITE_URL . '/admin/panel.php');
