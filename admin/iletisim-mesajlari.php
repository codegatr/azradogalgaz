<?php
require_once __DIR__ . '/_baslat.php';
page_title('İletişim Mesajları');

// Aksiyonlar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);

    if ($islem === 'durum_degistir' && $id) {
        $yeni = $_POST['durum'] ?? 'okundu';
        if (!in_array($yeni, ['yeni','okundu','arandi','kapali'], true)) $yeni = 'okundu';
        db_run("UPDATE iletisim_mesajlari SET durum=? WHERE id=?", [$yeni, $id]);
        flash_set('ok', 'Durum güncellendi.');
    } elseif ($islem === 'sil' && $id) {
        db_run("DELETE FROM iletisim_mesajlari WHERE id=?", [$id]);
        log_yaz('mesaj_sil', "Mesaj silindi (#$id)", (int)$_kul['id']);
        flash_set('ok', 'Mesaj silindi.');
    } elseif ($islem === 'toplu_okundu') {
        db_run("UPDATE iletisim_mesajlari SET durum='okundu' WHERE durum='yeni'");
        flash_set('ok', 'Tüm yeni mesajlar okundu olarak işaretlendi.');
    }
    redirect($_SERVER['REQUEST_URI']);
}

// Filtre
$durum = $_GET['durum'] ?? '';
$arama = clean($_GET['q'] ?? '');
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 20;
$ofset = ($sayfa - 1) * $limit;

$where = "1=1";
$params = [];
if ($durum && in_array($durum, ['yeni','okundu','arandi','kapali'], true)) {
    $where .= " AND durum=?"; $params[] = $durum;
}
if ($arama) {
    $where .= " AND (ad_soyad LIKE ? OR telefon LIKE ? OR eposta LIKE ? OR mesaj LIKE ?)";
    $w = "%$arama%"; $params[] = $w; $params[] = $w; $params[] = $w; $params[] = $w;
}

$toplam = (int)db_get("SELECT COUNT(*) c FROM iletisim_mesajlari WHERE $where", $params)['c'];
$toplam_sayfa = max(1, (int)ceil($toplam / $limit));

$mesajlar = db_all("SELECT * FROM iletisim_mesajlari WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $ofset", $params);

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">İletişim Mesajları</h1>
        <p class="page-sub">Sitedeki iletişim formundan gelen tüm mesajlar.</p>
    </div>
    <form method="post" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="islem" value="toplu_okundu">
        <button class="btn btn-out" data-onay="Tüm 'yeni' mesajlar okundu olarak işaretlensin mi?"><i class="fas fa-check-double"></i> Hepsini Okundu Yap</button>
    </form>
</div>

<form method="get" class="toolbar">
    <div class="filters">
        <input type="search" name="q" value="<?= e($arama) ?>" placeholder="Ad, telefon, mesajda ara…" class="input">
        <select name="durum">
            <option value="">Tüm durumlar</option>
            <option value="yeni"   <?= $durum==='yeni'?'selected':'' ?>>Yeni</option>
            <option value="okundu" <?= $durum==='okundu'?'selected':'' ?>>Okundu</option>
            <option value="arandi" <?= $durum==='arandi'?'selected':'' ?>>Arandı</option>
            <option value="kapali" <?= $durum==='kapali'?'selected':'' ?>>Kapalı</option>
        </select>
        <button class="btn btn-out btn-sm"><i class="fas fa-filter"></i> Filtrele</button>
        <?php if ($durum || $arama): ?>
            <a href="<?= SITE_URL ?>/admin/iletisim-mesajlari.php" class="btn btn-out btn-sm">Temizle</a>
        <?php endif; ?>
    </div>
    <div><span class="badge badge-info"><?= $toplam ?> kayıt</span></div>
</form>

<div class="tbl-wrap">
<table class="tbl">
<thead>
<tr>
    <th style="width:140px">Tarih</th>
    <th>Gönderen</th>
    <th>İletişim</th>
    <th>Konu / Mesaj</th>
    <th style="width:110px">Durum</th>
    <th style="width:160px;text-align:right">İşlem</th>
