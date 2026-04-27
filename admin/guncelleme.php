<?php
require_once __DIR__ . '/_baslat.php';
require_once __DIR__ . '/../inc/updater.php';
page_title('Güncelleme Merkezi');

if ($_kul['rol'] !== 'admin') {
    flash_set('err', 'Bu sayfaya erişim yok.');
    redirect(SITE_URL . '/admin/panel.php');
}

$U = new Guncelleyici(__DIR__ . '/..');
$mevcut_surum = $U->mevcut_surum();

// Ayarlardan repo + token oku
$repo  = (string)(db_get("SELECT deger FROM ayarlar WHERE anahtar='github_repo'")['deger']  ?? '');
$token = (string)(db_get("SELECT deger FROM ayarlar WHERE anahtar='github_token'")['deger'] ?? '');

// Sonuç placeholder'ları
$kontrol_sonuc = null;
$uygulama_sonuc = null;

// ============================================================
//  AKSİYONLAR
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err','Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';

    // -- GitHub kontrol ---------------------------------------
    if ($islem === 'kontrol') {
        $kontrol_sonuc = $U->github_kontrol($repo, $token);
        log_yaz('guncelleme_kontrol', 'GitHub kontrolü yapıldı.', (int)$_kul['id']);
    }

    // -- GitHub indirme + uygulama ----------------------------
    elseif ($islem === 'github_indir_uygula') {
        $asset_url = (string)($_POST['asset_url'] ?? '');
        if (!$asset_url || !str_starts_with($asset_url, 'https://api.github.com/')) {
            flash_set('err','Geçersiz asset URL.');
            redirect($_SERVER['REQUEST_URI']);
        }
        @set_time_limit(0);
        $hedef = $U->temp_dir . '/gh-' . date('Ymd-His') . '.zip';
        $indir = $U->github_indir($asset_url, $token, $hedef);
        if (!$indir['ok']) {
            flash_set('err','İndirme hatası: ' . $indir['hata']);
            redirect($_SERVER['REQUEST_URI']);
        }
        $uygulama_sonuc = $U->uygula($hedef);
        if ($uygulama_sonuc['ok']) {
            log_yaz('guncelleme_uygulandi', "GitHub: v{$uygulama_sonuc['eski']} → v{$uygulama_sonuc['yeni']}", (int)$_kul['id']);
            flash_set('ok',"Güncelleme uygulandı: v{$uygulama_sonuc['eski']} → v{$uygulama_sonuc['yeni']}");
        } else {
            log_yaz('guncelleme_hata', $uygulama_sonuc['hata'] ?? 'bilinmeyen hata', (int)$_kul['id']);
        }
    }

    // -- Manuel ZIP yükleme -----------------------------------
    elseif ($islem === 'manuel_zip') {
        if (empty($_FILES['zip']) || $_FILES['zip']['error'] !== UPLOAD_ERR_OK) {
            flash_set('err','ZIP yüklenemedi (' . ($_FILES['zip']['error'] ?? '?') . ').');
            redirect($_SERVER['REQUEST_URI']);
        }
        if (strtolower(pathinfo($_FILES['zip']['name'], PATHINFO_EXTENSION)) !== 'zip') {
            flash_set('err','Sadece .zip dosyası yüklenebilir.');
            redirect($_SERVER['REQUEST_URI']);
        }
        @set_time_limit(0);
        $hedef = $U->temp_dir . '/manuel-' . date('Ymd-His') . '.zip';
        if (!move_uploaded_file($_FILES['zip']['tmp_name'], $hedef)) {
            flash_set('err','Yüklenen ZIP taşınamadı.');
            redirect($_SERVER['REQUEST_URI']);
        }
        $uygulama_sonuc = $U->uygula($hedef);
        if ($uygulama_sonuc['ok']) {
            log_yaz('guncelleme_uygulandi', "Manuel: v{$uygulama_sonuc['eski']} → v{$uygulama_sonuc['yeni']}", (int)$_kul['id']);
            flash_set('ok',"Güncelleme uygulandı: v{$uygulama_sonuc['eski']} → v{$uygulama_sonuc['yeni']}");
        } else {
            log_yaz('guncelleme_hata', $uygulama_sonuc['hata'] ?? 'bilinmeyen hata', (int)$_kul['id']);
        }
    }

    // -- Yedekten geri al -------------------------------------
    elseif ($islem === 'geri_al') {
        $yedek = basename((string)($_POST['yedek'] ?? ''));
        $r = $U->geri_al($yedek);
        if ($r['ok']) {
            flash_set('ok',"Geri alındı: $yedek ({$r['yazilan']} dosya)");
            log_yaz('guncelleme_geri_al', "Yedek: $yedek", (int)$_kul['id']);
        } else {
            flash_set('err', 'Geri alma hatası: ' . $r['hata']);
        }
        redirect($_SERVER['REQUEST_URI']);
    }

    // -- Yedek sil --------------------------------------------
    elseif ($islem === 'yedek_sil') {
        $yedek = basename((string)($_POST['yedek'] ?? ''));
        if ($U->yedek_sil($yedek)) {
            flash_set('ok','Yedek silindi: ' . $yedek);
            log_yaz('yedek_sil', $yedek, (int)$_kul['id']);
        } else {
            flash_set('err','Yedek silinemedi.');
        }
        redirect($_SERVER['REQUEST_URI']);
    }
}

