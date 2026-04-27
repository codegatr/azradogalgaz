<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik = 'Kombi Kapasite Hesaplama Aracı — Azra Doğalgaz';
$sayfa_aciklama = 'Evinize kaç kW kombi alacağınızı saniyeler içinde hesaplayın. TSE-825 mantığıyla, m², izolasyon, cephe, kat yüksekliği ve bölgeye göre kombi kapasite hesabı.';
$kanonik_url = SITE_URL . '/kombi-hesaplama';

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Kombi Hesaplama</span>
        </div>
        <h1>Kombi Kapasite Hesaplama</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Evinizin metrekaresi, kat yüksekliği, bölgesi ve izolasyon durumuna göre ihtiyacınız olan kombi kapasitesini hesaplar. TSE-825 standartlarına uygun.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="calc-widget">
            <div class="form-row cols-2">
                <div class="field">
                    <label>Evinizin Brüt Alanı <span class="req">*</span></label>
                    <input type="number" id="alan" class="input" min="20" max="800" value="100" placeholder="m²">
                </div>
                <div class="field">
                    <label>Kat Yüksekliği</label>
                    <select id="kat_yuksek">
                        <option value="2.7">Standart (2.7 m)</option>
                        <option value="3">3 m</option>
                        <option value="3.5">3.5 m (Yüksek tavan)</option>
                    </select>
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Bölge / İklim Bölgesi <span class="req">*</span></label>
                    <select id="bolge">
                        <option value="30" selected>Ege / Akdeniz (İzmir, Antalya, Aydın, Muğla)</option>
                        <option value="45">Marmara / İç Ege (İstanbul, Bursa, Manisa)</option>
                        <option value="60">İç Anadolu (Ankara, Konya, Eskişehir)</option>
                        <option value="80">Doğu Anadolu (Erzurum, Kars, Ardahan)</option>
                        <option value="50">Karadeniz (Trabzon, Samsun, Rize)</option>
                    </select>
                </div>
                <div class="field">
                    <label>Cephe Yönü</label>
                    <select id="cephe">
                        <option value="0">Güney / Doğu / Batı</option>
                        <option value="-15">Kuzey cephe (-15 m²)</option>
                    </select>
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>İzolasyon Durumu</label>
                    <select id="izolasyon">
                        <option value="1">Yalıtım var (mantolama / iyi)</option>
                        <option value="1.15">Orta (kısmen yalıtım)</option>
                        <option value="1.3">Yalıtım yok (eski bina)</option>
                    </select>
                </div>
                <div class="field">
                    <label>Konut Tipi</label>
                    <select id="kat_tipi">
                        <option value="1">Ara kat / standart daire</option>
                        <option value="1.1">Çatı katı (+%10)</option>
                        <option value="1.1">Bodrum / Zemin (+%10)</option>
                        <option value="1.33">Dubleks / Tripleks (+%33)</option>
                    </select>
                </div>
            </div>

            <button id="hesapla" class="btn btn-primary btn-lg btn-block"><i class="fas fa-calculator"></i> Kapasiteyi Hesapla</button>

            <div id="sonuc" class="calc-result empty" style="display:block">
                <h4>Sonuç</h4>
                <div class="num">— <small>kW</small></div>
                <div class="desc">Yukarıdaki bilgileri girip <strong>Hesapla</strong>'ya basın.</div>
            </div>

            <div id="oneri" style="display:none;background:var(--c-blue-l);border:1px solid #bae6fd;border-radius:var(--r);padding:18px;margin-top:14px;font-size:.92rem;color:var(--c-text-2);line-height:1.7"></div>

            <div style="margin-top:24px;text-align:center">
                <p style="color:var(--c-muted);font-size:.88rem;margin-bottom:14px">Sonucunuz var mı? Şimdi keşif ekibimizi de talep edin:</p>
                <a href="<?= SITE_URL ?>/kesif" class="btn btn-out"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif İste</a>
            </div>
        </div>

        <div class="prose" style="margin-top:60px">
            <h2>Kombi Kapasite Hesaplama Mantığı</h2>
            <p>Kombi seçimi yaparken sadece evin metrekaresine bakmak yetersizdir. Doğru kapasite seçimi için <strong>TSE-825 standartlarında</strong> belirtildiği gibi ısı kaybı hesabı yapılmalıdır:</p>
            <blockquote><strong>Q (kcal/h) = Alan (m²) × Kat yüksekliği (m) × Bölge katsayısı × İzolasyon faktörü × Konut tipi faktörü</strong></blockquote>
            <p>Bu kcal/h değeri 860'a bölünerek kW cinsine dönüştürülür. Piyasada satılan kombiler 18, 20, 24, 28, 30, 35 kW gibi standart kapasitelerde geldiği için, hesabımız bunlardan en yakın üst değere yuvarlanır.</p>

            <h3>Kapasite Tablosu (Yaklaşık)</h3>
            <ul>
                <li><strong>17–18 kW kombi:</strong> 60–80 m² küçük daireler. Petek uzunluğu 6–7 metre.</li>
                <li><strong>20 kW kombi:</strong> 80–100 m² standart daireler. Petek uzunluğu 7–9 metre.</li>
                <li><strong>24 kW kombi:</strong> 100–150 m² ortalama daireler için (en yaygın seçim). 9–11 m petek.</li>
                <li><strong>28 kW kombi:</strong> 150–180 m² büyük daireler. 2 banyoda eşzamanlı sıcak su için ideal.</li>
                <li><strong>30 kW kombi:</strong> 180–220 m² ev veya küçük dubleks. 12–14 metre petek.</li>
                <li><strong>35+ kW veya kazanlı sistem:</strong> 220+ m² büyük dubleks/tripleks veya ticari mekan.</li>
            </ul>

            <h3>Yoğuşmalı Kombi Avantajları</h3>
            <p>2019'dan beri Türkiye'de satılan tüm kombiler yasa gereği yoğuşmalı teknolojiye sahip olmak zorundadır.</p>
            <ul>
                <li>%20–25 daha az gaz tüketir</li>
                <li>Düşük sıcaklıkta (55°C–35°C) maksimum verim — yerden ısıtma için ideal</li>
                <li>Modülasyon (oransal güç ayarı) ile sürekli açıp kapanmadan stabil çalışır</li>
                <li>Tam yoğuşma için petek uzunluğu en az 8 metre olmalıdır</li>
            </ul>

            <h3>Kombi Seçerken 8 Önemli Nokta</h3>
            <ol>
                <li><strong>Doğru kapasite</strong> — Evi ısıtacak ama gereksiz büyük olmayacak güç</li>
                <li><strong>Bilinen marka</strong> — Demirdöküm, Bosch, Vaillant, Buderus, ECA, Baymak, Ariston</li>
                <li><strong>Yetkili servis ağı</strong> — Bölgenizde servis kolaylığı önemli</li>
                <li><strong>ERP enerji etiketi</strong> — A veya A+ sınıfı tercih edin</li>
                <li><strong>Modülasyon oranı</strong> — Geniş aralıklı (1:8 ve üstü) daha tasarruflu</li>
                <li><strong>Garanti süresi</strong> — Kombi 2, eşanjör 5 yıl ve üstü</li>
                <li><strong>Yoğuşma teknolojisi</strong> — Tam yoğuşmalı modeller daha verimli</li>
                <li><strong>Yetkili tesisat firması</strong> — Garantinin geçerli olması için yetkili firmaca montaj şart</li>
            </ol>

            <h3>Demirdöküm Kombi Modelleri</h3>
            <p>Azra Doğalgaz olarak <strong>Demirdöküm yetkili bayisiyiz</strong>. Yaygın tercih edilen modeller:</p>
            <ul>
                <li><strong>Ademix:</strong> 24 / 28 kW — Ekonomik tam yoğuşmalı, en popüler model</li>
                <li><strong>Adesso:</strong> 24 / 28 / 32 kW — Geniş modülasyon, daha sessiz</li>
                <li><strong>Atron:</strong> 24 / 28 kW — Premium seri, akıllı kontrol özellikleri</li>
            </ul>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Kombi seçiminde profesyonel destek</h3>
            <p>Hesaplamanız sonucunda ihtiyacınız olan kapasiteyi öğrendiniz mi? Ücretsiz keşifle modeli birlikte seçelim.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
    </div>
