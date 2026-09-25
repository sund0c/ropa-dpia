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

    /** Indikator risiko yang terpenuhi: pilihan manual Tab VI + data spesifik dari Tab III. */
    public static function indikatorAktif(array $ropa): array
    {
        $indikator = (array) ($ropa['risiko']['indikator'] ?? []);

        if (! empty($ropa['pemetaan']['jenis_spesifik'])) {
            $indikator[] = RopaController::INDIKATOR_OTOMATIS;
        }

        return array_values(array_unique($indikator));
    }

    public static function wajibDpia(array $ropa): bool
    {
        return self::indikatorAktif($ropa) !== [];
    }
}
