<?php
/**
 * İletişim Formu İşleyici
 */
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

$donus = SITE_URL . '/iletisim';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect($donus);

// CSRF
if (!csrf_check($_POST['csrf'] ?? null)) {
    redirect($donus . '?hata=' . urlencode('Oturum süresi doldu, lütfen sayfayı yenileyin.'));
}
// Honeypot
if (!empty($_POST['website'])) {
    redirect($donus . '?ok=1'); // botu sessizce gönder
}

$ad     = clean($_POST['ad_soyad'] ?? '');
$tel    = clean($_POST['telefon'] ?? '');
$mail   = clean($_POST['eposta'] ?? '');
$konu   = clean($_POST['konu'] ?? '');
$mesaj  = clean($_POST['mesaj'] ?? '');
$kvkk   = !empty($_POST['kvkk']);

if (mb_strlen($ad) < 3 || mb_strlen($tel) < 7 || mb_strlen($mesaj) < 10) {
    redirect($donus . '?hata=' . urlencode('Lütfen ad, telefon ve mesaj alanlarını eksiksiz doldurun.'));
}
if (!$kvkk) {
    redirect($donus . '?hata=' . urlencode('KVKK metnini onaylamanız gerekir.'));
}
if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    redirect($donus . '?hata=' . urlencode('Geçerli bir e-posta adresi girin veya boş bırakın.'));
}

// Basit hız sınırı (5 dk içinde 3 mesajdan fazlasını engelle)
try {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $say = (int)db_get(
        "SELECT COUNT(*) c FROM iletisim_mesajlari WHERE ip=? AND olusturma_tarihi > NOW() - INTERVAL 5 MINUTE",
        [$ip]
    )['c'];
    if ($say >= 3) {
        redirect($donus . '?hata=' . urlencode('Çok fazla mesaj gönderildi. Lütfen biraz sonra tekrar deneyin.'));
    }

    db_run(
        "INSERT INTO iletisim_mesajlari (ad_soyad, eposta, telefon, konu, mesaj, ip)
         VALUES (?,?,?,?,?,?)",
        [$ad, $mail, $tel, $konu, $mesaj, $ip]
    );

    // Yöneticiye e-posta (sunucu mail desteği varsa)
    $alici = ayar('firma_eposta', FIRMA_EMAIL);
    if ($alici) {
        $body = "Yeni iletişim mesajı:\n\n"
              . "Ad Soyad: $ad\n"
              . "Telefon : $tel\n"
              . "E-posta : $mail\n"
              . "Konu    : $konu\n"
              . "IP      : $ip\n\n"
              . "Mesaj:\n$mesaj\n";
        $headers = "From: " . SITE_TITLE . " <noreply@" . parse_url(SITE_URL, PHP_URL_HOST) . ">\r\n";
        $headers.= "Reply-To: $mail\r\n";
        $headers.= "Content-Type: text/plain; charset=UTF-8\r\n";
        @mail($alici, '[' . SITE_TITLE . '] Yeni iletişim mesajı', $body, $headers);
    }

    redirect($donus . '?ok=1');
} catch (Throwable $e) {
    error_log('iletisim-gonder hata: ' . $e->getMessage());
    redirect($donus . '?hata=' . urlencode('Mesaj kaydedilemedi, lütfen daha sonra tekrar deneyin.'));
}
