<?php
require_once __DIR__ . '/_baslat.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(404); exit('Teklif bulunamadı'); }

$t = db_get("SELECT * FROM teklifler WHERE id=?", [$id]);
if (!$t) { http_response_code(404); exit('Teklif bulunamadı'); }

$kalemler = db_all("SELECT * FROM teklif_kalemleri WHERE teklif_id=? ORDER BY sira ASC, id ASC", [$id]);

$firma_unvan = ayar('firma_unvan',  'Azra Doğalgaz Tesisat');
$firma_adres = ayar('firma_adres',  'Laleli Menderes Cd. No:392/C 35370 / Buca - İzmir');
$firma_tel1  = ayar('firma_telefon_1', '0546 790 78 77');
$firma_tel2  = ayar('firma_telefon_2', '0546 820 60 80');
$firma_eposta= ayar('firma_eposta', 'info@azradogalgaz.com');
$firma_web   = preg_replace('#^https?://#', '', SITE_URL);

$durum_renkler = [
    'taslak'      => ['#e0e7ff', '#4338ca'],
    'gonderildi'  => ['#fef3c7', '#92400e'],
    'goruntulendi'=> ['#fef3c7', '#92400e'],
    'kabul'       => ['#dcfce7', '#166534'],
    'red'         => ['#fee2e2', '#991b1b'],
    'iptal'       => ['#fee2e2', '#991b1b'],
    'faturalandi' => ['#dcfce7', '#166534'],
];
$durum_label = [
    'taslak'=>'Taslak','gonderildi'=>'Gönderildi','goruntulendi'=>'Görüntülendi',
    'kabul'=>'Kabul Edildi','red'=>'Reddedildi','iptal'=>'İptal','faturalandi'=>'Faturalandı',
];
[$d_bg, $d_fg] = $durum_renkler[$t['durum']] ?? ['#f1f5f9', '#0f172a'];

// Geçerlilik gün sayısı
$gun = max(0, (int)round((strtotime($t['gecerlilik_tarihi']) - strtotime($t['teklif_tarihi'])) / 86400));

