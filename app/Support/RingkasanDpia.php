<?php

namespace App\Support;

use App\Http\Controllers\DpiaController;

class RingkasanDpia
{
    /** Data ringkasan dari Tab V–VIII. */
    public static function hitung(array $dpia): array
    {
        $pen    = $dpia['penilaian'] ?? [];
        $m      = MetodologiRisiko::nilai($pen['metodologi'] ?? []);
        $risiko = MetodologiRisiko::daftarRisiko($pen);

        // Tab V: pertanyaan yang dijawab Tidak
        $jawaban = (array) ($dpia['kebutuhan']['jawaban'] ?? []);
        $tidak   = [];
        foreach (DpiaController::PENILAIAN as $grup) {
            foreach ($grup['items'] as $key => $pertanyaan) {
                if (($jawaban[$key] ?? null) === 'tidak') $tidak[] = $pertanyaan;
            }
        }

        // Tab VI: risiko inheren dengan skor tertinggi
        $inheren = null;
        foreach ($risiko as $r) {
            if (! $inheren || $r['skor'] > $inheren['skor']) $inheren = $r;
        }

        // Tab VII: risiko residual dengan skor tertinggi
        $residual = null;
        foreach ((array) ($dpia['pengendalian']['residual'] ?? []) as $rid => $daftar) {
            $rid = (string) $rid;
            if (! isset($risiko[$rid])) continue;
            foreach ((array) $daftar as $res) {
                $skor = (int) ($res['kemungkinan'] ?? 0) * (int) ($res['dampak'] ?? 0);
                if ($skor && (! $residual || $skor > $residual['skor'])) {
                    $residual = [
                        'skor'   => $skor,
                        'level'  => MetodologiRisiko::level($skor, $m),
                        'kode'   => $risiko[$rid]['kode'],
                        'risiko' => $res['risiko'] ?? '',
                    ];
                }
            }
        }

        return [
            'kebutuhan_terisi' => ! empty($jawaban),
            'kebutuhan_tidak'  => $tidak,
            'inheren'          => $inheren,
            'residual'         => $residual,
            'lembaga'          => $dpia['rekomendasi']['lembaga_konsultasi'] ?? null,
        ];
    }

    /** Usulan teks kesimpulan untuk tiga aspek pertama (bisa diedit user). */
    public static function usulan(array $h): array
    {
        $teksRisiko = function (?array $r): string {
            if (! $r) return '';
            $level = MetodologiRisiko::KATEGORI[$r['level']]['nama'];
            return 'Skor ' . $r['skor'] . ' (' . $level . ') pada ' . $r['kode'] . ': ' . $r['risiko'];
        };

        if (! $h['kebutuhan_terisi']) {
            $kebutuhan = '';
        } elseif (empty($h['kebutuhan_tidak'])) {
            $kebutuhan = 'Pemrosesan dinilai diperlukan dan proporsional; seluruh aspek kebutuhan dan proporsionalitas terpenuhi.';
        } else {
            $kebutuhan = 'Terdapat ' . count($h['kebutuhan_tidak']) . ' aspek yang belum terpenuhi: '
                . implode(' ', $h['kebutuhan_tidak']);
        }

        return [
            'kebutuhan' => $kebutuhan,
            'inheren'   => $teksRisiko($h['inheren']),
            'residual'  => $teksRisiko($h['residual']),
        ];
    }
}
