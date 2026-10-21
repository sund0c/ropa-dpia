<?php

namespace App\Support;

/**
 * Peta Risiko (Modul C, Tab II): matriks 5×5, kategori tingkat risiko, skala kemungkinan,
 * dan skala dampak 6 dimensi. Kategori & kemungkinan mengikuti metodologi DPIA (MetodologiRisiko);
 * skala dampak khusus manajemen risiko aset organisasi.
 */
class PetaRisiko
{
    /** Dimensi dampak, urutan tampilan. */
    public const DIMENSI = [
        'finansial'       => ['label' => 'Finansial',           'kelompok' => 'finansial'],
        'reputasi'        => ['label' => 'Reputasi',            'kelompok' => 'non'],
        'kinerja'         => ['label' => 'Kinerja',             'kelompok' => 'non'],
        'layanan'         => ['label' => 'Layanan Organisasi',  'kelompok' => 'non'],
        'operasional_tik' => ['label' => 'Operasional TIK',     'kelompok' => 'non'],
        'hukum_regulasi'  => ['label' => 'Hukum dan Regulasi',  'kelompok' => 'non'],
    ];

    /** Kriteria dampak default per level. */
    private const DAMPAK = [
        1 => [
            'finansial'       => 'Jumlah kerugian organisasi ≤ Rp 10 juta',
            'reputasi'        => 'Keluhan stakeholder secara langsung lisan/tertulis ke organisasi jumlahnya ≤ 3 kali dalam satu periode',
            'kinerja'         => 'Penurunan < 20%',
            'layanan'         => 'Business process tertunda ≤ 1 hari',
            'operasional_tik' => 'Gangguan kecil',
            'hukum_regulasi'  => 'Tidak ada pelanggaran hukum',
        ],
        2 => [
            'finansial'       => 'Jumlah kerugian organisasi lebih dari Rp 10 juta s.d. Rp 50 juta',
            'reputasi'        => 'Keluhan stakeholder secara langsung lisan/tertulis ke organisasi jumlahnya lebih dari 3 kali dalam satu periode',
            'kinerja'         => 'Penurunan 20% < x ≤ 40%',
            'layanan'         => 'Business process tertunda di atas 1 hari s.d. 5 hari',
            'operasional_tik' => 'Gangguan sporadis',
            'hukum_regulasi'  => 'Pelanggaran ringan dengan teguran',
        ],
        3 => [
            'finansial'       => 'Jumlah kerugian organisasi lebih dari Rp 50 juta s.d. Rp 100 juta',
            'reputasi'        => 'Pemberitaan negatif di dalam media massa lokal',
            'kinerja'         => 'Penurunan 40% < x ≤ 60%',
            'layanan'         => 'Business process tertunda di atas 5 hari s.d. 15 hari',
            'operasional_tik' => 'Terhenti sementara',
            'hukum_regulasi'  => 'Pelanggaran ringan dengan surat peringatan',
        ],
        4 => [
            'finansial'       => 'Jumlah kerugian organisasi lebih dari Rp 100 juta s.d. Rp 500 juta',
            'reputasi'        => 'Pemberitaan negatif di dalam media massa nasional',
            'kinerja'         => 'Penurunan 60% < x ≤ 80%',
            'layanan'         => 'Business process tertunda di atas 15 hari s.d. 30 hari',
            'operasional_tik' => 'Terhenti lama',
            'hukum_regulasi'  => 'Pelanggaran sedang yang dikenakan sanksi administratif',
        ],
        5 => [
            'finansial'       => 'Jumlah kerugian organisasi lebih dari Rp 500 juta',
            'reputasi'        => 'Pemberitaan negatif di dalam media massa internasional',
            'kinerja'         => 'Penurunan > 80%',
            'layanan'         => 'Business process tertunda di atas 30 hari',
            'operasional_tik' => 'Tidak berfungsi',
            'hukum_regulasi'  => 'Pelanggaran berat dengan sanksi hukum',
        ],
    ];

    /** Seluruh nilai default: kategori & kemungkinan dari metodologi DPIA, dampak dari tabel di atas. */
    public static function bawaan(): array
    {
        $m      = MetodologiRisiko::DEFAULT;
        $dampak = [];

        foreach (range(1, 5) as $i) {
            $dampak[$i] = ['nama' => $m['dampak'][$i]['nama']] + self::DAMPAK[$i];
        }

        return [
            'kategori_batas' => $m['kategori_batas'],
            'kemungkinan'    => $m['kemungkinan'],
            'dampak'         => $dampak,
        ];
    }

    /** Peta yang berlaku: default ditimpa nilai tersimpan, kecuali nama level (selalu default). */
    public static function nilai(array $tersimpan = []): array
    {
        $m = array_replace_recursive(self::bawaan(), $tersimpan);

        foreach (range(1, 5) as $i) {
            $m['kemungkinan'][$i]['nama'] = MetodologiRisiko::DEFAULT['kemungkinan'][$i]['nama'];
            $m['dampak'][$i]['nama']      = MetodologiRisiko::DEFAULT['dampak'][$i]['nama'];
        }

        return $m;
    }

    /** Level risiko (1–5) untuk skor tertentu. */
    public static function level(int $skor, array $m): int
    {
        return MetodologiRisiko::level($skor, $m);
    }

    public static function rules(): array
    {
        $r = [];

        foreach (range(1, 5) as $i) {
            $r["kemungkinan.$i.periode"] = ['required', 'string', 'max:255'];
            foreach (array_keys(self::DIMENSI) as $k) {
                $r["dampak.$i.$k"] = ['required', 'string', 'max:1000'];
            }
        }

        foreach (range(1, 4) as $i) {
            $r["kategori_batas.$i"] = ['required', 'integer', 'between:1,24'];
            if ($i > 1) {
                $r["kategori_batas.$i"][] = 'gt:kategori_batas.' . ($i - 1);
            }
        }

        return $r;
    }

    public static function messages(): array
    {
        return [
            'kategori_batas.*.required' => 'Batas wajib diisi.',
            'kategori_batas.*.integer'  => 'Batas harus berupa angka bulat.',
            'kategori_batas.*.between'  => 'Batas harus antara :min dan :max.',
            'kategori_batas.*.gt'       => 'Batas harus lebih besar dari batas level sebelumnya.',
        ];
    }

    public static function attributes(): array
    {
        $a = ['kemungkinan.*.periode' => 'Periode kejadian'];
        foreach (self::DIMENSI as $k => $dim) {
            $a["dampak.*.$k"] = 'Dampak ' . strtolower($dim['label']);
        }

        return $a;
    }
}
