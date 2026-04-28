<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/sema-muhasebe.php';
page_title('Cari Hesap Ekstresi');

$cari_id = (int)($_GET['cari'] ?? 0);
$bas     = $_GET['bas'] ?? date('Y-01-01');     // varsayılan: yıl başı
$bit     = $_GET['bit'] ?? date('Y-m-d');        // varsayılan: bugün
$yazdir  = isset($_GET['yazdir']);

$cari = $cari_id ? db_get("SELECT * FROM cariler WHERE id=?", [$cari_id]) : null;

/* Cari seçilmediyse — seçim formu */
if (!$cari) {
    require_once __DIR__ . '/_header.php';
    $cariler = db_all("SELECT id, cari_kodu, unvan, bakiye FROM cariler WHERE aktif=1 ORDER BY unvan");
?>
<div class="page-head">
    <div>
        <h1 class="page-h1">Cari Hesap Ekstresi</h1>
        <p class="page-sub">Belirli bir tarih aralığı için cari hareketleri ve yürüyen bakiye raporu.</p>
    </div>
    <a href="cariler.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Cariler</a>
</div>

<div class="card">
    <h3>Ekstre Parametreleri</h3>
    <form method="get">
        <div class="form-row cols-3">
            <div class="field"><label>Cari *</label>
                <select name="cari" required>
                    <option value="">-- Seçin --</option>
                    <?php foreach ($cariler as $c):
                        $b = (float)$c['bakiye'];
                        $isaret = $b > 0 ? ' (Borçlu '.tl($b).')' : ($b < 0 ? ' (Alacaklı '.tl(abs($b)).')' : '');
                    ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['cari_kodu']) ?> — <?= e($c['unvan']) ?><?= e($isaret) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Başlangıç Tarihi</label><input class="input" type="date" name="bas" value="<?= e($bas) ?>"></div>
            <div class="field"><label>Bitiş Tarihi</label><input class="input" type="date" name="bit" value="<?= e($bit) ?>"></div>
        </div>
        <div class="form-actions">
            <button class="btn btn-pri"><i class="fas fa-file-lines"></i> Ekstre Oluştur</button>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fas fa-info-circle" style="color:var(--c-blue)"></i> Bilgi</h3>
    <p style="color:var(--c-muted);font-size:.92rem;line-height:1.7">
        Ekstre raporu seçilen tarih aralığında cariye ait <strong>tüm hareketleri</strong> (manuel borç/alacak, fatura, fiş, tahsilat, ödeme) yürüyen bakiye ile gösterir.
        Açılış bakiyesi tarihinden önceki tüm hareketlerden hesaplanır. Yazdırma butonuyla kâğıda dökülebilir.
    </p>
</div>

<?php require_once __DIR__ . '/_footer.php'; exit; }

/* ============================================================
   EKSTRE HESAPLAMA
   ============================================================ */
