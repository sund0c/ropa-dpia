<?php

namespace App\Support;

class MetodologiRisiko
{
    /** Level risiko: nama & warna tetap (tidak bisa diedit). */
    public const KATEGORI = [
        1 => ['nama' => 'Sangat Rendah', 'warna' => '#4caf50'],
        2 => ['nama' => 'Rendah',        'warna' => '#9ccc65'],
        3 => ['nama' => 'Sedang',        'warna' => '#fff14d'],
        4 => ['nama' => 'Tinggi',        'warna' => '#f4b942'],
        5 => ['nama' => 'Sangat Tinggi', 'warna' => '#e0362b'],
    ];

    /** Level yang memakai teks putih di atas warnanya. */
    public const TEKS_PUTIH = [1, 5];

    /** Siklus pemrosesan Data Pribadi, Pasal 16 ayat (1) UU PDP. Label tetap. */
    public const SIKLUS = [
        'pemerolehan' => 'Pemerolehan dan Pengumpulan',
        'pengolahan'  => 'Pengolahan dan Penganalisisan',
        'penyimpanan' => 'Penyimpanan',
        'perbaikan'   => 'Perbaikan dan Pembaruan',
        'penampilan'  => 'Penampilan, Pengumuman, Transfer, Penyebarluasan, atau Pengungkapan',
        'penghapusan' => 'Penghapusan atau Pemusnahan',
    ];

    /** Contoh risiko inheren default per siklus (ID tetap agar bisa dirujuk Tab VII). */
    public const DEFAULT_INHEREN = [
        'pemerolehan' => [['id' => 'def-1', 'risiko' => 'Memperoleh data dari sumber yang tidak sah', 'kemungkinan' => 3, 'dampak' => 4]],
        'pengolahan'  => [['id' => 'def-2', 'risiko' => 'Data tidak akurat, tidak lengkap, atau tidak terkini', 'kemungkinan' => 3, 'dampak' => 3]],
        'penyimpanan' => [['id' => 'def-3', 'risiko' => 'Kebocoran data akibat serangan siber atau human error, tidak ada kebijakan retensi', 'kemungkinan' => 3, 'dampak' => 5]],
        'perbaikan'   => [['id' => 'def-4', 'risiko' => 'Memberikan akses kepada pihak yang tidak berwenang', 'kemungkinan' => 5, 'dampak' => 3]],
        'penampilan'  => [['id' => 'def-5', 'risiko' => 'Penerima data tidak memiliki standar keamanan yang memadai', 'kemungkinan' => 4, 'dampak' => 3]],
        'penghapusan' => [['id' => 'def-6', 'risiko' => 'Menghapus data sebelum masa retensi berakhir', 'kemungkinan' => 2, 'dampak' => 4]],
    ];

