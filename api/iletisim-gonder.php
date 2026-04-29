<?php
/**
 * İletişim Formu İşleyici — v1.12.20
 *
 * Akış:
 *   1. CSRF + Honeypot + validation
 *   2. DB'ye kayıt (iletisim_mesajlari)
 *   3. Yöneticiye bildirim e-postası (SMTP class, HTML şablon)
 *   4. Müşteriye opsiyonel teşekkür e-postası
 *   5. Hız sınırı: 5 dk içinde aynı IP'den max 3 mesaj
 */
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/mail.php';

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

$ad     = clean($_POST['ad_soyad']  ?? '');
$tel    = clean($_POST['telefon']   ?? '');
$mail   = clean($_POST['eposta']    ?? '');
$konu   = clean($_POST['konu']      ?? 'Genel Bilgi');
$mesaj  = clean($_POST['mesaj']     ?? '');
$kvkk   = !empty($_POST['kvkk_onay']);   // ← v1.12.20 fix: önceden 'kvkk' arıyordu

if (mb_strlen($ad) < 3 || mb_strlen($tel) < 7 || mb_strlen($mesaj) < 10) {
    redirect($donus . '?hata=' . urlencode('Lütfen ad, telefon ve mesaj alanlarını eksiksiz doldurun.'));
}
if (!$kvkk) {
    redirect($donus . '?hata=' . urlencode('KVKK metnini onaylamanız gerekir.'));
}
if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    redirect($donus . '?hata=' . urlencode('Geçerli bir e-posta adresi girin veya boş bırakın.'));
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    // Hız sınırı (5 dk / 3 mesaj)
    $say = (int)(db_get(
        "SELECT COUNT(*) c FROM iletisim_mesajlari WHERE ip=? AND olusturma_tarihi > NOW() - INTERVAL 5 MINUTE",
        [$ip]
    )['c'] ?? 0);
    if ($say >= 3) {
        redirect($donus . '?hata=' . urlencode('Çok fazla mesaj gönderildi. Lütfen biraz sonra tekrar deneyin.'));
    }

    // Kayıt
    db_run(
        "INSERT INTO iletisim_mesajlari (ad_soyad, eposta, telefon, konu, mesaj, ip)
         VALUES (?,?,?,?,?,?)",
        [$ad, $mail, $tel, $konu, $mesaj, $ip]
    );
    $mesaj_id = (int)db()->lastInsertId();

    // ─── Yöneticiye Bildirim ───
    $alici = (string)ayar('iletisim_bildirim_eposta', '');
    if ($alici === '') $alici = (string)ayar('firma_eposta', defined('FIRMA_EMAIL') ? FIRMA_EMAIL : '');

    $kesif_mi = stripos($konu, 'keşif') !== false || stripos($konu, 'kesif') !== false;
    $tag      = $kesif_mi ? '⭐ KEŞİF TALEBİ' : 'Yeni Mesaj';
    $vurgu    = $kesif_mi ? '#10b981' : '#f59e0b';
    $vurgu_bg = $kesif_mi ? '#ecfdf5' : '#fef3c7';
    $vurgu_tx = $kesif_mi ? '#065f46' : '#92400e';

    $admin_url = SITE_URL . '/admin/iletisim-mesajlari.php';
    $tarih_tr  = date('d.m.Y H:i');
    $firma     = (string)ayar('firma_unvan', 'Azra Doğalgaz');
    $logo_url  = SITE_URL . '/assets/img/logo-header.png';

    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    $eposta_link = $mail ? '<a href="mailto:' . $h($mail) . '" style="color:#1e40af;text-decoration:none">' . $h($mail) . '</a>' : '<span style="color:#94a3b8">—</span>';
    $tel_link    = '<a href="tel:' . $h(preg_replace('/\s/', '', $tel)) . '" style="color:#1e40af;text-decoration:none">' . $h($tel) . '</a>';
    $mesaj_html  = nl2br($h($mesaj));

    $bildirim_html = '<!DOCTYPE html>
