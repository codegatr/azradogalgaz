<?php
require_once __DIR__ . '/config.php';

$token = trim((string)($_GET['token'] ?? ''));
if (!$token || !preg_match('/^[a-f0-9]{32}$/i', $token)) {
    http_response_code(404);
    $sayfa_baslik = 'Teklif Bulunamadı';
    require_once __DIR__ . '/inc/header.php';
    echo '<section class="s"><div class="container"><div class="prose" style="text-align:center;padding:40px 0"><h1>Teklif Bulunamadı</h1><p>Geçersiz veya süresi dolmuş teklif linki.</p><p><a href="' . SITE_URL . '/" class="btn btn-pri">Ana Sayfaya Dön</a></p></div></div></section>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

$t = db_get("SELECT * FROM teklifler WHERE public_token=?", [$token]);
if (!$t) {
    http_response_code(404);
    $sayfa_baslik = 'Teklif Bulunamadı';
    require_once __DIR__ . '/inc/header.php';
    echo '<section class="s"><div class="container"><div class="prose" style="text-align:center;padding:40px 0"><h1>Teklif Bulunamadı</h1><p>Bu link geçersiz veya kaldırılmış olabilir.</p><p>Lütfen size gönderilen orijinal linki kontrol edin veya bizimle iletişime geçin: <a href="tel:' . e(preg_replace('/\s/','',ayar('firma_telefon_1','0546 790 78 77'))) . '">' . e(ayar('firma_telefon_1','0546 790 78 77')) . '</a></p></div></div></section>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250);

// Kabul/Red işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $hata = 'Oturum süresi doldu, sayfayı yenileyin.';
    } else {
        $aksiyon = $_POST['aksiyon'] ?? '';
        if (in_array($t['durum'], ['kabul', 'red', 'iptal', 'faturalandi'], true)) {
            $hata = 'Bu teklif zaten karara bağlanmış. Değişiklik için bizimle iletişime geçin.';
        } elseif ($aksiyon === 'kabul') {
            db_run("UPDATE teklifler SET durum='kabul' WHERE id=?", [$t['id']]);
            db_run("INSERT INTO teklif_log (teklif_id, olay, aciklama, ip, user_agent) VALUES (?,?,?,?,?)",
                [$t['id'], 'kabul', 'Müşteri tarafından KABUL edildi.', $ip, $ua]);
            redirect(SITE_URL . '/teklif/' . $token . '?onay=kabul');
        } elseif ($aksiyon === 'red') {
            $sebep = clean($_POST['sebep'] ?? '');
            db_run("UPDATE teklifler SET durum='red' WHERE id=?", [$t['id']]);
            db_run("INSERT INTO teklif_log (teklif_id, olay, aciklama, ip, user_agent) VALUES (?,?,?,?,?)",
                [$t['id'], 'red', 'Müşteri tarafından reddedildi.' . ($sebep ? ' Sebep: ' . $sebep : ''), $ip, $ua]);
            redirect(SITE_URL . '/teklif/' . $token . '?onay=red');
        }
    }
}

// Görüntülenme logla (taslak değilse)
if ($t['durum'] === 'gonderildi') {
    db_run("UPDATE teklifler SET durum='goruntulendi' WHERE id=?", [$t['id']]);
    db_run("INSERT INTO teklif_log (teklif_id, olay, aciklama, ip, user_agent) VALUES (?,?,?,?,?)",
        [$t['id'], 'goruntulendi', 'Müşteri linki ilk kez açtı.', $ip, $ua]);
    $t['durum'] = 'goruntulendi';
}

$kalemler = db_all("SELECT * FROM teklif_kalemleri WHERE teklif_id=? ORDER BY sira ASC, id ASC", [$t['id']]);

$kalan_gun = (int)((strtotime($t['gecerlilik_tarihi']) - time()) / 86400);
$sure_doldu = $kalan_gun < 0;
$kararlandi = in_array($t['durum'], ['kabul', 'red', 'iptal', 'faturalandi'], true);

$sayfa_baslik = 'Teklif ' . $t['teklif_no'] . ' — ' . SITE_TITLE;
$sayfa_aciklama = $t['konu'];
$kanonik_url = SITE_URL . '/teklif/' . $token;

require_once __DIR__ . '/inc/header.php';

$onay = $_GET['onay'] ?? '';
?>

<section class="s" style="background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);min-height:80vh">
<div class="container" style="max-width:920px">

