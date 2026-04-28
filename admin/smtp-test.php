<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php';
require_once __DIR__ . '/../inc/mail.php';

header('Content-Type: application/json; charset=utf-8');
admin_zorunlu();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) {
    echo json_encode(['ok'=>false, 'hata'=>'Yetkisiz istek.']);
    exit;
}

$alici = trim((string)($_POST['eposta'] ?? ''));
if (!filter_var($alici, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok'=>false, 'hata'=>'Geçerli bir e-posta gir.']);
    exit;
}

$M = Mail::ayardan_yukle();
if (!$M->konfigure_mi()) {
    echo json_encode(['ok'=>false, 'hata'=>'SMTP ayarları eksik. Önce host/user/şifre/gönderen alanlarını kaydet.']);
    exit;
}

$firma = (string)ayar('firma_unvan', 'Azra Doğalgaz');
$tarih = date('d.m.Y H:i');

$html = <<<HTML
<!DOCTYPE html>
<html lang="tr"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:30px">
<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)">
<div style="background:#ff7a00;padding:24px 30px"><h1 style="color:#fff;margin:0;font-size:22px">{$firma}</h1></div>
<div style="padding:30px"><h2 style="color:#16a34a;margin:0 0 12px">✅ SMTP Test Başarılı</h2>
<p>Bu mail SMTP konfigürasyonunun doğru çalıştığını doğrulamak için gönderilmiştir.</p>
<p style="color:#6b7280;font-size:13px;margin-top:18px;border-top:1px solid #e5e7eb;padding-top:14px">
Gönderim zamanı: <strong>{$tarih}</strong><br>
SMTP sunucu: <strong>{$M->host}:{$M->port}</strong><br>
Şifreleme: <strong>{$M->secure}</strong><br>
Gönderen: <strong>{$M->gonderen_eposta}</strong>
</p></div></div></body></html>
HTML;

$r = $M->gonder($alici, 'Test Alıcı', "$firma - SMTP Test", $html);

if ($r['ok']) log_yaz('smtp_test_ok', "SMTP test: $alici", (int)$_kul['id']);
else log_yaz('smtp_test_fail', "SMTP test: $alici - " . $r['hata'], (int)$_kul['id']);

echo json_encode($r);
