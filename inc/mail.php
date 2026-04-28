<?php
/**
 * Basit SMTP Mail Sınıfı
 * Native fsockopen tabanlı — composer/vendor gerektirmez.
 * SMTPS (port 465 SSL) ve STARTTLS (port 587) destekler.
 */

class Mail
{
    public string $host = '';
    public int    $port = 587;
    public string $user = '';
    public string $sifre = '';
    public string $secure = 'tls';      // tls (STARTTLS), ssl (SMTPS), ''
    public string $gonderen_eposta = '';
    public string $gonderen_ad = '';
    public int    $timeout = 15;

    public array $log = [];

    public static function ayardan_yukle(): self
    {
        $m = new self();
        $m->host  = (string)ayar('smtp_host', '');
        $m->port  = (int)(ayar('smtp_port', 587));
        $m->user  = (string)ayar('smtp_user', '');
        $m->sifre = (string)ayar('smtp_sifre', '');
        $m->secure = strtolower((string)ayar('smtp_secure', 'tls'));
        $m->gonderen_eposta = (string)ayar('smtp_gonderen_eposta', $m->user);
        $m->gonderen_ad     = (string)ayar('smtp_gonderen_ad', ayar('firma_unvan', 'Azra Doğalgaz'));
        return $m;
    }

    public function konfigure_mi(): bool
    {
        return $this->host !== '' && $this->user !== '' && $this->sifre !== '' && $this->gonderen_eposta !== '';
    }

    /**
     * Mail gönder.
     * @param string $alici_eposta
     * @param string $alici_ad
     * @param string $konu
     * @param string $html
     * @param string $duz Düz metin alternatifi (boşsa otomatik HTML→text)
     * @return array{ok:bool, hata?:string, log?:array}
     */
    public function gonder(string $alici_eposta, string $alici_ad, string $konu, string $html, string $duz = ''): array
    {
        if (!$this->konfigure_mi()) {
            return ['ok'=>false, 'hata'=>'SMTP ayarları eksik (host/user/sifre/gonderen).'];
        }
        if (!filter_var($alici_eposta, FILTER_VALIDATE_EMAIL)) {
            return ['ok'=>false, 'hata'=>'Alıcı e-posta geçersiz: ' . $alici_eposta];
        }
        if ($duz === '') {
            $duz = trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
        }

        // SMTPS (port 465) için ssl://, STARTTLS için plain → STARTTLS sonra
        $proto = ($this->secure === 'ssl') ? 'ssl://' : '';
        $errno = 0; $errstr = '';
        $sock = @stream_socket_client($proto . $this->host . ':' . $this->port, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT);
        if (!$sock) return ['ok'=>false, 'hata'=>"Bağlanılamadı: $errstr ($errno)", 'log'=>$this->log];

        try {
            stream_set_timeout($sock, $this->timeout);
            $this->_oku($sock, 220);
            $this->_yaz($sock, "EHLO " . ($this->_host_kendisi()));
            $this->_oku($sock, 250);

            // STARTTLS (port 587 standart)
            if ($this->secure === 'tls' || $this->secure === 'starttls') {
                $this->_yaz($sock, "STARTTLS");
                $this->_oku($sock, 220);
                // Birden fazla TLS sürümünü destekle (DirectAdmin'lerde TLS 1.3 zorla başarısız olur)
                $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) $crypto |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT')) $crypto |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) $crypto |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                if (!@stream_socket_enable_crypto($sock, true, $crypto)) {
                    $err = error_get_last();
                    $this->log[] = '! TLS handshake başarısız: ' . ($err['message'] ?? '?');
                    return ['ok'=>false, 'hata'=>'STARTTLS TLS handshake başarısız: ' . ($err['message'] ?? 'detay yok') . '. Sertifika veya TLS sürüm uyuşmazlığı olabilir.', 'log'=>$this->log];
                }
                $this->_yaz($sock, "EHLO " . $this->_host_kendisi());
                $this->_oku($sock, 250);
            }

            // AUTH LOGIN
            $this->_yaz($sock, "AUTH LOGIN");
            $this->_oku($sock, 334);
            $this->_yaz($sock, base64_encode($this->user));
            $this->_oku($sock, 334);
            $this->_yaz($sock, base64_encode($this->sifre));
            $this->_oku($sock, 235);

            // MAIL FROM / RCPT TO
            $this->_yaz($sock, "MAIL FROM:<{$this->gonderen_eposta}>");
            $this->_oku($sock, 250);
            $this->_yaz($sock, "RCPT TO:<{$alici_eposta}>");
            $this->_oku($sock, [250, 251]);

            // DATA
            $this->_yaz($sock, "DATA");
            $this->_oku($sock, 354);