    public const DEFAULT = [
        // Batas atas level 1–4 (batas bawah = batas atas level sebelumnya + 1; level 5 selalu s.d. 25)
        'kategori_batas' => [1 => 5, 2 => 10, 3 => 15, 4 => 20],

        'kemungkinan' => [
            1 => ['nama' => 'Kecil Kemungkinan',     'periode' => 'Kemungkinan terjadi > 1 tahun'],
            2 => ['nama' => 'Jarang Terjadi',        'periode' => 'Kemungkinan terjadi > 3 bulan dan ≤ 1 tahun'],
            3 => ['nama' => 'Kadang-Kadang Terjadi', 'periode' => 'Kemungkinan terjadi > 1 bulan dan ≤ 3 bulan'],
            4 => ['nama' => 'Sering Terjadi',        'periode' => 'Kemungkinan terjadi > 1 hari dan ≤ 1 bulan'],
            5 => ['nama' => 'Sangat Sering Terjadi', 'periode' => 'Kemungkinan terjadi ≤ 1 hari'],
        ],

        'dampak' => [
            1 => [
                'nama'      => 'Tidak Signifikan',
                'finansial' => '< 100 jt',
                'reputasi'  => 'Pemberitaan negatif yang sempat muncul pada salah satu media utama/medsos namun hanya sesaat sehingga respons publik sangat terbatas',
                'kepatuhan' => 'Tindakan regulator berdampak kecil kepada institusi, seperti peringatan secara verbal',
                'hukum'     => 'Adanya tindakan hukum terhadap perusahaan namun kecil kemungkinan perusahaan akan kalah',
            ],
            2 => [
                'nama'      => 'Kurang Signifikan',
                'finansial' => '100 jt s.d. < 300 jt',
                'reputasi'  => 'Pemberitaan negatif yang muncul pada salah satu media utama/medsos. Respons publik sudah mulai meningkat',
                'kepatuhan' => 'Berpotensi teguran tertulis dari regulator',
                'hukum'     => 'Adanya tindakan hukum terhadap perusahaan dan ada kemungkinan perusahaan akan kalah, namun penyelesaian di luar pengadilan dimungkinkan',
            ],
            3 => [
                'nama'      => 'Cukup Signifikan',
                'finansial' => '300 jt s.d. 1 miliar',
                'reputasi'  => 'Pemberitaan negatif yang muncul pada beberapa media utama maupun medsos. Respons publik tinggi',
                'kepatuhan' => 'Berpotensi teguran tertulis dari regulator dan diikuti dengan suspend kegiatan bisnis/operasional perusahaan',
                'hukum'     => 'Adanya tindakan hukum terhadap perusahaan atas pelanggaran besar dan ada kemungkinan perusahaan akan kalah, serta penyelesaian di luar pengadilan kecil kemungkinan, juga melibatkan investigasi oleh penegak hukum',
            ],
            4 => [
                'nama'      => 'Signifikan',
                'finansial' => '> 1 miliar s.d. 5 miliar',
                'reputasi'  => 'Pemberitaan negatif sudah menyebar ke semua media utama maupun beberapa medsos dan mendapat respons yang masif dari publik. Berdampak pada tingkat kepercayaan publik',
                'kepatuhan' => 'Berpotensi teguran tertulis dari regulator, diikuti dengan suspend kegiatan bisnis/operasional perusahaan serta dimungkinkan akan diikuti pencabutan izin usaha',
                'hukum'     => 'Adanya proses peradilan class action terhadap perusahaan atas pelanggaran sangat besar. Kemungkinan besar perusahaan akan kalah tanpa banyak potensi alternatif lainnya',
            ],
            5 => [
                'nama'      => 'Sangat Signifikan',
                'finansial' => '> 5 miliar',
                'reputasi'  => 'Pemberitaan negatif yang masif dan berkelanjutan di seluruh media nasional/internasional serta media sosial, memicu krisis kepercayaan publik yang akut secara luas, boikot layanan, hingga kecaman terbuka dari lembaga tertentu',
                'kepatuhan' => 'Tindakan penegakan hukum dan sanksi administratif terberat dari lembaga seperti pencabutan izin operasional/kegiatan bisnis institusi secara permanen, denda administratif maksimum berskala nasional, serta audit kepatuhan khusus secara menyeluruh oleh instansi berwenang',
                'hukum'     => 'Adanya proses peradilan gugatan massal (class action) berskala besar atau gugatan perdata bertubi-tubi dari para subjek data (pasien/keluarga), serta investigasi hukum pidana mendalam oleh aparat penegak hukum terhadap manajemen atau penanggung jawab institusi atas kelalaian fatal pelindungan data spesifik',
            ],
        ],
    ];

    /** Metodologi yang berlaku: default ditimpa nilai yang disimpan user, kecuali nama level (selalu default). */
    public static function nilai(array $tersimpan = []): array
    {
        $m = array_replace_recursive(self::DEFAULT, $tersimpan);

        foreach (range(1, 5) as $i) {
            $m['kemungkinan'][$i]['nama'] = self::DEFAULT['kemungkinan'][$i]['nama'];
            $m['dampak'][$i]['nama']      = self::DEFAULT['dampak'][$i]['nama'];
        }

        return $m;
    }

    /** Level risiko (1–5) untuk skor tertentu berdasarkan batas kategori. */
    public static function level(int $skor, array $m): int
    {
        foreach ([1, 2, 3, 4] as $i) {
            if ($skor <= (int) ($m['kategori_batas'][$i] ?? 0)) {
                return $i;
            }
        }
        return 5;
    }

    /**
     * Daftar risiko inheren yang bisa dirujuk: [id => kode, siklus, risiko, skor, level].
     * Kode (R{no siklus}.{urutan}) mengikuti posisi saat ini; ID tetap.
     */
    public static function daftarRisiko(array $penilaian): array
    {
        $m       = self::nilai($penilaian['metodologi'] ?? []);
        $inheren = $penilaian['inheren'] ?? self::DEFAULT_INHEREN;
        $hasil   = [];
        $no      = 0;

        foreach (self::SIKLUS as $s => $label) {
            $no++;
            $urut = 0;
            foreach ((array) ($inheren[$s] ?? []) as $r) {
                $urut++;
                if (empty($r['id'])) continue;   // data lama tanpa ID: simpan ulang Tab VI sekali

                $skor = (int) $r['kemungkinan'] * (int) $r['dampak'];
                $hasil[(string) $r['id']] = [
                    'kode'   => "R$no.$urut",
                    'siklus' => $label,
                    'risiko' => $r['risiko'],
                    'skor'   => $skor,
                    'level'  => self::level($skor, $m),
                ];
            }
        }

        return $hasil;
    }
}