// Hazırlayan
$hazirlayan = '';
if ($t['olusturan_id']) {
    $kul = db_get("SELECT ad_soyad, kullanici_adi FROM kullanicilar WHERE id=?", [(int)$t['olusturan_id']]);
    $hazirlayan = $kul['ad_soyad'] ?? $kul['kullanici_adi'] ?? '';
}
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Teklif <?= e($t['teklif_no']) ?> — <?= e($firma_unvan) ?></title>
<style>
@page { size: A4; margin: 12mm 14mm; }
*{box-sizing:border-box;margin:0;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
html,body{
    font-family:'Helvetica Neue','Inter','Segoe UI',Arial,sans-serif;
    font-size:10.5px;line-height:1.45;color:#1e293b;background:#fff;
    -webkit-font-smoothing:antialiased;
}
body{padding:18px}
.sheet{max-width:186mm;margin:0 auto}

/* ─── Yazdır barı ─── */
.no-print{
    margin:0 auto 14px;max-width:186mm;
    padding:9px 14px;background:#0f172a;color:#fff;border-radius:6px;
    display:flex;align-items:center;justify-content:space-between;font-size:11px;
}
.no-print .sol{opacity:.7;font-style:italic}
.no-print .sag{display:flex;gap:8px}
.no-print button,.no-print a{
    padding:6px 14px;background:#f59e0b;color:#fff;border:0;border-radius:4px;
    cursor:pointer;font-weight:600;text-decoration:none;font-size:11px;
}
.no-print a.geri{background:transparent;border:1px solid #475569;color:#cbd5e1}

/* ─── HEADER ─── */
.tepe{
    display:flex;justify-content:space-between;align-items:flex-start;
    padding-bottom:12px;border-bottom:2px solid #f59e0b;margin-bottom:14px;gap:24px;
}
.tepe-sol{flex:1;min-width:0}
.tepe-sol .logo{
    height:54px;max-width:230px;object-fit:contain;display:block;margin-bottom:8px;
}
.tepe-sol .firma-bilgi{font-size:9.2px;color:#475569;line-height:1.65}
.tepe-sol .firma-bilgi strong{color:#0f172a;font-weight:700;font-size:10.5px;display:block;margin-bottom:2px;letter-spacing:.3px}
.tepe-sol .firma-bilgi .yetki{color:#f59e0b;font-weight:700;font-size:8.5px;text-transform:uppercase;letter-spacing:1px;display:inline-block;margin-top:3px}

.tepe-sag{text-align:right;min-width:185px}
.tepe-sag h1{
    font-size:24px;font-weight:800;letter-spacing:6px;color:#f59e0b;
    margin-bottom:8px;line-height:1;
}
.tepe-sag .meta{font-size:9.5px;color:#475569;margin-bottom:1.5px}
.tepe-sag .meta strong{color:#0f172a;font-weight:600}
.tepe-sag .badge{
    display:inline-block;padding:3px 11px;font-size:8.5px;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;border-radius:3px;margin-top:6px;
    background:<?= $d_bg ?>;color:<?= $d_fg ?>;
}

/* ─── MÜŞTERİ + DETAY ─── */
.iki-blok{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-bottom:13px}
.iki-blok h3{
    font-size:8.5px;font-weight:700;color:#94a3b8;
    text-transform:uppercase;letter-spacing:1.5px;
    margin-bottom:5px;padding-bottom:3px;border-bottom:1px solid #e2e8f0;
}
.iki-blok .deger{font-size:10px;line-height:1.6}
.iki-blok .deger .ad{color:#0f172a;font-size:11.5px;font-weight:700;letter-spacing:.2px;display:block;margin-bottom:2px}
.iki-blok .deger .iletisim{color:#475569}
.iki-blok .satir{display:flex;justify-content:space-between;padding:1.5px 0;font-size:9.5px}
.iki-blok .satir span:first-child{color:#64748b}
.iki-blok .satir strong{color:#0f172a;font-weight:600}

/* ─── KONU ─── */
.konu{
    background:#fef3c7;border-left:3px solid #f59e0b;
    padding:7px 12px;margin-bottom:12px;border-radius:0 3px 3px 0;
    display:flex;align-items:baseline;gap:10px;
}
.konu .lbl{font-size:8.5px;color:#92400e;text-transform:uppercase;letter-spacing:1.2px;font-weight:700;flex-shrink:0}
.konu .val{color:#0f172a;font-weight:700;font-size:11px}

/* ─── KALEMLER ─── */
table.kalemler{width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px}
table.kalemler thead th{
    background:#0f172a;color:#fff;padding:6.5px 8px;
    font-size:8.5px;font-weight:600;text-align:left;
    text-transform:uppercase;letter-spacing:.7px;
}
table.kalemler th.num,table.kalemler td.num{text-align:right}
table.kalemler th.cen,table.kalemler td.cen{text-align:center}
table.kalemler tbody td{
    padding:6.5px 8px;border-bottom:1px solid #e2e8f0;
    font-size:10px;vertical-align:top;
}
table.kalemler tbody tr:nth-child(even) td{background:#f8fafc}
table.kalemler .aciklama-h{font-weight:600;color:#0f172a}
table.kalemler tfoot td{
    padding:6px 8px;font-size:9.5px;border-top:1px solid #cbd5e1;
}

/* ─── TOPLAMLAR (sağa hizalı kompakt kutu) ─── */
.toplamlar{
    margin-left:auto;width:260px;
    background:#f8fafc;border-left:3px solid #f59e0b;
    padding:9px 14px;margin-bottom:12px;
    page-break-inside:avoid;
}
.toplamlar .satir{display:flex;justify-content:space-between;padding:2px 0;font-size:10.5px}
.toplamlar .satir.bos{color:#64748b}
.toplamlar .satir.bos span:last-child{color:#334155;font-weight:500}
.toplamlar hr{border:0;border-top:1px solid #cbd5e1;margin:5px 0}
.toplamlar .genel{
    font-size:14px;font-weight:800;color:#f59e0b;
    padding-top:3px;letter-spacing:.3px;
}

/* ─── NOTLAR / ŞARTLAR ─── */
.bilgi{
    margin-bottom:10px;padding:8px 11px;
    border:1px dashed #cbd5e1;border-radius:3px;
    font-size:9.2px;line-height:1.55;
    page-break-inside:avoid;
}
.bilgi h4{
    font-size:8.5px;font-weight:700;color:#f59e0b;
    text-transform:uppercase;letter-spacing:1px;
    margin-bottom:4px;
}
.bilgi p{white-space:pre-wrap;color:#334155}

/* ─── İMZA ─── */
.imzalar{
    display:grid;grid-template-columns:1fr 1fr;gap:30px;
    margin-top:22px;page-break-inside:avoid;
}
.imza{
    text-align:center;padding-top:32px;
    border-top:1px solid #94a3b8;font-size:9.5px;
}
.imza strong{display:block;color:#0f172a;font-weight:700;margin-bottom:1px}
.imza span{color:#94a3b8;font-size:8.5px}

/* ─── FOOTER ─── */
.alt{
    margin-top:14px;padding-top:7px;
    border-top:1px solid #e2e8f0;
    text-align:center;font-size:8px;color:#94a3b8;line-height:1.6;
}
.alt strong{color:#475569;font-weight:600;letter-spacing:.4px}
.alt .nokta{color:#cbd5e1;margin:0 4px}

@media print{
    body{padding:0}
    .no-print{display:none!important}
    .sheet{max-width:none}
}
</style>
</head>
<body>

<div class="no-print">
    <span class="sol">Yazdırma sayfası — Ctrl+P ile yazdır veya PDF olarak kaydet</span>
    <div class="sag">
        <a href="<?= SITE_URL ?>/admin/teklifler.php?duzenle=<?= $t['id'] ?>" class="geri">← Düzenle</a>
        <button onclick="window.print()">🖨️ Yazdır / PDF</button>
    </div>
</div>

<div class="sheet">

    <!-- HEADER -->
    <header class="tepe">
        <div class="tepe-sol">
            <img class="logo" src="<?= SITE_URL ?>/assets/img/logo-header.png" alt="<?= e($firma_unvan) ?>">
            <div class="firma-bilgi">
                <strong><?= e(mb_strtoupper($firma_unvan, 'UTF-8')) ?></strong>
                <?= e($firma_adres) ?><br>
                Tel: <?= e($firma_tel1) ?><?php if ($firma_tel2): ?> <span style="color:#cbd5e1">·</span> <?= e($firma_tel2) ?><?php endif; ?>
                <span style="color:#cbd5e1">·</span> <?= e($firma_eposta) ?><br>
                <span class="yetki">İZMİRGAZ Yetkili</span>
                <span style="color:#cbd5e1">·</span>
                <span class="yetki">Demirdöküm Bayisi</span>
                <span style="color:#cbd5e1">·</span>
                <?= e($firma_web) ?>
            </div>
        </div>
        <div class="tepe-sag">
            <h1>TEKLİF</h1>
            <div class="meta">No: <strong><?= e($t['teklif_no']) ?></strong></div>
            <div class="meta">Tarih: <?= tarih_tr($t['teklif_tarihi']) ?></div>
            <div class="meta">Geçerlilik: <strong><?= tarih_tr($t['gecerlilik_tarihi']) ?></strong></div>
            <span class="badge"><?= e($durum_label[$t['durum']] ?? $t['durum']) ?></span>
        </div>
    </header>

    <!-- MÜŞTERİ + DETAY -->
    <section class="iki-blok">
        <div>
            <h3>Sayın Müşterimiz</h3>
            <div class="deger">
                <span class="ad"><?= e($t['musteri_ad']) ?></span>
                <div class="iletisim">
                    <?php if ($t['musteri_telefon']): ?>Tel: <?= e($t['musteri_telefon']) ?><br><?php endif; ?>
                    <?php if ($t['musteri_eposta']): ?>E-posta: <?= e($t['musteri_eposta']) ?><br><?php endif; ?>
                    <?php if ($t['musteri_adres']): ?><?= nl2br(e($t['musteri_adres'])) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div>
            <h3>Teklif Detayları</h3>
            <div class="deger">
                <div class="satir"><span>Para Birimi</span><strong><?= e($t['para_birimi'] ?: 'TL') ?></strong></div>
                <div class="satir"><span>Düzenleme Tarihi</span><strong><?= tarih_tr($t['olusturma'], true) ?></strong></div>
                <div class="satir"><span>Geçerlilik Süresi</span><strong><?= $gun ?> gün</strong></div>
                <?php if ($hazirlayan): ?>
                <div class="satir"><span>Hazırlayan</span><strong><?= e($hazirlayan) ?></strong></div>
                <?php endif; ?>
                <div class="satir"><span>Kalem Sayısı</span><strong><?= count($kalemler) ?> adet</strong></div>
            </div>
        </div>
    </section>

    <!-- KONU -->
    <div class="konu">
        <span class="lbl">Konu</span>
        <span class="val"><?= e($t['konu']) ?></span>
    </div>

    <!-- KALEMLER -->
    <table class="kalemler">
        <thead>
            <tr>
                <th class="cen" style="width:24px">#</th>
                <th>Açıklama</th>
                <th class="num" style="width:48px">Miktar</th>
                <th class="cen" style="width:42px">Birim</th>
                <th class="num" style="width:74px">B.Fiyat</th>
                <th class="num" style="width:38px">İsk%</th>
                <th class="num" style="width:38px">KDV%</th>
                <th class="num" style="width:84px">Toplam</th>
            </tr>
        </thead>
        <tbody>
        <?php $no = 0; foreach ($kalemler as $k): $no++; ?>
            <tr>
                <td class="cen" style="color:#94a3b8;font-weight:600"><?= $no ?></td>
                <td class="aciklama-h"><?= nl2br(e($k['aciklama'])) ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float)$k['miktar'], 3, ',', '.'), '0'), ',') ?></td>
                <td class="cen"><?= e($k['birim']) ?></td>
                <td class="num"><?= number_format((float)$k['birim_fiyat'], 2, ',', '.') ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float)$k['iskonto_yuzde'], 2, ',', '.'), '0'), ',') ?: '0' ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float)$k['kdv_orani'], 2, ',', '.'), '0'), ',') ?></td>
                <td class="num"><strong><?= number_format((float)$k['toplam'], 2, ',', '.') ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TOPLAMLAR -->
    <div class="toplamlar">
        <div class="satir bos"><span>Ara Toplam</span><span><?= tl((float)$t['ara_toplam']) ?></span></div>
        <?php if ((float)$t['iskonto_tutar'] > 0): ?>
            <div class="satir bos"><span>Genel İskonto</span><span>-<?= tl((float)$t['iskonto_tutar']) ?></span></div>
        <?php endif; ?>
        <div class="satir bos"><span>KDV Toplam</span><span><?= tl((float)$t['kdv_toplam']) ?></span></div>
        <hr>
        <div class="satir genel"><span>GENEL TOPLAM</span><span><?= tl((float)$t['genel_toplam']) ?></span></div>
    </div>

    <!-- NOTLAR / ŞARTLAR — sadece doluysa göster -->
    <?php if ($t['notlar'] || $t['sartlar']): ?>
    <div class="bilgi">
        <?php if ($t['sartlar']): ?>
            <h4>Şartlar ve Ödeme Koşulları</h4>
            <p><?= nl2br(e($t['sartlar'])) ?></p>
        <?php endif; ?>
        <?php if ($t['notlar']): ?>
            <h4 style="<?= $t['sartlar'] ? 'margin-top:8px' : '' ?>">Notlar</h4>
            <p><?= nl2br(e($t['notlar'])) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- İMZA -->
    <section class="imzalar">
        <div class="imza">
            <strong><?= e($firma_unvan) ?></strong>
            <span>Yetkili İmza · Kaşe</span>
        </div>
        <div class="imza">
            <strong><?= e($t['musteri_ad']) ?></strong>
            <span>Onay İmzası · Tarih</span>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="alt">
        <strong><?= e(mb_strtoupper($firma_unvan, 'UTF-8')) ?></strong>
        <span class="nokta">·</span><?= e($firma_adres) ?>
        <span class="nokta">·</span>Tel: <?= e($firma_tel1) ?>
        <span class="nokta">·</span><?= e($firma_eposta) ?>
        <span class="nokta">·</span><?= e($firma_web) ?>
    </footer>

</div>

</body>
</html>