// Yedek indirme (GET)
if (isset($_GET['indir'])) {
    $yedek = basename((string)$_GET['indir']);
    $yol = $U->yedek_dir . '/' . $yedek;
    if (file_exists($yol) && str_starts_with($yedek, 'yedek-')) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $yedek . '"');
        header('Content-Length: ' . filesize($yol));
        readfile($yol);
        exit;
    }
    flash_set('err','Yedek bulunamadı.');
    redirect(SITE_URL . '/admin/guncelleme.php');
}

// Yedek listesi
$yedekler = $U->yedekleri_listele();

// Son güncelleme logları
$son_loglar = db_all("SELECT * FROM guncelleme_log ORDER BY id DESC LIMIT 10");

$aktif_tab = $_GET['tab'] ?? ($uygulama_sonuc !== null ? 'sonuc' : 'github');

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Güncelleme Merkezi</h1>
        <p class="page-sub">Mevcut sürüm: <strong style="color:var(--c-orange)">v<?= e($mevcut_surum) ?></strong> · Yedek sayısı: <?= count($yedekler) ?> · Son log: <?= $son_loglar ? tarih_tr($son_loglar[0]['olusturma_tarihi'], true) : '—' ?></p>
    </div>
</div>

<?php if ($uygulama_sonuc): ?>
<div class="card" style="border-left:4px solid <?= $uygulama_sonuc['ok'] ? 'var(--c-green)' : 'var(--c-red)' ?>">
    <h3>
        <?= $uygulama_sonuc['ok'] ? '<i class="fas fa-circle-check" style="color:var(--c-green)"></i> Güncelleme Tamam' : '<i class="fas fa-circle-xmark" style="color:var(--c-red)"></i> Güncelleme Hatası' ?>
    </h3>
    <?php if ($uygulama_sonuc['ok']): ?>
        <p>Sürüm: <strong>v<?= e($uygulama_sonuc['eski']) ?></strong> → <strong style="color:var(--c-green)">v<?= e($uygulama_sonuc['yeni']) ?></strong></p>
        <p>Yazılan dosya: <?= (int)$uygulama_sonuc['yazilan'] ?> · Atlanan: <?= (int)$uygulama_sonuc['atlanan'] ?> · Yedek: <code><?= e($uygulama_sonuc['yedek']) ?></code></p>
    <?php else: ?>
        <p style="color:#ff8b8b"><strong>Hata:</strong> <?= e($uygulama_sonuc['hata'] ?? 'bilinmiyor') ?></p>
    <?php endif; ?>
    <?php if (!empty($uygulama_sonuc['log'])): ?>
        <details style="margin-top:10px">
            <summary style="cursor:pointer;color:var(--c-orange)">İşlem Logu (<?= count($uygulama_sonuc['log']) ?> satır)</summary>
            <pre style="margin-top:8px;padding:12px;background:#0a0f1c;border-radius:8px;font-size:.82rem;max-height:280px;overflow:auto;color:#9aa3b8"><?php foreach ($uygulama_sonuc['log'] as $l) echo e($l) . "\n"; ?></pre>
        </details>
    <?php endif; ?>
</div>
<?php endif; ?>

