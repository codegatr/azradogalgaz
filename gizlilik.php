<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Gizlilik Politikası — Azra Doğalgaz';
$sayfa_aciklama = 'Web sitemizin gizlilik politikası, çerez kullanımı ve kişisel veri uygulamaları.';
$kanonik_url    = SITE_URL . '/gizlilik';

$icerik = ayar('gizlilik_metni', '');

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Gizlilik Politikası</span>
        </div>
        <h1>Gizlilik Politikası</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Web sitemizi ziyaret eden kullanıcılarımızın bilgi gizliliği konusundaki yaklaşımımız.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <h2>Çerez Kullanımı</h2>
                <p>Web sitemizde, kullanıcı deneyimini iyileştirmek, site performansını analiz etmek ve hizmetlerimizi geliştirmek amacıyla çerezler (cookies) kullanılmaktadır.</p>
                <ul>
                    <li><strong>Zorunlu çerezler:</strong> Site işlevselliği için gerekli (oturum, güvenlik)</li>
                    <li><strong>Performans çerezleri:</strong> Analiz amaçlı (Google Analytics)</li>
                    <li><strong>Tercih çerezleri:</strong> Kullanıcı deneyimi (dil, tema seçimleri)</li>
                </ul>
                <p>Tarayıcınızdan çerez ayarlarını yönetebilir veya tüm çerezleri reddedebilirsiniz. Ancak bu durumda bazı site özellikleri düzgün çalışmayabilir.</p>

                <h2>Toplanan Bilgiler</h2>
                <p>İletişim formları ve hizmet talepleri aracılığıyla aşağıdaki bilgileri talep ediyoruz:</p>
                <ul>
                    <li>Ad, soyad</li>
                    <li>Telefon numarası</li>
                    <li>E-posta adresi</li>
                    <li>Adres bilgisi (keşif için)</li>
                    <li>Talep konusu ve mesaj içeriği</li>
                </ul>

                <h2>Bilgilerin Kullanımı</h2>
                <p>Toplanan bilgiler, sadece sizinle iletişim kurmak, hizmet teklifi sunmak ve talep ettiğiniz hizmeti gerçekleştirmek için kullanılır. <strong>Üçüncü taraflarla paylaşılmaz, satılmaz veya kiralanmaz</strong>. Yasal zorunluluklar (mahkeme kararı, resmi kurum talebi) saklı tutulur.</p>

                <h2>Bilgi Güvenliği</h2>
                <p>Sitemize iletilen bilgiler güvenli altyapı üzerinde saklanır. SSL sertifikası ile şifreli iletişim sağlanır. Veritabanına şifre koruması ve sınırlı erişim politikası uygulanır.</p>

                <h2>Üçüncü Taraf Hizmetler</h2>
                <p>Web sitemizde aşağıdaki üçüncü taraf servisler kullanılabilir:</p>
                <ul>
                    <li>Google Analytics (kullanıcı analizi)</li>
                    <li>Google Maps (harita gösterimi)</li>
                    <li>Sosyal medya entegrasyonları (Facebook, Instagram, YouTube)</li>
                </ul>
                <p>Bu servislerin kendi gizlilik politikaları uygulanır.</p>

                <h2>Bilgilerinizi Düzenleme / Silme</h2>
                <p>Toplanan kişisel bilgilerinizin düzenlenmesi veya silinmesini talep edebilirsiniz. Lütfen <a href="mailto:<?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?>"><?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?></a> adresine yazılı talebinizi iletin.</p>

                <h2>Politika Değişiklikleri</h2>
                <p>İşbu Gizlilik Politikası, mevzuattaki değişiklikler veya hizmet kapsamımızdaki gelişmelere bağlı olarak güncellenebilir. Önemli değişiklikler bu sayfada duyurulur.</p>

                <p style="margin-top:30px;color:var(--c-muted);font-size:.88rem"><em>Son güncelleme tarihi: <?= date('d.m.Y') ?></em></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
