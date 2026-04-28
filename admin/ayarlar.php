<?php
require_once __DIR__ . '/_baslat.php';
page_title('Site Ayarları');

if ($_kul['rol'] !== 'admin') {
    flash_set('err', 'Bu sayfaya erişiminiz yok.');
    redirect(SITE_URL . '/admin/panel.php');
}

// Kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        flash_set('err', 'Oturum süresi doldu, sayfayı yenileyin.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $izinli = [
        'site_baslik','site_aciklama','site_anahtar_kelime',
        'firma_unvan','firma_telefon_1','firma_telefon_2','firma_eposta','firma_adres','firma_calisma_saatleri',
        'sosyal_facebook','sosyal_instagram','sosyal_youtube','sosyal_x','whatsapp_numara',
        'google_analytics','google_search_console_meta','harita_iframe',
        'aktif_kampanya_id',
        'github_repo','github_token',
        'smtp_host','smtp_port','smtp_user','smtp_sifre','smtp_secure','smtp_gonderen_eposta','smtp_gonderen_ad',
        'bakim_bildirim_aktif','bakim_bildirim_gun',
    ];

    // SMTP alanları doğrulaması (yanlış girişleri yakalar)
    $smtp_host = trim((string)($_POST['smtp_host'] ?? ''));
    $smtp_port = trim((string)($_POST['smtp_port'] ?? ''));
    $smtp_user = trim((string)($_POST['smtp_user'] ?? ''));
    $smtp_gond = trim((string)($_POST['smtp_gonderen_eposta'] ?? ''));
    $smtp_hata = '';

    if ($smtp_host !== '') {
        if (strpos($smtp_host, '@') !== false) {
            $smtp_hata = 'SMTP Sunucu alanına e-posta adresi yazılmış. Burası hostname olmalı (örn. mail.azradogalgaz.com), e-posta DEĞİL.';
        } elseif (preg_match('~^https?://~i', $smtp_host)) {
            $smtp_hata = 'SMTP Sunucu alanına URL (http://...) yazılmış. Sadece hostname yaz (örn. mail.azradogalgaz.com).';
        } elseif (strpos($smtp_host, ':') !== false) {
            $smtp_hata = 'SMTP Sunucu alanında iki nokta (:) var. Port ayrı alana yazılmalı.';
        } elseif (strpos($smtp_host, ' ') !== false) {
            $smtp_hata = 'SMTP Sunucu alanında boşluk var, hatalı.';
        }
    }
    if (!$smtp_hata && $smtp_port !== '') {
        $p = (int)$smtp_port;
        if ($p < 1 || $p > 65535) {
            $smtp_hata = 'SMTP Port geçersiz (1-65535 arası olmalı).';
        }
    }
    if (!$smtp_hata && $smtp_user !== '' && !filter_var($smtp_user, FILTER_VALIDATE_EMAIL)) {
        $smtp_hata = 'SMTP Kullanıcı Adı geçerli bir e-posta adresi olmalı (örn. info@azradogalgaz.com).';
    }
    if (!$smtp_hata && $smtp_gond !== '' && !filter_var($smtp_gond, FILTER_VALIDATE_EMAIL)) {
        $smtp_hata = 'Gönderen E-posta geçerli bir adres olmalı.';
    }
    if ($smtp_hata) {
        flash_set('err', $smtp_hata);
        redirect(SITE_URL . '/admin/ayarlar.php?tab=smtp');
    }

    $stmt = db()->prepare("INSERT INTO ayarlar (anahtar, deger) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE deger = VALUES(deger)");
    foreach ($izinli as $k) {
        $v = (string)($_POST[$k] ?? '');
        $stmt->execute([$k, $v]);
    }
    log_yaz('ayar_guncelle', 'Site ayarları güncellendi.', (int)$_kul['id']);
    flash_set('ok', 'Ayarlar güncellendi.');
    redirect(SITE_URL . '/admin/ayarlar.php' . (isset($_POST['_tab']) ? '?tab=' . urlencode($_POST['_tab']) : ''));
}