<div data-tabs>
<div class="tabs-h">
    <div class="t <?= $aktif_tab==='github'?'active':'' ?>" data-tab="github"><i class="fab fa-github"></i> GitHub Releases</div>
    <div class="t <?= $aktif_tab==='manuel'?'active':'' ?>" data-tab="manuel"><i class="fas fa-upload"></i> Manuel ZIP</div>
    <div class="t <?= $aktif_tab==='yedekler'?'active':'' ?>" data-tab="yedekler"><i class="fas fa-clock-rotate-left"></i> Yedekler & Geri Al (<?= count($yedekler) ?>)</div>
    <div class="t <?= $aktif_tab==='log'?'active':'' ?>" data-tab="log"><i class="fas fa-list"></i> Geçmiş</div>
    <div class="t <?= $aktif_tab==='manifest'?'active':'' ?>" data-tab="manifest"><i class="fas fa-file-code"></i> Manifest</div>
</div>

<!-- ===================================================== -->
<!-- TAB 1 — GITHUB                                          -->
<!-- ===================================================== -->
<div class="tab-body <?= $aktif_tab==='github'?'active':'' ?>" data-tab="github">
    <div class="card">
        <h3>GitHub Releases üzerinden otomatik güncelleme</h3>

        <?php if (!$repo): ?>
            <div class="alert alert-warn">
                <i class="fas fa-triangle-exclamation"></i>
                Önce <a href="<?= SITE_URL ?>/admin/ayarlar.php?tab=github" style="color:var(--c-orange);text-decoration:underline">Ayarlar → GitHub</a> sekmesinden repo bilgisini gir. Private repolar için ayrıca Personal Access Token gerekli (<code>repo</code> izni).
            </div>
        <?php else: ?>
            <table style="width:100%;font-size:.92rem;margin-bottom:14px">
                <tr><td style="color:var(--c-muted);width:160px">Repository</td><td><code><?= e($repo) ?></code></td></tr>
                <tr><td style="color:var(--c-muted)">Token</td><td><?= $token ? '<span class="badge badge-ok">tanımlı (' . strlen($token) . ' karakter)</span>' : '<span class="badge badge-warn">tanımlı değil — public repo için zorunlu değil</span>' ?></td></tr>
                <tr><td style="color:var(--c-muted)">Mevcut Sürüm</td><td><strong>v<?= e($mevcut_surum) ?></strong></td></tr>
            </table>

            <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="islem" value="kontrol">
                <button class="btn btn-pri"><i class="fas fa-cloud-arrow-down"></i> En Son Sürümü Kontrol Et</button>
            </form>
            <a href="https://github.com/<?= e($repo) ?>/releases" target="_blank" class="btn btn-out"><i class="fab fa-github"></i> GitHub'da Releases</a>

            <?php if ($kontrol_sonuc !== null): ?>
                <hr style="border-color:var(--c-line);margin:18px 0">
                <?php if (!$kontrol_sonuc['ok']): ?>
                    <div class="alert alert-err"><i class="fas fa-circle-xmark"></i> <?= e($kontrol_sonuc['hata']) ?></div>
                <?php elseif (!$kontrol_sonuc['yeni_sürüm_var']): ?>
                    <div class="alert alert-ok"><i class="fas fa-circle-check"></i>
                        Sistemin güncel. En son sürüm: <strong>v<?= e($kontrol_sonuc['version']) ?></strong> (mevcut: v<?= e($kontrol_sonuc['mevcut']) ?>)
                    </div>
                <?php else: ?>
                    <div class="alert alert-warn"><i class="fas fa-bell"></i>
                        Yeni sürüm var! <strong style="color:var(--c-orange)">v<?= e($kontrol_sonuc['version']) ?></strong> (mevcut: v<?= e($kontrol_sonuc['mevcut']) ?>)
                    </div>
                    <div style="margin-top:10px;padding:14px;background:var(--c-card-2);border-radius:10px">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:10px">
                            <div>
                                <strong>v<?= e($kontrol_sonuc['version']) ?></strong>
                                <span style="color:var(--c-muted);font-size:.85rem">· <?= e($kontrol_sonuc['asset_name']) ?> · <?= $U->boyut_format((int)$kontrol_sonuc['size']) ?></span>
                            </div>
                            <form method="post" style="margin:0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="islem" value="github_indir_uygula">
                                <input type="hidden" name="asset_url" value="<?= e($kontrol_sonuc['asset_url']) ?>">
                                <button class="btn btn-pri" data-onay="Sürüm v<?= e($kontrol_sonuc['version']) ?> indirilip uygulansın mı? Otomatik yedek alınır."><i class="fas fa-download"></i> İndir & Uygula</button>
                            </form>
                        </div>
                        <?php if (!empty($kontrol_sonuc['body'])): ?>
                            <details>
                                <summary style="cursor:pointer;color:var(--c-orange);font-size:.88rem">Sürüm Notları</summary>
                                <pre style="margin-top:8px;padding:12px;background:var(--c-bg);border-radius:6px;font-size:.85rem;white-space:pre-wrap;color:var(--c-text)"><?= e($kontrol_sonuc['body']) ?></pre>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ===================================================== -->
