<?php
/**
 * Migration motoru
 *
 * migrations/*.sql dosyalarını sırayla çalıştırır, uygulanmışları
 * `migrasyonlar` tablosunda izler. Idempotent: aynı dosya iki kez uygulanmaz.
 */

class Migrator
{
    public string $kok;
    public string $klasor;

    public function __construct(string $kok)
    {
        $this->kok = rtrim($kok, '/');
        $this->klasor = $this->kok . '/migrations';
    }

    /** Tracking tablosunu garanti et. */
    public function tablo_garanti(): bool
    {
        try {
            db()->exec("
                CREATE TABLE IF NOT EXISTS migrasyonlar (
                    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    dosya           VARCHAR(160) NOT NULL UNIQUE,
                    sha1            VARCHAR(40) NOT NULL,
                    uygulama_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
                    sure_ms         INT UNSIGNED DEFAULT 0,
                    sonuc           ENUM('ok','hata') DEFAULT 'ok',
                    hata_mesaji     TEXT DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            return true;
        } catch (Throwable $e) {
            error_log('[Migrator] tablo_garanti: ' . $e->getMessage());
            return false;
        }
    }

    /** Uygulanmış migration dosyalarının listesi. */
    public function uygulanmislar(): array
    {
        $this->tablo_garanti();
        try {
            $rows = db_all("SELECT dosya, sha1, uygulama_tarihi, sonuc FROM migrasyonlar ORDER BY id ASC");
            $out = [];
            foreach ($rows as $r) $out[$r['dosya']] = $r;
            return $out;
        } catch (Throwable $e) {
            return [];
        }
    }

    /** migrations/ klasöründeki tüm .sql dosyaları (alfabetik sıralı). */
    public function tum_dosyalar(): array
    {
        if (!is_dir($this->klasor)) return [];
        $list = glob($this->klasor . '/*.sql') ?: [];
        sort($list, SORT_NATURAL);
        return array_map('basename', $list);
    }

    /** Bekleyen migration listesi (henüz uygulanmamış). */
    public function bekleyenler(): array
    {
        $tum = $this->tum_dosyalar();
        $ugl = $this->uygulanmislar();
        $bekleyen = [];
        foreach ($tum as $d) {
            if (!isset($ugl[$d]) || ($ugl[$d]['sonuc'] ?? '') === 'hata') {
                $bekleyen[] = $d;
            }
        }
        return $bekleyen;
    }

    /** Tek bir SQL dosyasını çalıştır. */
    public function uygula(string $dosya): array
    {
        $this->tablo_garanti();
        $tam = $this->klasor . '/' . basename($dosya);
        if (!is_file($tam)) return ['ok'=>false, 'hata'=>"Dosya yok: $dosya"];

        $sql = file_get_contents($tam);
        if ($sql === false) return ['ok'=>false, 'hata'=>'Dosya okunamadı'];

        $sha1 = sha1($sql);
        $bas = microtime(true);

        try {
            // SQL'i ; ile böl ve sırayla çalıştır (multi-statement güvenliği için)
            $stmts = $this->sql_bol($sql);
            $sayim = 0;
            foreach ($stmts as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '') continue;
                db()->exec($stmt);
                $sayim++;
            }

            $sure = (int)round((microtime(true) - $bas) * 1000);

            // Kayıt — daha önce hata aldıysa update et
            db_run("INSERT INTO migrasyonlar (dosya, sha1, sure_ms, sonuc, hata_mesaji)
                    VALUES (?,?,?,'ok',NULL)
                    ON DUPLICATE KEY UPDATE sha1=VALUES(sha1), uygulama_tarihi=CURRENT_TIMESTAMP, sure_ms=VALUES(sure_ms), sonuc='ok', hata_mesaji=NULL",
                [basename($dosya), $sha1, $sure]);

            return ['ok'=>true, 'dosya'=>basename($dosya), 'stmts'=>$sayim, 'sure_ms'=>$sure];

        } catch (Throwable $e) {
            $sure = (int)round((microtime(true) - $bas) * 1000);
            $msg  = $e->getMessage();
            try {
                db_run("INSERT INTO migrasyonlar (dosya, sha1, sure_ms, sonuc, hata_mesaji)
                        VALUES (?,?,?,'hata',?)
                        ON DUPLICATE KEY UPDATE sha1=VALUES(sha1), uygulama_tarihi=CURRENT_TIMESTAMP, sure_ms=VALUES(sure_ms), sonuc='hata', hata_mesaji=VALUES(hata_mesaji)",
                    [basename($dosya), $sha1, $sure, $msg]);
            } catch (Throwable $e2) {}
            error_log("[Migrator] $dosya başarısız: $msg");
            return ['ok'=>false, 'dosya'=>basename($dosya), 'hata'=>$msg];
        }
    }

    /** Tüm bekleyenleri sırayla uygula. */
    public function bekleyenleri_uygula(): array
    {
        $bekleyen = $this->bekleyenler();
        if (!$bekleyen) return ['ok'=>true, 'uygulananlar'=>[], 'mesaj'=>'Bekleyen migration yok.'];

        $sonuc = ['ok'=>true, 'uygulananlar'=>[], 'hatalar'=>[]];
        foreach ($bekleyen as $d) {
            $r = $this->uygula($d);
            if ($r['ok']) {
                $sonuc['uygulananlar'][] = $r;
            } else {
                $sonuc['ok'] = false;
                $sonuc['hatalar'][] = $r;
                // İlk hatada dur — sıraya bağımlı olabilir
                break;
            }
        }
        return $sonuc;
    }

    /** Otomatik tetikleme — bekleyenlerin olup olmadığını cache'li kontrol eder. */
    public function otomatik_uygula_lazim_mi(): bool
    {
        // mtime tabanlı sentinel: migrations/ son değişmemişse skip
        $sentinel = $this->kok . '/assets/uploads/_migration_son_kontrol.txt';
        $klasor_mtime = is_dir($this->klasor) ? filemtime($this->klasor) : 0;
        $son_kontrol  = is_file($sentinel) ? (int)file_get_contents($sentinel) : 0;
        if ($klasor_mtime > 0 && $klasor_mtime === $son_kontrol) {
            return false; // Klasör değişmemiş, kontrol gereksiz
        }
        return true;
    }

    /** Bekleyenleri uyguladıktan sonra sentinel'i güncelle. */
    public function sentinel_kaydet(): void
    {
        $sentinel = $this->kok . '/assets/uploads/_migration_son_kontrol.txt';
        $klasor_mtime = is_dir($this->klasor) ? filemtime($this->klasor) : 0;
        @file_put_contents($sentinel, (string)$klasor_mtime);
    }

    /**
     * Ham SQL'i statement'lara böl. Basit ; tabanlı parser, comment'leri yok sayar.
     * (-- ile başlayan satırlar ve /* * / blokları çıkarılır.)
     */
    private function sql_bol(string $sql): array
    {
        // Yorumları kaldır
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        // ; ile böl
        $parts = explode(';', $sql);
        return array_filter(array_map('trim', $parts));
    }
}
