<?php
/**
 * Azra Doğalgaz — Güncelleyici Çekirdek
 *
 * GitHub Releases tabanlı + manuel ZIP destekli güncelleme sistemi.
 * Private repo desteği için API URL + Authorization token kullanır.
 *
 * Çalışma Mantığı:
 * 1) Manifest.json zorunlu — ZIP içinde olmalı.
 * 2) Manifest'teki "files" listesindeki dosyalar yazılır; liste yoksa tüm
 *    ZIP içeriği yazılır (config.php ve korumalı yollar hariç).
 * 3) Her güncellemeden ÖNCE etkilenen dosyaların yedeği alınır.
 * 4) Tek tıkla yedekten geri dönüş yapılabilir.
 * 5) Path traversal, korumalı dosyalar, dış yol yazımı engellenir.
 */
declare(strict_types=1);

class Guncelleyici
{
    public string $kok;            // Site kök dizini
    public string $yedek_dir;       // Yedek ZIP'lerinin tutulduğu dizin
    public string $temp_dir;        // İndirilen geçici ZIP'ler
    public string $manifest_yolu;   // /manifest.json yolu
    public int    $max_yedek = 15;  // Tutulacak en fazla yedek sayısı

    /** Üzerine yazılması ASLA güvenli olmayan göreli yollar (kök bazlı). */
    public array $korumali_yollar = [
        'config.php',
        'assets/uploads/',
    ];

    /**
     * Karşılaştırma sırasında hariç tutulacak dosyalar.
     * v1.12.26: manifest.json her release'de değişir — kendisi ile kıyaslamak
     * her zaman "1 Değişmiş" gösterir. SHA hariç tutulur, sadece sürüm karşılaştırma için kullanılır.
     */
    public array $karsilastirma_haric = [
        'manifest.json',
        // README ve LICENSE gibi statik repo dosyaları da gereksizce flagleniyor
        'README.md',
        'LICENSE',
        '.gitignore',
        '.gitattributes',
    ];

    /** İzinli dosya uzantıları (manifest yoksa filtre uygulanır). */
    public array $izinli_uzantilar = [
        'php','html','htm','css','js','json','txt','md','xml',
        'jpg','jpeg','png','gif','webp','svg','ico',
        'woff','woff2','ttf','eot',
        'sql','htaccess',
        // Dotfile'lar (PHP pathinfo('.gitignore', EXT) === 'gitignore')
        'gitignore','gitattributes','editorconfig','env','sample','example',
    ];

    public function __construct(string $kok)
    {
        $this->kok = rtrim($kok, '/');
        $this->yedek_dir     = $this->kok . '/assets/uploads/_yedekler';
        $this->temp_dir      = $this->kok . '/assets/uploads/_temp';
        $this->manifest_yolu = $this->kok . '/manifest.json';

        foreach ([$this->yedek_dir, $this->temp_dir] as $d) {
            if (!is_dir($d)) @mkdir($d, 0755, true);
            // Erişim engelle
            $h = $d . '/.htaccess';
            if (!file_exists($h)) @file_put_contents($h, "Require all denied\n");
        }
    }

    // --------------------------------------------------------------
    //  MANIFEST
    // --------------------------------------------------------------
    public function manifest_oku(?string $yol = null): array
    {
        $yol = $yol ?: $this->manifest_yolu;
        if (!file_exists($yol)) return [];
        $data = json_decode((string)file_get_contents($yol), true);
        return is_array($data) ? $data : [];
    }

    public function manifest_yaz(array $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) return false;