<!-- TAB 2 — MANUEL ZIP                                      -->
<!-- ===================================================== -->
<div class="tab-body <?= $aktif_tab==='manuel'?'active':'' ?>" data-tab="manuel">
    <div class="card">
        <h3>Manuel ZIP yükleyerek güncelle</h3>
        <p style="color:var(--c-muted);font-size:.9rem">
            CODEGA tarafından sağlanan ZIP paketini buraya yükle. ZIP içinde
            <code>manifest.json</code> bulunmalı. Sistem önce yedek alır, sonra dosyaları yazar.
            <strong style="color:var(--c-orange)"><code>config.php</code> ve <code>assets/uploads/</code> içeriğine asla dokunulmaz.</strong>
        </p>

        <form method="post" enctype="multipart/form-data" id="zip-form">
            <?= csrf_field() ?>
            <input type="hidden" name="islem" value="manuel_zip">

            <div id="dropzone" style="
                border:2px dashed var(--c-line);border-radius:14px;padding:40px 20px;text-align:center;
                cursor:pointer;transition:.2s;background:rgba(255,255,255,.02);margin-top:14px">
                <i class="fas fa-cloud-arrow-up" style="font-size:2.5rem;color:var(--c-orange);margin-bottom:10px;display:block"></i>
                <strong>ZIP dosyasını sürükle bırak</strong>
                <p style="color:var(--c-muted);font-size:.85rem;margin-top:6px">veya tıklayarak seç (max <?= ini_get('upload_max_filesize') ?>)</p>
                <input type="file" name="zip" accept=".zip" id="zip-input" style="display:none" required>
                <p id="zip-name" style="margin-top:14px;color:var(--c-green);font-weight:600"></p>
            </div>

            <div class="form-actions">
                <button class="btn btn-pri" id="zip-submit" disabled><i class="fas fa-rocket"></i> Yükle ve Uygula</button>
            </div>
        </form>

        <div style="margin-top:18px;padding:14px;background:var(--c-card-2);border-radius:10px;font-size:.85rem">
            <strong>📦 manifest.json formatı:</strong>
            <pre style="margin-top:8px;background:var(--c-bg);padding:12px;border-radius:6px;font-size:.8rem;color:#86efac;overflow:auto">{
  "name": "Azra Doğalgaz Web Sistemi",
  "version": "1.3.0",
  "release_date": "2026-04-27",
  "min_php": "8.1",
  "changelog": "Ne değişti açıklaması",
  "files": ["index.php", "admin/panel.php"]
}</pre>
            <p style="color:var(--c-muted);margin-top:6px;font-size:.82rem">
                <code>files</code> alanı varsa sadece o dosyalar yazılır. Yoksa ZIP içindeki tüm güvenli dosyalar yazılır.
            </p>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- TAB 3 — YEDEKLER                                        -->
<!-- ===================================================== -->
<div class="tab-body <?= $aktif_tab==='yedekler'?'active':'' ?>" data-tab="yedekler">
    <div class="card">
        <h3>Yedekler — Tek tıkla geri alma</h3>
        <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">
            Her güncellemeden ÖNCE etkilenen dosyaların yedeği alınır. En son <?= $U->max_yedek ?> yedek tutulur, eskiler otomatik silinir.
        </p>

        <?php if (!$yedekler): ?>
            <div class="tbl-wrap"><table class="tbl"><tbody><tr><td class="empty">Henüz yedek yok.</td></tr></tbody></table></div>
        <?php else: ?>
        <div class="tbl-wrap">
        <table class="tbl">
        <thead><tr><th>Yedek Adı</th><th style="width:120px">Boyut</th><th style="width:160px">Tarih</th><th style="width:240px;text-align:right">İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($yedekler as $y): ?>
            <tr>
                <td><code style="font-size:.82rem"><?= e($y['ad']) ?></code></td>
                <td class="num"><?= $U->boyut_format((int)$y['boyut']) ?></td>
                <td class="num"><?= tarih_tr(date('Y-m-d H:i:s', (int)$y['tarih']), true) ?></td>
                <td>
                    <div class="actions">
                        <a href="?indir=<?= urlencode($y['ad']) ?>" class="btn btn-out btn-sm" title="ZIP indir"><i class="fas fa-download"></i></a>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="geri_al">
                            <input type="hidden" name="yedek" value="<?= e($y['ad']) ?>">
                            <button class="btn btn-blue btn-sm" data-onay="Bu yedekten geri alınsın mı? Mevcut dosyalar üzerine yazılır."><i class="fas fa-rotate-left"></i> Geri Al</button>
                        </form>
                        <form method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="islem" value="yedek_sil">
                            <input type="hidden" name="yedek" value="<?= e($y['ad']) ?>">
                            <button class="btn btn-danger btn-sm" data-onay="Bu yedek silinsin mi?"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================================================== -->
