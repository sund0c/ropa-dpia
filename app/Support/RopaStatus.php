<?php

namespace App\Support;

use App\Http\Controllers\RopaController;

class RopaStatus
{
    /** Label tab yang belum pernah disimpan. */
    public static function tabBelumDisimpan(array $ropa): array
    {
        return array_values(array_diff_key(RopaController::TABS, $ropa));
    }

    public static function lengkap(array $ropa): bool
    {
        return self::tabBelumDisimpan($ropa) === [];
    }

    /** Koordinator PPDP wajib ada untuk cetak PDF (file impor lama bisa belum memilikinya). */
    public static function koordinatorLengkap(array $ropa): bool
    {
        $o = $ropa['organisasi'] ?? [];
        return filled($o['koordinator_nama'] ?? null) && filled($o['koordinator_jabatan'] ?? null);
    }

    /**
     * Indikator risiko yang terpenuhi: pilihan manual Tab VI + data spesifik dari Tab III
     * + pembatasan hak bila ada hak subjek di Tab IV yang tidak difasilitasi. Urutan mengikuti INDIKATOR_RISIKO.
     */
    public static function indikatorAktif(array $ropa): array
    {
        $indikator = array_diff(
            (array) ($ropa['risiko']['indikator'] ?? []),
            [RopaController::INDIKATOR_OTOMATIS, RopaController::INDIKATOR_HAK]
        );

        if (! empty($ropa['pemetaan']['jenis_spesifik'])) {
            $indikator[] = RopaController::INDIKATOR_OTOMATIS;
        }
        if (self::hakTidakDifasilitasi($ropa) !== []) {
            $indikator[] = RopaController::INDIKATOR_HAK;
        }

        return array_values(array_intersect(array_keys(RopaController::INDIKATOR_RISIKO), $indikator));
    }

    /** Kunci hak subjek yang tidak dicentang di Tab IV (kosong bila Tab IV belum disimpan). */
    public static function hakTidakDifasilitasi(array $ropa): array
    {
        if (! isset($ropa['hak'])) return [];

        return array_values(array_diff(
            array_keys(RopaController::HAK_SUBJEK),
            (array) ($ropa['hak']['hak'] ?? [])
        ));
    }

    public static function wajibDpia(array $ropa): bool
    {
        return self::indikatorAktif($ropa) !== [];
    }
}