// Açılış bakiyesi (devir): bas tarihinden ÖNCEKİ tüm hareketlerin net etkisi
$acilis_q = db_get("SELECT
    COALESCE(SUM(CASE WHEN tip IN ('borc','odeme') THEN tutar ELSE 0 END),0) -
    COALESCE(SUM(CASE WHEN tip IN ('alacak','tahsilat') THEN tutar ELSE 0 END),0) acilis
    FROM cari_hareketler WHERE cari_id=? AND tarih < ?", [$cari_id, $bas]);
$acilis = (float)($acilis_q['acilis'] ?? 0);

// Aralıktaki hareketler
$hareketler = db_all("SELECT * FROM cari_hareketler
    WHERE cari_id=? AND tarih >= ? AND tarih <= ?
    ORDER BY tarih ASC, id ASC", [$cari_id, $bas, $bit]);

// Yürüyen bakiye + dönem toplamları
$yuruyen = $acilis;
$donem_borc = 0; $donem_alacak = 0;
foreach ($hareketler as &$h) {
    $delta = match($h['tip']) {
        'borc'     =>  (float)$h['tutar'],
        'odeme'    =>  (float)$h['tutar'],
        'alacak'   => -(float)$h['tutar'],
        'tahsilat' => -(float)$h['tutar'],
        default    => 0.0,
    };
    $yuruyen += $delta;
    $h['_yuruyen'] = $yuruyen;
    if ($delta > 0) $donem_borc   += $delta;
    if ($delta < 0) $donem_alacak += abs($delta);
}
unset($h);
$kapanis = $yuruyen;

// Firma bilgisi (header için)
$firma_ad   = ayar('firma_unvan', 'Azra Doğalgaz');
$firma_tel  = ayar('firma_telefon', '');
$firma_mail = ayar('firma_eposta', '');
$firma_adr  = ayar('firma_adres', '');

if ($yazdir) {
    /* ============================================================
       YAZDIRMA MODU - sadece ekstre, header/sidebar yok
       ============================================================ */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Ekstre - <?= e($cari['unvan']) ?> - <?= tarih_tr($bas) ?>...<?= tarih_tr($bit) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,Helvetica,sans-serif; font-size:11pt; color:#000; padding:18mm 14mm; }
h1 { font-size:18pt; margin-bottom:4px; color:#ff7a00; }
h2 { font-size:13pt; margin:14px 0 6px; }
.firma { border-bottom:2px solid #ff7a00; padding-bottom:10px; margin-bottom:16px; }
.firma .ad { font-size:16pt; font-weight:700; color:#ff7a00; }
.firma .det { font-size:9pt; color:#444; margin-top:4px; }
.cari-blok { background:#f8f8f8; padding:10px 14px; margin-bottom:14px; border-left:4px solid #ff7a00; }
.cari-blok .ad { font-size:14pt; font-weight:700; }
.cari-blok .meta { font-size:9pt; color:#444; margin-top:3px; }
.aralik { display:flex; justify-content:space-between; margin-bottom:14px; font-size:10pt; }
table { width:100%; border-collapse:collapse; margin:10px 0; }
th { background:#ff7a00; color:#fff; padding:7px 8px; text-align:left; font-size:9pt; }
td { padding:6px 8px; border-bottom:1px solid #ccc; font-size:9.5pt; }
td.num, th.num { text-align:right; font-variant-numeric:tabular-nums; }
.acilis td, .kapanis td { background:#fff3e6; font-weight:700; }
.kapanis td { border-top:2px solid #ff7a00; }
.ozet { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin:16px 0; }
.ozet .k { padding:10px; border:1px solid #ddd; border-radius:4px; }
.ozet .k .l { font-size:8pt; color:#666; text-transform:uppercase; }
.ozet .k .v { font-size:13pt; font-weight:700; margin-top:2px; }
.borc { color:#dc2626; }
.alacak { color:#16a34a; }
.muted { color:#666; }
.foot { margin-top:30px; font-size:8pt; color:#666; text-align:center; border-top:1px solid #ccc; padding-top:8px; }
.aksiyon { margin:14px 0; }
.btn { display:inline-block; padding:8px 16px; background:#ff7a00; color:#fff; text-decoration:none; border-radius:4px; border:none; cursor:pointer; font-size:10pt; }
.btn.out { background:#fff; color:#333; border:1px solid #ccc; }
@media print {
    .aksiyon { display:none; }
    body { padding:5mm; }
}
</style>
</head>
<body>

<div class="aksiyon">
    <button class="btn" onclick="window.print()">🖨️ Yazdır</button>
    <a class="btn out" href="cari-ekstre.php?cari=<?= $cari_id ?>&bas=<?= e($bas) ?>&bit=<?= e($bit) ?>">← Geri</a>
</div>

<div class="firma">
    <div class="ad"><?= e($firma_ad) ?></div>
    <div class="det">
        <?php if ($firma_adr): ?><?= e($firma_adr) ?> · <?php endif; ?>
        <?php if ($firma_tel): ?>Tel: <?= e($firma_tel) ?> · <?php endif; ?>
        <?php if ($firma_mail): ?><?= e($firma_mail) ?><?php endif; ?>
    </div>
</div>

<h1>CARİ HESAP EKSTRESİ</h1>
<div class="aralik">
    <div>Tarih Aralığı: <strong><?= tarih_tr($bas) ?> — <?= tarih_tr($bit) ?></strong></div>
    <div class="muted">Düzenleme: <?= tarih_tr(date('Y-m-d'), true) ?></div>
</div>

<div class="cari-blok">
    <div class="ad"><?= e($cari['unvan']) ?></div>
    <div class="meta">
        Kod: <strong><?= e($cari['cari_kodu']) ?></strong> ·
        Tip: <?= $cari['tip']==='kurumsal' ? 'Kurumsal' : 'Bireysel' ?>
        <?php if ($cari['tckn_vkn']): ?> · TCKN/VKN: <?= e($cari['tckn_vkn']) ?><?php endif; ?>
        <?php if ($cari['vergi_dairesi']): ?> · V.D: <?= e($cari['vergi_dairesi']) ?><?php endif; ?>
        <?php if ($cari['telefon']): ?><br>Tel: <?= e($cari['telefon']) ?><?php endif; ?>
        <?php if ($cari['adres']): ?> · <?= e($cari['adres']) ?><?php endif; ?>
    </div>
</div>

<div class="ozet">
    <div class="k">
        <div class="l">Devir Bakiye</div>
        <div class="v <?= $acilis>0?'borc':($acilis<0?'alacak':'') ?>"><?= tl(abs($acilis)) ?> <?= $acilis>0?'B':($acilis<0?'A':'') ?></div>
    </div>
    <div class="k">
        <div class="l">Dönem Borç (+)</div>
        <div class="v borc"><?= tl($donem_borc) ?></div>
    </div>
    <div class="k">
        <div class="l">Dönem Alacak (−)</div>
        <div class="v alacak"><?= tl($donem_alacak) ?></div>
    </div>
    <div class="k">
        <div class="l">Kapanış Bakiye</div>
        <div class="v <?= $kapanis>0?'borc':($kapanis<0?'alacak':'') ?>"><?= tl(abs($kapanis)) ?> <?= $kapanis>0?'B':($kapanis<0?'A':'') ?></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:90px">Tarih</th>
            <th style="width:90px">Belge</th>
            <th>Açıklama</th>
            <th class="num" style="width:110px">Borç (+)</th>
            <th class="num" style="width:110px">Alacak (−)</th>
            <th class="num" style="width:120px">Bakiye</th>
        </tr>
    </thead>
    <tbody>
        <tr class="acilis">
            <td><?= tarih_tr($bas) ?></td>
            <td>—</td>
            <td>DEVİR / AÇILIŞ BAKİYESİ</td>
            <td class="num">—</td>
            <td class="num">—</td>
            <td class="num <?= $acilis>0?'borc':'alacak' ?>"><?= tl(abs($acilis)) ?> <?= $acilis>0?'B':($acilis<0?'A':'') ?></td>
        </tr>
        <?php if (!$hareketler): ?>
            <tr><td colspan="6" style="text-align:center;color:#666;padding:20px">Bu tarih aralığında hareket yok.</td></tr>
        <?php else: foreach ($hareketler as $h):
            $borc = in_array($h['tip'], ['borc','odeme'], true) ? (float)$h['tutar'] : 0;
            $alacak = in_array($h['tip'], ['alacak','tahsilat'], true) ? (float)$h['tutar'] : 0;
        ?>
            <tr>
                <td><?= tarih_tr($h['tarih']) ?></td>
                <td><?= e(strtoupper($h['belge_tip'])) ?><?= $h['belge_no']?'<br><small style="color:#666">'.e($h['belge_no']).'</small>':'' ?></td>
                <td>
                    <strong><?= ucfirst($h['tip']) ?></strong>
                    <?php if ($h['aciklama']): ?> — <?= e($h['aciklama']) ?><?php endif; ?>
                </td>
                <td class="num <?= $borc>0?'borc':'' ?>"><?= $borc>0 ? tl($borc) : '—' ?></td>
                <td class="num <?= $alacak>0?'alacak':'' ?>"><?= $alacak>0 ? tl($alacak) : '—' ?></td>
                <td class="num <?= $h['_yuruyen']>0?'borc':($h['_yuruyen']<0?'alacak':'') ?>"><?= tl(abs($h['_yuruyen'])) ?> <?= $h['_yuruyen']>0?'B':($h['_yuruyen']<0?'A':'') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        <tr class="kapanis">
            <td><?= tarih_tr($bit) ?></td>
            <td>—</td>
            <td>KAPANIŞ BAKİYESİ</td>
            <td class="num borc"><?= tl($donem_borc) ?></td>
            <td class="num alacak"><?= tl($donem_alacak) ?></td>
            <td class="num <?= $kapanis>0?'borc':'alacak' ?>"><?= tl(abs($kapanis)) ?> <?= $kapanis>0?'B':($kapanis<0?'A':'') ?></td>
        </tr>
    </tbody>
</table>

<div class="foot">
    <?= e($firma_ad) ?> · Bu ekstre <?= tarih_tr(date('Y-m-d'), true) ?> tarihinde düzenlenmiştir.
    <br>"B" = Borçlu (cari size borçlu), "A" = Alacaklı (siz cariye borçlusunuz)
</div>

</body>
</html>
<?php
exit;
}

/* ============================================================
   NORMAL GÖRÜNÜM (admin chrome içinde)
   ============================================================ */
require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Ekstre: <?= e($cari['unvan']) ?></h1>
        <p class="page-sub"><code><?= e($cari['cari_kodu']) ?></code> · <?= tarih_tr($bas) ?> — <?= tarih_tr($bit) ?></p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="?cari=<?= $cari_id ?>&bas=<?= e($bas) ?>&bit=<?= e($bit) ?>&yazdir=1" class="btn btn-pri" target="_blank"><i class="fas fa-print"></i> Yazdırılabilir Görünüm</a>
        <a href="cariler.php?detay=<?= $cari_id ?>" class="btn btn-out"><i class="fas fa-arrow-left"></i> Cari Detay</a>
        <a href="cari-ekstre.php" class="btn btn-out"><i class="fas fa-filter"></i> Yeni Ekstre</a>
    </div>
</div>

<div class="card">
    <h3>Tarih Aralığı</h3>
    <form method="get">
        <input type="hidden" name="cari" value="<?= $cari_id ?>">
        <div class="form-row cols-3">
            <div class="field"><label>Başlangıç</label><input class="input" type="date" name="bas" value="<?= e($bas) ?>"></div>
            <div class="field"><label>Bitiş</label><input class="input" type="date" name="bit" value="<?= e($bit) ?>"></div>
            <div class="field" style="display:flex;align-items:flex-end"><button class="btn btn-pri" style="width:100%"><i class="fas fa-sync"></i> Yenile</button></div>
        </div>
    </form>
</div>

<div class="stats">
    <div class="stat">
        <div class="ico <?= $acilis > 0 ? 'r' : ($acilis < 0 ? 'g' : 'b') ?>"><i class="fas fa-flag-checkered"></i></div>
        <div><strong><?= tl(abs($acilis)) ?> <?= $acilis>0?'B':($acilis<0?'A':'') ?></strong><span>Devir Bakiye</span></div>
    </div>
    <div class="stat">
        <div class="ico r"><i class="fas fa-arrow-up"></i></div>
        <div><strong><?= tl($donem_borc) ?></strong><span>Dönem Borç (+)</span></div>
    </div>
    <div class="stat">
        <div class="ico g"><i class="fas fa-arrow-down"></i></div>
        <div><strong><?= tl($donem_alacak) ?></strong><span>Dönem Alacak (−)</span></div>
    </div>
    <div class="stat">
        <div class="ico <?= $kapanis > 0 ? 'r' : ($kapanis < 0 ? 'g' : 'b') ?>"><i class="fas fa-flag"></i></div>
        <div><strong><?= tl(abs($kapanis)) ?> <?= $kapanis>0?'B':($kapanis<0?'A':'') ?></strong><span>Kapanış Bakiye</span></div>
    </div>
</div>

<div class="card">
    <h3>Hareketler (<?= count($hareketler) ?>)</h3>
    <div class="tbl-wrap">
    <table class="tbl">
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Belge</th>
                <th>Açıklama</th>
                <th class="num" style="text-align:right">Borç (+)</th>
                <th class="num" style="text-align:right">Alacak (−)</th>
                <th class="num" style="text-align:right">Bakiye</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background:rgba(255,140,0,.08);font-weight:700">
                <td class="num"><?= tarih_tr($bas) ?></td>
                <td>—</td>
                <td>DEVİR / AÇILIŞ BAKİYESİ</td>
                <td class="num" style="text-align:right">—</td>
                <td class="num" style="text-align:right">—</td>
                <td class="num" style="text-align:right;color:<?= $acilis>0?'var(--c-red)':'var(--c-green)' ?>"><?= tl(abs($acilis)) ?> <?= $acilis>0?'B':($acilis<0?'A':'') ?></td>
            </tr>
            <?php if (!$hareketler): ?>
                <tr><td colspan="6" class="empty" style="text-align:center;padding:30px">Bu tarih aralığında hareket yok.</td></tr>
            <?php else: foreach ($hareketler as $h):
                $borc = in_array($h['tip'], ['borc','odeme'], true) ? (float)$h['tutar'] : 0;
                $alacak = in_array($h['tip'], ['alacak','tahsilat'], true) ? (float)$h['tutar'] : 0;
            ?>
                <tr>
                    <td class="num"><?= tarih_tr($h['tarih']) ?></td>
                    <td><span class="badge badge-info"><?= e(strtoupper($h['belge_tip'])) ?></span><?php if ($h['belge_no']): ?><br><small style="color:var(--c-muted)"><?= e($h['belge_no']) ?></small><?php endif; ?></td>
                    <td>
                        <strong><?= ucfirst($h['tip']) ?></strong>
                        <?php if ($h['aciklama']): ?> — <span style="color:var(--c-muted)"><?= e($h['aciklama']) ?></span><?php endif; ?>
                    </td>
                    <td class="num" style="text-align:right;color:<?= $borc>0?'var(--c-red)':'var(--c-muted)' ?>;font-weight:<?= $borc>0?'600':'400' ?>"><?= $borc>0 ? tl($borc) : '—' ?></td>
                    <td class="num" style="text-align:right;color:<?= $alacak>0?'var(--c-green)':'var(--c-muted)' ?>;font-weight:<?= $alacak>0?'600':'400' ?>"><?= $alacak>0 ? tl($alacak) : '—' ?></td>
                    <td class="num" style="text-align:right;font-weight:600;color:<?= $h['_yuruyen']>0?'var(--c-red)':($h['_yuruyen']<0?'var(--c-green)':'var(--c-muted)') ?>"><?= tl(abs($h['_yuruyen'])) ?> <?= $h['_yuruyen']>0?'B':($h['_yuruyen']<0?'A':'') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            <tr style="background:rgba(255,140,0,.15);font-weight:700;border-top:2px solid var(--c-orange)">
                <td class="num"><?= tarih_tr($bit) ?></td>
                <td>—</td>
                <td>KAPANIŞ BAKİYESİ</td>
                <td class="num" style="text-align:right;color:var(--c-red)"><?= tl($donem_borc) ?></td>
                <td class="num" style="text-align:right;color:var(--c-green)"><?= tl($donem_alacak) ?></td>
                <td class="num" style="text-align:right;color:<?= $kapanis>0?'var(--c-red)':'var(--c-green)' ?>"><?= tl(abs($kapanis)) ?> <?= $kapanis>0?'B':($kapanis<0?'A':'') ?></td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<div class="card">
    <p style="color:var(--c-muted);font-size:.88rem;line-height:1.7;margin:0">
        <strong style="color:var(--c-text)">B</strong> = Borçlu (cari size borçlu, sizden alacağınız var) ·
        <strong style="color:var(--c-text)">A</strong> = Alacaklı (siz cariye borçlusunuz)<br>
        <strong>Belge tipleri:</strong> MANUEL = elle girilen hareket, FATURA = fatura kaynaklı, FİS = fiş kaynaklı (tahsilat/ödeme).
    </p>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
