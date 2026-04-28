<?php
/**
 * Cari & Muhasebe modülleri için şema garanti edici.
 * Artık inline CREATE TABLE değil — migration sistemi üzerinden çalışıyor.
 * Modüllerin başında include edilir; bekleyen migration varsa otomatik uygular.
 */

if (!function_exists('db')) return; // _baslat.php yüklü değilse atla

require_once __DIR__ . '/migrator.php';

try {
    $M = new Migrator(__DIR__ . '/..');

    // Cache: migrations/ klasörü mtime değişmemişse skip
    if ($M->otomatik_uygula_lazim_mi()) {
        $sonuc = $M->bekleyenleri_uygula();
        if ($sonuc['ok']) {
            $M->sentinel_kaydet();
            if (!empty($sonuc['uygulananlar'])) {
                error_log('[sema-muhasebe] ' . count($sonuc['uygulananlar']) . ' migration uygulandı.');
            }
        } else {
            error_log('[sema-muhasebe] Migration hatası: ' . json_encode($sonuc['hatalar']));
        }
    }
} catch (Throwable $e) {
    error_log('[sema-muhasebe] ' . $e->getMessage());
}
