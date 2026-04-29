<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Hakkımızda — Azra Doğalgaz İzmir';
$sayfa_aciklama = 'Azra Doğalgaz, İzmir\'de doğalgaz tesisatı, kombi ve klima montajı alanında 10+ yıl tecrübe, 2.500+ teslim edilmiş proje. Demirdöküm yetkili bayisi.';
$kanonik_url    = SITE_URL . '/hakkimizda';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'AboutPage',
        'name'     => 'Hakkımızda — Azra Doğalgaz',
        'url'      => SITE_URL . '/hakkimizda',
        'mainEntity' => [
            '@type'    => 'HVACBusiness',
            'name'     => 'Azra Doğalgaz',
            'url'      => SITE_URL,
            'telephone'=> ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:''),
            'address'  => ['@type'=>'PostalAddress','addressLocality'=>'İzmir','addressCountry'=>'TR'],
            'foundingDate' => '2014',
            'numberOfEmployees' => '6-10',
        ],
    ],
];

require_once __DIR__ . '/inc/header.php';

$tel = ayar('firma_telefon_1', '0546 790 78 77');
?>

<style>
/* ─── Kurumsal Hakkımızda — özel stiller ─── */
.hk-hero{
    position:relative;background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);
    color:#fff;padding:80px 0 100px;overflow:hidden;
}
.hk-hero::before{
    content:'';position:absolute;top:-20%;right:-15%;width:520px;height:520px;
    background:radial-gradient(circle,rgba(255,107,0,.18) 0%,transparent 60%);
    border-radius:50%;pointer-events:none;
}
.hk-hero::after{
    content:'';position:absolute;bottom:-30%;left:-10%;width:420px;height:420px;
    background:radial-gradient(circle,rgba(245,158,11,.10) 0%,transparent 60%);
    border-radius:50%;pointer-events:none;
}
.hk-hero .container{position:relative;z-index:2;display:grid;grid-template-columns:1.4fr 1fr;gap:60px;align-items:center}
.hk-hero .badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,107,0,.15);border:1px solid rgba(255,107,0,.4);color:#fdba74;
    padding:8px 16px;border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:18px;
}
.hk-hero .badge i{color:#f97316}
.hk-hero h1{
    font-family:var(--font-display);font-size:clamp(2rem,4.2vw,3.4rem);
    font-weight:800;line-height:1.08;margin-bottom:18px;letter-spacing:-.5px;
}
.hk-hero h1 strong{color:#fdba74;font-weight:800}
.hk-hero .lead{font-size:1.08rem;color:#cbd5e1;line-height:1.6;max-width:540px;margin-bottom:32px}
.hk-hero .ctas{display:flex;gap:12px;flex-wrap:wrap}
.hk-hero .btn-amber{
    background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);color:#fff;
    padding:14px 26px;border-radius:10px;font-weight:700;font-size:.98rem;
    text-decoration:none;display:inline-flex;align-items:center;gap:9px;
    box-shadow:0 8px 24px rgba(234,88,12,.35);transition:.25s;border:0;
}
.hk-hero .btn-amber:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(234,88,12,.5)}
.hk-hero .btn-ghost{
    background:transparent;color:#fff;border:1px solid rgba(255,255,255,.25);
    padding:14px 22px;border-radius:10px;font-weight:600;font-size:.98rem;
    text-decoration:none;display:inline-flex;align-items:center;gap:9px;transition:.25s;
}
.hk-hero .btn-ghost:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.4)}

