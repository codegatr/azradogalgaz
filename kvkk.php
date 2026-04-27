<?php
require_once __DIR__ . '/config.php';

set_meta([
    'baslik'    => 'KVKK Aydınlatma Metni | ' . SITE_TITLE,
    'aciklama'  => 'Kişisel verilerin korunması kanunu kapsamında aydınlatma metni.',
    'canonical' => SITE_URL . '/kvkk',
]);

$icerik = ayar('kvkk_metni', '');

require_once INC_PATH . '/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>KVKK</span>
        </nav>
        <h1>KVKK Aydınlatma Metni</h1>
    </div>
</section>
<section class="sec">
    <div class="container">
        <article class="legal-doc">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <h2>Genel</h2>
                <p>Azra Doğalgaz olarak, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") kapsamında veri sorumlusu sıfatıyla, web sitemiz aracılığıyla bizimle paylaşılan kişisel verilerinizin gizliliğini ve güvenliğini önemsiyoruz.</p>

                <h2>İşlenen Veriler</h2>
                <p>Sitemiz üzerinden ilettiğiniz <strong>ad-soyad, telefon, e-posta, mesaj içeriği</strong> gibi bilgiler; teklif sunmak, taleplerinizi karşılamak ve sizinle iletişime geçmek amacıyla işlenmektedir.</p>

                <h2>İşleme Amaçları</h2>
                <ul>
                    <li>Sunduğumuz hizmetler hakkında bilgi vermek</li>
                    <li>Teklif ve fiyatlandırma süreçlerini yürütmek</li>
                    <li>Yasal yükümlülüklerimizi yerine getirmek</li>
                </ul>

                <h2>Haklarınız</h2>
                <p>KVKK'nın 11. maddesi kapsamında verilerinize ilişkin <em>bilgi alma, düzeltme, silme, işlenmesine itiraz etme</em> haklarınız saklıdır. Taleplerinizi <a href="mailto:<?= e(FIRMA_EMAIL) ?>"><?= e(FIRMA_EMAIL) ?></a> adresine iletebilirsiniz.</p>

                <p style="margin-top:32px;color:var(--c-muted);font-size:.85rem">Bu metin, yönetim panelinden güncellendiğinde otomatik olarak buraya yansıyacaktır.</p>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php require_once INC_PATH . '/footer.php'; ?>
