<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Ücretsiz Keşif İste — Azra Doğalgaz İzmir';
$sayfa_aciklama = 'Adresinize ücretsiz keşif ekibi gönderiyoruz. Doğalgaz, kombi, klima, yerden ısıtma, sıhhi tesisat — yerinde analiz, en uygun çözüm.';
$kanonik_url    = SITE_URL . '/kesif';

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/">Ana Sayfa</a> <i class="fas fa-chevron-right" style="font-size:.7rem"></i> <span>Ücretsiz Keşif</span></div>
        <h1>Ücretsiz Keşif İstek Formu</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Aşağıdaki formu doldurun, en kısa sürede sizi arayarak randevu oluşturalım. Keşif ücretsizdir, hiçbir yükümlülük yaratmaz.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="kesif-grid" style="display:grid;grid-template-columns:1.4fr 1fr;gap:40px;max-width:1100px;margin:0 auto">

            <div class="calc-widget" style="margin:0">
                <h3 style="font-family:var(--font-display);font-weight:800;font-size:1.4rem;margin-bottom:8px">Keşif Bilgileri</h3>
                <p style="color:var(--c-muted);margin-bottom:24px;font-size:.92rem">Tüm alanları doldurun. Telefonunuz aktif olsun.</p>

                <?php if (!empty($_GET['ok'])): ?>
                    <div class="alert alert-ok"><i class="fas fa-circle-check"></i> Keşif talebiniz alındı. En kısa sürede sizi arayacağız.</div>
                <?php endif; ?>

                <form method="post" action="<?= SITE_URL ?>/api/iletisim-gonder.php?donus=<?= urlencode(SITE_URL.'/kesif?ok=1') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="konu" value="Ücretsiz Keşif Talebi">
                    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

                    <div class="form-row cols-2">
                        <div class="field"><label>Ad Soyad <span class="req">*</span></label><input type="text" name="ad_soyad" class="input" required maxlength="120"></div>
                        <div class="field"><label>Telefon <span class="req">*</span></label><input type="tel" name="telefon" class="input" required placeholder="0 5xx xxx xx xx" maxlength="40"></div>
                    </div>

                    <div class="form-row cols-2">
                        <div class="field"><label>E-posta <span style="color:var(--c-muted);font-weight:normal">(opsiyonel)</span></label><input type="email" name="eposta" class="input" maxlength="160"></div>
                        <div class="field"><label>İlçe / Mahalle <span class="req">*</span></label><input type="text" name="ilce" class="input" required placeholder="Örn: Bornova / Erzene" maxlength="100"></div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Hizmet Türü <span class="req">*</span></label>
                            <select name="hizmet_tip" required>
                                <option value="">Lütfen seçin...</option>
                                <option>Doğalgaz Tesisatı (Yeni)</option>
                                <option>Doğalgaz Tesisatı (Tadilat / Revizyon)</option>
                                <option>Kombi Satış + Montaj</option>
                                <option>Kombi Bakım / Arıza</option>
                                <option>Klima Satış + Montaj</option>
                                <option>Klima Bakım / Servis</option>
                                <option>Yerden Isıtma Tesisatı</option>
                                <option>Sıhhi Tesisat</option>
                                <option>Havalandırma Sistemi</option>
                                <option>Yangın Tesisatı</option>
                                <option>Isı Pompası</option>
                                <option>Diğer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row cols-2">
                        <div class="field">
                            <label>Konut Tipi</label>
                            <select name="konut_tip">
                                <option value="">Seçin...</option>
                                <option>Daire (Apartman)</option>
                                <option>Müstakil Ev</option>
                                <option>Dubleks / Tripleks</option>
                                <option>Villa</option>
                                <option>İş Yeri / Ofis</option>
                                <option>Site / Toplu Konut</option>
                            </select>
                        </div>
                        <div class="field"><label>Yaklaşık Metrekare</label><input type="text" name="m2" class="input" placeholder="Örn: 120 m²"></div>
                    </div>

                    <div class="form-row">
                        <div class="field"><label>Açıklama / İhtiyacınız <span class="req">*</span></label>
                        <textarea name="mesaj" class="textarea" required minlength="10" placeholder="İhtiyacınızı kısaca anlatın. Örnek: 110 m² 2+1 daire, sıfır doğalgaz tesisatı, kombi dahil paket düşünüyoruz."></textarea></div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Tercih ettiğiniz arama saati</label>
                            <select name="aranma_saati">
                                <option value="">Farketmez</option>
                                <option>09:00 - 12:00 arası</option>
                                <option>12:00 - 15:00 arası</option>
                                <option>15:00 - 18:00 arası</option>
                                <option>18:00 - 20:00 arası</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom:8px">
                        <label class="check">
                            <input type="checkbox" name="kvkk" value="1" required>
                            <span><a href="<?= SITE_URL ?>/kvkk" target="_blank">KVKK Aydınlatma Metni</a>\'ni okudum, kişisel verilerimin işlenmesine onay veriyorum. <span class="req">*</span></span>
                        </label>
                    </div>

                    <button class="btn btn-primary btn-block btn-lg" type="submit"><i class="fas fa-paper-plane"></i> Keşif Talebi Gönder</button>
                </form>
            </div>

            <div>
                <div style="background:#fff;border:1px solid var(--c-line);border-radius:var(--r-lg);padding:28px;margin-bottom:18px;box-shadow:var(--sh-sm)">
                    <h3 style="font-family:var(--font-display);font-size:1.15rem;margin-bottom:14px"><i class="fas fa-clipboard-list" style="color:var(--c-primary)"></i> Keşif Süreci</h3>
                    <ol style="padding-left:18px;font-size:.92rem;color:var(--c-text-2);line-height:1.8">
                        <li><strong>Form alımı</strong> — Talebiniz bize ulaşır.</li>
                        <li><strong>Ön görüşme</strong> — Sizi ararız, randevu belirleriz.</li>
                        <li><strong>Yerinde keşif</strong> — Adresinize gelir, ölçü ve fotoğraf alırız.</li>
                        <li><strong>Detaylı teklif</strong> — Yazılı, kalemli teklif veririz.</li>
                        <li><strong>Sözleşme + iş başlangıcı</strong> — Anlaştığımız tarihte başlarız.</li>
                    </ol>
                </div>

                <div style="background:var(--c-primary-l);border:1px solid #fed7aa;border-radius:var(--r-lg);padding:24px;margin-bottom:18px">
                    <h3 style="font-family:var(--font-display);font-size:1.05rem;margin-bottom:10px;color:var(--c-primary-d)"><i class="fas fa-circle-info"></i> Önemli Bilgi</h3>
                    <p style="font-size:.88rem;color:var(--c-text-2);line-height:1.7;margin:0">Keşif ekibimiz <strong>hiçbir ücret almadan</strong> adresinize gelir. Verdiğimiz teklifin altında bir karara <strong>zorlanmazsınız</strong>, yükümlülük yoktur.</p>
                </div>

                <div style="background:#fff;border:1px solid var(--c-line);border-radius:var(--r-lg);padding:28px">
                    <h3 style="font-family:var(--font-display);font-size:1.05rem;margin-bottom:14px"><i class="fas fa-headset" style="color:var(--c-primary)"></i> Hemen iletişim</h3>
                    <a href="tel:<?= e(preg_replace('/\s/','',ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:''))) ?>" class="btn btn-out btn-block" style="margin-bottom:8px"><i class="fas fa-phone"></i> <?= e(ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:'')) ?></a>
                    <a href="https://wa.me/<?= e(ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'')) ?>" target="_blank" class="btn btn-green btn-block"><i class="fab fa-whatsapp"></i> WhatsApp\'tan Yaz</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 900px) { .kesif-grid { grid-template-columns: 1fr !important; } }
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