// Mevcut değerler
$ayarlar = db_all("SELECT anahtar, deger FROM ayarlar");
$a = [];
foreach ($ayarlar as $row) $a[$row['anahtar']] = (string)$row['deger'];

$kampanyalar = db_all("SELECT id, baslik FROM kampanyalar WHERE aktif=1 ORDER BY id DESC");

$aktif_tab = $_GET['tab'] ?? 'genel';

require_once __DIR__ . '/_header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-h1">Site Ayarları</h1>
        <p class="page-sub">Tüm site genelindeki yapılandırma buradan yönetilir.</p>
    </div>
</div>

<form method="post" data-tabs>
    <?= csrf_field() ?>
    <input type="hidden" name="_tab" id="_tab" value="<?= e($aktif_tab) ?>">

    <div class="tabs-h">
<?php
$_tabs = [
    'genel'  => 'Genel',
    'firma'  => 'Firma Bilgileri',
    'sosyal' => 'Sosyal & İletişim',
    'seo'    => 'SEO & Analytics',
    'harita' => 'Harita & Anasayfa',
    'smtp'   => 'SMTP & Bildirim',
    'github' => 'GitHub Güncelleme',
];
$_tabSwitchJs = "var k=this.dataset.tab;var f=this.closest('form');if(!f)return;f.querySelectorAll('.tabs-h .t').forEach(function(x){x.classList.toggle('active',x.dataset.tab===k)});f.querySelectorAll('.tab-body').forEach(function(x){x.classList.toggle('active',x.dataset.tab===k)});var h=document.getElementById('_tab');if(h)h.value=k;";
foreach ($_tabs as $_k => $_l):
?>
        <div class="t <?= $aktif_tab===$_k?'active':'' ?>" data-tab="<?= $_k ?>" onclick="<?= e($_tabSwitchJs) ?>"><?= e($_l) ?></div>