<?php if ($onay === 'kabul'): ?>
<div class="card" style="background:#dcfce7;border:2px solid #16a34a;color:#166534;text-align:center;padding:30px;margin-bottom:24px">
    <i class="fas fa-circle-check" style="font-size:3rem;color:#16a34a;display:block;margin-bottom:10px"></i>
    <h2 style="margin:0 0 8px;color:#166534">Teklif Kabul Edildi — Teşekkür Ederiz!</h2>
    <p style="margin:0">En kısa sürede sizinle iletişime geçeceğiz. Sorularınız için: <a href="tel:<?= e(preg_replace('/\s/','',ayar('firma_telefon_1','0546 790 78 77'))) ?>" style="color:#15803d;font-weight:700"><?= e(ayar('firma_telefon_1','0546 790 78 77')) ?></a></p>
</div>
<?php elseif ($onay === 'red'): ?>
<div class="card" style="background:#fef3c7;border:2px solid #f59e0b;color:#92400e;text-align:center;padding:30px;margin-bottom:24px">
    <i class="fas fa-circle-info" style="font-size:3rem;color:#f59e0b;display:block;margin-bottom:10px"></i>
    <h2 style="margin:0 0 8px;color:#92400e">Geri Bildiriminiz İçin Teşekkürler</h2>
    <p style="margin:0">Tercih ettiğiniz için tekrar değerlendirilecek bir teklif istiyorsanız bizimle iletişime geçin: <a href="tel:<?= e(preg_replace('/\s/','',ayar('firma_telefon_1','0546 790 78 77'))) ?>" style="color:#92400e;font-weight:700"><?= e(ayar('firma_telefon_1','0546 790 78 77')) ?></a></p>
</div>
<?php endif; ?>

<?php if (!empty($hata)): ?>
<div class="card" style="background:#fee2e2;border:1px solid #dc2626;color:#991b1b;margin-bottom:24px">
    <i class="fas fa-circle-xmark"></i> <?= e($hata) ?>
</div>
<?php endif; ?>

