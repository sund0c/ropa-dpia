<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/** Kategorisasi Sistem Elektronik (Tab IV). */
class Kse
{
    /** Nilai jawaban. */
    public const NILAI = ['a' => 5, 'b' => 2, 'c' => 1];

    public const KATEGORI = [
        'rendah'    => 'Rendah',
        'tinggi'    => 'Tinggi',
        'strategis' => 'Strategis',
    ];

    public const CATATAN = [
        'rendah'    => 'Dapat mengacu pada SNI ISO/IEC 27001 atau standar keamanan siber lain yang ditetapkan oleh BSSN.',
        'tinggi'    => 'Wajib menerapkan standar SNI ISO/IEC 27001 serta pemenuhan tambahan kontrol keamanan dari BSSN atau kementerian/lembaga terkait.',
        'strategis' => 'Wajib menerapkan standar SNI ISO/IEC 27001 serta pemenuhan tambahan kontrol keamanan dari BSSN atau kementerian/lembaga terkait.',
    ];

    public const PERTANYAAN = [
        1 => [
            'tanya' => 'Nilai investasi sistem elektronik yang terpasang',
            'opsi'  => [
                'a' => 'Lebih dari Rp30 miliar',
                'b' => 'Lebih dari Rp3 miliar s.d. Rp30 miliar',
                'c' => 'Kurang dari Rp3 miliar',
            ],
        ],
        2 => [
            'tanya' => 'Total anggaran operasional tahunan yang dialokasikan untuk pengelolaan Sistem Elektronik',
            'opsi'  => [
                'a' => 'Lebih dari Rp10 miliar',
                'b' => 'Lebih dari Rp1 miliar s.d. Rp10 miliar',
                'c' => 'Kurang dari Rp1 miliar',
            ],
        ],
        3 => [
            'tanya' => 'Memiliki kewajiban kepatuhan terhadap Peraturan atau Standar tertentu',
            'opsi'  => [
                'a' => 'Peraturan atau Standar nasional dan internasional',
                'b' => 'Peraturan atau Standar nasional',
                'c' => 'Tidak ada Peraturan khusus',
            ],
        ],
        4 => [
            'tanya' => 'Menggunakan teknik kriptografi khusus untuk keamanan informasi dalam Sistem Elektronik',
            'opsi'  => [
                'a' => 'Teknik kriptografi khusus yang disertifikasi oleh Negara',
                'b' => 'Teknik kriptografi sesuai standar industri, tersedia secara publik atau dikembangkan sendiri',
                'c' => 'Tidak ada penggunaan teknik kriptografi',
            ],
        ],
        5 => [
            'tanya' => 'Jumlah pengguna Sistem Elektronik',
            'opsi'  => [
                'a' => 'Lebih dari 5.000 pengguna',
                'b' => '1.000 sampai dengan 5.000 pengguna',
                'c' => 'Kurang dari 1.000 pengguna',
            ],
        ],
        6 => [
            'tanya' => 'Data pribadi yang dikelola Sistem Elektronik',
            'opsi'  => [
                'a' => 'Data pribadi yang memiliki hubungan dengan Data Pribadi lainnya',
                'b' => 'Data pribadi yang bersifat individu dan/atau data pribadi yang terkait dengan kepemilikan badan usaha',
                'c' => 'Tidak ada data pribadi',
            ],
        ],
        7 => [
            'tanya' => 'Tingkat klasifikasi/kekritisan Data yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau penerobosan keamanan informasi',
            'opsi'  => [
                'a' => 'Sangat Rahasia',
                'b' => 'Rahasia dan/atau Terbatas',
                'c' => 'Biasa',
            ],
        ],
        8 => [
            'tanya' => 'Tingkat kekritisan proses yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau penerobosan keamanan informasi',
            'opsi'  => [
                'a' => 'Proses yang berisiko mengganggu hajat hidup orang banyak dan memberi dampak langsung pada layanan publik',
                'b' => 'Proses yang berisiko mengganggu hajat hidup orang banyak dan memberi dampak tidak langsung',
                'c' => 'Proses yang hanya berdampak pada bisnis perusahaan',
            ],
        ],
        9 => [
            'tanya' => 'Dampak dari kegagalan Sistem Elektronik',
            'opsi'  => [
                'a' => 'Membahayakan pertahanan keamanan negara',
                'b' => 'Tidak tersedianya layanan publik berskala nasional atau berdampak pada layanan di sektor lain',
                'c' => 'Tidak tersedianya layanan publik dalam 1 provinsi atau internal 1 instansi/perusahaan',
            ],
        ],
        10 => [
            'tanya' => 'Potensi kerugian atau dampak negatif dari insiden ditembusnya keamanan informasi Sistem Elektronik (sabotase, terorisme)',
            'opsi'  => [
                'a' => 'Menimbulkan korban jiwa',
                'b' => 'Terbatas pada kerugian finansial',
                'c' => 'Mengakibatkan gangguan operasional sementara (tidak membahayakan dan mengakibatkan kerugian finansial)',
            ],
        ],
    ];

    /** Kategori berdasarkan skor: 10–15 Rendah, 16–34 Tinggi, 35–50 Strategis. */
    public static function kategori(int $skor): string
    {
        return match (true) {
            $skor >= 35 => 'strategis',
            $skor >= 16 => 'tinggi',
            default     => 'rendah',
        };
    }

    /** ['skor' => int, 'kategori' => string], atau null bila ada pertanyaan yang belum dijawab. */
    public static function hitung(array $d): ?array
    {
        $skor = 0;
        foreach (array_keys(self::PERTANYAAN) as $n) {
            $v = $d["kse_$n"] ?? null;
            if (! isset(self::NILAI[$v])) return null;
            $skor += self::NILAI[$v];
        }

        return ['skor' => $skor, 'kategori' => self::kategori($skor)];
    }

    public static function rules(): array
    {
        $rules = [];
        foreach (array_keys(self::PERTANYAAN) as $n) {
            $rules["kse_$n"] = ['required', Rule::in(array_keys(self::NILAI))];
            $rules["ket_$n"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }

    public static function messages(): array
    {
        $pesan = [];
        foreach (array_keys(self::PERTANYAAN) as $n) {
            $pesan["kse_$n.required"] = "Pilih salah satu jawaban untuk pertanyaan $n.";
            $pesan["kse_$n.in"]       = "Jawaban pertanyaan $n tidak valid.";
            $pesan["ket_$n.max"]      = "Keterangan pertanyaan $n maksimal :max karakter.";
        }

        return $pesan;
    }
}