</section>

<script>
(function(){
    const $ = id => document.getElementById(id);
    const fmtKW = ['18','20','24','28','30','35','45','60'];

    function hesapla(){
        const alan = Math.max(20, parseFloat($('alan').value || 100));
        const ky   = parseFloat($('kat_yuksek').value);
        const bk   = parseFloat($('bolge').value);
        const cep  = parseFloat($('cephe').value);
        const izo  = parseFloat($('izolasyon').value);
        const kt   = parseFloat($('kat_tipi').value);

        const efektif_alan = Math.max(20, alan + cep);
        const kcal = efektif_alan * ky * bk * izo * kt;
        const kw   = kcal / 860;

        let onerilen = fmtKW[fmtKW.length-1];
        for (const k of fmtKW) {
            if (parseFloat(k) >= kw) { onerilen = k; break; }
        }

        $('sonuc').classList.remove('empty');
        $('sonuc').innerHTML = `
            <h4>Önerilen Kombi Kapasitesi</h4>
            <div class="num">${onerilen} <small>kW yoğuşmalı</small></div>
            <div class="desc">
                Hesaplanan ısı yükü: <strong>${kw.toFixed(1)} kW</strong> (${Math.round(kcal).toLocaleString('tr-TR')} kcal/h)<br>
                Efektif alan: ${efektif_alan.toFixed(0)} m² · Petek uzunluğu önerisi: <strong>${Math.ceil(efektif_alan/15)}-${Math.ceil(efektif_alan/12)} metre</strong>
            </div>`;

        const o = $('oneri');
        o.style.display = 'block';
        let metin = '';
        const kwn = parseFloat(onerilen);
        if (kwn <= 20)      metin = '<strong>Küçük daireniz için</strong> 18-20 kW yoğuşmalı kombi yeterli olacaktır. Demirdöküm Ademix 20 kW, ECA Confeo Premix 18 kW gibi modeller idealdir.';
        else if (kwn <= 24) metin = '<strong>Standart daireniz için</strong> 24 kW yoğuşmalı kombi en uygun seçim. Türkiye\'nin en yaygın kapasitesi. Demirdöküm Ademix 24, Bosch Condens 5700i 24, Vaillant ecoTEC 24 önerilebilir.';
        else if (kwn <= 28) metin = '<strong>Geniş daireniz için</strong> 28 kW yoğuşmalı kombi idealdir. İki banyoda eşzamanlı sıcak su kullanımı için yeterli kapasite.';
        else if (kwn <= 30) metin = '<strong>Büyük ev veya küçük dubleks için</strong> 30 kW yoğuşmalı kombi önerilir. Buderus GB172, Demirdöküm Adesso 30 önerilebilir.';
        else                metin = '<strong>Çok büyük ev veya işyeri için</strong> ' + onerilen + ' kW kapasite gerekir. Bu büyüklükte kazanlı sistem veya kaskad çözüm de değerlendirilmelidir. Mutlaka uzman keşfi alınmalıdır.';

        o.innerHTML = '<i class="fas fa-lightbulb" style="color:var(--c-primary)"></i> <strong>Öneri:</strong> ' + metin;
    }

    $('hesapla').addEventListener('click', hesapla);
    ['alan','kat_yuksek','bolge','cephe','izolasyon','kat_tipi'].forEach(id => {
        $(id).addEventListener('change', hesapla);
    });
})();
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
