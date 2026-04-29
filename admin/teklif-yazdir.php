<?php
require_once __DIR__ . '/_baslat.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(404); exit('Teklif bulunamadı'); }

$t = db_get("SELECT * FROM teklifler WHERE id=?", [$id]);
if (!$t) { http_response_code(404); exit('Teklif bulunamadı'); }

$kalemler = db_all("SELECT * FROM teklif_kalemleri WHERE teklif_id=? ORDER BY sira ASC, id ASC", [$id]);

$firma_unvan = ayar('firma_unvan', 'Azra Doğalgaz Tesisat');
$firma_adres = ayar('firma_adres', 'Laleli Menderes Cd. No:392/C 35370 / Buca - İzmir');
$firma_tel1  = ayar('firma_telefon_1', '0546 790 78 77');
$firma_tel2  = ayar('firma_telefon_2', '0546 820 60 80');
$firma_eposta= ayar('firma_eposta', 'info@azradogalgaz.com');
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Teklif <?= e($t['teklif_no']) ?> — <?= e($firma_unvan) ?></title>
<style>
@page { size: A4; margin: 18mm 14mm; }
* { box-sizing: border-box; }
body {
    font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
    color: #1e293b;
    background: #fff;
    margin: 0;
    padding: 22px;
    font-size: 13px;
    line-height: 1.55;
}
.wrap { max-width: 820px; margin: 0 auto; background: #fff; }
.tepe {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding-bottom: 18px; border-bottom: 3px solid #f59e0b; margin-bottom: 22px;
    gap: 30px;
}
.tepe-sol img { height: 80px; width: auto; }
.tepe-sol .firma-bilgi { margin-top: 10px; font-size: 11.5px; color: #475569; line-height: 1.6; }
.tepe-sol .firma-unvan { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.tepe-sag { text-align: right; }
.tepe-sag h1 { font-size: 28px; font-weight: 800; color: #f59e0b; margin: 0 0 6px; letter-spacing: 2px; }
.tepe-sag .tek-no { font-size: 14px; color: #1e293b; font-weight: 600; }
.tepe-sag .durum {
    display: inline-block; margin-top: 10px; padding: 4px 12px; border-radius: 6px;
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
}
.durum-taslak { background: #e0e7ff; color: #4338ca; }
.durum-gonderildi, .durum-goruntulendi { background: #fef3c7; color: #92400e; }
.durum-kabul, .durum-faturalandi { background: #dcfce7; color: #166534; }
.durum-red, .durum-iptal { background: #fee2e2; color: #991b1b; }

.iki-kolon { display: flex; gap: 30px; margin-bottom: 22px; }
.iki-kolon > div { flex: 1; }
.kart { padding: 12px 14px; background: #f8fafc; border-left: 3px solid #f59e0b; border-radius: 4px; }
.kart h3 { font-size: 11px; font-weight: 700; color: #64748b; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 1.2px; }
.kart .deger { font-size: 13.5px; line-height: 1.6; }
.kart .deger strong { color: #0f172a; }

.konu { padding: 12px 16px; background: #fef3c7; border-radius: 6px; margin-bottom: 22px; font-size: 14px; }
.konu strong { color: #92400e; }

table.kalemler { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
table.kalemler th {
    background: #1e293b; color: #fff; padding: 10px 8px; font-size: 11.5px;
    font-weight: 600; text-align: left; text-transform: uppercase; letter-spacing: .5px;
}
table.kalemler th.num, table.kalemler td.num { text-align: right; }
table.kalemler td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; font-size: 12.5px; vertical-align: top; }
table.kalemler tr:nth-child(even) td { background: #f8fafc; }
.aciklama-h { font-weight: 600; color: #0f172a; }

.toplamlar { margin-left: auto; width: 320px; background: #f8fafc; padding: 14px 18px; border-radius: 6px; }
.toplamlar .satir { display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; }
.toplamlar .satir.bos { color: #64748b; }
.toplamlar hr { border: 0; border-top: 1px solid #cbd5e1; margin: 8px 0; }
.toplamlar .genel { font-size: 17px; font-weight: 700; color: #f59e0b; padding-top: 6px; }

.bilgi-kutusu { margin-top: 22px; padding: 14px 16px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 12.5px; }
.bilgi-kutusu h4 { margin: 0 0 6px; font-size: 12px; color: #f59e0b; text-transform: uppercase; letter-spacing: 1px; }
.bilgi-kutusu p { margin: 0; white-space: pre-wrap; line-height: 1.7; }

.imzalar { display: flex; gap: 30px; margin-top: 36px; }
.imza-kutu { flex: 1; padding-top: 60px; border-top: 1px solid #94a3b8; text-align: center; font-size: 12px; color: #475569; }

.alt-bilgi { margin-top: 30px; padding-top: 14px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10.5px; color: #94a3b8; }

.no-print { margin: 22px auto; max-width: 820px; padding: 12px 16px; background: #1e293b; color: #fff; border-radius: 6px; display: flex; gap: 10px; align-items: center; justify-content: space-between; }
.no-print button, .no-print a {
    padding: 8px 16px; background: #f59e0b; color: #fff; border: 0; border-radius: 5px;
    cursor: pointer; font-weight: 600; text-decoration: none; font-size: 13px;
}
.no-print a.geri { background: transparent; border: 1px solid #475569; color: #cbd5e1; }
@media print { .no-print { display: none !important; } body { padding: 0; } }
</style>
</head>
<body>

<div class="no-print">
    <span><i style="opacity:.7">Yazdırma sayfası — F5 ile sayfayı yenileyebilirsin</i></span>
    <div style="display:flex;gap:8px">
        <a href="<?= SITE_URL ?>/admin/teklifler.php?duzenle=<?= $t['id'] ?>" class="geri">← Düzenle</a>
        <button onclick="window.print()">🖨️ Yazdır / PDF</button>
    </div>
</div>

<div class="wrap">

    <div class="tepe">
        <div class="tepe-sol">
            <img src="<?= SITE_URL ?>/assets/img/logo-header.png" alt="<?= e($firma_unvan) ?>">
            <div class="firma-bilgi">
                <div class="firma-unvan"><?= e($firma_unvan) ?></div>
                <div><?= e($firma_adres) ?></div>
                <div>Tel: <?= e($firma_tel1) ?> · <?= e($firma_tel2) ?></div>
                <div>E-posta: <?= e($firma_eposta) ?></div>
            </div>
        </div>
        <div class="tepe-sag">
            <h1>TEKLİF</h1>
            <div class="tek-no">No: <strong><?= e($t['teklif_no']) ?></strong></div>
            <div style="font-size:11.5px;color:#475569;margin-top:4px">Tarih: <?= tarih_tr($t['teklif_tarihi']) ?></div>
            <div style="font-size:11.5px;color:#475569">Geçerlilik: <strong><?= tarih_tr($t['gecerlilik_tarihi']) ?></strong></div>
            <span class="durum durum-<?= e($t['durum']) ?>"><?= e($t['durum']) ?></span>
        </div>
    </div>

    <div class="iki-kolon">
        <div class="kart">
            <h3><i></i> Sayın Müşterimiz</h3>
            <div class="deger">
                <strong><?= e($t['musteri_ad']) ?></strong><br>
                <?php if ($t['musteri_telefon']): ?>Tel: <?= e($t['musteri_telefon']) ?><br><?php endif; ?>
                <?php if ($t['musteri_eposta']): ?>E-posta: <?= e($t['musteri_eposta']) ?><br><?php endif; ?>
                <?php if ($t['musteri_adres']): ?><span style="color:#64748b"><?= nl2br(e($t['musteri_adres'])) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="kart">
            <h3>Teklif Detayları</h3>
            <div class="deger">
                <div style="margin-bottom:6px"><strong>Para Birimi:</strong> <?= e($t['para_birimi'] ?: 'TL') ?></div>
                <div><strong>Düzenleme Tarihi:</strong> <?= tarih_tr($t['olusturma'], true) ?></div>
            </div>
        </div>
    </div>

    <div class="konu">
        <strong>Konu:</strong> <?= e($t['konu']) ?>
    </div>

    <table class="kalemler">
        <thead>
            <tr>
                <th style="width:38px">#</th>
                <th>Açıklama</th>
                <th class="num" style="width:65px">Miktar</th>
                <th style="width:60px">Birim</th>
                <th class="num" style="width:90px">B.Fiyat</th>
                <th class="num" style="width:50px">İsk%</th>
                <th class="num" style="width:50px">KDV%</th>
                <th class="num" style="width:100px">Toplam</th>
            </tr>
        </thead>
        <tbody>
        <?php $no = 0; foreach ($kalemler as $k): $no++; ?>
            <tr>
                <td class="num"><?= $no ?></td>
                <td class="aciklama-h"><?= nl2br(e($k['aciklama'])) ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float)$k['miktar'], 3, ',', '.'), '0'), ',') ?></td>
                <td><?= e($k['birim']) ?></td>
                <td class="num"><?= number_format((float)$k['birim_fiyat'], 2, ',', '.') ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float)$k['iskonto_yuzde'], 2, ',', '.'), '0'), ',') ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float)$k['kdv_orani'], 2, ',', '.'), '0'), ',') ?></td>
                <td class="num"><strong><?= number_format((float)$k['toplam'], 2, ',', '.') ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="toplamlar">
        <div class="satir bos"><span>Ara Toplam</span><span><?= tl((float)$t['ara_toplam']) ?></span></div>
        <?php if ((float)$t['iskonto_tutar'] > 0): ?>
            <div class="satir bos"><span>Genel İskonto</span><span>-<?= tl((float)$t['iskonto_tutar']) ?></span></div>
        <?php endif; ?>
        <div class="satir bos"><span>KDV Toplam</span><span><?= tl((float)$t['kdv_toplam']) ?></span></div>
        <hr>
        <div class="satir genel"><span>GENEL TOPLAM</span><span><?= tl((float)$t['genel_toplam']) ?></span></div>
    </div>

    <?php if ($t['notlar']): ?>
    <div class="bilgi-kutusu">
        <h4>Notlar</h4>
        <p><?= nl2br(e($t['notlar'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($t['sartlar']): ?>
    <div class="bilgi-kutusu">
        <h4>Şartlar ve Ödeme Koşulları</h4>
        <p><?= nl2br(e($t['sartlar'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="imzalar">
        <div class="imza-kutu">
            <strong><?= e($firma_unvan) ?></strong><br>
            <span style="color:#94a3b8">Yetkili İmza · Kaşe</span>
        </div>
        <div class="imza-kutu">
            <strong><?= e($t['musteri_ad']) ?></strong><br>
            <span style="color:#94a3b8">Onay İmzası · Tarih</span>
        </div>
    </div>

    <div class="alt-bilgi">
        <strong><?= e($firma_unvan) ?></strong> · <?= e($firma_adres) ?> · Tel: <?= e($firma_tel1) ?>
        <br>www.azradogalgaz.com · İZMİRGAZ Yetkili Tesisat Firması
    </div>

</div>

</body>
</html>
