<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Klima BTU Hesaplama Aracı — Azra Doğalgaz';
$sayfa_aciklama = 'Klimanız için doğru BTU değerini hesaplayın. Oda büyüklüğü, bölge, kişi sayısı ve aydınlatmaya göre 9.000–24.000 BTU arası önerimizi alın.';
$kanonik_url    = SITE_URL . '/klima-hesaplama';

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Klima BTU Hesaplama</span>
        </div>
        <h1>Klima BTU Hesaplama</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">Odanızın metrekaresi, bölgenizin iklim katsayısı, kişi sayısı ve aydınlatma yüküne göre size uygun klima kapasitesini saniyeler içinde bulun.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="calc-widget">
            <div class="form-row cols-2">
                <div class="field">
                    <label>Oda Alanı (m²) <span class="req">*</span></label>
                    <input type="number" id="oda" class="input" min="5" max="200" value="20">
                </div>
                <div class="field">
                    <label>Kişi Sayısı (Aktif Kullanım)</label>
                    <input type="number" id="kisi" class="input" min="1" max="20" value="3">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Bölge / İklim <span class="req">*</span></label>
                    <select id="bolge_btu">
                        <option value="425" selected>Ege (İzmir, Aydın, Manisa, Muğla)</option>
                        <option value="450">Akdeniz (Antalya, Mersin, Adana)</option>
                        <option value="385">Marmara (İstanbul, Bursa, Tekirdağ)</option>
                        <option value="350">İç Anadolu (Ankara, Konya)</option>
                        <option value="300">Karadeniz / Doğu Anadolu</option>
                    </select>
                </div>
                <div class="field">
                    <label>Aydınlatma 500W üzerinde mi?</label>
                    <select id="aydinlatma">
                        <option value="0">Hayır / Standart LED</option>
                        <option value="1700">Evet (~500W) — +1700 BTU</option>
                        <option value="3400">Yüksek (~1000W) — +3400 BTU</option>
                    </select>
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field">
                    <label>Güneş Alma Durumu</label>
                    <select id="gunes">
                        <option value="1">Standart / Az</option>
                        <option value="1.1">Orta (öğleden sonra alır)</option>
                        <option value="1.2">Yoğun güneş alır (gün boyu)</option>
                    </select>
                </div>
                <div class="field">
                    <label>Tavan Yüksekliği</label>
                    <select id="tavan">
                        <option value="1">Standart (2.5–2.7 m)</option>
                        <option value="1.1">Yüksek (3 m+) — +%10</option>
                    </select>
                </div>
            </div>

            <button id="hesapla" class="btn btn-primary btn-lg btn-block"><i class="fas fa-snowflake"></i> BTU Değerini Hesapla</button>

            <div id="sonuc" class="calc-result empty" style="display:block">
                <h4>Sonuç</h4>
                <div class="num">— <small>BTU/h</small></div>
                <div class="desc">Yukarıdaki bilgileri girip <strong>Hesapla</strong>'ya basın.</div>
            </div>

            <div id="oneri" style="display:none;background:var(--c-blue-l);border:1px solid #bae6fd;border-radius:var(--r);padding:18px;margin-top:14px;font-size:.92rem;color:var(--c-text-2);line-height:1.7"></div>

            <div style="margin-top:24px;text-align:center">
                <p style="color:var(--c-muted);font-size:.88rem;margin-bottom:14px">Klima montajı için keşif talep edebilirsiniz:</p>
                <a href="<?= SITE_URL ?>/kesif" class="btn btn-out"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif İste</a>
            </div>
        </div>

        <div class="prose" style="margin-top:60px">
            <h2>Klima BTU Hesaplama Formülü</h2>
            <p>BTU (British Thermal Unit), klimanızın bir saatte ortamdan uzaklaştırabildiği ısı miktarını gösteren birimdir. Doğru BTU seçimi hem konfor hem de elektrik faturası için kritiktir. Yetersiz kapasite odayı soğutamaz, fazla kapasite ise nemi alamadan sıcaklığı düşürür ve sağlıksız ortam yaratır.</p>

            <blockquote><strong>BTU/h = (Oda m² × Bölge Katsayısı) + (Kişi sayısı – 1) × 600 + Aydınlatma BTU'su × Cephe çarpanı × Tavan çarpanı</strong></blockquote>

            <h3>Türkiye Bölgesel Katsayılar</h3>
            <ul>
                <li><strong>Ege Bölgesi:</strong> 425 (İzmir, Aydın, Muğla, Manisa)</li>
                <li><strong>Akdeniz:</strong> 450 (Antalya, Mersin, Adana, Hatay)</li>
                <li><strong>Marmara:</strong> 385 (İstanbul, Bursa, Tekirdağ, Çanakkale)</li>
                <li><strong>İç Anadolu:</strong> 350 (Ankara, Konya, Kayseri)</li>
                <li><strong>Karadeniz / Doğu Anadolu:</strong> 300</li>
            </ul>

            <h3>Pratik Kapasite Tablosu</h3>
            <ul>
                <li><strong>9.000 BTU:</strong> 12–18 m² (yatak odası, çocuk odası)</li>
                <li><strong>12.000 BTU:</strong> 18–25 m² (oturma odası, küçük salon)</li>
                <li><strong>18.000 BTU:</strong> 28–35 m² (büyük salon)</li>
                <li><strong>24.000 BTU:</strong> 40–50 m² (geniş salon, küçük ofis)</li>
                <li><strong>36.000 BTU+:</strong> 55+ m² (ticari mekan, mağaza)</li>
            </ul>

            <h3>Klima Seçerken Dikkat Edilecekler</h3>
            <ol>
                <li><strong>Inverter teknolojisi</strong> — Sürekli açıp kapanmaz, sabit sıcaklık tutar, %30 daha tasarruflu</li>
                <li><strong>A++ veya A+++ enerji sınıfı</strong> — Düşük elektrik tüketimi</li>
                <li><strong>R32 soğutucu gaz</strong> — Çevre dostu, yeni nesil</li>
                <li><strong>Sessiz çalışma</strong> — İç ünite 19–25 dB tercih edilmeli</li>
                <li><strong>WiFi kontrol</strong> — Uzaktan açma/kapama, akıllı ev entegrasyonu</li>
                <li><strong>Marka servis ağı</strong> — Bölgenizde servis kolaylığı</li>
                <li><strong>Profesyonel montaj</strong> — Yetkili teknisyenle kurulum, garantinin geçerliliği için şart</li>
            </ol>

            <h3>Önerilen Markalar</h3>
            <p>Azra Doğalgaz olarak çoklu marka klima satışı ve montajı yapıyoruz:</p>
            <ul>
                <li><strong>Daikin</strong> — Premium Japon teknolojisi, en iyi inverter</li>
                <li><strong>Mitsubishi Electric</strong> — Sessiz ve uzun ömür</li>
                <li><strong>Bosch / Siemens</strong> — Avrupa standardı, güçlü servis ağı</li>
                <li><strong>LG / Samsung</strong> — Akıllı özellikler, modern tasarım</li>
                <li><strong>Vestel / Beko / Arçelik</strong> — Yerli üretim, uygun fiyat</li>
                <li><strong>ECA</strong> — Demirdöküm grubu, R32 inverter</li>
            </ul>

            <h3>Sık Yapılan Hatalar</h3>
            <p><strong>Çok büyük klima almak:</strong> Bilinenin aksine "büyük olsun da garanti olsun" yanlıştır. Fazla kapasiteli klima, oda hızla soğuduğu için sürekli durup kalkar; nemi alamadan kapanır, ortam soğuk-nemli olur. Yarısı çalıştırarak idare etmek de inverter klimanın enerji avantajını yok eder.</p>
            <p><strong>Tek klima ile birden fazla oda soğutmak:</strong> Kapı kapalıysa imkansız, açıksa verim çok düşüktür. Her odaya ayrı klima veya multi-split sistem ideal.</p>
            <p><strong>Yanlış konum:</strong> Direkt güneş alan duvar, mutfak ocak yakını, perde arkası — klimanın verim ve ömrünü düşürür.</p>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Klima alımı + montaj tek fiyat</h3>
            <p>Inverter klima + profesyonel montaj + 2 yıl işçilik garantisi. Şimdi keşif talep edin.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et</a>
    </div>