        // v1.12.26: atomik yazım — temp dosyaya yaz, rename ile yer değiştir.
        // file_put_contents direkt path'e yazınca yetki sorunu olunca tüm yazma sessizce başarısız.
        $temp = $this->manifest_yolu . '.tmp.' . bin2hex(random_bytes(4));
        $bytes = @file_put_contents($temp, $json);
        if ($bytes === false || $bytes === 0) {
            error_log('manifest_yaz: temp yazılamadı → ' . $temp);
            @unlink($temp);
            return false;
        }
        if (!@rename($temp, $this->manifest_yolu)) {
            // rename başarısızsa direkt yazmayı dene
            $bytes2 = @file_put_contents($this->manifest_yolu, $json);
            @unlink($temp);
            if ($bytes2 === false) {
                error_log('manifest_yaz: hedef yazılamadı → ' . $this->manifest_yolu . ' (yetki sorunu olabilir)');
                return false;
            }
        }
        @chmod($this->manifest_yolu, 0644);
        return true;
    }

    public function mevcut_surum(): string
    {
        $manifest = $this->manifest_oku();
        $v = (string)($manifest['version'] ?? '');
        if ($v !== '' && preg_match('/^\d+\.\d+\.\d+/', $v)) {
            return $v;
        }
        // v1.12.26: yerel manifest bozuk veya yoksa ayarlar.son_yuklenen_surum'a bak
        if (function_exists('ayar')) {
            $kayit = (string)ayar('son_yuklenen_surum', '');
            if ($kayit !== '' && preg_match('/^\d+\.\d+\.\d+/', $kayit)) return $kayit;
        }
        return '0.0.0';
    }

    /**
     * v1.12.26: Başarılı bir update'ten sonra ayarlar tablosuna sürüm not düşülür.
     * manifest.json bozuk/silindiği zamanlarda fallback kaynak olur.
     */
    public function surum_kaydet(string $surum): void
    {
        if (!preg_match('/^\d+\.\d+\.\d+/', $surum)) return;
        try {
            if (function_exists('db_run')) {
                db_run(
                    "INSERT INTO ayarlar (anahtar, deger) VALUES ('son_yuklenen_surum', ?)
                     ON DUPLICATE KEY UPDATE deger=VALUES(deger)",
                    [$surum]
                );
                db_run(
                    "INSERT INTO ayarlar (anahtar, deger) VALUES ('son_yukleme_tarihi', ?)
                     ON DUPLICATE KEY UPDATE deger=VALUES(deger)",
                    [date('Y-m-d H:i:s')]
                );
            }
        } catch (Throwable $e) {
            error_log('surum_kaydet hata: ' . $e->getMessage());
        }
    }

    // --------------------------------------------------------------
    //  GITHUB RELEASES — KONTROL
    // --------------------------------------------------------------
    /**
     * GitHub'dan latest release'ı çeker.
     * @return array{ok:bool, version?:string, asset_url?:string, asset_id?:int, asset_name?:string,
     *               size?:int, body?:string, hata?:string, mevcut?:string, yeni_sürüm_var?:bool}
     */
    public function github_kontrol(string $repo, string $token = ''): array
    {
        if (!$repo || !preg_match('~^[\w.\-]+/[\w.\-]+$~', $repo)) {
            return ['ok'=>false, 'hata'=>'Geçersiz repo formatı (kullanici/repo bekleniyor).'];
        }

        $url = "https://api.github.com/repos/$repo/releases/latest";
        [$body, $code] = $this->http_get($url, $token, 'application/vnd.github+json');
        if ($code === 404) {
            return ['ok'=>false, 'hata'=>'Henüz yayınlanmış release yok (404).'];
        }
        if ($code !== 200) {
            $msg = (json_decode((string)$body, true)['message'] ?? '') ?: "GitHub yanıtı: $code";
            return ['ok'=>false, 'hata'=>$msg];
        }
        $rel = json_decode((string)$body, true);
        if (!is_array($rel)) return ['ok'=>false,'hata'=>'GitHub yanıtı çözümlenemedi.'];

        $tag = ltrim((string)($rel['tag_name'] ?? ''), 'v');
        // ZIP asset bul
        $asset = null;
        foreach (($rel['assets'] ?? []) as $a) {
            if (str_ends_with(strtolower((string)$a['name']), '.zip')) { $asset = $a; break; }
        }
        if (!$asset) {
            return ['ok'=>false,'hata'=>"v$tag için ZIP asset bulunamadı."];
        }

        $mevcut = $this->mevcut_surum();
        return [
            'ok'             => true,
            'version'        => $tag,
            'mevcut'         => $mevcut,
            'yeni_sürüm_var' => version_compare($tag, $mevcut, '>'),
            'asset_url'      => (string)$asset['url'],          // API URL (private repo'da çalışan)
            'asset_id'       => (int)$asset['id'],
            'asset_name'     => (string)$asset['name'],
            'size'           => (int)($asset['size'] ?? 0),
            'body'           => (string)($rel['body'] ?? ''),
            'tarih'          => (string)($rel['published_at'] ?? ''),
        ];
    }

    /**
     * GitHub'dan ZIP indir (private repo desteği için API URL + token).
     */
    public function github_indir(string $asset_url, string $token, string $hedef): array
    {
        $ch = curl_init($asset_url);
        $fp = fopen($hedef, 'wb');
        if (!$fp) return ['ok'=>false,'hata'=>'İndirme dosyası açılamadı.'];

        $headers = [
            'Accept: application/octet-stream',
            'User-Agent: AzraDogalgaz-Updater/1.0',
        ];
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;

        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $tamam = curl_exec($ch);
        $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err   = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$tamam || $code !== 200) {
            @unlink($hedef);
            return ['ok'=>false, 'hata'=>"İndirme başarısız (HTTP $code) " . $err];
        }
        // ZIP doğrulama
        $zip = new ZipArchive();
        if ($zip->open($hedef) !== true) {
            @unlink($hedef);
            return ['ok'=>false,'hata'=>'İndirilen dosya geçerli ZIP değil.'];
        }
        $zip->close();
        return ['ok'=>true, 'yol'=>$hedef, 'boyut'=>filesize($hedef)];
    }

    // --------------------------------------------------------------
    //  ZIP UYGULA — Çekirdek
    // --------------------------------------------------------------
    /**
     * Verilen ZIP'i uygula. Önce manifest okur, sonra yedek alır, dosyaları kopyalar.
     */
    public function uygula(string $zip_yolu): array
    {
        $log = [];
        $log[] = "Paket açılıyor: " . basename($zip_yolu);

        $zip = new ZipArchive();
        if ($zip->open($zip_yolu) !== true) {
            return ['ok'=>false,'hata'=>'ZIP açılamadı.','log'=>$log];
        }

        // 1) ZIP içinde manifest.json var mı?
        $manifest_icerik = false;
        for ($i=0; $i<$zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === 'manifest.json' || str_ends_with($name, '/manifest.json')) {
                $manifest_icerik = $zip->getFromIndex($i);
                $manifest_kok = (str_ends_with($name, '/manifest.json'))
                    ? rtrim(dirname($name), '/') . '/'
                    : '';
                break;
            }
        }
        if ($manifest_icerik === false) {
            $zip->close();
            return ['ok'=>false,'hata'=>'ZIP içinde manifest.json bulunamadı. Geçerli güncelleme paketi değil.','log'=>$log];
        }
        $manifest = json_decode((string)$manifest_icerik, true);
        if (!is_array($manifest) || empty($manifest['version'])) {
            $zip->close();
            return ['ok'=>false,'hata'=>'manifest.json geçerli değil veya version alanı eksik.','log'=>$log];
        }
        $log[] = "Manifest okundu — sürüm: " . $manifest['version'];

        // 2) PHP sürüm kontrolü
        if (!empty($manifest['min_php']) && version_compare(PHP_VERSION, (string)$manifest['min_php'], '<')) {
            $zip->close();
            return ['ok'=>false,'hata'=>"Bu güncelleme PHP {$manifest['min_php']}+ gerektiriyor. Mevcut: " . PHP_VERSION,'log'=>$log];
        }

        // 3) Hangi dosyalar yazılacak? Listeyi belirle
        $hedef_dosyalar = [];
        $manifest_kok ??= '';

        if (!empty($manifest['files']) && is_array($manifest['files'])) {
            // Manifest'te listelenmiş — sadece onları al
            foreach ($manifest['files'] as $rel) {
                $rel = trim((string)$rel, '/');
                $zip_path = $manifest_kok . $rel;
                if ($zip->locateName($zip_path) !== false) {
                    $hedef_dosyalar[$rel] = $zip_path;
                }
            }
            $log[] = "Manifest'te " . count($manifest['files']) . " dosya listelendi, " . count($hedef_dosyalar) . " bulundu.";
        } else {
            // Liste yok — tüm ZIP içeriğini al (manifest.json + korumalı yollar hariç)
            for ($i=0; $i<$zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with($name, '/')) continue; // klasör
                if ($name === 'manifest.json') continue;
                if ($manifest_kok && !str_starts_with($name, $manifest_kok)) continue;
                $rel = $manifest_kok ? substr($name, strlen($manifest_kok)) : $name;
                $hedef_dosyalar[$rel] = $name;
            }
            $log[] = "Manifest'te files listesi yok, ZIP içeriği taranıyor: " . count($hedef_dosyalar) . " dosya.";
        }

        if (!$hedef_dosyalar) {
            $zip->close();
            return ['ok'=>false,'hata'=>'Yazılacak dosya bulunamadı.','log'=>$log];
        }

        // 4) Güvenlik filtresi
        $guvenli_dosyalar = [];
        $atlanan = [];
        foreach ($hedef_dosyalar as $rel => $zip_path) {
            $rel = str_replace('\\', '/', $rel);
            // Path traversal
            if (str_contains($rel, '..') || str_starts_with($rel, '/') || preg_match('~^[a-z]:/~i', $rel)) {
                $atlanan[] = $rel . ' (geçersiz yol)';
                continue;
            }
            // Korumalı yol mu?
            $korumali = false;
            foreach ($this->korumali_yollar as $k) {
                if ($rel === $k || str_starts_with($rel, rtrim($k,'/').'/')) { $korumali = true; break; }
            }
            if ($korumali) { $atlanan[] = $rel . ' (korumalı)'; continue; }

            // Uzantı
            $uz = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if ($uz === '' && basename($rel) === '.htaccess') $uz = 'htaccess';
            if ($uz && !in_array($uz, $this->izinli_uzantilar, true)) {
                $atlanan[] = $rel . ' (izinsiz uzantı .' . $uz . ')';
                continue;
            }
            $guvenli_dosyalar[$rel] = $zip_path;
        }
        if ($atlanan) $log[] = "Atlandı: " . count($atlanan) . " dosya — " . implode(', ', array_slice($atlanan, 0, 5)) . (count($atlanan)>5?'…':'');
        if (!$guvenli_dosyalar) {
            $zip->close();
            return ['ok'=>false,'hata'=>'Güvenlik filtresinden geçen dosya yok.','log'=>$log];
        }

        // 5) YEDEK AL — sadece etkilenen dosyaların yedeği
        $eski_surum = $this->mevcut_surum();
        $yedek = $this->yedek_al(array_keys($guvenli_dosyalar), $eski_surum);
        if (!$yedek['ok']) {
            $zip->close();
            return ['ok'=>false, 'hata'=>'Yedek alınamadı: ' . $yedek['hata'], 'log'=>$log];
        }
        $log[] = "✅ Yedek alındı: " . basename($yedek['yol']) . " (" . $this->boyut_format($yedek['boyut']) . ")";

        // 6) Dosyaları yaz
        $yazilan = 0;
        foreach ($guvenli_dosyalar as $rel => $zip_path) {
            $hedef = $this->kok . '/' . $rel;
            $hedef_dir = dirname($hedef);
            if (!is_dir($hedef_dir)) {
                if (!@mkdir($hedef_dir, 0755, true)) {
                    $log[] = "❌ Klasör oluşturulamadı: " . dirname($rel);
                    continue;
                }
            }
            $icerik = $zip->getFromName($zip_path);
            if ($icerik === false) {
                $log[] = "❌ ZIP'ten okunamadı: " . $rel;
                continue;
            }
            // Realpath kontrol — kök dışına çıkma engeli
            $tam_yol = realpath($hedef_dir) . '/' . basename($hedef);
            if (!str_starts_with($tam_yol, realpath($this->kok))) {
                $log[] = "❌ Kök dışı yol engellendi: " . $rel;
                continue;
            }
            if (file_put_contents($hedef, $icerik) === false) {
                $log[] = "❌ Yazılamadı: " . $rel;
                continue;
            }
            $yazilan++;
        }
        $zip->close();

        // 7) manifest.json'u son olarak yaz (atomik)
        $manifest_clean = $manifest;
        unset($manifest_clean['files']); // files listesini ana manifest'te tutma
        $this->manifest_yaz($manifest_clean);

        // v1.12.26: Ayarlar tablosuna da yaz (manifest.json bozulursa fallback)
        $this->surum_kaydet((string)$manifest['version']);

        $log[] = "✅ $yazilan dosya yazıldı.";
        $log[] = "Sürüm: $eski_surum → " . $manifest['version'];

        // 8) Eski yedekleri temizle
        $this->eski_yedekleri_sil();

        // 9) DB log
        $this->db_log($eski_surum, (string)$manifest['version'], 'basarili', implode("\n", $log));

        // 10) Geçici ZIP'i sil
        @unlink($zip_yolu);

        return [
            'ok'        => true,
            'eski'      => $eski_surum,
            'yeni'      => (string)$manifest['version'],
            'yazilan'   => $yazilan,
            'atlanan'   => count($atlanan),
            'yedek'     => basename($yedek['yol']),
            'log'       => $log,
        ];
    }

    // --------------------------------------------------------------
    //  YEDEK
    // --------------------------------------------------------------
    public function yedek_al(array $rel_yollar, string $surum): array
    {
        $tarih = date('Ymd-His');
        $ad    = "yedek-v{$surum}-{$tarih}.zip";
        $yol   = $this->yedek_dir . '/' . $ad;

        $zip = new ZipArchive();
        if ($zip->open($yol, ZipArchive::CREATE) !== true) {
            return ['ok'=>false,'hata'=>'Yedek ZIP oluşturulamadı.'];
        }

        // Etkilenen dosyaları yedekle (varsa)
        $eklenen = 0;
        foreach ($rel_yollar as $rel) {
            $tam = $this->kok . '/' . $rel;
            if (file_exists($tam) && is_file($tam)) {
                $zip->addFile($tam, $rel);
                $eklenen++;
            }
        }

        // Yedek manifesti
        $yedek_manifest = [
            'tip'           => 'yedek',
            'kaynak_surum'  => $surum,
            'tarih'         => date('c'),
            'dosya_sayisi'  => $eklenen,
            'dosyalar'      => array_values($rel_yollar),
        ];
        $zip->addFromString('_yedek-bilgi.json', json_encode($yedek_manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        return ['ok'=>true, 'yol'=>$yol, 'boyut'=>filesize($yol), 'dosya_sayisi'=>$eklenen];
    }

    public function yedekleri_listele(): array
    {
        $liste = [];
        foreach (glob($this->yedek_dir . '/yedek-*.zip') as $y) {
            $liste[] = [
                'ad'     => basename($y),
                'boyut'  => filesize($y),
                'tarih'  => filemtime($y),
            ];
        }
        usort($liste, fn($a,$b) => $b['tarih'] <=> $a['tarih']);
        return $liste;
    }

    public function geri_al(string $yedek_ad): array
    {
        $yol = $this->yedek_dir . '/' . basename($yedek_ad);
        if (!file_exists($yol)) return ['ok'=>false,'hata'=>'Yedek dosyası bulunamadı.'];

        $zip = new ZipArchive();
        if ($zip->open($yol) !== true) return ['ok'=>false,'hata'=>'Yedek ZIP açılamadı.'];

        $yazilan = 0;
        for ($i=0; $i<$zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === '_yedek-bilgi.json') continue;
            if (str_contains($name, '..')) continue;
            // Korumalı yol mu?
            $korumali = false;
            foreach ($this->korumali_yollar as $k) {
                if ($name === $k || str_starts_with($name, rtrim($k,'/').'/')) { $korumali = true; break; }
            }
            if ($korumali) continue;
            $hedef = $this->kok . '/' . $name;
            $hedef_dir = dirname($hedef);
            if (!is_dir($hedef_dir)) @mkdir($hedef_dir, 0755, true);
            $icerik = $zip->getFromIndex($i);
            if ($icerik !== false) {
                file_put_contents($hedef, $icerik);
                $yazilan++;
            }
        }
        $zip->close();

        $this->db_log($this->mevcut_surum(), 'rollback', 'basarili', "Yedekten geri alındı: $yedek_ad ($yazilan dosya)");
        return ['ok'=>true, 'yazilan'=>$yazilan];
    }

    public function yedek_sil(string $yedek_ad): bool
    {
        $yol = $this->yedek_dir . '/' . basename($yedek_ad);
        return file_exists($yol) && @unlink($yol);
    }

    public function eski_yedekleri_sil(): int
    {
        $liste = $this->yedekleri_listele();
        $silinen = 0;
        if (count($liste) > $this->max_yedek) {
            $silinecekler = array_slice($liste, $this->max_yedek);
            foreach ($silinecekler as $y) {
                if (@unlink($this->yedek_dir . '/' . $y['ad'])) $silinen++;
            }
        }
        return $silinen;
    }

    // --------------------------------------------------------------
    //  YARDIMCILAR
    // --------------------------------------------------------------
    public function http_get(string $url, string $token = '', string $accept = 'application/json'): array
    {
        $ch = curl_init($url);
        $headers = [
            "Accept: $accept",
            'User-Agent: AzraDogalgaz-Updater/1.0',
        ];
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$body, $code];
    }

    public function boyut_format(int $bayt): string
    {
        if ($bayt < 1024)         return $bayt . ' B';
        if ($bayt < 1024*1024)    return round($bayt/1024, 1) . ' KB';
        if ($bayt < 1024*1024*1024) return round($bayt/(1024*1024), 1) . ' MB';
        return round($bayt/(1024*1024*1024), 2) . ' GB';
    }

    private function db_log(string $eski, string $yeni, string $durum, string $detay): void
    {
        try {
            db_run("INSERT INTO guncelleme_log (eski_surum, yeni_surum, durum, detay) VALUES (?,?,?,?)",
                [$eski, $yeni, $durum, mb_substr($detay, 0, 5000, 'UTF-8')]);
        } catch (Throwable $e) { /* tablo yoksa sessiz geç */ }
    }

    // ==============================================================
    //  AKILLI SENKRONİZASYON (CodeGa ERP tarzı dosya bazlı kontrol)
    // ==============================================================

    /**
     * Git blob SHA1 hesapla (GitHub Tree API'deki SHA ile birebir aynı format).
     * Format: sha1("blob " + length + "\0" + content)
     */
    public function git_blob_sha1(string $icerik): string
    {
        return sha1("blob " . strlen($icerik) . "\0" . $icerik);
    }

    /**
     * Yerel dosyanın Git blob SHA1'ini hesapla. Dosya yoksa null döner.
     */
    public function yerel_dosya_sha1(string $rel_yol): ?string
    {
        $tam = $this->kok . '/' . ltrim($rel_yol, '/');
        if (!is_file($tam)) return null;
        $icerik = file_get_contents($tam);
        if ($icerik === false) return null;
        return $this->git_blob_sha1($icerik);
    }

    /**
     * GitHub repository tree (recursive) — tüm dosyalar + SHA'leri.
     * @return array{ok:bool, files?:array<string,array{sha:string,size:int}>, hata?:string}
     */
    public function github_tree(string $repo, string $token = '', string $branch = 'main'): array
    {
        if (!preg_match('~^[\w.\-]+/[\w.\-]+$~', $repo)) {
            return ['ok'=>false, 'hata'=>'Geçersiz repo formatı.'];
        }

        // Branch'ın commit SHA'sını al
        $url = "https://api.github.com/repos/$repo/branches/" . urlencode($branch);
        [$body, $code] = $this->http_get($url, $token, 'application/vnd.github+json');
        if ($code !== 200) {
            $msg = (json_decode((string)$body, true)['message'] ?? '') ?: "HTTP $code";
            return ['ok'=>false, 'hata'=>"Branch alınamadı ($branch): $msg"];
        }
        $branch_data = json_decode((string)$body, true);
        $tree_sha = $branch_data['commit']['commit']['tree']['sha'] ?? '';
        if (!$tree_sha) return ['ok'=>false, 'hata'=>'Branch tree SHA bulunamadı.'];

        // Recursive tree
        $url = "https://api.github.com/repos/$repo/git/trees/$tree_sha?recursive=1";
        [$body, $code] = $this->http_get($url, $token, 'application/vnd.github+json');
        if ($code !== 200) {
            return ['ok'=>false, 'hata'=>"Tree alınamadı: HTTP $code"];
        }
        $tree = json_decode((string)$body, true);
        if (!isset($tree['tree']) || !is_array($tree['tree'])) {
            return ['ok'=>false, 'hata'=>'Tree çözümlenemedi.'];
        }

        $files = [];
        foreach ($tree['tree'] as $node) {
            if (($node['type'] ?? '') !== 'blob') continue;
            $path = (string)($node['path'] ?? '');
            if (!$path) continue;
            $files[$path] = [
                'sha'  => (string)($node['sha'] ?? ''),
                'size' => (int)($node['size'] ?? 0),
            ];
        }

        return ['ok'=>true, 'files'=>$files, 'truncated'=>!empty($tree['truncated']), 'sayi'=>count($files)];
    }

    /**
     * Tek dosyayı GitHub raw content'ten indir.
     */
    public function github_raw_indir(string $repo, string $branch, string $rel_yol, string $token = ''): array
    {
        $rel_yol = ltrim($rel_yol, '/');
        $url = "https://api.github.com/repos/$repo/contents/" . str_replace('%2F','/', rawurlencode($rel_yol)) . "?ref=" . urlencode($branch);
        [$body, $code] = $this->http_get($url, $token, 'application/vnd.github.raw');
        if ($code !== 200) {
            return ['ok'=>false, 'hata'=>"İndirilemedi ($rel_yol): HTTP $code"];
        }
        return ['ok'=>true, 'icerik'=>(string)$body, 'boyut'=>strlen((string)$body)];
    }

    /**
     * GitHub repo'sundaki son N commit'i al.
     */
    public function github_commits(string $repo, string $token = '', string $branch = 'main', int $n = 20): array
    {
        $n = max(1, min(100, $n));
        $url = "https://api.github.com/repos/$repo/commits?sha=" . urlencode($branch) . "&per_page=$n";
        [$body, $code] = $this->http_get($url, $token, 'application/vnd.github+json');
        if ($code !== 200) return ['ok'=>false, 'hata'=>"HTTP $code"];
        $list = json_decode((string)$body, true);
        if (!is_array($list)) return ['ok'=>false, 'hata'=>'Çözümlenemedi.'];
        $out = [];
        foreach ($list as $c) {
            $out[] = [
                'sha'     => substr((string)($c['sha'] ?? ''), 0, 7),
                'sha_full'=> (string)($c['sha'] ?? ''),
                'mesaj'   => mb_substr((string)($c['commit']['message'] ?? ''), 0, 200, 'UTF-8'),
                'yazar'   => (string)($c['commit']['author']['name'] ?? '?'),
                'tarih'   => (string)($c['commit']['author']['date'] ?? ''),
                'url'     => (string)($c['html_url'] ?? ''),
            ];
        }
        return ['ok'=>true, 'commits'=>$out];
    }

    /**
     * GitHub ile yerel dosya durumunu karşılaştır.
     * @return array{ok:bool, dosyalar?:array<string,array{durum:string,yerel_sha:?string,github_sha:string,boyut:int}>,
     *               istatistik?:array{guncel:int,degismis:int,eksik:int,toplam:int}, hata?:string}
     */
    public function dosya_durumu(string $repo, string $token = '', string $branch = 'main'): array
    {
        $tree = $this->github_tree($repo, $token, $branch);
        if (!$tree['ok']) return $tree;

        $sonuc = [];
        $stat = ['guncel'=>0, 'degismis'=>0, 'eksik'=>0, 'toplam'=>0, 'korumali'=>0];

        foreach ($tree['files'] as $rel => $info) {
            $stat['toplam']++;

            // v1.12.26: Karşılaştırma sırasında her zaman değişen dosyaları (manifest.json, README) hariç tut
            if (in_array($rel, $this->karsilastirma_haric, true)) {
                $sonuc[$rel] = [
                    'durum'      => 'haric',
                    'yerel_sha'  => null,
                    'github_sha' => $info['sha'],
                    'boyut'      => $info['size'],
                ];
                continue;
            }

            // Korumalı yol mu? (config.php, uploads/ vb)
            $korumali = false;
            foreach ($this->korumali_yollar as $k) {
                if ($rel === $k || str_starts_with($rel, rtrim($k,'/').'/')) { $korumali = true; break; }
            }

            $yerel_sha = $this->yerel_dosya_sha1($rel);
            if ($yerel_sha === null) {
                $durum = 'eksik';
                $stat['eksik']++;
            } elseif ($yerel_sha === $info['sha']) {
                $durum = 'guncel';
                $stat['guncel']++;
            } else {
                $durum = 'degismis';
                $stat['degismis']++;
            }
            if ($korumali) $stat['korumali']++;

            $sonuc[$rel] = [
                'durum'      => $durum,
                'yerel_sha'  => $yerel_sha,
                'github_sha' => $info['sha'],
                'boyut'      => $info['size'],
                'korumali'   => $korumali,
            ];
        }

        ksort($sonuc);
        return ['ok'=>true, 'dosyalar'=>$sonuc, 'istatistik'=>$stat];
    }

    /**
     * Tek dosyayı yerel olarak günceller (GitHub raw'dan indirip yazar).
     */
    public function tek_dosya_sync(string $repo, string $token, string $branch, string $rel_yol): array
    {
        $rel_yol = str_replace('\\', '/', ltrim($rel_yol, '/'));

        // Path traversal
        if (str_contains($rel_yol, '..') || str_starts_with($rel_yol, '/')) {
            return ['ok'=>false, 'hata'=>'Geçersiz yol.'];
        }

        // Korumalı mı?
        foreach ($this->korumali_yollar as $k) {
            if ($rel_yol === $k || str_starts_with($rel_yol, rtrim($k,'/').'/')) {
                return ['ok'=>false, 'hata'=>"Korumalı yol: $rel_yol"];
            }
        }

        // İzinli uzantı mı?
        $ext = strtolower(pathinfo($rel_yol, PATHINFO_EXTENSION));
        if ($ext && !in_array($ext, $this->izinli_uzantilar, true)) {
            return ['ok'=>false, 'hata'=>"İzinsiz uzantı: .$ext"];
        }

        $r = $this->github_raw_indir($repo, $branch, $rel_yol, $token);
        if (!$r['ok']) return $r;

        // Hedef klasör
        $hedef = $this->kok . '/' . $rel_yol;
        $hedef_dir = dirname($hedef);
        if (!is_dir($hedef_dir)) {
            if (!@mkdir($hedef_dir, 0755, true)) {
                return ['ok'=>false, 'hata'=>"Klasör oluşturulamadı: $hedef_dir"];
            }
        }

        // Path traversal son kontrol
        $tam_yol = realpath($hedef_dir);
        if (!$tam_yol || !str_starts_with($tam_yol . '/', realpath($this->kok) . '/')) {
            return ['ok'=>false, 'hata'=>'Kök dizini dışına yazma reddedildi.'];
        }

        if (file_put_contents($hedef, $r['icerik']) === false) {
            return ['ok'=>false, 'hata'=>"Yazılamadı: $rel_yol"];
        }

        return ['ok'=>true, 'rel'=>$rel_yol, 'boyut'=>$r['boyut']];
    }

    /**
     * Akıllı senkronizasyon: değişmiş + eksik tüm dosyaları sırayla günceller.
     * Önce yedek alır, sonra her dosyayı tek_dosya_sync ile yazar.
     * @param array $sadece_bunlari Sadece bu yolları senkronize et (boşsa: hepsi)
     * @param bool  $zorla          true ise durum farketmez tüm uygun dosyaları yeniden indirir
     */
    public function akilli_senkronize(string $repo, string $token, string $branch, array $sadece_bunlari = [], bool $zorla = false): array
    {
        $log = [];
        $log[] = "Dosya durumu sorgulanıyor: $repo / $branch";

        $durum = $this->dosya_durumu($repo, $token, $branch);
        if (!$durum['ok']) return ['ok'=>false, 'hata'=>$durum['hata'], 'log'=>$log];

        // Senkronize edilecekler
        $hedefler = [];
        foreach ($durum['dosyalar'] as $rel => $d) {
            if ($sadece_bunlari && !in_array($rel, $sadece_bunlari, true)) continue;
            if ($d['korumali']) continue;
            if ($zorla || in_array($d['durum'], ['degismis', 'eksik'], true)) {
                $hedefler[] = $rel;
            }
        }

        if (!$hedefler) {
            return ['ok'=>true, 'log'=>array_merge($log, ['Güncellenecek dosya yok.']), 'sayi'=>0, 'basarili'=>0, 'hata_sayisi'=>0, 'hatalar'=>[], 'eski_surum'=>$this->mevcut_surum(), 'yeni_surum'=>$this->mevcut_surum()];
        }

        $log[] = count($hedefler) . " dosya güncellenecek" . ($zorla ? ' (zorla)' : '') . ".";

        // Yedek al
        $eski_surum = $this->mevcut_surum();
        $yedek = $this->yedek_al(array_filter($hedefler, fn($p) => is_file($this->kok.'/'.$p)), $eski_surum . '_pre-sync');
        if ($yedek['ok']) {
            $log[] = "Yedek alındı: " . basename($yedek['yol']) . ' (' . $this->boyut_format((int)$yedek['boyut']) . ')';
        } else {
            $log[] = "[UYARI] Yedek alınamadı: " . ($yedek['hata'] ?? '?');
        }

        // Her dosyayı senkronize et
        $basarili = 0; $hatalar = [];
        foreach ($hedefler as $rel) {
            $r = $this->tek_dosya_sync($repo, $token, $branch, $rel);
            if ($r['ok']) {
                $basarili++;
                $log[] = "✓ $rel (" . $this->boyut_format((int)$r['boyut']) . ")";
            } else {
                $hatalar[] = "✗ $rel: " . ($r['hata'] ?? '?');
                $log[] = end($hatalar);
            }
        }

        // Manifest'i yenile (GitHub'dan en taze halini al)
        $r = $this->github_raw_indir($repo, $branch, 'manifest.json', $token);
        if ($r['ok']) {
            @file_put_contents($this->manifest_yolu, $r['icerik']);
            $log[] = "manifest.json yenilendi.";
        }

        // Migrasyonları otomatik uygula
        if (file_exists($this->kok . '/inc/migrator.php')) {
            require_once $this->kok . '/inc/migrator.php';
            try {
                $M = new Migrator($this->kok);
                $mig = $M->bekleyenleri_uygula();
                if (!empty($mig['uygulananlar'])) {
                    $log[] = '──── Migrasyon ────';
                    foreach ($mig['uygulananlar'] as $u) {
                        $log[] = "✓ DB: {$u['dosya']} ({$u['stmts']} stmt, {$u['sure_ms']}ms)";
                    }
                    $M->sentinel_kaydet();
                }
                if (!empty($mig['hatalar'])) {
                    foreach ($mig['hatalar'] as $h) {
                        $log[] = "✗ DB: " . ($h['dosya'] ?? '?') . ': ' . ($h['hata'] ?? '?');
                        $hatalar[] = "DB migration: " . ($h['dosya'] ?? '?');
                    }
                }
            } catch (Throwable $e) {
                $log[] = "[UYARI] Migrator yüklenemedi: " . $e->getMessage();
            }
        }

        $yeni_surum = $this->mevcut_surum();
        $this->db_log($eski_surum, $yeni_surum, $hatalar ? 'kismi' : 'basarili',
            "Akıllı sync: $basarili başarılı, " . count($hatalar) . " hata");

        // OPcache reset
        if (function_exists('opcache_reset')) @opcache_reset();
        clearstatcache(true);

        return [
            'ok'        => true,
            'log'       => $log,
            'basarili'  => $basarili,
            'hata_sayisi' => count($hatalar),
            'hatalar'   => $hatalar,
            'eski_surum'=> $eski_surum,
            'yeni_surum'=> $yeni_surum,
        ];
    }
}