            // Header + body (multipart alternative)
            $boundary = '=_b_' . md5(uniqid('', true));
            $headers  = $this->_headers($alici_eposta, $alici_ad, $konu, $boundary);
            $body     = $this->_multipart($duz, $html, $boundary);

            // RFC 5321: önce CRLF normalize, sonra dot-stuffing (SADECE body'de),
            // EN SON terminator (\r\n.\r\n) eklenir — terminator escape EDİLMEZ.
            $body = preg_replace("/\r?\n/", "\r\n", $body);
            $body = preg_replace('/^\./m', '..', $body);
            $msg = $headers . "\r\n" . $body;
            // Mesajı sonlandır: tek başına nokta + CRLF
            fwrite($sock, $msg . "\r\n.\r\n");
            $this->_oku($sock, 250);

            $this->_yaz($sock, "QUIT");
            @fclose($sock);
            return ['ok'=>true, 'log'=>$this->log];

        } catch (Throwable $e) {
            @fclose($sock);
            return ['ok'=>false, 'hata'=>$e->getMessage(), 'log'=>$this->log];
        }
    }

    private function _headers(string $alici_eposta, string $alici_ad, string $konu, string $boundary): string
    {
        $tarih = date('r');
        $msgid = '<' . md5(uniqid('', true)) . '@' . $this->_host_kendisi() . '>';
        $from_ad = $this->_mime_ad($this->gonderen_ad);
        $alici_ad_mime = $this->_mime_ad($alici_ad ?: $alici_eposta);
        $konu_mime = $this->_mime_konu($konu);

        $h  = "Date: $tarih\r\n";
        $h .= "From: $from_ad <{$this->gonderen_eposta}>\r\n";
        $h .= "To: $alici_ad_mime <$alici_eposta>\r\n";
        $h .= "Subject: $konu_mime\r\n";
        $h .= "Message-ID: $msgid\r\n";
        $h .= "MIME-Version: 1.0\r\n";
        $h .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $h .= "X-Mailer: AzraDogalgaz-Mail/1.0\r\n";
        return $h;
    }

    private function _multipart(string $duz, string $html, string $boundary): string
    {
        $b  = "--$boundary\r\n";
        $b .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $b .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $b .= chunk_split(base64_encode($duz)) . "\r\n";
        $b .= "--$boundary\r\n";
        $b .= "Content-Type: text/html; charset=UTF-8\r\n";
        $b .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $b .= chunk_split(base64_encode($html)) . "\r\n";
        $b .= "--$boundary--\r\n";
        return $b;
    }

    private function _mime_ad(string $ad): string
    {
        if (preg_match('/^[\x20-\x7e]+$/', $ad)) return $ad;
        return '=?UTF-8?B?' . base64_encode($ad) . '?=';
    }

    private function _mime_konu(string $konu): string
    {
        if (preg_match('/^[\x20-\x7e]+$/', $konu)) return $konu;
        return '=?UTF-8?B?' . base64_encode($konu) . '?=';
    }

    private function _host_kendisi(): string
    {
        return $_SERVER['SERVER_NAME'] ?? 'localhost';
    }

    private function _yaz($sock, string $cmd): void
    {
        $this->log[] = '> ' . (str_starts_with($cmd, 'AUTH') || strlen($cmd) > 30 && !str_contains($cmd, ' ') ? '[gizli]' : $cmd);
        fwrite($sock, $cmd . "\r\n");
    }

    /**
     * @param int|int[] $beklenen Beklenen tek kod veya kod dizisi
     */
    private function _oku($sock, $beklenen): string
    {
        $yanit = '';
        while (!feof($sock)) {
            $line = fgets($sock, 1024);
            if ($line === false) break;
            $yanit .= $line;
            if (preg_match('/^\d{3} /', $line)) break;
        }
        $info = stream_get_meta_data($sock);
        $this->log[] = '< ' . trim($yanit);
        $beklenen_arr = is_array($beklenen) ? $beklenen : [$beklenen];

        if ($yanit === '') {
            // Sunucu hiç yanıt vermedi — timeout veya connection drop
            $sebep = !empty($info['timed_out']) ? 'TIMEOUT (' . $this->timeout . 'sn)' : 'BAĞLANTI KAPATILDI';
            $this->log[] = '! ' . $sebep . ' — sunucu yanıt vermedi';
            throw new RuntimeException("SMTP sunucu yanıt vermedi: $sebep. Beklenen: " . implode(',', $beklenen_arr) . ". Olası sebepler: yanlış host/port, TLS uyumsuzluğu, IP bloklu, firewall.");
        }

        $kod = (int)substr($yanit, 0, 3);
        if (!in_array($kod, $beklenen_arr, true)) {
            throw new RuntimeException("SMTP yanıt beklenmedik: " . trim($yanit) . " (beklenen: " . implode(',', $beklenen_arr) . ")");
        }
        return $yanit;
    }
}