</tr>
</thead>
<tbody>
<?php if (!$mesajlar): ?>
    <tr><td colspan="6" class="empty"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>Mesaj bulunamadı.</td></tr>
<?php else: foreach ($mesajlar as $m): ?>
<tr>
    <td class="num">
        <?= tarih_tr($m['olusturma_tarihi']) ?><br>
        <small style="color:var(--c-muted)"><?= e(date('H:i', strtotime((string)$m['olusturma_tarihi']))) ?></small>
    </td>
    <td>
        <strong><?= e($m['ad_soyad']) ?></strong>
        <?php if ($m['durum']==='yeni'): ?> <span class="badge badge-warn">YENİ</span><?php endif; ?>
        <?php if ($m['ip']): ?><br><small style="color:var(--c-muted)"><?= e($m['ip']) ?></small><?php endif; ?>
    </td>
    <td>
        <?php if ($m['telefon']): ?>
            <a href="tel:<?= e(preg_replace('/\s/','',(string)$m['telefon'])) ?>"><i class="fas fa-phone"></i> <?= e($m['telefon']) ?></a><br>
        <?php endif; ?>
        <?php if ($m['eposta']): ?>
            <a href="mailto:<?= e($m['eposta']) ?>"><i class="fas fa-envelope"></i> <?= e($m['eposta']) ?></a>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($m['konu']): ?><strong><?= e($m['konu']) ?></strong><br><?php endif; ?>
        <span style="color:var(--c-muted);font-size:.85rem"><?= nl2br(e(mb_substr((string)$m['mesaj'], 0, 200))) ?><?= mb_strlen((string)$m['mesaj']) > 200 ? '…' : '' ?></span>
        <?php if (mb_strlen((string)$m['mesaj']) > 200): ?>
            <details style="margin-top:6px">
                <summary style="cursor:pointer;color:var(--c-orange);font-size:.82rem">Tamamını gör</summary>
                <div style="margin-top:8px;padding:10px;background:var(--c-bg);border-radius:6px;font-size:.85rem"><?= nl2br(e((string)$m['mesaj'])) ?></div>
            </details>
        <?php endif; ?>
    </td>
    <td>
        <form method="post" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="durum_degistir">
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <select name="durum" onchange="this.form.submit()" style="padding:6px 8px;font-size:.8rem">
                <option value="yeni"   <?= $m['durum']==='yeni'?'selected':'' ?>>Yeni</option>
                <option value="okundu" <?= $m['durum']==='okundu'?'selected':'' ?>>Okundu</option>
                <option value="arandi" <?= $m['durum']==='arandi'?'selected':'' ?>>Arandı</option>
                <option value="kapali" <?= $m['durum']==='kapali'?'selected':'' ?>>Kapalı</option>
            </select>
        </form>
    </td>
    <td>
        <div class="actions">
            <?php if ($m['telefon']): ?>
                <a href="tel:<?= e(preg_replace('/\s/','',(string)$m['telefon'])) ?>" class="btn btn-blue btn-sm" title="Ara"><i class="fas fa-phone"></i></a>
            <?php endif; ?>
            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="sil">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button class="btn btn-danger btn-sm" data-onay="Bu mesaj silinsin mi? Geri alınamaz." title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php if ($toplam_sayfa > 1):
    $base = SITE_URL . '/admin/iletisim-mesajlari.php?'
        . http_build_query(array_filter(['q'=>$arama, 'durum'=>$durum]));
    $base .= ($base[strlen($base)-1] === '?') ? '' : '&';
?>
<nav class="pager">
    <?php if ($sayfa > 1): ?><a href="<?= $base ?>sayfa=<?= $sayfa-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($p=1;$p<=$toplam_sayfa;$p++): ?>
        <a href="<?= $base ?>sayfa=<?= $p ?>" class="<?= $p===$sayfa?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($sayfa < $toplam_sayfa): ?><a href="<?= $base ?>sayfa=<?= $sayfa+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