<html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . $h($tag) . '</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Helvetica,Arial,sans-serif;color:#1e293b;line-height:1.5">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f1f5f9;padding:24px 12px">
  <tr><td align="center">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;width:100%;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.08)">

      <tr><td style="background:#0f172a;padding:18px 24px;color:#fff">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
          <tr>
            <td style="font-size:18px;font-weight:700;letter-spacing:.3px">' . $h($firma) . '</td>
            <td align="right" style="font-size:11px;color:#94a3b8">' . $h($tarih_tr) . '</td>
          </tr>
        </table>
      </td></tr>

      <tr><td style="background:' . $vurgu_bg . ';padding:14px 24px;border-bottom:3px solid ' . $vurgu . '">
        <div style="font-size:11px;color:' . $vurgu_tx . ';text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:3px">' . $h($tag) . '</div>
        <div style="font-size:18px;font-weight:700;color:#0f172a">' . $h($konu) . '</div>
      </td></tr>

      <tr><td style="padding:24px">
        <div style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:10px">Müşteri Bilgileri</div>
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:14px;border-collapse:collapse">
          <tr>
            <td style="padding:8px 0;color:#64748b;width:120px">Ad Soyad</td>
            <td style="padding:8px 0;color:#0f172a;font-weight:600">' . $h($ad) . '</td>
          </tr>
          <tr style="border-top:1px solid #e2e8f0">
            <td style="padding:8px 0;color:#64748b">Telefon</td>
            <td style="padding:8px 0;color:#0f172a;font-weight:600">' . $tel_link . '</td>
          </tr>
          <tr style="border-top:1px solid #e2e8f0">
            <td style="padding:8px 0;color:#64748b">E-posta</td>
            <td style="padding:8px 0;color:#0f172a">' . $eposta_link . '</td>
          </tr>
          <tr style="border-top:1px solid #e2e8f0">
            <td style="padding:8px 0;color:#64748b">IP / Tarih</td>
            <td style="padding:8px 0;color:#94a3b8;font-size:12px">' . $h($ip) . ' · ' . $h($tarih_tr) . '</td>
          </tr>
        </table>

        <div style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin:24px 0 10px">Mesaj</div>
        <div style="background:#f8fafc;border-left:3px solid ' . $vurgu . ';padding:14px 16px;border-radius:0 4px 4px 0;font-size:14px;line-height:1.6;color:#334155;white-space:pre-wrap">' . $mesaj_html . '</div>

        <div style="margin-top:24px;text-align:center">
          <a href="' . $h($admin_url) . '" style="display:inline-block;background:' . $vurgu . ';color:#fff;padding:10px 22px;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px">Admin Panelinde Görüntüle →</a>
        </div>

        <div style="margin-top:18px;text-align:center;font-size:12px;color:#94a3b8">
          Mesaj #' . $h((string)$mesaj_id) . ' · Hızlı yanıt için <a href="tel:' . $h(preg_replace('/\s/', '', $tel)) . '" style="color:' . $vurgu . ';text-decoration:none">' . $h($tel) . '</a> arayın
        </div>
      </td></tr>

      <tr><td style="background:#f8fafc;padding:14px 24px;text-align:center;font-size:11px;color:#94a3b8;border-top:1px solid #e2e8f0">
        Bu e-posta <strong style="color:#475569">' . $h(parse_url(SITE_URL, PHP_URL_HOST)) . '</strong> üzerinden otomatik gönderildi
      </td></tr>

    </table>
  </td></tr>