<div class="card" style="padding:0;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.08)">

    <!-- Üst başlık şeridi -->
    <div style="background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);color:#fff;padding:30px 36px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
        <div>
            <img src="<?= SITE_URL ?>/assets/img/logo-header.png" alt="Azra Doğalgaz" style="height:64px;background:#fff;padding:10px 14px;border-radius:8px;display:block;margin-bottom:14px">
            <div style="font-size:.78rem;color:#94a3b8;letter-spacing:1.5px;text-transform:uppercase;font-weight:700">Size özel teklif</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:2rem;font-weight:800;color:#f59e0b;letter-spacing:2px">TEKLİF</div>
            <div style="font-family:monospace;font-size:.95rem;background:rgba(255,255,255,.1);padding:5px 10px;border-radius:5px;margin-top:6px"><?= e($t['teklif_no']) ?></div>
            <?php
                $durum_renkler = [
                    'taslak' => ['#e0e7ff','#4338ca'],
                    'gonderildi' => ['#fef3c7','#92400e'],
                    'goruntulendi' => ['#fef3c7','#92400e'],
                    'kabul' => ['#dcfce7','#166534'],
                    'red' => ['#fee2e2','#991b1b'],
                    'iptal' => ['#fee2e2','#991b1b'],
                    'faturalandi' => ['#dcfce7','#166534'],
                ];
                $durum_label = [
                    'taslak'=>'Taslak','gonderildi'=>'Beklemede','goruntulendi'=>'Beklemede',
                    'kabul'=>'Kabul Edildi','red'=>'Reddedildi','iptal'=>'İptal','faturalandi'=>'Onaylandı',
                ];
                [$bg, $fg] = $durum_renkler[$t['durum']] ?? ['#fff','#000'];
            ?>
            <div style="display:inline-block;margin-top:10px;padding:6px 14px;border-radius:6px;background:<?= $bg ?>;color:<?= $fg ?>;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px"><?= e($durum_label[$t['durum']] ?? $t['durum']) ?></div>
        </div>
    </div>

    <div style="padding:30px 36px">

    <!-- Müşteri & Tarih bilgileri -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:26px">
        <div>
            <div style="font-size:.75rem;color:#64748b;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:8px">Müşteri</div>
            <div style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:6px"><?= e($t['musteri_ad']) ?></div>
            <?php if ($t['musteri_telefon']): ?><div style="color:#475569"><i class="fas fa-phone" style="width:16px;color:#f59e0b"></i> <?= e($t['musteri_telefon']) ?></div><?php endif; ?>
            <?php if ($t['musteri_eposta']): ?><div style="color:#475569"><i class="fas fa-envelope" style="width:16px;color:#f59e0b"></i> <?= e($t['musteri_eposta']) ?></div><?php endif; ?>
            <?php if ($t['musteri_adres']): ?><div style="color:#475569;margin-top:6px;font-size:.92rem"><i class="fas fa-map-marker-alt" style="width:16px;color:#f59e0b"></i> <?= nl2br(e($t['musteri_adres'])) ?></div><?php endif; ?>
        </div>
        <div>
            <div style="font-size:.75rem;color:#64748b;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:8px">Tarih Bilgileri</div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9"><span style="color:#64748b">Düzenleme Tarihi:</span><strong><?= tarih_tr($t['teklif_tarihi']) ?></strong></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9"><span style="color:#64748b">Geçerlilik Tarihi:</span><strong style="color:<?= $sure_doldu?'#dc2626':'#0f172a' ?>"><?= tarih_tr($t['gecerlilik_tarihi']) ?></strong></div>
            <?php if (!$kararlandi && !$sure_doldu): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0"><span style="color:#64748b">Kalan Süre:</span><strong style="color:#f59e0b"><?= $kalan_gun ?> gün</strong></div>
            <?php elseif ($sure_doldu && !$kararlandi): ?>
                <div style="background:#fee2e2;color:#991b1b;padding:8px 12px;border-radius:5px;margin-top:6px;font-size:.85rem;font-weight:700"><i class="fas fa-clock"></i> Geçerlilik süresi dolmuştur. Yeni teklif için lütfen bizimle iletişime geçin.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Konu -->
    <div style="background:#fef3c7;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:6px;margin-bottom:24px">
        <div style="font-size:.75rem;color:#92400e;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:4px">Konu</div>
        <div style="font-size:1.05rem;color:#0f172a;font-weight:600"><?= e($t['konu']) ?></div>
    </div>

    <!-- Kalemler -->
    <h3 style="font-size:.95rem;color:#0f172a;margin:0 0 12px;text-transform:uppercase;letter-spacing:1.2px">Teklif Detayları</h3>
    <div style="overflow-x:auto;margin-bottom:20px">
    <table style="width:100%;border-collapse:collapse;background:#fff;font-size:.92rem">
        <thead>
            <tr style="background:#0f172a;color:#fff">
                <th style="padding:11px 10px;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px">Açıklama</th>
                <th style="padding:11px 10px;text-align:right;width:80px;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px">Miktar</th>
                <th style="padding:11px 10px;text-align:right;width:120px;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px">B.Fiyat</th>
                <th style="padding:11px 10px;text-align:right;width:120px;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px">Toplam</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($kalemler as $i => $k): ?>
            <tr style="<?= $i%2 ? 'background:#f8fafc' : '' ?>">
                <td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;color:#0f172a;line-height:1.5">
                    <?= nl2br(e($k['aciklama'])) ?>
                    <?php if ((float)$k['iskonto_yuzde'] > 0): ?>
                        <div style="font-size:.78rem;color:#16a34a;margin-top:3px"><i class="fas fa-tag"></i> %<?= rtrim(rtrim(number_format((float)$k['iskonto_yuzde'], 2, ',', '.'), '0'), ',') ?> indirim uygulandı</div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#475569"><?= rtrim(rtrim(number_format((float)$k['miktar'], 3, ',', '.'), '0'), ',') ?> <?= e($k['birim']) ?></td>
                <td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#475569"><?= number_format((float)$k['birim_fiyat'], 2, ',', '.') ?> ₺</td>
                <td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:700;color:#0f172a"><?= number_format((float)$k['toplam'], 2, ',', '.') ?> ₺</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Toplam tablosu -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:24px">
        <div style="min-width:300px;background:#f8fafc;padding:18px 22px;border-radius:8px">
            <div style="display:flex;justify-content:space-between;padding:4px 0;color:#64748b"><span>Ara Toplam</span><span><?= tl((float)$t['ara_toplam']) ?></span></div>
            <?php if ((float)$t['iskonto_tutar'] > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:4px 0;color:#16a34a"><span>Genel İskonto</span><span>-<?= tl((float)$t['iskonto_tutar']) ?></span></div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:4px 0;color:#64748b"><span>KDV Toplam</span><span><?= tl((float)$t['kdv_toplam']) ?></span></div>
            <hr style="border:0;border-top:1px solid #cbd5e1;margin:10px 0">
            <div style="display:flex;justify-content:space-between;font-size:1.4rem;font-weight:800"><span>TOPLAM</span><span style="color:#f59e0b"><?= tl((float)$t['genel_toplam']) ?></span></div>
        </div>
    </div>

    <?php if ($t['notlar']): ?>
    <div style="background:#eff6ff;border-left:3px solid #3b82f6;padding:14px 18px;border-radius:6px;margin-bottom:18px">
        <div style="font-size:.75rem;color:#1e40af;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:6px"><i class="fas fa-note-sticky"></i> Notlar</div>
        <div style="white-space:pre-wrap;line-height:1.7"><?= nl2br(e($t['notlar'])) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($t['sartlar']): ?>
    <div style="background:#f8fafc;border:1px dashed #cbd5e1;padding:14px 18px;border-radius:6px;margin-bottom:24px;font-size:.9rem">
        <div style="font-size:.75rem;color:#64748b;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:6px"><i class="fas fa-circle-info"></i> Şartlar ve Ödeme Koşulları</div>
        <div style="white-space:pre-wrap;line-height:1.7;color:#475569"><?= nl2br(e($t['sartlar'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Aksiyon butonları -->
    <?php if (!$kararlandi && !$sure_doldu): ?>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:30px;padding-top:24px;border-top:2px solid #f1f5f9">
        <form method="post" style="flex:1;min-width:240px" onsubmit="return confirm('Bu teklifi kabul etmek istediğinizden emin misiniz?')">
            <?= csrf_field() ?>
            <input type="hidden" name="aksiyon" value="kabul">
            <button type="submit" style="width:100%;padding:14px 24px;background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);color:#fff;border:0;border-radius:8px;font-size:1.05rem;font-weight:700;cursor:pointer;transition:.2s;box-shadow:0 4px 12px rgba(22,163,74,.3)" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(22,163,74,.4)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 12px rgba(22,163,74,.3)'">
                <i class="fas fa-check-circle"></i> Teklifi Kabul Ediyorum
            </button>
        </form>

        <button type="button" onclick="document.getElementById('redForm').style.display='block';this.style.display='none'" style="flex:0 0 auto;padding:14px 24px;background:#fff;color:#dc2626;border:1px solid #fee2e2;border-radius:8px;font-size:1.05rem;font-weight:600;cursor:pointer;transition:.2s">
            <i class="fas fa-circle-xmark"></i> Reddet
        </button>
    </div>

    <form method="post" id="redForm" style="display:none;margin-top:14px;padding:18px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px">
        <?= csrf_field() ?>
        <input type="hidden" name="aksiyon" value="red">
        <label style="font-weight:600;color:#991b1b;margin-bottom:8px;display:block">Geri bildirim (opsiyonel — bize daha iyi hizmet vermemize yardımcı olur):</label>
        <textarea name="sebep" rows="3" placeholder="Fiyat / kapsam / zamanlama / başka bir teklif tercih edildi …" style="width:100%;padding:10px;border:1px solid #fecaca;border-radius:5px;font-family:inherit;font-size:.92rem;background:#fff"></textarea>
        <div style="display:flex;gap:8px;margin-top:10px">
            <button type="submit" style="padding:10px 20px;background:#dc2626;color:#fff;border:0;border-radius:5px;font-weight:600;cursor:pointer">Reddet ve Bilgilendir</button>
            <button type="button" onclick="document.getElementById('redForm').style.display='none'" style="padding:10px 20px;background:transparent;color:#64748b;border:1px solid #cbd5e1;border-radius:5px;font-weight:600;cursor:pointer">Vazgeç</button>
        </div>
    </form>
    <?php elseif ($kararlandi): ?>
    <div style="margin-top:30px;padding:20px;background:#f8fafc;border-radius:8px;text-align:center;color:#64748b">
        <i class="fas fa-circle-info" style="font-size:1.5rem;color:#94a3b8;display:block;margin-bottom:8px"></i>
        Bu teklif <strong><?= e($durum_label[$t['durum']] ?? $t['durum']) ?></strong> durumundadır. Değişiklik için bizimle iletişime geçin.
    </div>
    <?php endif; ?>

    </div><!-- /padding wrapper -->

    <!-- Alt bilgi şeridi -->
    <div style="background:#f8fafc;padding:18px 36px;text-align:center;font-size:.85rem;color:#64748b;border-top:1px solid #e2e8f0">
        <strong style="color:#0f172a"><?= e(ayar('firma_unvan', 'Azra Doğalgaz Tesisat')) ?></strong>
        · Tel: <a href="tel:<?= e(preg_replace('/\s/','',ayar('firma_telefon_1',''))) ?>" style="color:#f59e0b;font-weight:600"><?= e(ayar('firma_telefon_1','0546 790 78 77')) ?></a>
        · <a href="mailto:<?= e(ayar('firma_eposta','info@azradogalgaz.com')) ?>" style="color:#f59e0b;font-weight:600"><?= e(ayar('firma_eposta','info@azradogalgaz.com')) ?></a>
    </div>

</div><!-- /card -->

</div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
