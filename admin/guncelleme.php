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
$repo  = (string)(db_get("SELECT deger FROM ayarlar WHERE anahtar='github_repo'")['deger']  ?? '');
$token = (string)(db_get("SELECT deger FROM ayarlar WHERE anahtar='github_token'")['deger'] ?? '');
$branch = (string)(db_get("SELECT deger FROM ayarlar WHERE anahtar='github_branch'")['deger'] ?? '') ?: 'main';

/* ============================================================
   AJAX ENDPOİNTLERİ
   ============================================================ */
if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $ajax = (string)($_POST['ajax'] ?? $_GET['ajax'] ?? '');

    // CSRF (POST'larda zorunlu)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check($_POST['csrf'] ?? null)) {
        echo json_encode(['ok'=>false, 'hata'=>'CSRF token geçersiz.']);
        exit;
    }

    if (!$repo) {
        echo json_encode(['ok'=>false, 'hata'=>'GitHub repo ayarlanmamış. Ayarlar sekmesinden gir.']);
        exit;
    }

    @set_time_limit(120);

    if ($ajax === 'durum') {
        $r = $U->dosya_durumu($repo, $token, $branch);
        if ($r['ok']) {
            $r['mevcut_surum'] = $mevcut_surum;
            $r['repo'] = $repo;
            $r['branch'] = $branch;
            $rel = $U->github_kontrol($repo, $token);
            if ($rel['ok']) {
                $r['github_surum'] = $rel['version'];
                $r['yeni_surum_var'] = (bool)($rel['yeni_sürüm_var'] ?? false);
                $r['release_url']    = $rel['asset_url'] ?? '';
                $r['release_body']   = $rel['body'] ?? '';
                $r['release_tarih']  = $rel['tarih'] ?? '';
            }
        }
        echo json_encode($r);
        exit;
    }

    if ($ajax === 'commits') {
        echo json_encode($U->github_commits($repo, $token, $branch, 20));
        exit;
    }

    if ($ajax === 'sync_dosya') {
        $rel = (string)($_POST['rel'] ?? '');
        $r = $U->tek_dosya_sync($repo, $token, $branch, $rel);
        if ($r['ok']) log_yaz('guncelleme_dosya', "Sync: $rel", (int)$_kul['id']);
        if (function_exists('opcache_reset')) @opcache_reset();
        echo json_encode($r);
        exit;
    }

    if ($ajax === 'akilli_sync') {
        $r = $U->akilli_senkronize($repo, $token, $branch);
        if ($r['ok']) log_yaz('guncelleme_akilli', "Akıllı sync: {$r['basarili']} dosya", (int)$_kul['id']);
        echo json_encode($r);
        exit;
    }

    if ($ajax === 'zorla_sync') {
        // Tüm GitHub dosyalarını yeniden indir (durum farketmeksizin)
        $tree = $U->github_tree($repo, $token, $branch);
        if (!$tree['ok']) { echo json_encode($tree); exit; }
        $tum_dosyalar = array_keys($tree['files']);
        $r = $U->akilli_senkronize($repo, $token, $branch, $tum_dosyalar);
        if ($r['ok']) log_yaz('guncelleme_zorla', "Zorla sync: {$r['basarili']} dosya", (int)$_kul['id']);
        echo json_encode($r);
        exit;
    }

    if ($ajax === 'yedekler') {
        $liste = $U->yedekleri_listele();
        // Boyut formatı
        foreach ($liste as &$y) $y['boyut_fmt'] = $U->boyut_format((int)$y['boyut']);
        echo json_encode(['ok'=>true, 'yedekler'=>$liste]);
        exit;
    }

    if ($ajax === 'geri_al') {
        $yedek = basename((string)($_POST['yedek'] ?? ''));
        $r = $U->geri_al($yedek);
        if ($r['ok']) log_yaz('guncelleme_geri_al', $yedek, (int)$_kul['id']);
        if (function_exists('opcache_reset')) @opcache_reset();
        echo json_encode($r);
        exit;
    }

    if ($ajax === 'yedek_sil') {
        $yedek = basename((string)($_POST['yedek'] ?? ''));
        $ok = $U->yedek_sil($yedek);
        echo json_encode(['ok'=>$ok, 'hata'=>$ok ? null : 'Silinemedi.']);
        exit;
    }

    if ($ajax === 'token_kaydet') {
        $yeni_token  = trim((string)($_POST['github_token']  ?? ''));
        $yeni_repo   = trim((string)($_POST['github_repo']   ?? ''));
        $yeni_branch = trim((string)($_POST['github_branch'] ?? 'main'));
        if ($yeni_repo && !preg_match('~^[\w.\-]+/[\w.\-]+$~', $yeni_repo)) {
            echo json_encode(['ok'=>false, 'hata'=>'Repo formatı: kullanici/repo']);
            exit;
        }
        db_run("INSERT INTO ayarlar (anahtar, deger) VALUES ('github_token', ?) ON DUPLICATE KEY UPDATE deger=VALUES(deger)", [$yeni_token]);
        db_run("INSERT INTO ayarlar (anahtar, deger) VALUES ('github_repo', ?) ON DUPLICATE KEY UPDATE deger=VALUES(deger)",  [$yeni_repo]);
        db_run("INSERT INTO ayarlar (anahtar, deger) VALUES ('github_branch', ?) ON DUPLICATE KEY UPDATE deger=VALUES(deger)",[$yeni_branch ?: 'main']);
        log_yaz('guncelleme_ayar', "GitHub ayarları güncellendi: $yeni_repo / $yeni_branch", (int)$_kul['id']);
        echo json_encode(['ok'=>true, 'mesaj'=>'Ayarlar kaydedildi.']);
        exit;
    }

    if ($ajax === 'token_test') {
        $r = $U->github_kontrol($repo, $token);
        echo json_encode($r);
        exit;
    }

    echo json_encode(['ok'=>false, 'hata'=>"Bilinmeyen ajax: $ajax"]);
    exit;
}

