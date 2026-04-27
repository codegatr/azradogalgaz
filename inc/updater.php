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

    /** İzinli dosya uzantıları (manifest yoksa filtre uygulanır). */
    public array $izinli_uzantilar = [
        'php','html','htm','css','js','json','txt','md','xml',
        'jpg','jpeg','png','gif','webp','svg','ico',
        'woff','woff2','ttf','eot',
        'sql','htaccess',
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
        return (bool)file_put_contents($this->manifest_yolu, $json);
    }

    public function mevcut_surum(): string
    {
        return (string)($this->manifest_oku()['version'] ?? '0.0.0');
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
}