</table>
</body></html>';

    $bildirim_konu = ($kesif_mi ? '[KEŞİF] ' : '[İLETİŞİM] ') . $konu . ' — ' . $ad;

    $smtp = Mail::ayardan_yukle();
    if ($alici && $smtp->konfigure_mi()) {
        // Reply-to istemcinin e-postası olmalı (yöneticinin "Yanıtla" tuşu doğru hedefe gitsin)
        $sonuc = $smtp->gonder($alici, $firma, $bildirim_konu, $bildirim_html);
        if (!$sonuc['ok']) {
            error_log('iletisim bildirim SMTP hata: ' . ($sonuc['hata'] ?? '?'));
            // Fallback PHP mail() — son çare
            $headers = "From: " . $firma . " <noreply@" . parse_url(SITE_URL, PHP_URL_HOST) . ">\r\n"
                     . ($mail ? "Reply-To: $mail\r\n" : "")
                     . "Content-Type: text/html; charset=UTF-8\r\n"
                     . "MIME-Version: 1.0\r\n";
            @mail($alici, $bildirim_konu, $bildirim_html, $headers);
        }
    } elseif ($alici) {
        // SMTP yapılandırılmamış → fallback mail()
        $headers = "From: " . $firma . " <noreply@" . parse_url(SITE_URL, PHP_URL_HOST) . ">\r\n"
                 . ($mail ? "Reply-To: $mail\r\n" : "")
                 . "Content-Type: text/html; charset=UTF-8\r\n"
                 . "MIME-Version: 1.0\r\n";
        @mail($alici, $bildirim_konu, $bildirim_html, $headers);
    }

    // ─── Müşteriye Teşekkür Maili (opsiyonel — ayar'a bağlı, e-posta verdiyse) ───
    $musteri_onay = (int)ayar('iletisim_musteri_onay', '1');
    if ($musteri_onay === 1 && $mail !== '' && $smtp->konfigure_mi()) {
        $tel_html  = $h(ayar('firma_telefon_1', '0546 790 78 77'));
        $tesekkur_html = '<!DOCTYPE html>
<html lang="tr"><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Helvetica,Arial,sans-serif;color:#1e293b;line-height:1.5">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f1f5f9;padding:24px 12px">
  <tr><td align="center">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="max-width:560px;width:100%;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.08)">
      <tr><td style="background:#0f172a;padding:18px 24px;color:#fff;text-align:center">
        <div style="font-size:18px;font-weight:700;letter-spacing:.3px">' . $h($firma) . '</div>
      </td></tr>
      <tr><td style="padding:30px 24px;text-align:center">
        <div style="display:inline-block;width:56px;height:56px;line-height:56px;background:#ecfdf5;color:#10b981;border-radius:50%;font-size:28px;margin-bottom:16px">✓</div>
        <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0f172a">Mesajınız bize ulaştı, ' . $h(explode(' ', $ad)[0]) . '!</h1>
        <p style="margin:0 0 22px;color:#64748b;font-size:14px">Talebinizi aldık. Ekibimiz <strong style="color:#0f172a">en geç 1 iş günü içinde</strong> sizinle iletişime geçecek.</p>

        <div style="background:#f8fafc;border-left:3px solid #f59e0b;padding:14px 16px;border-radius:0 4px 4px 0;text-align:left;font-size:13px;color:#475569;margin-bottom:22px">
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.2px;font-weight:700;color:#92400e;margin-bottom:6px">Talebiniz Özeti</div>
          <div><strong style="color:#0f172a">' . $h($konu) . '</strong></div>
          <div style="margin-top:6px;color:#64748b;white-space:pre-wrap">' . $h(mb_substr($mesaj, 0, 200)) . (mb_strlen($mesaj) > 200 ? '…' : '') . '</div>
        </div>

        <div style="font-size:13px;color:#64748b;margin-bottom:6px">Acil bir durumunuz varsa</div>
        <a href="tel:' . $h(preg_replace('/\s/', '', (string)ayar('firma_telefon_1', ''))) . '" style="display:inline-block;background:#f59e0b;color:#fff;padding:10px 22px;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px">' . $tel_html . ' · Hemen Ara</a>
      </td></tr>
      <tr><td style="background:#f8fafc;padding:14px 24px;text-align:center;font-size:11px;color:#94a3b8;border-top:1px solid #e2e8f0">
        ' . $h($firma) . ' · ' . $h(parse_url(SITE_URL, PHP_URL_HOST)) . '
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>';
        $smtp->gonder($mail, $ad, '✓ Mesajınızı aldık — ' . $firma, $tesekkur_html);
        // Müşteri mailinin başarısız olması admin akışını etkilemesin
    }

    redirect($donus . '?ok=1');

} catch (Throwable $e) {
    error_log('iletisim-gonder hata: ' . $e->getMessage());
    redirect($donus . '?hata=' . urlencode('Mesaj kaydedilemedi, lütfen daha sonra tekrar deneyin.'));
}
