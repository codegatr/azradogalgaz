<?php
if (!isset($_kul)) { redirect(SITE_URL . '/admin/'); }
$page_title = $GLOBALS['_admin_page_title'] ?? 'Yönetim Paneli';

// Yeni mesaj sayısı (sidebar rozeti)
$_yeni_mesaj = (int)(db_get("SELECT COUNT(*) c FROM iletisim_mesajlari WHERE durum='yeni'") ?? ['c' => 0])['c'];
// Yaklaşan/gecikmiş bakım sayısı (sidebar rozeti)
$_bakim_uyari = 0;
try {
    $_bakim_uyari = (int)(db_get("SELECT COUNT(*) c FROM bakim_hatirlaticilari WHERE durum='aktif' AND sonraki_bakim_tarihi <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)") ?? ['c' => 0])['c'];
} catch (Throwable $e) { /* tablo henüz yok */ }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> · Azra Doğalgaz Yönetim</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?: time() ?>">
</head>
<body>
<div class="adm-body">

<aside class="adm-sidebar" id="admSidebar">
    <div class="adm-brand">
        <span class="azra">AZRA</span>
        <span class="doga">DOĞALGAZ</span>
        <small>Yönetim Paneli</small>
    </div>

    <nav class="adm-nav">
        <div class="group">Genel</div>
        <a href="<?= SITE_URL ?>/admin/panel.php" class="<?= nav_active('panel.php') ?>">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/admin/iletisim-mesajlari.php" class="<?= nav_active('iletisim-mesajlari.php') ?>">
            <i class="fas fa-envelope"></i> İletişim Mesajları
            <?php if ($_yeni_mesaj > 0): ?><span class="badge"><?= $_yeni_mesaj ?></span><?php endif; ?>
        </a>

        <div class="group">İçerik</div>
        <a href="<?= SITE_URL ?>/admin/hizmet-kategorileri.php" class="<?= nav_active('hizmet-kategorileri.php') ?>">
            <i class="fas fa-folder-tree"></i> Hizmet Kategorileri
        </a>
        <a href="<?= SITE_URL ?>/admin/hizmetler.php" class="<?= nav_active('hizmetler.php') ?>">
            <i class="fas fa-tools"></i> Hizmetler
        </a>
        <a href="<?= SITE_URL ?>/admin/markalar.php" class="<?= nav_active('markalar.php') ?>">
            <i class="fas fa-trademark"></i> Markalar
        </a>
        <a href="<?= SITE_URL ?>/admin/urun-kategorileri.php" class="<?= nav_active('urun-kategorileri.php') ?>">
            <i class="fas fa-tags"></i> Ürün Kategorileri
        </a>
        <a href="<?= SITE_URL ?>/admin/urunler.php" class="<?= nav_active('urunler.php') ?>">
            <i class="fas fa-fire-flame-curved"></i> Ürünler
        </a>
        <a href="<?= SITE_URL ?>/admin/kampanyalar.php" class="<?= nav_active('kampanyalar.php') ?>">
            <i class="fas fa-bullhorn"></i> Kampanyalar
        </a>
        <a href="<?= SITE_URL ?>/admin/blog.php" class="<?= nav_active('blog.php') ?>">
            <i class="fas fa-newspaper"></i> Blog
        </a>
        <a href="<?= SITE_URL ?>/admin/projeler.php" class="<?= nav_active('projeler.php') ?>">
            <i class="fas fa-building"></i> Projeler / Referanslar
        </a>
        <a href="<?= SITE_URL ?>/admin/sayfalar.php" class="<?= nav_active('sayfalar.php') ?>">
            <i class="fas fa-file-lines"></i> KVKK / Gizlilik
        </a>

        <?php if (($_kul['rol'] ?? '') === 'admin'): ?>
        <div class="group">Sistem</div>
        <a href="<?= SITE_URL ?>/admin/ayarlar.php" class="<?= nav_active('ayarlar.php') ?>">
            <i class="fas fa-gear"></i> Ayarlar
        </a>
        <a href="<?= SITE_URL ?>/admin/gorsel-onarim.php" class="<?= nav_active('gorsel-onarim.php') ?>">
            <i class="fas fa-image"></i> Görsel Onarım
        </a>
        <a href="<?= SITE_URL ?>/admin/kullanicilar.php" class="<?= nav_active('kullanicilar.php') ?>">
            <i class="fas fa-users-gear"></i> Kullanıcılar
        </a>
        <a href="<?= SITE_URL ?>/admin/loglar.php" class="<?= nav_active('loglar.php') ?>">
            <i class="fas fa-clipboard-list"></i> Sistem Logları
        </a>
        <a href="<?= SITE_URL ?>/admin/sistem-tani.php" class="<?= nav_active('sistem-tani.php') ?>">
            <i class="fas fa-stethoscope"></i> Sistem Tanı
        </a>
        <a href="<?= SITE_URL ?>/admin/guncelleme.php" class="<?= nav_active('guncelleme.php') ?>">
            <i class="fas fa-cloud-arrow-down"></i> Güncelleme
        </a>
        <a href="<?= SITE_URL ?>/admin/cikis.php" style="color:#fca5a5">
            <i class="fas fa-right-from-bracket"></i> Çıkış Yap
        </a>
        <?php endif; ?>
    </nav>

    <div class="adm-side-foot">
        <span>v<?= e(json_decode(file_get_contents(__DIR__.'/../manifest.json'),true)['version'] ?? '1.0') ?></span>
        <a href="<?= SITE_URL ?>/" target="_blank">Siteyi Gör <i class="fas fa-external-link-alt"></i></a>
    </div>
