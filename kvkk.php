<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'KVKK Aydınlatma Metni — Azra Doğalgaz';
$sayfa_aciklama = 'Kişisel verilerin korunması kanunu kapsamında aydınlatma metni.';
$kanonik_url    = SITE_URL . '/kvkk';

$icerik = ayar('kvkk_metni', '');

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>KVKK</span>
        </div>
        <h1>KVKK Aydınlatma Metni</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">6698 Sayılı Kişisel Verilerin Korunması Kanunu kapsamında aydınlatma metnimiz.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <h2>Veri Sorumlusu</h2>
                <p>İşbu Aydınlatma Metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, veri sorumlusu sıfatıyla <strong>Azra Doğalgaz</strong> tarafından hazırlanmıştır.</p>

                <h2>İşlenen Kişisel Veriler</h2>
                <ul>
                    <li><strong>Kimlik bilgileri:</strong> Ad, soyad</li>
                    <li><strong>İletişim bilgileri:</strong> Telefon, e-posta, adres</li>
                    <li><strong>Talep bilgileri:</strong> Hizmet türü, tesisat detayları, mesaj içeriği</li>
                    <li><strong>İşlem güvenliği:</strong> IP adresi, çerez bilgileri</li>
                </ul>

                <h2>Kişisel Verilerin İşlenme Amaçları</h2>
                <p>Toplanan kişisel verileriniz aşağıdaki amaçlarla işlenmektedir:</p>
                <ul>
                    <li>Hizmet talebinizin yanıtlanması ve teklif sunulması</li>
                    <li>Doğalgaz tesisat projelerinin yerel dağıtım şirketi sistemine yüklenmesi</li>
                    <li>Sözleşme süreçlerinin yürütülmesi</li>
                    <li>Müşteri hizmetlerinin gerçekleştirilmesi</li>
                    <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                    <li>Hizmet kalitesinin geliştirilmesi</li>
                </ul>

                <h2>Kişisel Verilerin Aktarılması</h2>
                <p>Kişisel verileriniz; yasal düzenlemelerin öngördüğü ölçüde yerel doğalgaz dağıtım şirketi, ilgili kamu kurum ve kuruluşları, yetkili tedarikçilerimiz, bayilik ilişkimiz olan üretici firmalar (Demirdöküm vb.) ve hizmet aldığımız üçüncü taraflar (muhasebe, hukuk vb.) ile paylaşılabilir.</p>

                <h2>Kişisel Veri Sahibinin Hakları</h2>
                <p>KVKK'nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
                <ul>
                    <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
                    <li>İşlenmişse buna ilişkin bilgi talep etme</li>
                    <li>İşlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
                    <li>Yurt içinde veya yurt dışında aktarıldığı üçüncü kişileri bilme</li>
                    <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme</li>
                    <li>Silinmesini veya yok edilmesini isteme</li>
                    <li>Otomatik sistemlerle yapılan analiz sonucunda aleyhinize sonuç doğurmasına itiraz etme</li>
                    <li>Kanuna aykırı işlenmesi sebebiyle zarara uğramışsanız zararın giderilmesini talep etme</li>
                </ul>

                <h2>İletişim</h2>
                <p>Yukarıda belirtilen haklarınızı kullanmak için <a href="mailto:<?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?>"><?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?></a> adresine yazılı talebinizi iletebilirsiniz.</p>

                <p style="margin-top:30px;color:var(--c-muted);font-size:.88rem"><em>Son güncelleme tarihi: <?= date('d.m.Y') ?></em></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
