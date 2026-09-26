<?php

namespace App\Support;

use App\Http\Controllers\DpiaController;

class DpiaStatus
{
    /**
     * Daftar hal yang membuat DPIA belum bisa diekspor. Kosong = lengkap.
     * Selain "tab belum disimpan", juga menangkap data yang tidak lagi konsisten
     * karena RoPA atau Tab VI diubah setelah tab lain disimpan.
     */
    public static function masalah(array $ropa, array $dpia): array
    {
        $m = [];

        // Semua tab (kecuali yang otomatis dari RoPA) sudah pernah disimpan
        foreach (DpiaController::TABS as $key => $label) {
            if (in_array($key, DpiaController::TABS_OTOMATIS, true)) continue;
            if (! isset($dpia[$key])) $m[] = "$label belum disimpan.";
        }

        // III: setiap indikator Ya (menurut RoPA terbaru) punya keterangan
        if (isset($dpia['risiko'])) {
            foreach (RopaStatus::indikatorAktif($ropa) as $key) {
                if (! filled($dpia['risiko']['keterangan'][$key] ?? null)) {
                    $m[] = 'III. Ada potensi risiko bernilai Ya tanpa keterangan (indikator di RoPA berubah). Lengkapi dan simpan ulang Tab III.';
                    break;
                }
            }
        }

        // IV: data yang diakses setiap pihak masih termasuk jenis data di RoPA
        if (isset($dpia['deskripsi'])) {
            $jenis = array_merge(
                (array) ($ropa['pemetaan']['jenis_umum'] ?? []),
                (array) ($ropa['pemetaan']['jenis_spesifik'] ?? []),
            );
            foreach ((array) ($dpia['deskripsi']['pihak'] ?? []) as $pihak) {
                if (array_diff((array) ($pihak['data'] ?? []), $jenis)) {
                    $m[] = 'IV. Data yang diakses pihak tidak lagi sesuai jenis data di RoPA. Periksa dan simpan ulang Tab IV.';
                    break;
                }
            }
        }

        // VI: semua risiko inheren punya ID (data lama)
        $pen = $dpia['penilaian'] ?? [];
        foreach ((array) ($pen['inheren'] ?? []) as $rows) {
            foreach ((array) $rows as $r) {
                if (empty($r['id'])) {
                    $m[] = 'VI. Simpan ulang Tab VI agar setiap risiko memiliki ID.';
                    break 2;
                }
            }
        }

        // VII: setiap risiko inheren saat ini punya minimal satu risiko residual
        if (isset($dpia['pengendalian'])) {
            $residual = (array) ($dpia['pengendalian']['residual'] ?? []);
            $kurang   = [];
            foreach (MetodologiRisiko::daftarRisiko($pen) as $id => $r) {
                if (empty($residual[$id])) $kurang[] = $r['kode'];
            }
            if ($kurang) {
                $m[] = 'VII. Risiko ' . implode(', ', $kurang) . ' belum memiliki risiko residual. Lengkapi Tab VII.';
            }
        }

        return $m;
    }
}