</section>

<script>
(function(){
    const $ = id => document.getElementById(id);
    const stdBTU = ['9000','12000','18000','24000','36000','48000'];

    function hesapla(){
        const oda = Math.max(5, parseFloat($('oda').value || 20));
        const kisi = Math.max(1, parseInt($('kisi').value || 3));
        const bk = parseFloat($('bolge_btu').value);
        const ay = parseFloat($('aydinlatma').value);
        const gun = parseFloat($('gunes').value);
        const tav = parseFloat($('tavan').value);

        const ek_kisi = Math.max(0, (kisi - 1) * 600);
        const temel_btu = (oda * bk) + ek_kisi + ay;
        const final_btu = Math.round(temel_btu * gun * tav);

        let onerilen = stdBTU[stdBTU.length-1];
        for (const b of stdBTU) {
            if (parseFloat(b) >= final_btu) { onerilen = b; break; }
        }

        $('sonuc').classList.remove('empty');
        $('sonuc').innerHTML = `
            <h4>Önerilen Klima Kapasitesi</h4>
            <div class="num">${parseInt(onerilen).toLocaleString('tr-TR')} <small>BTU/h</small></div>
            <div class="desc">
                Hesaplanan ısı yükü: <strong>${final_btu.toLocaleString('tr-TR')} BTU</strong><br>
                Oda: ${oda} m² · ${kisi} kişi · Bölge katsayısı: ${bk}
            </div>`;

        const o = $('oneri');
        o.style.display = 'block';
        let metin = '';
        const btun = parseFloat(onerilen);
        if (btun <= 9000)       metin = '<strong>9.000 BTU klima</strong> küçük yatak odası, çocuk odası için idealdir. Daikin Sensira 9.000 BTU, Bosch Climate 5000 9.000 BTU, ECA Spylos 9.000 önerilebilir.';
        else if (btun <= 12000) metin = '<strong>12.000 BTU klima</strong> standart oturma odaları için en yaygın seçimdir. Mitsubishi Electric MSZ-HR 12, Daikin Sensira 12, Vestel inverter 12.000 BTU iyi seçimler.';
        else if (btun <= 18000) metin = '<strong>18.000 BTU klima</strong> büyük salon, açık plan oturma alanları için. Daikin Sensira 18, Bosch Climate 5000 18, LG Standard Plus 18.000 değerlendirilebilir.';
        else if (btun <= 24000) metin = '<strong>24.000 BTU klima</strong> 40–50 m² geniş salon veya küçük ofis için yeterlidir. Daikin Stylish 24, Mitsubishi Electric Premium 24, Samsung WindFree 24.';
        else                    metin = '<strong>' + (parseInt(onerilen).toLocaleString('tr-TR')) + ' BTU+</strong> büyük alanlar için kanal tipi veya VRF sistem önerilir. Mağaza, restoran, ofis için uzman keşfi şart.';

        o.innerHTML = '<i class="fas fa-lightbulb" style="color:var(--c-primary)"></i> <strong>Öneri:</strong> ' + metin;
    }

    $('hesapla').addEventListener('click', hesapla);
    ['oda','kisi','bolge_btu','aydinlatma','gunes','tavan'].forEach(id => $(id).addEventListener('change', hesapla));
})();
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
