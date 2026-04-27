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
    ];
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
        <div class="t <?= $aktif_tab==='genel'?'active':'' ?>" data-tab="genel" onclick="document.getElementById('_tab').value='genel'">Genel</div>
        <div class="t <?= $aktif_tab==='firma'?'active':'' ?>" data-tab="firma" onclick="document.getElementById('_tab').value='firma'">Firma Bilgileri</div>
        <div class="t <?= $aktif_tab==='sosyal'?'active':'' ?>" data-tab="sosyal" onclick="document.getElementById('_tab').value='sosyal'">Sosyal & İletişim</div>
        <div class="t <?= $aktif_tab==='seo'?'active':'' ?>" data-tab="seo" onclick="document.getElementById('_tab').value='seo'">SEO & Analytics</div>
        <div class="t <?= $aktif_tab==='harita'?'active':'' ?>" data-tab="harita" onclick="document.getElementById('_tab').value='harita'">Harita & Anasayfa</div>
        <div class="t <?= $aktif_tab==='github'?'active':'' ?>" data-tab="github" onclick="document.getElementById('_tab').value='github'">GitHub Güncelleme</div>
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
                    <input class="input" name="google_search_console_meta" value="<?= e($a['google_search_console_meta'] ?? '') ?>">
                    <p class="help">Sadece <code>content="..."</code> kısmını yapıştır. Tüm meta etiketini değil.</p>
                </div>
            </div>
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

<?php require_once __DIR__ . '/_footer.php'; ?>