<?php endforeach; ?>
    </div>

    <!-- GENEL -->
    <div class="tab-body <?= $aktif_tab==='genel'?'active':'' ?>" data-tab="genel">
        <div class="card">
            <div class="form-row">
                <div class="field">
                    <label>Site Başlığı (HTML &lt;title&gt;)</label>
                    <input class="input" name="site_baslik" value="<?= e($a['site_baslik'] ?? '') ?>" maxlength="200">
                    <p class="help">Tarayıcı sekmesinde ve Google sonuçlarında görünür. 60 karakteri aşmamasına dikkat.</p>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Site Açıklaması (meta description)</label>
                    <textarea class="textarea" name="site_aciklama" maxlength="300"><?= e($a['site_aciklama'] ?? '') ?></textarea>
                    <p class="help">Google sonuçlarında başlığın altında çıkar. 155-160 karakter ideal.</p>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Anahtar Kelimeler <span class="opt">(virgülle ayır)</span></label>
                    <textarea class="textarea" name="site_anahtar_kelime" maxlength="500"><?= e($a['site_anahtar_kelime'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- FİRMA -->
    <div class="tab-body <?= $aktif_tab==='firma'?'active':'' ?>" data-tab="firma">
        <div class="card">
            <div class="form-row">
                <div class="field">
                    <label>Firma Unvanı</label>
                    <input class="input" name="firma_unvan" value="<?= e($a['firma_unvan'] ?? '') ?>" maxlength="200">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>Telefon 1</label>
                    <input class="input" name="firma_telefon_1" value="<?= e($a['firma_telefon_1'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Telefon 2</label>
                    <input class="input" name="firma_telefon_2" value="<?= e($a['firma_telefon_2'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>E-posta</label>
                    <input class="input" type="email" name="firma_eposta" value="<?= e($a['firma_eposta'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Çalışma Saatleri</label>
                    <input class="input" name="firma_calisma_saatleri" value="<?= e($a['firma_calisma_saatleri'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Adres</label>
                    <input class="input" name="firma_adres" value="<?= e($a['firma_adres'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- SOSYAL -->
    <div class="tab-body <?= $aktif_tab==='sosyal'?'active':'' ?>" data-tab="sosyal">
        <div class="card">
            <div class="form-row cols-2">
                <div class="field">
                    <label><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook URL</label>
                    <input class="input" type="url" name="sosyal_facebook" value="<?= e($a['sosyal_facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="field">
                    <label><i class="fab fa-instagram" style="color:#e4405f"></i> Instagram URL</label>
                    <input class="input" type="url" name="sosyal_instagram" value="<?= e($a['sosyal_instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label><i class="fab fa-youtube" style="color:#ff0000"></i> YouTube URL</label>
                    <input class="input" type="url" name="sosyal_youtube" value="<?= e($a['sosyal_youtube'] ?? '') ?>">
                </div>
                <div class="field">
                    <label><i class="fab fa-x-twitter"></i> X (Twitter) URL</label>
                    <input class="input" type="url" name="sosyal_x" value="<?= e($a['sosyal_x'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label><i class="fab fa-whatsapp" style="color:#25d366"></i> WhatsApp Numarası <span class="opt">(uluslararası, başında 90)</span></label>
                    <input class="input" name="whatsapp_numara" value="<?= e($a['whatsapp_numara'] ?? '') ?>" placeholder="905467907877">
                    <p class="help">Sitedeki WhatsApp düğmesi bu numarayı kullanır. Türkiye için: <code>90</code> + numara, boşluksuz.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SEO -->
    <div class="tab-body <?= $aktif_tab==='seo'?'active':'' ?>" data-tab="seo">
        <div class="card">
            <div class="form-row">
                <div class="field">
                    <label>Google Analytics ID <span class="opt">(GA4: G-XXXXXX)</span></label>
                    <input class="input" name="google_analytics" value="<?= e($a['google_analytics'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Google Search Console doğrulama meta içeriği</label>
                    <input class="input" name="google_search_console_meta" value="<?= e($a['google_search_console_meta'] ?? '') ?>" placeholder="örn. abc123XYZ...">
                    <p class="help">
                        Search Console'da <strong>Mülkiyet doğrula → HTML etiketi</strong> seçeneğini kullan.
                        Verilen <code>&lt;meta name="google-site-verification" content="<u>BURASI</u>"&gt;</code>
                        etiketinden <strong>sadece <code>content</code> içindeki değeri</strong> buraya yapıştır.
                        Kaydedince site &lt;head&gt; bölümüne otomatik eklenir.
                    </p>
                </div>
            </div>
        </div>

        <div class="card" style="background:rgba(34,197,94,.05);border-left:3px solid #22c55e">
            <h3><i class="fas fa-sitemap"></i> Site Haritası (Sitemap)</h3>
            <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">
                Site haritası dinamik olarak üretilir. Tüm hizmetler, ürünler, kampanyalar, blog yazıları, kategoriler ve projeler otomatik dahil edilir.
            </p>
            <div class="form-row" style="margin-bottom:10px">
                <div class="field">
                    <label>Sitemap URL'i</label>
                    <div style="display:flex;gap:8px">
                        <input class="input" type="text" id="sitemapUrl" value="<?= SITE_URL ?>/sitemap.xml" readonly onclick="this.select()">
                        <a href="<?= SITE_URL ?>/sitemap.xml" target="_blank" class="btn btn-out"><i class="fas fa-external-link"></i> Aç</a>
                        <button type="button" class="btn btn-out" onclick='navigator.clipboard.writeText("<?= SITE_URL ?>/sitemap.xml").then(()=>this.innerHTML="<i class=\"fas fa-check\"></i> Kopyalandı");'><i class="fas fa-copy"></i> Kopyala</button>
                    </div>
                    <p class="help">Bu URL'i Google Search Console → <strong>Site Haritaları</strong> bölümünde "Yeni site haritası ekle" alanına yapıştır → Gönder.</p>
                </div>
            </div>
        </div>

        <div class="card" style="background:rgba(59,130,246,.05);border-left:3px solid #3b82f6">
            <h3><i class="fab fa-google"></i> Search Console Kurulum Adımları</h3>
            <ol style="color:var(--c-text);font-size:.92rem;line-height:1.8;margin:0;padding-left:22px">
                <li><a href="https://search.google.com/search-console" target="_blank" style="color:#3b82f6">Search Console</a>'a gir → <strong>Mülk ekle</strong> → "URL ön eki" seç → <code><?= SITE_URL ?></code> yapıştır.</li>
                <li>Doğrulama yöntemi olarak <strong>HTML etiketi</strong> seç. Verilen <code>content="..."</code> değerini yukarıdaki "Google Search Console doğrulama meta içeriği" alanına yapıştır → <strong>Tüm Ayarları Kaydet</strong>.</li>
                <li>Search Console'a dön → <strong>Doğrula</strong> tıkla.</li>
                <li>Doğrulandıktan sonra sol menüden <strong>Site Haritaları</strong> → "Yeni site haritası ekle" → <code>sitemap.xml</code> yaz → Gönder.</li>
                <li>24-72 saat içinde Google sayfaları indekslemeye başlar.</li>
            </ol>
        </div>
    </div>

    <!-- HARITA & ANASAYFA -->
    <div class="tab-body <?= $aktif_tab==='harita'?'active':'' ?>" data-tab="harita">
        <div class="card">
            <div class="form-row">
                <div class="field">
                    <label>Anasayfada Gösterilecek Aktif Kampanya</label>
                    <select name="aktif_kampanya_id">
                        <option value="">— Otomatik (en yeni aktif kampanya) —</option>
                        <?php foreach ($kampanyalar as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= ((int)($a['aktif_kampanya_id'] ?? 0) === (int)$k['id'])?'selected':'' ?>>
                                <?= e($k['baslik']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Google Maps Iframe Kodu</label>
                    <textarea class="textarea" name="harita_iframe" style="min-height:140px" placeholder='<iframe src="https://www.google.com/maps/embed?..."></iframe>'><?= e($a['harita_iframe'] ?? '') ?></textarea>
                    <p class="help">Google Maps'te konumu aç → Paylaş → Haritayı yerleştir → HTML'i kopyala buraya yapıştır. Boş bırakılırsa İzmir genel haritası gösterilir.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SMTP & BİLDİRİM -->
    <div class="tab-body <?= $aktif_tab==='smtp'?'active':'' ?>" data-tab="smtp"
         data-csrf="<?= e(csrf_token()) ?>"
         data-url="<?= e(SITE_URL) ?>"
         data-cron-key="<?= e((string)ayar('cron_anahtar', '')) ?>">
        <div class="card">
            <h3>SMTP Sunucusu</h3>
            <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">
                Bakım hatırlatma maili göndermek için SMTP sunucusu yapılandırması. DirectAdmin/cPanel'de oluşturduğun bir e-posta hesabını kullanabilirsin (örn. <code>info@azradogalgaz.com</code>).
            </p>
            <div class="form-row cols-2">
                <div class="field"><label>SMTP Sunucu <span style="color:#fbbf24;font-weight:normal">(hostname — e-posta DEĞİL)</span></label><input class="input" name="smtp_host" value="<?= e($a['smtp_host'] ?? '') ?>" placeholder="mail.azradogalgaz.com"><p class="help" style="color:#fbbf24;margin-top:4px"><i class="fas fa-triangle-exclamation"></i> İçinde <code>@</code> OLMAMALI. DirectAdmin Webmail ayarlarındaki "Outgoing Mail Server" değerini yaz.</p></div>
                <div class="field"><label>Port</label><input class="input" type="number" name="smtp_port" value="<?= e($a['smtp_port'] ?? '587') ?>" placeholder="587"></div>
            </div>
            <div class="form-row cols-2">
                <div class="field"><label>Kullanıcı Adı (e-posta)</label><input class="input" name="smtp_user" value="<?= e($a['smtp_user'] ?? '') ?>" placeholder="info@azradogalgaz.com" autocomplete="off"></div>
                <div class="field"><label>Şifre</label><input class="input" type="password" name="smtp_sifre" value="<?= e($a['smtp_sifre'] ?? '') ?>" autocomplete="new-password"></div>
            </div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>Şifreleme</label>
                    <select class="input" name="smtp_secure">
                        <option value="tls" <?= ($a['smtp_secure'] ?? 'tls')==='tls'?'selected':'' ?>>TLS / STARTTLS (Port 587 — önerilen)</option>
                        <option value="ssl" <?= ($a['smtp_secure'] ?? '')==='ssl'?'selected':'' ?>>SSL / SMTPS (Port 465)</option>
                        <option value=""    <?= ($a['smtp_secure'] ?? '')===''?'selected':'' ?>>Şifreleme Yok (Port 25 — önerilmez)</option>
                    </select>
                </div>
                <div class="field"><label>Gönderen Adı</label><input class="input" name="smtp_gonderen_ad" value="<?= e($a['smtp_gonderen_ad'] ?? '') ?>" placeholder="Azra Doğalgaz"></div>
            </div>
            <div class="form-row">
                <div class="field"><label>Gönderen E-posta <span class="opt">(boşsa SMTP kullanıcı adı)</span></label><input class="input" type="email" name="smtp_gonderen_eposta" value="<?= e($a['smtp_gonderen_eposta'] ?? '') ?>" placeholder="info@azradogalgaz.com"></div>
            </div>
        </div>

        <div class="card">
            <h3>Test Maili</h3>
            <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:10px">SMTP ayarlarını <strong>önce</strong> "Tüm Ayarları Kaydet" ile kaydet. Sonra bu kartta test maili gönderebilirsin.</p>
            <div class="form-row">
                <div class="field">
                    <label>Test Alıcı E-posta</label>
                    <input class="input" type="email" id="testMailAdres" placeholder="ornek@gmail.com" value="<?= e($_kul['eposta'] ?? '') ?>">
                </div>
            </div>
            <div class="form-actions" style="margin-top:14px">
                <button type="button" class="btn btn-pri" onclick="azraSmtpTest(this)"><i class="fas fa-paper-plane"></i> Test Maili Gönder</button>
            </div>
            <div id="testMailSonuc" style="margin-top:12px"></div>
        </div>

        <div class="card">
            <h3>Bakım Hatırlatma Bildirimi</h3>
            <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:14px">Bakım tarihi yaklaşan müşterilere otomatik mail gönderir. Cron çalıştırıldığında devreye girer.</p>
            <div class="form-row cols-2">
                <div class="field">
                    <label>Otomatik Bildirim</label>
                    <select class="input" name="bakim_bildirim_aktif">
                        <option value="1" <?= ((string)($a['bakim_bildirim_aktif'] ?? '1'))==='1'?'selected':'' ?>>Aktif</option>
                        <option value="0" <?= ((string)($a['bakim_bildirim_aktif'] ?? ''))==='0'?'selected':'' ?>>Kapalı</option>
                    </select>
                </div>
                <div class="field"><label>Kaç Gün Önceden</label><input class="input" type="number" name="bakim_bildirim_gun" value="<?= e($a['bakim_bildirim_gun'] ?? '15') ?>" min="1" max="60" placeholder="15"></div>
            </div>
        </div>

        <div class="card" style="background:rgba(34,197,94,.05);border-left:3px solid #22c55e">
            <h3><i class="fas fa-clock-rotate-left"></i> Cron Kurulumu</h3>
            <p style="color:var(--c-muted);font-size:.9rem;margin-bottom:10px">Bildirimlerin otomatik gönderilebilmesi için DirectAdmin/cPanel'de bir cron job tanımla:</p>
            <pre style="background:#0a0f1f;color:#aaffcc;padding:12px;border-radius:6px;font-size:.8rem;font-family:monospace;overflow-x:auto;margin:0">0 9 * * * curl -s "<?= SITE_URL ?>/cron/bakim-bildirim.php?key=<?= e(ayar('cron_anahtar', 'KEY-EKSIK')) ?>" > /dev/null</pre>
            <p style="color:var(--c-muted);font-size:.85rem;margin-top:10px">
                <strong>Açıklama:</strong> Her gün 09:00'da çalışır, <?= e($a['bakim_bildirim_gun'] ?? '15') ?> gün içinde bakım tarihi olan müşterilere mail gönderir. Aynı bakım için iki kez mail göndermez (bildirim_gonderildi flag'i).<br>
                <strong>Test:</strong> Aşağıdaki butonla şimdi manuel çalıştırabilirsin.
            </p>
            <div style="margin-top:12px">
                <button type="button" class="btn btn-blue btn-sm" onclick="azraBakimBildirim(this)"><i class="fas fa-play"></i> Bildirimleri Şimdi Gönder</button>
                <a href="<?= SITE_URL ?>/cron/bakim-bildirim.php?key=<?= e(ayar('cron_anahtar', '')) ?>" target="_blank" class="btn btn-out btn-sm"><i class="fas fa-external-link"></i> Cron URL'ini Aç</a>
            </div>
            <div id="bakimSimdiSonuc" style="margin-top:12px"></div>
        </div>
    </div>

    <!-- GITHUB -->
    <div class="tab-body <?= $aktif_tab==='github'?'active':'' ?>" data-tab="github">
        <div class="card">
            <div class="alert alert-info"><i class="fas fa-circle-info"></i> GitHub güncelleme sistemi <strong>Aşama 5</strong>'te aktif edilecek. Şimdiden bilgileri kaydedebilirsin.</div>
            <div class="form-row cols-2">
                <div class="field">
                    <label>GitHub Repository <span class="opt">(kullanici/repo)</span></label>
                    <input class="input" name="github_repo" value="<?= e($a['github_repo'] ?? '') ?>" placeholder="codegatr/azradogalgaz">
                </div>
                <div class="field">
                    <label>Personal Access Token <span class="opt">(private repo için)</span></label>
                    <input class="input" type="password" name="github_token" value="<?= e($a['github_token'] ?? '') ?>" placeholder="ghp_...">
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-pri"><i class="fas fa-floppy-disk"></i> Tüm Ayarları Kaydet</button>
        <a href="<?= SITE_URL ?>/admin/panel.php" class="btn btn-out">İptal</a>
    </div>
</form>

<script>
function azraSmtpTest(btn) {
    var pane = btn.closest('.tab-body[data-tab="smtp"]');
    var csrf = pane.dataset.csrf;
    var url  = pane.dataset.url;
    var em = document.getElementById('testMailAdres').value.trim();
    var out = document.getElementById('testMailSonuc');
    if (!em) { alert('E-posta gir.'); return; }
    out.innerHTML = '<span style="color:var(--c-muted)">Gönderiliyor...</span>';
    var fd = new FormData();
    fd.append('eposta', em);
    fd.append('csrf', csrf);
    fetch(url + '/admin/smtp-test.php', {method:'POST', body: fd})
        .then(function(r){ return r.json(); })
        .then(function(d){
            out.innerHTML = d.ok
                ? '<div class="alert alert-ok"><i class="fas fa-check"></i> Mail gönderildi: ' + em + '</div>'
                : '<div class="alert alert-err"><i class="fas fa-xmark"></i> Hata: ' + (d.hata || 'bilinmiyor') + '</div>';
        })
        .catch(function(e){
            out.innerHTML = '<div class="alert alert-err">Hata: ' + e.message + '</div>';
        });
}

function azraBakimBildirim(btn) {
    if (!confirm('Bakım tarihi yaklaşan müşterilere mail gönderilsin mi?')) return;
    var pane = btn.closest('.tab-body[data-tab="smtp"]');
    var url  = pane.dataset.url;
    var key  = pane.dataset.cronKey;
    var out = document.getElementById('bakimSimdiSonuc');
    out.innerHTML = '<span style="color:var(--c-muted)">Çalıştırılıyor...</span>';
    fetch(url + '/cron/bakim-bildirim.php?key=' + encodeURIComponent(key))
        .then(function(r){ return r.text(); })
        .then(function(t){
            out.innerHTML = '<pre style="background:#0a0f1f;color:#aaffcc;padding:12px;border-radius:6px;font-size:.8rem;font-family:monospace;white-space:pre-wrap">' + t.replace(/</g,'&lt;') + '</pre>';
        })
        .catch(function(e){
            out.innerHTML = '<div class="alert alert-err">Hata: ' + e.message + '</div>';
        });
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
