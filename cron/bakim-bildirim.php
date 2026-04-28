<?php
/**
 * Bakım Bildirim Cron Endpoint
 * Yol: /cron/bakim-bildirim.php?key=GIZLI_ANAHTAR
 *
 * Cron örneği (DirectAdmin/cPanel):
 *   0 9 * * * curl -s "https://azradogalgaz.com/cron/bakim-bildirim.php?key=XYZ" > /dev/null
 *
 * Bakım tarihi N gün içinde olan müşterilere mail gönderir (varsayılan: 15 gün).
 * Aynı bakım için tekrar mail göndermez (bildirim_gonderildi=1).
 */

declare(strict_types=1);

require_once __DIR__ . '/../inc/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php'; // migration ekle: 004
require_once __DIR__ . '/../inc/mail.php';

// CLI'dan veya HTTP'den çağrılabilir
$cli = (PHP_SAPI === 'cli');
header('Content-Type: text/plain; charset=utf-8');

// Anahtar doğrulama
$gercek = (string)ayar('cron_anahtar', '');
$verilen = (string)($_GET['key'] ?? '');
if (!$cli && (!$gercek || !hash_equals($gercek, $verilen))) {
    http_response_code(403);
    echo "Geçersiz anahtar.\n";
    exit;
}

$aktif = (int)ayar('bakim_bildirim_aktif', '1') === 1;
$gun_sayisi = (int)ayar('bakim_bildirim_gun', '15');
if ($gun_sayisi < 1) $gun_sayisi = 15;

if (!$aktif) {
    echo "Bakım bildirimi sistem ayarından KAPALI.\n";
    exit;
}

$M = Mail::ayardan_yukle();
if (!$M->konfigure_mi()) {
    echo "SMTP ayarları eksik. Admin → Ayarlar → SMTP'den yapılandır.\n";
    exit(1);
}

$bas = date('Y-m-d');
$bit = date('Y-m-d', strtotime("+$gun_sayisi days"));

