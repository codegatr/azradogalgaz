<?php
require_once __DIR__ . '/config.php';

$basari = !empty($_GET['ok']);
$hata   = $_GET['hata'] ?? '';

$sayfa_baslik   = 'İletişim — Azra Doğalgaz İzmir';
$sayfa_aciklama = 'Azra Doğalgaz iletişim bilgileri. Telefon, WhatsApp ve form ile bize ulaşın. Ücretsiz keşif ve fiyat teklifi.';
$kanonik_url    = SITE_URL . '/iletisim';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'ContactPage',
        'name'     => 'İletişim — Azra Doğalgaz',
        'url'      => SITE_URL . '/iletisim',
    ],
];

$harita = ayar('harita_iframe', '');
$adres  = ayar('firma_adres', 'Bornova / İzmir');
$tel1   = ayar('firma_telefon_1', defined('FIRMA_TEL_1') ? FIRMA_TEL_1 : '');
$tel2   = ayar('firma_telefon_2', defined('FIRMA_TEL_2') ? FIRMA_TEL_2 : '');
$wa     = ayar('whatsapp_numara', defined('FIRMA_WHATSAPP') ? FIRMA_WHATSAPP : '');
$mail   = ayar('firma_eposta',    defined('FIRMA_EMAIL')   ? FIRMA_EMAIL   : '');
$saat   = ayar('firma_calisma_saatleri', 'Pzt-Cmt 08:00-20:00');

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>İletişim</span>
        </div>
        <h1>Bize Ulaşın</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Sorularınız, talepleriniz ve ücretsiz keşif için aşağıdaki kanallardan istediğiniz zaman ulaşabilirsiniz.</p>
    </div>
</section>