</aside>

<main class="adm-main">
    <header class="adm-topbar">
        <button class="menu-btn" id="admMenuBtn" aria-label="Menü"><i class="fas fa-bars"></i></button>
        <h1 class="page-title"><?= e($page_title) ?></h1>

        <!-- Hızlı Erişim: Muhasebe -->
        <div class="topbar-quick" id="topQuick" style="position:relative;margin-left:auto;margin-right:8px">
            <button type="button" id="topQuickBtn" class="topbar-quick-btn" style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(255,140,0,.12);color:var(--c-orange);border:1px solid rgba(255,140,0,.3);border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;transition:.15s">
                <i class="fas fa-briefcase"></i>
                <span class="lbl">Muhasebe</span>
                <i class="fas fa-chevron-down" style="font-size:.7rem;opacity:.7"></i>
            </button>
            <div id="topQuickMenu" style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:#0c1430;border:1px solid var(--c-line);border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,.4);z-index:1000;overflow:hidden">
                <a href="<?= SITE_URL ?>/admin/cariler.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:var(--c-text);text-decoration:none;font-size:.88rem;border-bottom:1px solid var(--c-line);transition:.12s" onmouseover="this.style.background='rgba(255,140,0,.08)';this.style.color='var(--c-orange)'" onmouseout="this.style.background='';this.style.color='var(--c-text)'">
                    <i class="fas fa-users" style="width:16px;color:var(--c-orange)"></i> Cariler
                </a>
                <a href="<?= SITE_URL ?>/admin/cari-ekstre.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:var(--c-text);text-decoration:none;font-size:.88rem;border-bottom:1px solid var(--c-line);transition:.12s" onmouseover="this.style.background='rgba(255,140,0,.08)';this.style.color='var(--c-orange)'" onmouseout="this.style.background='';this.style.color='var(--c-text)'">
                    <i class="fas fa-file-lines" style="width:16px;color:var(--c-orange)"></i> Cari Ekstre
                </a>
                <a href="<?= SITE_URL ?>/admin/teklifler.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:var(--c-text);text-decoration:none;font-size:.88rem;border-bottom:1px solid var(--c-line);transition:.12s" onmouseover="this.style.background='rgba(255,140,0,.08)';this.style.color='var(--c-orange)'" onmouseout="this.style.background='';this.style.color='var(--c-text)'">
                    <i class="fas fa-file-signature" style="width:16px;color:var(--c-orange)"></i> Teklifler
                </a>
                <a href="<?= SITE_URL ?>/admin/faturalar.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:var(--c-text);text-decoration:none;font-size:.88rem;border-bottom:1px solid var(--c-line);transition:.12s" onmouseover="this.style.background='rgba(255,140,0,.08)';this.style.color='var(--c-orange)'" onmouseout="this.style.background='';this.style.color='var(--c-text)'">
                    <i class="fas fa-file-invoice-dollar" style="width:16px;color:var(--c-orange)"></i> Faturalar
                </a>
                <a href="<?= SITE_URL ?>/admin/fisler.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:var(--c-text);text-decoration:none;font-size:.88rem;transition:.12s" onmouseover="this.style.background='rgba(255,140,0,.08)';this.style.color='var(--c-orange)'" onmouseout="this.style.background='';this.style.color='var(--c-text)'">
                    <i class="fas fa-receipt" style="width:16px;color:var(--c-orange)"></i> Fişler
                </a>
            </div>
        </div>

        <!-- Hızlı Erişim: Bakım Hatırlatıcıları (ayrı buton) -->
        <a href="<?= SITE_URL ?>/admin/bakim-hatirlaticilari.php" id="topBakimBtn" style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:rgba(34,197,94,.12);color:#22c55e;border:1px solid rgba(34,197,94,.3);border-radius:8px;font-weight:600;font-size:.88rem;text-decoration:none;transition:.15s;margin-right:14px;position:relative">
            <i class="fas fa-bell"></i>
            <span class="lbl">Bakım</span>
            <?php if ($_bakim_uyari > 0): ?>
                <span style="background:#dc2626;color:#fff;font-size:.7rem;padding:2px 7px;border-radius:10px;font-weight:700"><?= $_bakim_uyari ?></span>
            <?php endif; ?>
        </a>

        <!-- Kullanıcı Menüsü (sağ üst dropdown) -->
        <div class="adm-user" id="userQuick" style="position:relative;cursor:pointer;user-select:none">
            <span class="av"><?= mb_strtoupper(mb_substr($_kul['ad'] ?? 'A', 0, 1, 'UTF-8'), 'UTF-8') ?></span>
            <span><?= e(mb_strimwidth($_kul['ad'] ?? 'Yönetici', 0, 26, '…', 'UTF-8')) ?></span>
            <i class="fas fa-chevron-down" style="font-size:.7rem;opacity:.5;margin-left:4px"></i>
            <div id="userQuickMenu" style="display:none;position:absolute;top:calc(100% + 10px);right:0;min-width:200px;background:#0c1430;border:1px solid var(--c-line);border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,.4);z-index:1000;overflow:hidden">
                <div style="padding:12px 16px;border-bottom:1px solid var(--c-line);background:rgba(255,140,0,.05)">
                    <div style="font-weight:700;font-size:.92rem;color:var(--c-text)"><?= e($_kul['ad'] ?? 'Yönetici') ?></div>
                    <div style="font-size:.78rem;color:var(--c-muted);margin-top:2px"><?= e($_kul['eposta'] ?? '') ?></div>
                    <div style="font-size:.7rem;color:var(--c-orange);margin-top:4px;text-transform:uppercase;letter-spacing:.5px;font-weight:600"><?= e($_kul['rol'] ?? 'üye') ?></div>
                </div>
                <a href="<?= SITE_URL ?>/admin/profil.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:var(--c-text);text-decoration:none;font-size:.88rem;border-bottom:1px solid var(--c-line);transition:.12s" onmouseover="this.style.background='rgba(255,140,0,.08)';this.style.color='var(--c-orange)'" onmouseout="this.style.background='';this.style.color='var(--c-text)'">
                    <i class="fas fa-user-shield" style="width:16px;color:var(--c-orange)"></i> Profil & Şifre
                </a>
                <a href="<?= SITE_URL ?>/admin/cikis.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;color:#fca5a5;text-decoration:none;font-size:.88rem;transition:.12s" onmouseover="this.style.background='rgba(220,38,38,.12)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='#fca5a5'">
                    <i class="fas fa-right-from-bracket" style="width:16px"></i> Çıkış Yap
                </a>
            </div>
        </div>
    </header>

    <script>
    (function(){
        // ===== Muhasebe dropdown =====
        const btn  = document.getElementById('topQuickBtn');
        const menu = document.getElementById('topQuickMenu');
        if (btn && menu) {
            btn.addEventListener('click', function(e){
                e.stopPropagation();
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                const um = document.getElementById('userQuickMenu');
                if (um) um.style.display = 'none';
            });
            const sayfa = location.pathname.split('/').pop();
            if (['cariler.php','cari-ekstre.php','teklifler.php','faturalar.php','fisler.php'].includes(sayfa)) {
                btn.style.background = 'rgba(255,140,0,.22)';
                btn.style.borderColor = 'var(--c-orange)';
            }
            function mobilKontrol(){
                const lbl = btn.querySelector('.lbl');
                if (lbl) lbl.style.display = window.innerWidth < 640 ? 'none' : 'inline';
                const blbl = document.querySelector('#topBakimBtn .lbl');
                if (blbl) blbl.style.display = window.innerWidth < 640 ? 'none' : 'inline';
            }
            mobilKontrol();
            window.addEventListener('resize', mobilKontrol);
        }

        // ===== Bakım butonu aktif vurgusu =====
        const bb = document.getElementById('topBakimBtn');
        if (bb) {
            const sayfa = location.pathname.split('/').pop();
            if (sayfa === 'bakim-hatirlaticilari.php') {
                bb.style.background = 'rgba(34,197,94,.22)';
                bb.style.borderColor = '#22c55e';
            }
            bb.addEventListener('mouseenter', function(){
                bb.style.background = 'rgba(34,197,94,.22)';
            });
            bb.addEventListener('mouseleave', function(){
                if (location.pathname.split('/').pop() !== 'bakim-hatirlaticilari.php') {
                    bb.style.background = 'rgba(34,197,94,.12)';
                }
            });
        }

        // ===== Kullanıcı dropdown =====
        const uq  = document.getElementById('userQuick');
        const uqm = document.getElementById('userQuickMenu');
        if (uq && uqm) {
            uq.addEventListener('click', function(e){
                if (e.target.closest('a')) return;
                e.stopPropagation();
                uqm.style.display = uqm.style.display === 'none' ? 'block' : 'none';
                if (menu) menu.style.display = 'none';
            });
        }

        document.addEventListener('click', function(e){
            if (menu && !document.getElementById('topQuick').contains(e.target)) menu.style.display = 'none';
            if (uqm  && !document.getElementById('userQuick').contains(e.target)) uqm.style.display = 'none';
        });
    })();
    </script>

    <section class="adm-content">
        <?php foreach (flash_pop() as $f): ?>
            <div class="alert alert-<?= e($f['tip']) ?>"><?= $f['msg'] ?></div>
        <?php endforeach; ?>