try {
    $hedefler = db_all("
        SELECT b.*, c.unvan, c.eposta AS cari_eposta, c.telefon AS cari_telefon
        FROM bakim_hatirlaticilari b
        LEFT JOIN cariler c ON c.id = b.cari_id
        WHERE b.durum='aktif'
          AND b.sonraki_bakim_tarihi BETWEEN ? AND ?
          AND b.bildirim_gonderildi = 0
          AND (b.eposta IS NOT NULL AND b.eposta <> '' OR c.eposta IS NOT NULL AND c.eposta <> '')
        ORDER BY b.sonraki_bakim_tarihi ASC
    ", [$bas, $bit]);
} catch (Throwable $e) {
    echo "DB hatası: " . $e->getMessage() . "\n";
    exit(1);
}

$gonderilen = 0; $hata = 0;
$firma_ad   = (string)ayar('firma_unvan', 'Azra Doğalgaz');
$firma_tel  = (string)ayar('firma_telefon_1', '');
$firma_eposta = (string)ayar('firma_eposta', '');
$firma_adres  = (string)ayar('firma_adres', '');

echo "Hedef sayısı: " . count($hedefler) . " (tarih aralığı: $bas → $bit)\n";

foreach ($hedefler as $b) {
    $alici_eposta = (string)($b['eposta'] ?: $b['cari_eposta']);
    $alici_ad     = (string)($b['musteri_ad'] ?: $b['unvan'] ?: 'Müşterimiz');
    if (!filter_var($alici_eposta, FILTER_VALIDATE_EMAIL)) {
        echo "  [ATLA] #{$b['id']} geçersiz e-posta\n";
        continue;
    }

    $kalan_gun = (int)((strtotime($b['sonraki_bakim_tarihi']) - time()) / 86400);
    $tarih_tr  = date('d.m.Y', strtotime($b['sonraki_bakim_tarihi']));
    $cihaz     = ucfirst((string)$b['urun_tipi']);
    if ($b['marka']) $cihaz .= ' / ' . $b['marka'];
    if ($b['model']) $cihaz .= ' ' . $b['model'];

    $konu = "$cihaz Bakım Hatırlatması — $tarih_tr";
    $html = bakim_mail_html($alici_ad, $cihaz, $tarih_tr, $kalan_gun, $firma_ad, $firma_tel, $firma_eposta);

    $r = $M->gonder($alici_eposta, $alici_ad, $konu, $html);
    if ($r['ok']) {
        $gonderilen++;
        db_run("UPDATE bakim_hatirlaticilari SET bildirim_gonderildi=1, son_bildirim_tarihi=NOW() WHERE id=?", [(int)$b['id']]);
        db_run("INSERT INTO bakim_bildirim_log (bakim_id, eposta, konu, sonuc) VALUES (?,?,?,'basarili')",
            [(int)$b['id'], $alici_eposta, mb_substr($konu, 0, 250, 'UTF-8')]);
        echo "  [OK]   #{$b['id']} → $alici_eposta ($tarih_tr, $kalan_gun gün)\n";
    } else {
        $hata++;
        db_run("INSERT INTO bakim_bildirim_log (bakim_id, eposta, konu, sonuc, hata_mesaji) VALUES (?,?,?,'hata',?)",
            [(int)$b['id'], $alici_eposta, mb_substr($konu, 0, 250, 'UTF-8'), mb_substr((string)$r['hata'], 0, 500, 'UTF-8')]);
        echo "  [HATA] #{$b['id']} → $alici_eposta: " . $r['hata'] . "\n";
    }
}

echo "\nBitti. Gönderilen: $gonderilen, Hata: $hata\n";
log_yaz('bakim_bildirim_cron', "Cron: $gonderilen gönderildi, $hata hata", null);


/**
 * HTML mail şablonu — turuncu marka temalı, mobile-friendly.
 */
function bakim_mail_html(string $alici_ad, string $cihaz, string $tarih, int $kalan_gun, string $firma_ad, string $firma_tel, string $firma_eposta): string
{
    $alici = htmlspecialchars($alici_ad, ENT_QUOTES, 'UTF-8');
    $cihaz_e = htmlspecialchars($cihaz, ENT_QUOTES, 'UTF-8');
    $firma   = htmlspecialchars($firma_ad, ENT_QUOTES, 'UTF-8');
    $tel     = htmlspecialchars($firma_tel, ENT_QUOTES, 'UTF-8');
    $mail    = htmlspecialchars($firma_eposta, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f3f4f6;padding:30px 0">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#fff;max-width:600px;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)">
            <tr><td style="background:#ff7a00;padding:24px 30px">
                <h1 style="color:#fff;margin:0;font-size:22px;font-weight:700">{$firma}</h1>
                <p style="color:#fff;opacity:.9;margin:4px 0 0;font-size:13px">Bakım Hatırlatma Servisi</p>
            </td></tr>
            <tr><td style="padding:30px">
                <p style="font-size:16px;margin:0 0 12px"><strong>Sayın {$alici},</strong></p>
                <p style="font-size:14px;line-height:1.6;color:#374151;margin:0 0 18px">
                    {$cihaz} cihazınızın <strong style="color:#ff7a00">{$kalan_gun} gün sonra ({$tarih})</strong>
                    periyodik bakım zamanı geliyor. Cihazınızın güvenli ve verimli çalışması için bakımınızı zamanında yaptırmanızı öneriyoruz.
                </p>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;background:#fff7ed;border-left:4px solid #ff7a00;padding:14px 18px;margin:18px 0;border-radius:4px">
                    <tr><td>
                        <p style="margin:0 0 6px;font-size:13px;color:#9a3412"><strong>Cihaz:</strong> {$cihaz_e}</p>
                        <p style="margin:0 0 6px;font-size:13px;color:#9a3412"><strong>Bakım Tarihi:</strong> {$tarih}</p>
                        <p style="margin:0;font-size:13px;color:#9a3412"><strong>Kalan Süre:</strong> {$kalan_gun} gün</p>
                    </td></tr>
                </table>
                <p style="font-size:14px;line-height:1.6;color:#374151;margin:18px 0">
                    Randevu almak için bize aşağıdaki kanallardan ulaşabilirsiniz:
                </p>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:14px 0">
                    <tr>
HTML
        . ($firma_tel ? "<td style='padding:6px 0;font-size:14px'>📞 <a href='tel:" . htmlspecialchars(preg_replace('/\s+/', '', $firma_tel)) . "' style='color:#ff7a00;text-decoration:none'>{$tel}</a></td></tr><tr>" : '')
        . ($firma_eposta ? "<td style='padding:6px 0;font-size:14px'>✉️ <a href='mailto:{$firma_eposta}' style='color:#ff7a00;text-decoration:none'>{$mail}</a></td></tr>" : '') .
<<<HTML
                </table>
                <p style="font-size:13px;color:#6b7280;margin:24px 0 0;border-top:1px solid #e5e7eb;padding-top:16px">
                    Bu bildirim {$firma} bakım takip sistemi tarafından otomatik gönderilmiştir.
                    Hizmet vermediğimizi düşünüyorsanız lütfen bizi bilgilendirin.
                </p>
            </td></tr>
            <tr><td style="background:#1f2937;padding:14px 30px;text-align:center">
                <p style="color:#9ca3af;font-size:11px;margin:0">© {$firma} · Tüm hakları saklıdır</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
HTML;
}