<!-- TAB 4 — LOG GEÇMİŞİ                                     -->
<!-- ===================================================== -->
<div class="tab-body <?= $aktif_tab==='log'?'active':'' ?>" data-tab="log">
    <div class="card">
        <h3>Güncelleme Geçmişi</h3>
        <?php if (!$son_loglar): ?>
            <p style="color:var(--c-muted)">Henüz güncelleme kaydı yok.</p>
        <?php else: ?>
            <div class="tbl-wrap">
            <table class="tbl">
            <thead><tr><th style="width:160px">Tarih</th><th style="width:120px">Eski</th><th style="width:120px">Yeni</th><th style="width:90px">Durum</th><th>Detay</th></tr></thead>
            <tbody>
            <?php foreach ($son_loglar as $l): ?>
                <tr>
                    <td class="num"><?= tarih_tr($l['olusturma_tarihi'], true) ?></td>
                    <td><code><?= e($l['eski_surum'] ?? '—') ?></code></td>
                    <td><code><?= e($l['yeni_surum'] ?? '—') ?></code></td>
                    <td><span class="badge <?= $l['durum']==='basarili'?'badge-ok':'badge-danger' ?>"><?= e($l['durum']) ?></span></td>
                    <td><details><summary style="cursor:pointer;color:var(--c-orange);font-size:.85rem">Detay gör</summary><pre style="margin-top:6px;padding:8px;background:var(--c-bg);border-radius:6px;font-size:.78rem;color:var(--c-muted);max-height:200px;overflow:auto"><?= e($l['detay'] ?? '') ?></pre></details></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================================================== -->
<!-- TAB 5 — MANIFEST                                        -->
<!-- ===================================================== -->
<div class="tab-body <?= $aktif_tab==='manifest'?'active':'' ?>" data-tab="manifest">
    <div class="card">
        <h3>Mevcut manifest.json</h3>
        <pre style="background:var(--c-bg);padding:14px;border-radius:8px;font-size:.85rem;color:#86efac;overflow:auto;max-height:400px"><?= e(json_encode($U->manifest_oku(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <p style="color:var(--c-muted);font-size:.85rem;margin-top:10px">
            Bu dosya manuel düzenlenmemeli — güncelleme paketi uygulandığında otomatik yenilenir.
        </p>
    </div>
</div>

</div>

<script>
// Drag & drop ZIP
(function(){
    const dz = document.getElementById('dropzone');
    const fi = document.getElementById('zip-input');
    const nm = document.getElementById('zip-name');
    const sb = document.getElementById('zip-submit');
    const fr = document.getElementById('zip-form');
    if (!dz || !fi) return;
    dz.addEventListener('click', () => fi.click());
    fi.addEventListener('change', () => {
        if (fi.files[0]) {
            nm.textContent = '✓ ' + fi.files[0].name + ' (' + (fi.files[0].size/1024/1024).toFixed(2) + ' MB)';
            sb.disabled = false;
        }
    });
    ['dragenter','dragover'].forEach(e => dz.addEventListener(e, ev => {
        ev.preventDefault();
        dz.style.borderColor = 'var(--c-orange)';
        dz.style.background = 'rgba(255,140,0,.06)';
    }));
    ['dragleave','drop'].forEach(e => dz.addEventListener(e, ev => {
        ev.preventDefault();
        dz.style.borderColor = '';
        dz.style.background = '';
    }));
    dz.addEventListener('drop', ev => {
        if (ev.dataTransfer.files[0]) {
            fi.files = ev.dataTransfer.files;
            fi.dispatchEvent(new Event('change'));
        }
    });
    fr?.addEventListener('submit', () => {
        sb.disabled = true;
        sb.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uygulanıyor… (sayfayı kapatma)';
    });
})();
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