<section class="s">
    <div class="container">

        <?php if ($basari): ?>
            <div class="alert alert-ok" style="max-width:880px;margin:0 auto 24px">
                <i class="fas fa-circle-check" style="font-size:1.1rem"></i>
                <div>
                    <strong>Mesajınız alındı!</strong> En kısa sürede sizinle iletişime geçeceğiz. Acil durumlar için telefon veya WhatsApp tercih edebilirsiniz.
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hata): ?>
            <div class="alert alert-err" style="max-width:880px;margin:0 auto 24px">
                <i class="fas fa-circle-exclamation"></i>
                <div><?= e($hata) ?></div>
            </div>
        <?php endif; ?>

        <!-- 4'lü iletişim kartı -->
        <div class="services" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));margin-bottom:50px">
            <a href="tel:<?= e(preg_replace('/\s/','',$tel1)) ?>" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image" style="background:var(--c-green-l);height:120px"><i class="fas fa-phone" style="background:linear-gradient(135deg,#16a34a,#15803d);-webkit-background-clip:text;background-clip:text;color:transparent"></i></div>
                <div class="service-body"><h3 style="font-size:1rem">Telefon</h3><p style="font-size:.92rem"><?= e($tel1) ?><br><?= e($tel2) ?></p></div>
            </a>
            <a href="https://wa.me/<?= e($wa) ?>" target="_blank" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image" style="background:#dcfce7;height:120px"><i class="fab fa-whatsapp" style="color:#25d366"></i></div>
                <div class="service-body"><h3 style="font-size:1rem">WhatsApp</h3><p style="font-size:.92rem">Hızlı yanıt için<br>WhatsApp'tan yazın</p></div>
            </a>
            <a href="mailto:<?= e($mail) ?>" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image" style="background:var(--c-blue-l);height:120px"><i class="fas fa-envelope" style="background:var(--grad-blue);-webkit-background-clip:text;background-clip:text;color:transparent"></i></div>
                <div class="service-body"><h3 style="font-size:1rem">E-posta</h3><p style="font-size:.92rem;word-break:break-all"><?= e($mail) ?></p></div>
            </a>
            <div class="service-card">
                <div class="service-image" style="background:var(--c-primary-l);height:120px"><i class="fas fa-clock"></i></div>
                <div class="service-body"><h3 style="font-size:1rem">Çalışma Saatleri</h3><p style="font-size:.92rem"><?= e($saat) ?></p></div>
            </div>
        </div>

        <!-- Form + harita 2 kolon -->
        <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:36px;align-items:start" class="iletisim-grid">

            <div class="calc-widget">
                <h3 style="font-family:var(--font-display);font-size:1.3rem;font-weight:800;margin-bottom:8px">İletişim Formu</h3>
                <p style="color:var(--c-muted);margin-bottom:24px;font-size:.92rem">Mesajınızı bırakın, en kısa sürede dönüş yapalım.</p>

                <form method="post" action="<?= SITE_URL ?>/api/iletisim-gonder.php?donus=<?= urlencode('/iletisim') ?>">
                    <?= csrf_field() ?>
                    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-10000px">

                    <div class="form-row cols-2">
                        <div class="field">
                            <label>Ad Soyad <span class="req">*</span></label>
                            <input type="text" name="ad_soyad" class="input" required maxlength="100">
                        </div>
                        <div class="field">
                            <label>Telefon <span class="req">*</span></label>
                            <input type="tel" name="telefon" class="input" required maxlength="20" placeholder="0532 123 45 67">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>E-posta</label>
                            <input type="email" name="eposta" class="input" maxlength="120">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Konu</label>
                            <select name="konu">
                                <option value="Genel Bilgi">Genel Bilgi</option>
                                <option value="Doğalgaz Tesisatı">Doğalgaz Tesisatı</option>
                                <option value="Kombi Montajı / Servisi">Kombi Montajı / Servisi</option>
                                <option value="Klima Montajı">Klima Montajı</option>
                                <option value="Yerden Isıtma">Yerden Isıtma</option>
                                <option value="Tesisat Hizmeti">Tesisat Hizmeti</option>
                                <option value="Şikayet / Geri Bildirim">Şikayet / Geri Bildirim</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Mesajınız <span class="req">*</span></label>
                            <textarea name="mesaj" class="textarea" rows="5" required minlength="10" maxlength="2000" placeholder="Talebinizi detaylı şekilde yazabilirsiniz..."></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="check">
                            <input type="checkbox" name="kvkk_onay" required>
                            <span><a href="<?= SITE_URL ?>/kvkk" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum ve <a href="<?= SITE_URL ?>/gizlilik" target="_blank">Gizlilik Politikası</a>'nı kabul ediyorum.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-paper-plane"></i> Mesajı Gönder</button>
                </form>
            </div>

            <div>
                <div class="card" style="margin-bottom:20px;background:#fff;padding:0;overflow:hidden;border-radius:var(--r-lg)">
                    <?php if ($harita): ?>
                        <div style="aspect-ratio:4/3"><?= $harita ?></div>
                    <?php else: ?>
                        <div style="aspect-ratio:4/3;background:var(--c-bg-alt);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:14px;text-align:center;padding:30px">
                            <i class="fas fa-map-location-dot" style="font-size:3rem;color:var(--c-primary);opacity:.6"></i>
                            <div>
                                <strong style="display:block;color:var(--c-text);margin-bottom:4px">Adresimiz</strong>
                                <span style="color:var(--c-muted);font-size:.92rem"><?= e($adres) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card" style="background:var(--c-primary-l);border-color:#fed7aa">
                    <h4 style="font-family:var(--font-display);font-size:1.1rem;margin-bottom:12px"><i class="fas fa-circle-info" style="color:var(--c-primary);margin-right:8px"></i>Acil Durumda</h4>
                    <p style="color:var(--c-text-2);font-size:.92rem;line-height:1.6;margin-bottom:14px">Gaz kaçağı şüphesi gibi acil durumlarda <strong>önce 187 İzmirgaz Acil Müdahale</strong> hattını arayın, ardından bizi bilgilendirin.</p>
                    <a href="tel:187" class="btn btn-out btn-sm" style="background:#fff"><i class="fas fa-phone"></i> 187 İzmirgaz Acil</a>
                </div>
            </div>

        </div>

    </div>
</section>

<style>
@media (max-width: 880px) {
    .iletisim-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
