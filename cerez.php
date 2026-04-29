<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Çerez Politikası — Azra Doğalgaz';
$sayfa_aciklama = 'Web sitemizde kullanılan çerezler ve çerez yönetim seçenekleri.';
$kanonik_url    = SITE_URL . '/cerez';

$icerik = ayar('cerez_metni', '');

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Çerez Politikası</span>
        </div>
        <h1>Çerez Politikası</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Sitemizde kullanılan çerezler, amaçları ve kontrol etme yöntemleri.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <h2>Çerez Nedir?</h2>
                <p>Çerezler, ziyaret ettiğiniz web siteleri tarafından tarayıcınıza yerleştirilen küçük metin dosyalarıdır. Sitelerin sizi tanımasına ve tercihlerinizi hatırlamasına olanak tanır.</p>

                <h2>Kullanılan Çerez Türleri</h2>
                <ul>
                    <li><strong>Zorunlu Çerezler:</strong> Site işlevselliği için gerekli (oturum, güvenlik)</li>
                    <li><strong>Performans Çerezleri:</strong> Anonim analiz amaçlı (Google Analytics)</li>
                    <li><strong>İşlevsellik Çerezleri:</strong> Tercih ve dil ayarları</li>
                </ul>

                <h2>Çerezleri Yönetme</h2>
                <p>Tarayıcı ayarlarınızdan çerezleri yönetebilir veya tüm çerezleri reddedebilirsiniz. Ancak bu durumda bazı site özellikleri çalışmayabilir.</p>

                <p style="margin-top:30px;color:var(--c-muted);font-size:.88rem"><em>Son güncelleme: <?= date('d.m.Y') ?></em></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