/* ============================================================
   POST AKSIYONLARI (zip yükleme — non-AJAX, fallback)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err','Oturum süresi doldu.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'manuel_zip') {
        if (empty($_FILES['zip']) || $_FILES['zip']['error'] !== UPLOAD_ERR_OK) {
            flash_set('err','ZIP yüklenemedi.');
            redirect($_SERVER['REQUEST_URI']);
        }
        @set_time_limit(0);
        $hedef = $U->temp_dir . '/manuel-' . date('Ymd-His') . '.zip';
        if (!move_uploaded_file($_FILES['zip']['tmp_name'], $hedef)) {
            flash_set('err','ZIP taşınamadı.');
            redirect($_SERVER['REQUEST_URI']);
        }
        $r = $U->uygula($hedef);
        if ($r['ok']) {
            flash_set('ok', "ZIP uygulandı: v{$r['eski']} → v{$r['yeni']}");
            log_yaz('guncelleme_zip', "ZIP: v{$r['eski']} → v{$r['yeni']}", (int)$_kul['id']);
        } else {
            flash_set('err', 'Hata: ' . ($r['hata'] ?? '?'));
        }
        if (function_exists('opcache_reset')) @opcache_reset();
        redirect($_SERVER['REQUEST_URI']);
    }
}

require_once __DIR__ . '/_header.php';
$csrf = csrf_field();
?>

<style>
.upd-tabs{display:flex;gap:4px;margin-bottom:18px;flex-wrap:wrap;border-bottom:2px solid var(--c-line);padding-bottom:0}
.upd-tabs .tab{padding:10px 18px;cursor:pointer;border:0;background:transparent;color:var(--c-muted);font-weight:600;font-size:.92rem;border-bottom:3px solid transparent;margin-bottom:-2px;transition:.15s}
.upd-tabs .tab:hover{color:var(--c-text)}
.upd-tabs .tab.active{color:var(--c-orange);border-bottom-color:var(--c-orange)}
.upd-pane{display:none}
.upd-pane.active{display:block}
.dosya-durum{display:inline-block;width:14px;height:14px;border-radius:50%;margin-right:6px;vertical-align:middle}
.dd-guncel{background:#16a34a}
.dd-degismis{background:#dc2626}
.dd-eksik{background:#dc2626;border:2px dashed #fff}
.dd-korumali{background:#64748b}
.dosya-yol{font-family:'JetBrains Mono',monospace;font-size:.82rem;word-break:break-all}
.upd-loglar{background:#0a0f1f;color:#aaffcc;font-family:monospace;font-size:.78rem;padding:14px;border-radius:6px;max-height:400px;overflow-y:auto;white-space:pre-wrap}
.upd-loglar .err{color:#ff7a7a}
.upd-loglar .ok{color:#aaffcc}
.upd-spin{display:inline-block;width:14px;height:14px;border:2px solid var(--c-line);border-top-color:var(--c-orange);border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:middle;margin-right:6px}
@keyframes spin{to{transform:rotate(360deg)}}
.upd-stat{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}
.upd-stat .st{padding:18px;background:var(--c-bg);border:1px solid var(--c-line);border-radius:10px;text-align:center}
.upd-stat .st .v{font-size:2.2rem;font-weight:800;line-height:1}
.upd-stat .st .l{font-size:.78rem;color:var(--c-muted);text-transform:uppercase;letter-spacing:1px;margin-top:6px}
.upd-stat .ok .v{color:#16a34a}
.upd-stat .warn .v{color:#dc2626}
.upd-stat .info .v{color:var(--c-orange)}
</style>

<div class="page-head">
    <div>
        <h1 class="page-h1">Güncelleme Merkezi</h1>
        <p class="page-sub">Yüklü sürüm: <strong>v<?= e($mevcut_surum) ?></strong> · Repo: <code><?= e($repo ?: '(ayarlanmamış)') ?></code> · Branch: <code><?= e($branch) ?></code></p>
    </div>
    <a href="panel.php" class="btn btn-out"><i class="fas fa-arrow-left"></i> Panel</a>
</div>

<?php foreach (flash_pop() as $f): ?><div class="alert alert-<?= e($f['tip']) ?>"><?= $f['msg'] ?></div><?php endforeach; ?>

<div class="upd-tabs">
    <button class="tab active" data-pane="durum"><i class="fas fa-radar"></i> Genel Durum</button>
    <button class="tab" data-pane="dosyalar"><i class="fas fa-folder-tree"></i> Dosyalar</button>
    <button class="tab" data-pane="commits"><i class="fas fa-code-commit"></i> Commits</button>
    <button class="tab" data-pane="yedekler"><i class="fas fa-clock-rotate-left"></i> Yedekler</button>
    <button class="tab" data-pane="ayarlar"><i class="fas fa-gear"></i> Ayarlar</button>
</div>

<!-- ==================== GENEL DURUM ==================== -->
<div class="upd-pane active" id="pane-durum">
    <div class="card">
        <h3>Genel Durum</h3>
        <div id="durumOzet" style="padding:30px 0;text-align:center;color:var(--c-muted)"><span class="upd-spin"></span> Yükleniyor...</div>
        <div class="form-actions" style="margin-top:18px">
            <button class="btn btn-pri" onclick="durumYukle(true)"><i class="fas fa-sync"></i> Durum Kontrol</button>
            <button class="btn btn-blue" onclick="akilliSync()"><i class="fas fa-bolt"></i> Akıllı Güncelleme</button>
            <button class="btn btn-out" onclick="zorlaSync()" data-onay="TÜM dosyaları zorla yenilemek (sağlam olanlar dahil) istediğine emin misin?"><i class="fas fa-rotate"></i> Zorla Senkronize</button>
        </div>
    </div>

    <div class="card">
        <h3>Sürüm Bilgisi</h3>
        <div id="surumBilgi" style="color:var(--c-muted)">Yükleniyor...</div>
    </div>

    <div class="card">
        <h3>İşlem Logu</h3>
        <pre class="upd-loglar" id="loglarKutusu">Henüz işlem yok. "Akıllı Güncelleme" veya "Zorla Senkronize" butonuna bas.</pre>
    </div>
</div>

<!-- ==================== DOSYALAR ==================== -->
<div class="upd-pane" id="pane-dosyalar">
    <div class="card">
        <h3>Dosya Bazlı Durum</h3>
        <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">
            🟢 Güncel · 🔴 Değişmiş/Eksik · ⚫ Korumalı (config.php, uploads/ — asla yazılmaz). Tek tek "Güncelle" tıklayabilir veya "Akıllı Güncelleme" ile hepsini birden senkronize edebilirsin.
        </p>
        <div class="toolbar">
            <div class="filters">
                <input class="input" type="search" id="dosyaArama" placeholder="Yol ara…" oninput="dosyaFiltre()">
                <select id="dosyaDurumFiltre" onchange="dosyaFiltre()">
                    <option value="">Tümü</option>
                    <option value="degismis">🔴 Değişmiş</option>
                    <option value="eksik">🔴 Eksik</option>
                    <option value="guncel">🟢 Güncel</option>
                </select>
                <button class="btn btn-out btn-sm" onclick="durumYukle(true)"><i class="fas fa-sync"></i> Yenile</button>
            </div>
            <div id="dosyaSayim"><span class="badge badge-info">—</span></div>
        </div>
        <div class="tbl-wrap" style="margin-top:14px">
            <table class="tbl" id="dosyaTbl">
                <thead>
                    <tr><th style="width:60px">Durum</th><th>Yol</th><th style="width:90px">Boyut</th><th style="width:120px">İşlem</th></tr>
                </thead>
                <tbody><tr><td colspan="4" class="empty">Önce "Genel Durum" sekmesinde durum kontrol et.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== COMMITS ==================== -->
<div class="upd-pane" id="pane-commits">
    <div class="card">
        <h3>Son GitHub Commit'leri</h3>
        <div class="form-actions" style="margin-bottom:14px">
            <button class="btn btn-out btn-sm" onclick="commitsYukle()"><i class="fas fa-sync"></i> Yenile</button>
        </div>
        <div class="tbl-wrap">
            <table class="tbl" id="commitTbl">
                <thead><tr><th style="width:90px">SHA</th><th>Mesaj</th><th style="width:140px">Yazar</th><th style="width:140px">Tarih</th></tr></thead>
                <tbody><tr><td colspan="4" class="empty">Yenile butonuna bas.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== YEDEKLER ==================== -->
<div class="upd-pane" id="pane-yedekler">
    <div class="card">
        <h3>Yedekler</h3>
        <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">
            Her güncelleme/sync öncesi etkilenen dosyaların ZIP yedeği alınır. "Geri Al" tıklayarak rollback yapabilirsin.
        </p>
        <div class="form-actions" style="margin-bottom:14px">
            <button class="btn btn-out btn-sm" onclick="yedeklerYukle()"><i class="fas fa-sync"></i> Yenile</button>
        </div>
        <div class="tbl-wrap">
            <table class="tbl" id="yedekTbl">
                <thead><tr><th>Yedek Adı</th><th style="width:90px">Boyut</th><th style="width:160px">Tarih</th><th style="width:200px">İşlem</th></tr></thead>
                <tbody><tr><td colspan="4" class="empty">Yenile butonuna bas.</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3>Manuel ZIP Yükle (Yedekleme amaçlı)</h3>
        <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">
            GitHub erişimi yoksa yerel ZIP'i buradan yükleyip uygulayabilirsin (manifest.json içermesi şart).
        </p>
        <form method="post" enctype="multipart/form-data">
            <?= $csrf ?>
            <input type="hidden" name="islem" value="manuel_zip">
            <div class="form-row cols-2">
                <div class="field"><label>ZIP Dosyası</label><input class="input" type="file" name="zip" accept=".zip" required></div>
                <div class="field" style="display:flex;align-items:flex-end"><button class="btn btn-pri" data-onay="ZIP uygulansın mı?"><i class="fas fa-upload"></i> Yükle ve Uygula</button></div>
            </div>
        </form>
    </div>
</div>

<!-- ==================== AYARLAR ==================== -->
<div class="upd-pane" id="pane-ayarlar">
    <div class="card">
        <h3>GitHub Ayarları</h3>
        <form id="ayarForm">
            <div class="form-row cols-2">
                <div class="field"><label>Repo (kullanici/repo)</label><input class="input" type="text" id="ayar_repo" value="<?= e($repo) ?>" placeholder="codegatr/azradogalgaz"></div>
                <div class="field"><label>Branch</label><input class="input" type="text" id="ayar_branch" value="<?= e($branch) ?>" placeholder="main"></div>
            </div>
            <div class="form-row">
                <div class="field"><label>GitHub Token <span class="opt">(private repo veya rate limit için)</span></label><input class="input" type="password" id="ayar_token" value="<?= e($token) ?>" placeholder="ghp_..." autocomplete="off"></div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-pri" onclick="ayarKaydet()"><i class="fas fa-save"></i> Kaydet</button>
                <button type="button" class="btn btn-blue" onclick="ayarTest()"><i class="fas fa-vial"></i> Test Et</button>
            </div>
            <div id="ayarMesaj" style="margin-top:10px"></div>
        </form>
    </div>

    <div class="card">
        <h3>Korumalı Yollar</h3>
        <p style="color:var(--c-muted);font-size:.9rem">Bu yollar hiçbir koşulda yazılmaz/üzerine yazılmaz:</p>
        <ul style="margin-top:10px;padding-left:24px;color:var(--c-text);font-family:monospace;font-size:.88rem">
            <li>config.php — DB bilgileri</li>
            <li>assets/uploads/ — kullanıcı yüklemeleri (yedekler dahil)</li>
        </ul>
    </div>
</div>

<script>
const CSRF = document.querySelector('input[name=csrf]').value;
const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);

let DOSYA_DURUMU = null;

// Tab switching
$$('.upd-tabs .tab').forEach(t => {
    t.addEventListener('click', () => {
        $$('.upd-tabs .tab').forEach(x => x.classList.remove('active'));
        $$('.upd-pane').forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        $('#pane-' + t.dataset.pane).classList.add('active');
        // Lazy load
        if (t.dataset.pane === 'commits' && !commitsLoaded) commitsYukle();
        if (t.dataset.pane === 'yedekler' && !yedeklerLoaded) yedeklerYukle();
    });
});

function logYaz(metin, tip='ok') {
    const k = $('#loglarKutusu');
    if (k.textContent.startsWith('Henüz')) k.textContent = '';
    const span = document.createElement('span');
    span.className = tip === 'err' ? 'err' : 'ok';
    span.textContent = metin + '\n';
    k.appendChild(span);
    k.scrollTop = k.scrollHeight;
}

async function api(ajax, data={}) {
    const fd = new FormData();
    fd.append('ajax', ajax);
    fd.append('csrf', CSRF);
    for (const [k,v] of Object.entries(data)) fd.append(k, v);
    const r = await fetch('?ajax=' + ajax, {method:'POST', body:fd});
    return await r.json();
}

// ===== DURUM =====
async function durumYukle(force=false) {
    $('#durumOzet').innerHTML = '<span class="upd-spin"></span> GitHub Tree API\'den dosyalar çekiliyor, lokal SHA1 hesaplanıyor...';
    const r = await api('durum');
    if (!r.ok) {
        $('#durumOzet').innerHTML = '<div class="alert alert-err">' + r.hata + '</div>';
        return;
    }
    DOSYA_DURUMU = r;
    const s = r.istatistik;
    $('#durumOzet').innerHTML = `
        <div class="upd-stat">
            <div class="st ok"><div class="v">${s.guncel}</div><div class="l">Güncel</div></div>
            <div class="st warn"><div class="v">${s.degismis}</div><div class="l">Değişmiş</div></div>
            <div class="st warn"><div class="v">${s.eksik}</div><div class="l">Eksik</div></div>
            <div class="st info"><div class="v">${s.toplam}</div><div class="l">Toplam Dosya</div></div>
        </div>`;
    let bilgi = `<table class="tbl" style="font-size:.92rem">
        <tr><td><strong>Yüklü Sürüm</strong></td><td>v${r.mevcut_surum}</td></tr>
        <tr><td><strong>GitHub Sürüm</strong></td><td>${r.github_surum ? 'v'+r.github_surum : '—'} ${r.yeni_surum_var ? '<span class="badge badge-warn">Yeni Sürüm Var</span>' : '<span class="badge badge-ok">Güncel</span>'}</td></tr>
        <tr><td><strong>Repo / Branch</strong></td><td><code>${r.repo}</code> @ <code>${r.branch}</code></td></tr>
        <tr><td><strong>Korumalı Dosya</strong></td><td>${s.korumali} adet</td></tr>`;
    if (r.release_tarih) bilgi += `<tr><td><strong>Son Release</strong></td><td>${new Date(r.release_tarih).toLocaleString('tr-TR')}</td></tr>`;
    bilgi += `</table>`;
    $('#surumBilgi').innerHTML = bilgi;
    if (force) logYaz(`✓ Durum kontrolü: ${s.toplam} dosya tarandı (${s.guncel} güncel, ${s.degismis} değişmiş, ${s.eksik} eksik)`);
    dosyaTabloYaz();
}

function dosyaTabloYaz() {
    if (!DOSYA_DURUMU) return;
    const arama = $('#dosyaArama').value.toLowerCase();
    const filtre = $('#dosyaDurumFiltre').value;
    const tbody = $('#dosyaTbl tbody');
    tbody.innerHTML = '';
    let n = 0;
    for (const [yol, d] of Object.entries(DOSYA_DURUMU.dosyalar)) {
        if (arama && !yol.toLowerCase().includes(arama)) continue;
        if (filtre && d.durum !== filtre) continue;
        const ddCls = d.korumali ? 'dd-korumali' : 'dd-' + d.durum;
        const ddTxt = d.korumali ? 'Korumalı' : (d.durum === 'guncel' ? 'Güncel' : (d.durum === 'degismis' ? 'Değişmiş' : 'Eksik'));
        const boyut = d.boyut < 1024 ? d.boyut + ' B' : (d.boyut < 1048576 ? (d.boyut/1024).toFixed(1) + ' KB' : (d.boyut/1048576).toFixed(2) + ' MB');
        const aksiyon = d.korumali
            ? '<span class="badge badge-no">—</span>'
            : (d.durum === 'guncel'
                ? '<button class="btn btn-out btn-sm" onclick="syncDosya(\'' + yol.replace(/'/g,"\\'") + '\', this)">Yenile</button>'
                : '<button class="btn btn-pri btn-sm" onclick="syncDosya(\'' + yol.replace(/'/g,"\\'") + '\', this)">Güncelle</button>');
        tbody.insertAdjacentHTML('beforeend', `
            <tr data-yol="${yol}">
                <td><span class="dosya-durum ${ddCls}" title="${ddTxt}"></span><small style="color:var(--c-muted)">${ddTxt}</small></td>
                <td class="dosya-yol">${yol}</td>
                <td class="num">${boyut}</td>
                <td>${aksiyon}</td>
            </tr>`);
        n++;
    }
    if (n === 0) tbody.innerHTML = '<tr><td colspan="4" class="empty">Sonuç yok.</td></tr>';
    $('#dosyaSayim').innerHTML = `<span class="badge badge-info">${n} dosya gösteriliyor</span>`;
}

function dosyaFiltre() { dosyaTabloYaz(); }

async function syncDosya(yol, btn) {
    btn.disabled = true; btn.innerHTML = '<span class="upd-spin"></span>';
    const r = await api('sync_dosya', {rel: yol});
    if (r.ok) {
        logYaz(`✓ Sync: ${yol} (${r.boyut} B)`);
        await durumYukle();
    } else {
        logYaz(`✗ ${yol}: ${r.hata}`, 'err');
        btn.disabled = false; btn.textContent = 'Güncelle';
        alert('Hata: ' + r.hata);
    }
}

async function akilliSync() {
    if (!confirm('Değişmiş ve eksik dosyalar GitHub\'dan indirilip yazılacak. Önce yedek alınır. Devam?')) return;
    logYaz('▶ Akıllı senkronizasyon başlıyor...');
    const r = await api('akilli_sync');
    if (!r.ok) { logYaz('✗ Hata: ' + r.hata, 'err'); return; }
    (r.log || []).forEach(l => logYaz(l, l.startsWith('✗') ? 'err' : 'ok'));
    logYaz(`◆ Tamamlandı: ${r.basarili} başarılı, ${r.hata_sayisi} hata. v${r.eski_surum} → v${r.yeni_surum}`);
    await durumYukle();
    setTimeout(() => alert(`Akıllı sync tamam: ${r.basarili} dosya. ${r.hata_sayisi ? r.hata_sayisi + ' hata var, log\'a bak.' : ''}`), 100);
}

async function zorlaSync() {
    if (!confirm('TÜM dosyalar yeniden indirilecek (güncel olanlar bile). Devam?')) return;
    logYaz('▶ Zorla senkronizasyon başlıyor (tüm dosyalar)...');
    const r = await api('zorla_sync');
    if (!r.ok) { logYaz('✗ Hata: ' + r.hata, 'err'); return; }
    (r.log || []).forEach(l => logYaz(l, l.startsWith('✗') ? 'err' : 'ok'));
    logYaz(`◆ Zorla sync tamam: ${r.basarili} dosya, ${r.hata_sayisi} hata.`);
    await durumYukle();
}

// ===== COMMITS =====
let commitsLoaded = false;
async function commitsYukle() {
    commitsLoaded = true;
    const tbody = $('#commitTbl tbody');
    tbody.innerHTML = '<tr><td colspan="4" class="empty"><span class="upd-spin"></span> Yükleniyor...</td></tr>';
    const r = await api('commits');
    if (!r.ok) { tbody.innerHTML = '<tr><td colspan="4" class="empty">' + r.hata + '</td></tr>'; return; }
    if (!r.commits.length) { tbody.innerHTML = '<tr><td colspan="4" class="empty">Commit yok.</td></tr>'; return; }
    tbody.innerHTML = '';
    for (const c of r.commits) {
        const tarih = c.tarih ? new Date(c.tarih).toLocaleString('tr-TR') : '—';
        const msg = c.mesaj.split('\n')[0];
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><a href="${c.url}" target="_blank" style="font-family:monospace;color:var(--c-orange);text-decoration:none">${c.sha}</a></td>
                <td>${msg.replace(/</g,'&lt;')}</td>
                <td>${c.yazar}</td>
                <td class="num">${tarih}</td>
            </tr>`);
    }
}

// ===== YEDEKLER =====
let yedeklerLoaded = false;
async function yedeklerYukle() {
    yedeklerLoaded = true;
    const tbody = $('#yedekTbl tbody');
    tbody.innerHTML = '<tr><td colspan="4" class="empty"><span class="upd-spin"></span> Yükleniyor...</td></tr>';
    const r = await api('yedekler');
    if (!r.ok || !r.yedekler.length) { tbody.innerHTML = '<tr><td colspan="4" class="empty">Henüz yedek yok.</td></tr>'; return; }
    tbody.innerHTML = '';
    for (const y of r.yedekler) {
        const tarih = y.tarih ? new Date(y.tarih * 1000).toLocaleString('tr-TR') : '—';
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><code>${y.ad}</code></td>
                <td class="num">${y.boyut_fmt}</td>
                <td class="num">${tarih}</td>
                <td>
                    <button class="btn btn-pri btn-sm" onclick="yedekGeriAl('${y.ad}')"><i class="fas fa-undo"></i> Geri Al</button>
                    <button class="btn btn-danger btn-sm" onclick="yedekSil('${y.ad}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`);
    }
}

async function yedekGeriAl(ad) {
    if (!confirm('Yedekten geri alınsın mı?\n\nDikkat: Mevcut dosyalar yedek içeriğiyle değiştirilecek.\n\n' + ad)) return;
    const r = await api('geri_al', {yedek: ad});
    alert(r.ok ? 'Geri alındı: ' + ad : 'Hata: ' + r.hata);
    if (r.ok) location.reload();
}

async function yedekSil(ad) {
    if (!confirm('Yedek silinsin mi?\n' + ad)) return;
    const r = await api('yedek_sil', {yedek: ad});
    if (r.ok) yedeklerYukle();
    else alert('Silinemedi.');
}

// ===== AYARLAR =====
async function ayarKaydet() {
    const r = await api('token_kaydet', {
        github_repo:   $('#ayar_repo').value.trim(),
        github_branch: $('#ayar_branch').value.trim(),
        github_token:  $('#ayar_token').value.trim(),
    });
    $('#ayarMesaj').innerHTML = r.ok
        ? '<div class="alert alert-ok">' + r.mesaj + '</div>'
        : '<div class="alert alert-err">' + r.hata + '</div>';
    if (r.ok) setTimeout(() => location.reload(), 800);
}

async function ayarTest() {
    $('#ayarMesaj').innerHTML = '<span class="upd-spin"></span> Test ediliyor...';
    const r = await api('token_test');
    $('#ayarMesaj').innerHTML = r.ok
        ? '<div class="alert alert-ok">✓ Bağlantı OK. GitHub son release: v' + r.version + '</div>'
        : '<div class="alert alert-err">✗ ' + r.hata + '</div>';
}

// İlk yükleme
durumYukle();
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