/* Hero sağ kart — kompozit istatistik kartı */
.hk-hero-card{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
    border-radius:20px;padding:28px;backdrop-filter:blur(8px);
}
.hk-hero-card .ust{
    display:flex;align-items:center;gap:14px;padding-bottom:18px;
    border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:20px;
}
.hk-hero-card .ust .ico{
    width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#f97316,#ea580c);
    display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;
}
.hk-hero-card .ust .baslik{font-size:1.05rem;font-weight:700;color:#fff}
.hk-hero-card .ust .alt{font-size:.82rem;color:#94a3b8}
.hk-hero-card .satir{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.hk-hero-card .blok{padding:14px;background:rgba(255,255,255,.03);border-radius:10px;border:1px solid rgba(255,255,255,.06)}
.hk-hero-card .blok .num{font-size:1.8rem;font-weight:800;color:#fdba74;font-family:var(--font-display);line-height:1.1}
.hk-hero-card .blok .lbl{font-size:.78rem;color:#94a3b8;margin-top:4px;letter-spacing:.5px}

/* ─── Hikayemiz — Asymmetric Story ─── */
.hk-story{padding:90px 0;background:#fff}
.hk-story .grid{display:grid;grid-template-columns:1fr 1.1fr;gap:64px;align-items:center}
.hk-story .etiket{
    display:inline-block;padding:6px 14px;background:#fff7ed;color:#ea580c;
    border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:14px;
}
.hk-story h2{
    font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.4rem);
    font-weight:800;color:#0f172a;line-height:1.15;margin-bottom:18px;letter-spacing:-.4px;
}
.hk-story h2 strong{color:#ea580c}
.hk-story .metin{font-size:1.02rem;color:#475569;line-height:1.7;margin-bottom:14px}
.hk-story .alinti{
    font-family:Georgia,serif;font-style:italic;font-size:1.1rem;color:#1e293b;
    border-left:3px solid #ea580c;padding:12px 0 12px 20px;margin:24px 0;
}
.hk-story .alinti span{display:block;font-size:.85rem;color:#64748b;margin-top:8px;font-style:normal}

/* Timeline */
.hk-timeline{position:relative;padding-left:36px}
.hk-timeline::before{
    content:'';position:absolute;left:14px;top:8px;bottom:8px;width:2px;
    background:linear-gradient(to bottom,#ea580c,#f59e0b 50%,rgba(245,158,11,.2));
}
.hk-timeline .nokta{position:relative;padding-bottom:28px}
.hk-timeline .nokta:last-child{padding-bottom:0}
.hk-timeline .nokta::before{
    content:'';position:absolute;left:-30px;top:5px;width:14px;height:14px;border-radius:50%;
    background:#fff;border:3px solid #ea580c;box-shadow:0 0 0 4px rgba(234,88,12,.12);
}
.hk-timeline .nokta.aktif::before{background:#ea580c}
.hk-timeline .yil{font-family:var(--font-display);font-size:1.15rem;font-weight:800;color:#0f172a;letter-spacing:-.3px}
.hk-timeline .baslik{font-size:.92rem;color:#475569;margin-top:4px;line-height:1.55}

/* ─── Misyon-Vizyon-Değerler — 3 Pillar ─── */
.hk-pillars{padding:90px 0;background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%)}
.hk-pillars .baslik-bolum{text-align:center;max-width:680px;margin:0 auto 56px}
.hk-pillars .etiket{
    display:inline-block;padding:6px 14px;background:#fff;color:#ea580c;
    border:1px solid #fed7aa;border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;
}
.hk-pillars h2{
    font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.4rem);
    font-weight:800;color:#0f172a;letter-spacing:-.4px;
}
.hk-pillars .ust-aciklama{color:#64748b;font-size:1rem;margin-top:10px}
.hk-pillars .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.hk-pillar{
    background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:34px 28px;
    position:relative;transition:.3s;overflow:hidden;
}
.hk-pillar:hover{transform:translateY(-4px);box-shadow:0 14px 40px rgba(15,23,42,.08);border-color:#fed7aa}
.hk-pillar .num{
    position:absolute;top:16px;right:20px;font-family:var(--font-display);
    font-size:3.6rem;font-weight:800;color:#fff7ed;line-height:1;letter-spacing:-2px;
}
.hk-pillar:nth-child(2) .num{color:#fef3c7}
.hk-pillar:nth-child(3) .num{color:#dbeafe}
.hk-pillar .ico{
    width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#ff6b00,#ea580c);
    color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:18px;position:relative;z-index:1;
}
.hk-pillar:nth-child(2) .ico{background:linear-gradient(135deg,#f59e0b,#d97706)}
.hk-pillar:nth-child(3) .ico{background:linear-gradient(135deg,#0284c7,#0369a1)}
.hk-pillar h3{font-family:var(--font-display);font-size:1.3rem;font-weight:800;color:#0f172a;margin-bottom:10px;position:relative;z-index:1}
.hk-pillar p{color:#475569;font-size:.95rem;line-height:1.65;position:relative;z-index:1}
.hk-pillar .liste{margin-top:16px;display:flex;flex-direction:column;gap:8px;position:relative;z-index:1}
.hk-pillar .liste .madde{display:flex;align-items:flex-start;gap:10px;font-size:.88rem;color:#334155}
.hk-pillar .liste .madde i{color:#ea580c;font-size:.85rem;margin-top:4px;flex-shrink:0}
.hk-pillar:nth-child(2) .liste .madde i{color:#d97706}
.hk-pillar:nth-child(3) .liste .madde i{color:#0369a1}

/* ─── Çalışma Sürecimiz — Numaralı Süreç ─── */
.hk-process{padding:90px 0;background:#fff}
.hk-process .baslik-bolum{text-align:center;max-width:680px;margin:0 auto 56px}
.hk-process .etiket{
    display:inline-block;padding:6px 14px;background:#0f172a;color:#fdba74;
    border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;
}
.hk-process h2{font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.4rem);font-weight:800;color:#0f172a;letter-spacing:-.4px}
.hk-process .ust-aciklama{color:#64748b;font-size:1rem;margin-top:10px}
.hk-process .adimlar{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;position:relative}
.hk-process .adimlar::before{
    content:'';position:absolute;left:8%;right:8%;top:48px;height:2px;
    background:linear-gradient(to right,#ea580c,#f59e0b 50%,rgba(245,158,11,.2));
    z-index:0;
}
.hk-adim{background:#fff;text-align:center;position:relative;z-index:1;padding:0 8px}
.hk-adim .daire{
    width:96px;height:96px;border-radius:50%;
    background:#fff;border:3px solid #ea580c;
    display:flex;align-items:center;justify-content:center;
    font-family:var(--font-display);font-size:1.8rem;font-weight:800;color:#ea580c;
    margin:0 auto 22px;position:relative;
    box-shadow:0 8px 24px rgba(234,88,12,.15);
}
.hk-adim:nth-child(2) .daire{border-color:#f59e0b;color:#f59e0b;box-shadow:0 8px 24px rgba(245,158,11,.18)}
.hk-adim:nth-child(3) .daire{border-color:#0284c7;color:#0284c7;box-shadow:0 8px 24px rgba(2,132,199,.15)}
.hk-adim:nth-child(4) .daire{border-color:#16a34a;color:#16a34a;box-shadow:0 8px 24px rgba(22,163,74,.15)}
.hk-adim .ust-ikon{
    position:absolute;top:-8px;right:-8px;width:32px;height:32px;border-radius:50%;
    background:#fff;border:2px solid #fed7aa;display:flex;align-items:center;justify-content:center;font-size:.8rem;color:#ea580c;
}
.hk-adim:nth-child(2) .ust-ikon{border-color:#fcd34d;color:#d97706}
.hk-adim:nth-child(3) .ust-ikon{border-color:#bae6fd;color:#0369a1}
.hk-adim:nth-child(4) .ust-ikon{border-color:#86efac;color:#15803d}
.hk-adim h4{font-family:var(--font-display);font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:8px}
.hk-adim p{color:#64748b;font-size:.88rem;line-height:1.6}

/* ─── Garanti Vaadi — Lacivert Big Block ─── */
.hk-promise{padding:80px 0;background:#0f172a;color:#fff;position:relative;overflow:hidden}
.hk-promise::before{
    content:'';position:absolute;top:-30%;right:-10%;width:480px;height:480px;
    background:radial-gradient(circle,rgba(245,158,11,.15) 0%,transparent 60%);border-radius:50%;
}
.hk-promise .container{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1.2fr;gap:60px;align-items:center}
.hk-promise .rozet{
    width:200px;height:200px;border-radius:50%;
    background:linear-gradient(135deg,#f59e0b 0%,#ea580c 100%);
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:#fff;text-align:center;margin:0 auto;
    box-shadow:0 20px 60px rgba(245,158,11,.4),inset 0 -8px 0 rgba(0,0,0,.1),inset 0 4px 0 rgba(255,255,255,.15);
    position:relative;
}
.hk-promise .rozet::before{
    content:'';position:absolute;inset:-6px;border-radius:50%;
    border:2px dashed rgba(245,158,11,.4);
}
.hk-promise .rozet i{font-size:2.4rem;margin-bottom:6px}
.hk-promise .rozet .yil{font-family:var(--font-display);font-size:2.4rem;font-weight:800;line-height:1;letter-spacing:-1px}
.hk-promise .rozet .lbl{font-size:.78rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-top:4px;opacity:.95}
.hk-promise .etiket{
    display:inline-block;padding:6px 14px;background:rgba(245,158,11,.15);color:#fdba74;
    border:1px solid rgba(245,158,11,.3);border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:14px;
}
.hk-promise h2{
    font-family:var(--font-display);font-size:clamp(1.6rem,2.6vw,2.2rem);
    font-weight:800;line-height:1.2;margin-bottom:14px;letter-spacing:-.3px;
}
.hk-promise h2 strong{color:#fdba74}
.hk-promise .lead{color:#cbd5e1;font-size:1rem;line-height:1.65;margin-bottom:24px}
.hk-promise .madeleler{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.hk-promise .madeleler .item{display:flex;align-items:flex-start;gap:12px;font-size:.92rem}
.hk-promise .madeleler .item i{
    width:32px;height:32px;border-radius:8px;background:rgba(245,158,11,.15);
    color:#fdba74;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;
}
.hk-promise .madeleler .item strong{display:block;color:#fff;margin-bottom:2px}
.hk-promise .madeleler .item span{color:#94a3b8;font-size:.85rem;line-height:1.5}

/* ─── Hizmet Bölgeleri ─── */
.hk-region{padding:80px 0;background:#fff}
.hk-region .baslik-bolum{text-align:center;max-width:680px;margin:0 auto 40px}
.hk-region .etiket{
    display:inline-block;padding:6px 14px;background:#f0f9ff;color:#0369a1;
    border:1px solid #bae6fd;border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;
}
.hk-region h2{font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.4rem);font-weight:800;color:#0f172a;letter-spacing:-.4px}
.hk-region .ust-aciklama{color:#64748b;font-size:1rem;margin-top:10px;margin-bottom:0}
.hk-region .pill-grid{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;max-width:920px;margin:0 auto}
.hk-region .pill{
    background:#f8fafc;border:1px solid #e2e8f0;color:#334155;
    padding:9px 16px;border-radius:999px;font-size:.88rem;font-weight:600;transition:.2s;cursor:default;
}
.hk-region .pill:hover{background:#fff7ed;border-color:#fed7aa;color:#ea580c;transform:translateY(-1px)}
.hk-region .pill.merkez{background:#fff7ed;border-color:#fed7aa;color:#ea580c;font-weight:700}

/* ─── Müşteri Yorumları ─── */
.hk-yorumlar{padding:80px 0;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
.hk-yorumlar .baslik-bolum{text-align:center;max-width:680px;margin:0 auto 50px}
.hk-yorumlar .etiket{
    display:inline-block;padding:6px 14px;background:#fef3c7;color:#92400e;
    border:1px solid #fcd34d;border-radius:999px;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;
}
.hk-yorumlar h2{font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.4rem);font-weight:800;color:#0f172a;letter-spacing:-.4px}
.hk-yorumlar .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.hk-yorum{
    background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:28px 26px;
    position:relative;transition:.3s;
}
.hk-yorum:hover{transform:translateY(-3px);box-shadow:0 14px 36px rgba(15,23,42,.08);border-color:#fed7aa}
.hk-yorum .yildiz{color:#f59e0b;font-size:1rem;margin-bottom:16px;display:flex;gap:2px}
.hk-yorum .metin{color:#334155;font-size:.95rem;line-height:1.7;margin-bottom:24px;font-style:italic}
.hk-yorum .metin::before{content:'"';font-family:Georgia,serif;font-size:3.5rem;line-height:0;color:#fed7aa;display:block;margin-bottom:8px}
.hk-yorum .kisi{display:flex;align-items:center;gap:12px;padding-top:18px;border-top:1px solid #e2e8f0}
.hk-yorum .kisi .avatar{
    width:44px;height:44px;border-radius:50%;
    background:linear-gradient(135deg,#ff6b00,#ea580c);color:#fff;
    display:flex;align-items:center;justify-content:center;font-weight:800;font-family:var(--font-display);font-size:1rem;flex-shrink:0;
}
.hk-yorum .kisi .ad{font-weight:700;color:#0f172a;font-size:.95rem}
.hk-yorum .kisi .konum{font-size:.82rem;color:#64748b;margin-top:2px}

/* ─── Stats Band — Animated Counter ─── */
.hk-stats{padding:60px 0;background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff}
.hk-stats .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:30px;text-align:center}
.hk-stats .stat .num{
    font-family:var(--font-display);font-size:clamp(2.4rem,4vw,3.4rem);
    font-weight:800;line-height:1;background:linear-gradient(135deg,#fdba74,#f97316);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-1px;
}
.hk-stats .stat .lbl{color:#94a3b8;font-size:.92rem;margin-top:10px;letter-spacing:.5px}

/* ─── CTA Bölüm — Premium ─── */
.hk-cta{padding:80px 0;background:#fff}
.hk-cta .kart{
    background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);
    border-radius:24px;padding:60px;text-align:center;position:relative;overflow:hidden;color:#fff;
    box-shadow:0 30px 80px rgba(15,23,42,.18);
}
.hk-cta .kart::before{
    content:'';position:absolute;top:-30%;right:-10%;width:500px;height:500px;
    background:radial-gradient(circle,rgba(255,107,0,.18) 0%,transparent 60%);border-radius:50%;
}
.hk-cta .kart::after{
    content:'';position:absolute;bottom:-30%;left:-5%;width:400px;height:400px;
    background:radial-gradient(circle,rgba(245,158,11,.10) 0%,transparent 60%);border-radius:50%;
}
.hk-cta .ic{position:relative;z-index:1;max-width:600px;margin:0 auto}
.hk-cta h2{font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.6rem);font-weight:800;line-height:1.15;margin-bottom:14px;letter-spacing:-.4px}
.hk-cta h2 strong{color:#fdba74}
.hk-cta .lead{color:#cbd5e1;font-size:1.05rem;line-height:1.65;margin-bottom:30px}
.hk-cta .ctas{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.hk-cta .btn-amber{
    background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;
    padding:15px 30px;border-radius:12px;font-weight:700;font-size:1rem;
    text-decoration:none;display:inline-flex;align-items:center;gap:10px;
    box-shadow:0 10px 28px rgba(234,88,12,.4);transition:.25s;
}
.hk-cta .btn-amber:hover{transform:translateY(-2px);box-shadow:0 16px 36px rgba(234,88,12,.55)}
.hk-cta .btn-tel{
    background:rgba(255,255,255,.06);color:#fff;border:1px solid rgba(255,255,255,.18);
    padding:15px 26px;border-radius:12px;font-weight:600;font-size:1rem;
    text-decoration:none;display:inline-flex;align-items:center;gap:10px;transition:.25s;
}
.hk-cta .btn-tel:hover{background:rgba(255,255,255,.1)}

/* ─── Responsive ─── */
@media (max-width: 920px){
    .hk-hero{padding:60px 0 70px}
    .hk-hero .container{grid-template-columns:1fr;gap:36px}
    .hk-hero-card{order:-1}
    .hk-story .grid{grid-template-columns:1fr;gap:36px}
    .hk-promise .container{grid-template-columns:1fr;gap:30px;text-align:center}
    .hk-promise .madeleler{grid-template-columns:1fr;text-align:left}
    .hk-pillars .grid{grid-template-columns:1fr;gap:18px}
    .hk-process .adimlar{grid-template-columns:repeat(2,1fr);gap:36px 16px}
    .hk-process .adimlar::before{display:none}
    .hk-yorumlar .grid{grid-template-columns:1fr;gap:18px}
    .hk-stats .grid{grid-template-columns:repeat(2,1fr);gap:32px 16px}
    .hk-cta .kart{padding:40px 24px}
    .hk-story{padding:60px 0}
    .hk-pillars,.hk-process,.hk-promise,.hk-region,.hk-yorumlar,.hk-cta{padding:60px 0}
}
@media (max-width: 520px){
    .hk-process .adimlar{grid-template-columns:1fr}
    .hk-stats .grid{grid-template-columns:repeat(2,1fr)}
}
</style>

<!-- ─── HERO ─── -->
<section class="hk-hero">
    <div class="container">
        <div>
            <div class="badge">
                <i class="fas fa-shield-halved"></i>
                10+ yıl · 2.500+ proje · İzmir
            </div>
            <h1>İzmir'in güvendiği <strong>doğalgaz</strong> ve ısıtma çözüm ortağı</h1>
            <p class="lead">
                Demirdöküm yetkili bayisi olarak konuttan ticari yapıya, baştan sona profesyonel
                tesisat. Söz verilen tarihte, sigortalı işçilikle, sürpriz fiyat olmadan.
            </p>
            <div class="ctas">
                <a href="<?= SITE_URL ?>/iletisim" class="btn-amber">
                    <i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et
                </a>
                <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="btn-ghost">
                    <i class="fas fa-phone"></i> <?= e($tel) ?>
                </a>
            </div>
        </div>

        <div class="hk-hero-card">
            <div class="ust">
                <div class="ico"><i class="fas fa-fire-flame-curved"></i></div>
                <div>
                    <div class="baslik">Bu ay teslim edilen</div>
                    <div class="alt">İzmir genelinde aktif projeler</div>
                </div>
            </div>
            <div class="satir">
                <div class="blok"><div class="num">42</div><div class="lbl">DOĞALGAZ TESİSATI</div></div>
                <div class="blok"><div class="num">28</div><div class="lbl">KOMBİ MONTAJI</div></div>
                <div class="blok"><div class="num">19</div><div class="lbl">KLİMA KURULUMU</div></div>
                <div class="blok"><div class="num">7</div><div class="lbl">TOPLU PROJE</div></div>
            </div>
        </div>
    </div>
</section>

<!-- ─── BİZİM HİKAYEMİZ ─── -->
<section class="hk-story">
    <div class="container">
        <div class="grid">
            <div>
                <span class="etiket">Bizim Hikayemiz</span>
                <h2>2014'te küçük bir atölyeden, <strong>İzmir'in güvenilir markasına</strong>.</h2>
                <p class="metin">
                    Azra Doğalgaz, 2014'te aile işletmesi olarak doğdu. İlk yıllarda Buca ve
                    çevresinde konut tesisatlarıyla başlayan yolculuğumuz, dürüst fiyatlandırma
                    ve verilen söze sadakat ile büyüdü.
                </p>
                <p class="metin">
                    Bugün 6 kişilik teknik kadromuz ve <strong>15+ yetkili bayilik</strong> ile
                    İzmir merkez ve tüm ilçelerinde hizmet veriyoruz. Her işin başında ücretsiz
                    keşif, yazılı teklif ve şeffaf süreç bizim imzamızdır.
                </p>
                <div class="alinti">
                    "Bir tesisatın ömrü 25 yıldır — biz, bugünkü işin 25 yıl sonra da
                    kendinden utanmayacak şekilde yapılması için varız."
                    <span>— Azra Doğalgaz ekibi</span>
                </div>
            </div>

            <div class="hk-timeline">
                <div class="nokta">
                    <div class="yil">2014 · Kuruluş</div>
                    <div class="baslik">Buca'da küçük bir atölyede ilk konut tesisatları, sıfır müşteri kaybı</div>
                </div>
                <div class="nokta">
                    <div class="yil">2018 · Demirdöküm Yetkili Bayilik</div>
                    <div class="baslik">İlk resmi yetki — A++ kombi serisinde uzmanlaşma</div>
                </div>
                <div class="nokta">
                    <div class="yil">2021 · Klima ve Sıhhi Tesisat Ekibi</div>
                    <div class="baslik">Hizmet alanı genişlemesi — yerden ısıtma, mekanik tesisat</div>
                </div>
                <div class="nokta">
                    <div class="yil">2024 · Apartman Toplu Projeleri</div>
                    <div class="baslik">Bornova'da 48 daireli site projesinin tamamlanması</div>
                </div>
                <div class="nokta aktif">
                    <div class="yil">2026 · Bugün</div>
                    <div class="baslik">2.500+ teslim edilmiş proje · 15+ yetkili marka · 7/24 destek</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── STATS BAND ─── -->
<section class="hk-stats">
    <div class="container">
        <div class="grid">
            <div class="stat"><div class="num">2.500+</div><div class="lbl">Teslim Edilen Proje</div></div>
            <div class="stat"><div class="num">10+</div><div class="lbl">Yıl Tecrübe</div></div>
            <div class="stat"><div class="num">15+</div><div class="lbl">Yetkili Marka</div></div>
            <div class="stat"><div class="num">2 yıl</div><div class="lbl">İşçilik Garantisi</div></div>
        </div>
    </div>
</section>

<!-- ─── MİSYON-VİZYON-DEĞERLER ─── -->
<section class="hk-pillars">
    <div class="container">
        <div class="baslik-bolum">
            <span class="etiket">Bizi Biz Yapan</span>
            <h2>Üç temel üzerine kurulu.</h2>
            <p class="ust-aciklama">Misyon, vizyon ve değerlerimiz — markete giderken bile yanımızda.</p>
        </div>

        <div class="grid">
            <div class="hk-pillar">
                <div class="num">01</div>
                <div class="ico"><i class="fas fa-bullseye"></i></div>
                <h3>Misyonumuz</h3>
                <p>Müşterilerimize güvenilir, garantili ve şeffaf tesisat hizmeti sunmak.</p>
                <div class="liste">
                    <div class="madde"><i class="fas fa-check"></i><span>Yazılı sözleşme, sürpriz maliyet yok</span></div>
                    <div class="madde"><i class="fas fa-check"></i><span>Söz verilen tarihte teslim</span></div>
                    <div class="madde"><i class="fas fa-check"></i><span>Sigortalı, garantili işçilik</span></div>
                </div>
            </div>

            <div class="hk-pillar">
                <div class="num">02</div>
                <div class="ico"><i class="fas fa-rocket"></i></div>
                <h3>Vizyonumuz</h3>
                <p>İzmir'in en güvenilir, teknolojiyi en iyi takip eden tesisat firması olmak.</p>
                <div class="liste">
                    <div class="madde"><i class="fas fa-check"></i><span>Yenilenebilir enerji odaklı</span></div>
                    <div class="madde"><i class="fas fa-check"></i><span>Isı pompası, akıllı ev sistemleri</span></div>
                    <div class="madde"><i class="fas fa-check"></i><span>Yüksek verimli yoğuşmalı kombiler</span></div>
                </div>
            </div>

            <div class="hk-pillar">
                <div class="num">03</div>
                <div class="ico"><i class="fas fa-heart"></i></div>
                <h3>Değerlerimiz</h3>
                <p>Müşteri güveni, kalite tutkusu, mevzuata tam uyum.</p>
                <div class="liste">
                    <div class="madde"><i class="fas fa-check"></i><span>Güvenilirlik ve şeffaflık</span></div>
                    <div class="madde"><i class="fas fa-check"></i><span>Orijinal ürün, garantili kurulum</span></div>
                    <div class="madde"><i class="fas fa-check"></i><span>7/24 ulaşılabilir destek</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── ÇALIŞMA SÜRECİMİZ ─── -->
<section class="hk-process">
    <div class="container">
        <div class="baslik-bolum">
            <span class="etiket">Çalışma Sürecimiz</span>
            <h2>Dört adımda — keşiften garantiye.</h2>
            <p class="ust-aciklama">Her aşamada size raporluyoruz. Sürpriz yok, gizli madde yok.</p>
        </div>

        <div class="adimlar">
            <div class="hk-adim">
                <div class="daire">01<div class="ust-ikon"><i class="fas fa-search-location"></i></div></div>
                <h4>Ücretsiz Keşif</h4>
                <p>Adresinize geliyoruz, ihtiyacı yerinde analiz ediyoruz, fotoğraf ve ölçüleri alıyoruz.</p>
            </div>
            <div class="hk-adim">
                <div class="daire">02<div class="ust-ikon"><i class="fas fa-file-invoice"></i></div></div>
                <h4>Yazılı Teklif</h4>
                <p>Detaylı kalem listesi, malzeme markaları, işçilik ücreti, süreler — net ve sürpriz yok.</p>
            </div>
            <div class="hk-adim">
                <div class="daire">03<div class="ust-ikon"><i class="fas fa-tools"></i></div></div>
                <h4>Profesyonel Uygulama</h4>
                <p>İSG sertifikalı ekip, sigortalı uygulama, her aşamada fotoğraf raporu, evi temiz teslim.</p>
            </div>
            <div class="hk-adim">
                <div class="daire">04<div class="ust-ikon"><i class="fas fa-medal"></i></div></div>
                <h4>Garanti ve Destek</h4>
                <p>2 yıl işçilik garantisi, 7/24 ulaşılabilir teknik destek, periyodik bakım hatırlatma.</p>
            </div>
        </div>
    </div>
</section>

<!-- ─── GARANTI VAADI ─── -->
<section class="hk-promise">
    <div class="container">
        <div>
            <div class="rozet">
                <i class="fas fa-shield-halved"></i>
                <div class="yil">2 Yıl</div>
                <div class="lbl">İşçilik Garantisi</div>
            </div>
        </div>
        <div>
            <span class="etiket">Garanti Vaadimiz</span>
            <h2>Söz verdiklerimiz, <strong>yazıyla.</strong></h2>
            <p class="lead">
                Sözlü vaat değil, sözleşmeli güvence. Aşağıdaki dört maddeyi her müşterimize aynı şekilde uyguluyoruz.
            </p>
            <div class="madeleler">
                <div class="item">
                    <i class="fas fa-file-signature"></i>
                    <div>
                        <strong>Yazılı Sözleşme</strong>
                        <span>Her iş için detaylı sözleşme, kalem kalem işlenen maddeler.</span>
                    </div>
                </div>
                <div class="item">
                    <i class="fas fa-shield-halved"></i>
                    <div>
                        <strong>2 Yıl İşçilik Garantisi</strong>
                        <span>İşçilikten kaynaklı arızalarda ücretsiz müdahale.</span>
                    </div>
                </div>
                <div class="item">
                    <i class="fas fa-umbrella"></i>
                    <div>
                        <strong>Sigortalı Uygulama</strong>
                        <span>İş sağlığı ve mesuliyet sigortası, üçüncü taraf güvence.</span>
                    </div>
                </div>
                <div class="item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <strong>7/24 Destek Hattı</strong>
                        <span>Acil durumlarda gece-gündüz, hafta sonu dahil ulaşılabilirlik.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── HİZMET BÖLGELERİ ─── -->
<section class="hk-region">
    <div class="container">
        <div class="baslik-bolum">
            <span class="etiket">Hizmet Bölgelerimiz</span>
            <h2>İzmir'in tamamında, yanınızdayız.</h2>
            <p class="ust-aciklama">Merkez ve 19 ilçede ücretsiz keşif, ekibimiz aynı gün adresinizde.</p>
        </div>
        <div class="pill-grid">
            <span class="pill merkez"><i class="fas fa-map-marker-alt" style="font-size:.78rem;margin-right:4px"></i> Buca (Merkez)</span>
            <span class="pill">Bornova</span>
            <span class="pill">Karşıyaka</span>
            <span class="pill">Konak</span>
            <span class="pill">Çiğli</span>
            <span class="pill">Gaziemir</span>
            <span class="pill">Bayraklı</span>
            <span class="pill">Karabağlar</span>
            <span class="pill">Balçova</span>
            <span class="pill">Narlıdere</span>
            <span class="pill">Güzelbahçe</span>
            <span class="pill">Urla</span>
            <span class="pill">Çeşme</span>
            <span class="pill">Menemen</span>
            <span class="pill">Aliağa</span>
            <span class="pill">Foça</span>
            <span class="pill">Selçuk</span>
            <span class="pill">Torbalı</span>
            <span class="pill">Menderes</span>
            <span class="pill">Kemalpaşa</span>
        </div>
    </div>
</section>

<!-- ─── MÜŞTERİ YORUMLARI ─── -->
<section class="hk-yorumlar">
    <div class="container">
        <div class="baslik-bolum">
            <span class="etiket">Müşterilerimiz Diyor ki</span>
            <h2>Sözümüze sadık kalıyoruz.</h2>
        </div>

        <div class="grid">
            <div class="hk-yorum">
                <div class="yildiz">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="metin">
                    Konuşulan tarihte, konuşulan fiyatta tesisat tamamlandı. Keşfe geldiğinde
                    her detayı tek tek anlattılar, sorularıma sabırla cevap verdiler. 3+1 daire,
                    pürüzsüz teslim.
                </p>
                <div class="kisi">
                    <div class="avatar">MK</div>
                    <div>
                        <div class="ad">Mehmet K.</div>
                        <div class="konum">Bornova · Doğalgaz tesisatı</div>
                    </div>
                </div>
            </div>

            <div class="hk-yorum">
                <div class="yildiz">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="metin">
                    Eski kombim ani arıza yaptı, akşam aradım, ertesi sabah teknisyen kapımdaydı.
                    Yeni kombi montajı aynı gün tamamlandı. Annem bile "bu işi böyle yapacaksın"
                    dedi.
                </p>
                <div class="kisi">
                    <div class="avatar">AY</div>
                    <div>
                        <div class="ad">Ayşe Y.</div>
                        <div class="konum">Karşıyaka · Demirdöküm kombi</div>
                    </div>
                </div>
            </div>

            <div class="hk-yorum">
                <div class="yildiz">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="metin">
                    Apartmanımızın toplu doğalgaz dönüşümünü yaptılar. 18 daire, hiç aksilik
                    yaşamadık. Yöneticilik döneminin en pürüzsüz işi oldu — tavsiye ediyorum.
                </p>
                <div class="kisi">
                    <div class="avatar">MA</div>
                    <div>
                        <div class="ad">Mustafa A.</div>
                        <div class="konum">Buca · Apartman toplu dönüşüm</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── CTA ─── -->
<section class="hk-cta">
    <div class="container">
        <div class="kart">
            <div class="ic">
                <h2>Tanışmak ister misiniz? <strong>Keşif ücretsiz.</strong></h2>
                <p class="lead">
                    Adresinize gelelim, ihtiyacınızı yerinde inceleyelim, en uygun çözümü
                    yazılı teklif olarak sunalım — hiçbir bağlayıcılık yok.
                </p>
                <div class="ctas">
                    <a href="<?= SITE_URL ?>/iletisim" class="btn-amber">
                        <i class="fas fa-clipboard-check"></i> Ücretsiz Keşif Talep Et
                    </a>
                    <a href="tel:<?= e(preg_replace('/\s/','',$tel)) ?>" class="btn-tel">
                        <i class="fas fa-phone"></i> <?= e($tel) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
