<?php
require_once __DIR__ . '/config.php';

$basari = !empty($_GET['ok']);
$hata   = $_GET['hata'] ?? '';

set_meta([
    'baslik'    => 'İletişim — Azra Doğalgaz İzmir | ' . SITE_TITLE,
    'aciklama'  => 'Azra Doğalgaz İzmir iletişim bilgileri. Telefon: ' . FIRMA_TEL_1 . ' / ' . FIRMA_TEL_2 . '. Ücretsiz keşif ve fiyat teklifi için bize ulaşın.',
    'canonical' => SITE_URL . '/iletisim',
]);

$ekstra = schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'BreadcrumbList',
    'itemListElement'=>[
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'İletişim','item'=>SITE_URL.'/iletisim'],
    ],
]) . schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'ContactPage',
    'name'=>'İletişim — '.SITE_TITLE,
    'url'=>SITE_URL.'/iletisim',
]);
set_meta(['extra_schema' => $ekstra]);

$harita = ayar('harita_iframe', '');

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>İletişim</span>
        </nav>
        <h1>İletişim</h1>
        <p>Bize ulaşmanın en hızlı yolu telefon ya da WhatsApp. Aşağıdaki formdan da yazabilirsiniz.</p>
    </div>
</section>

<section class="sec">
    <div class="container">
        <div class="contact-cards">
            <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="contact-card">
                <div class="cc-icon o"><i class="fas fa-phone-volume"></i></div>
                <div>
                    <small>Telefon</small>
                    <strong><?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?></strong>
                </div>
            </a>
            <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_2',FIRMA_TEL_2)) ?>" class="contact-card">
                <div class="cc-icon b"><i class="fas fa-headset"></i></div>
                <div>
                    <small>2. Hat</small>
                    <strong><?= e(ayar('firma_telefon_2', FIRMA_TEL_2)) ?></strong>
                </div>
            </a>
            <?php $wp = ayar('whatsapp_numara'); if ($wp): ?>
                <a href="https://wa.me/<?= e($wp) ?>" target="_blank" rel="noopener" class="contact-card">
                    <div class="cc-icon g"><i class="fab fa-whatsapp"></i></div>
                    <div>
                        <small>WhatsApp</small>
                        <strong>Hızlı Yanıt</strong>
                    </div>
                </a>
            <?php endif; ?>
            <a href="mailto:<?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?>" class="contact-card">
                <div class="cc-icon y"><i class="fas fa-envelope"></i></div>
                <div>
                    <small>E-posta</small>
                    <strong><?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?></strong>
                </div>
            </a>
        </div>

        <div class="contact-grid">
            <div class="contact-form-box">
                <h2 style="margin-bottom:14px">Bize Yazın</h2>
                <p style="color:var(--c-muted);margin-bottom:24px">Mesajınızı bırakın, en kısa sürede size geri dönelim.</p>

                <?php if ($basari): ?>
                    <div class="alert ok"><i class="fas fa-check-circle"></i> Mesajınız bize ulaştı. Teşekkürler! En kısa sürede dönüş yapacağız.</div>
                <?php elseif ($hata): ?>
                    <div class="alert err"><i class="fas fa-circle-xmark"></i> <?= e($hata) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= SITE_URL ?>/api/iletisim-gonder.php" class="form" novalidate>
                    <?= csrf_field() ?>
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-5000px" aria-hidden="true">

                    <div class="grid-2">
                        <div class="field">
                            <label>Ad Soyad *</label>
                            <input type="text" name="ad_soyad" required maxlength="120">
                        </div>
                        <div class="field">
                            <label>Telefon *</label>
                            <input type="tel" name="telefon" required maxlength="30" placeholder="05_______">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="field">
                            <label>E-posta</label>
                            <input type="email" name="eposta" maxlength="160">
                        </div>
                        <div class="field">
                            <label>Konu</label>
                            <select name="konu">
                                <option>Kombi Paket Bilgi</option>
                                <option>Doğalgaz Tesisat Keşif</option>
                                <option>Klima Montaj</option>
                                <option>Servis / Bakım</option>
                                <option>Diğer</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label>Mesajınız *</label>
                        <textarea name="mesaj" rows="5" required maxlength="2000"></textarea>
                    </div>
                    <div class="field check-field">
                        <input type="checkbox" id="kvkk" name="kvkk" required>
                        <label for="kvkk">
                            <a href="<?= SITE_URL ?>/kvkk" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum ve verilerimin işlenmesine onay veriyorum.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:1.05rem">
                        <i class="fas fa-paper-plane"></i> Mesajı Gönder
                    </button>
                </form>
            </div>

            <div class="contact-info-box">
                <h3>İletişim Bilgileri</h3>
                <ul class="info-list">
                    <li><i class="fas fa-map-marker-alt"></i> <span><?= e(ayar('firma_adres', FIRMA_ADRES)) ?></span></li>
                    <li><i class="fas fa-clock"></i> <span><?= e(ayar('firma_calisma_saatleri','Pzt-Cmt 08:00-20:00')) ?></span></li>
                    <li><i class="fas fa-phone-volume"></i> <span><?= e(ayar('firma_telefon_1',FIRMA_TEL_1)) ?></span></li>
                    <li><i class="fas fa-phone-volume"></i> <span><?= e(ayar('firma_telefon_2',FIRMA_TEL_2)) ?></span></li>
                    <li><i class="fas fa-envelope"></i> <span><?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?></span></li>
                </ul>

                <?php if ($harita): ?>
                    <div class="map-box">
                        <?= $harita ?>
                    </div>
                <?php else: ?>
                    <div class="map-box map-fallback">
                        <iframe
                            src="https://www.google.com/maps?q=İzmir&output=embed"
                            loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                            style="border:0;width:100%;height:100%"></iframe>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
