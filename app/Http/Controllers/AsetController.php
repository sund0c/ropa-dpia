<?php

namespace App\Http\Controllers;

use App\Support\Kse;
use App\Support\PdfFooter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\LabelTte;

class AsetController extends Controller
{
    /** Daftar tab. Tab berikutnya cukup ditambahkan di sini + di rules(). */
    public const TABS = [
        'informasi'    => 'I. Informasi Aset',
        'kritikalitas' => 'II. Kritikalitas Aset (CIA)',
        'iiv'          => 'III. Infrastruktur Informasi Vital (IIV)',
        'kse'          => 'IV. Kategorisasi Sistem Elektronik',
    ];

    public const KLASIFIKASI = [
        'data'             => 'Data dan Informasi',
        'perangkat_lunak'  => 'Perangkat Lunak',
        'perangkat_keras'  => 'Perangkat Keras',
        'sarana_penunjang' => 'Sarana Penunjang',
        'sdm'              => 'SDM',
    ];

    /** Klasifikasi → kelompok field Kelengkapan Data (PK dan Sarana Penunjang memakai field yang sama). */
    public const KELOMPOK = [
        'data'             => 'data',
        'perangkat_lunak'  => 'pl',
        'perangkat_keras'  => 'pk',
        'sarana_penunjang' => 'pk',
        'sdm'              => 'sdm',
    ];

    /** Urutan field Kelengkapan Data (Tab I bagian B) per kelompok, untuk PDF. */
    private const FIELD_KELOMPOK = [
        'pl'   => ['url', 'versi', 'lisensi', 'tgl_lisensi_berakhir', 'vendor', 'platform', 'lead_developer', 'lokasi_hosting'],
        'pk'   => ['merk', 'model', 'serial_number', 'kapasitas', 'tahun_perolehan', 'kondisi', 'lokasi_fisik', 'spesifikasi', 'ip_address'],
        'data' => ['bentuk_informasi', 'format', 'klasifikasi_data', 'retensi', 'enkripsi', 'metode_enkripsi'],
        'sdm'  => ['jabatan', 'unit_kerja', 'no_hp', 'email', 'tipe', 'tgl_kontrak_berakhir', 'akses_sistem'],
    ];

    // --- Perangkat Lunak ---
    public const LISENSI = [
        'proprietary' => 'Proprietary',
        'opensource'  => 'Open Source',
        'freeware'    => 'Freeware',
        'in_house'    => 'In-House',
    ];

    public const VENDOR = [
        'mandiri'      => 'Mandiri',
        'pihak_ketiga' => 'Pihak Ketiga',
    ];

    public const PLATFORM = [
        'web'     => 'Web',
        'mobile'  => 'Mobile',
        'desktop' => 'Desktop',
        'server'  => 'Server',
    ];

    public const HOSTING = [
        'pihak_ketiga'     => 'Pihak Ketiga',
        'pdn'              => 'PDN (Pusat Data Nasional)',
        'pusat_data_pemda' => 'Pusat Data Pemda',
        'pemilik_aset'     => 'Pemilik Aset',
    ];

    // --- Perangkat Keras & Sarana Penunjang ---
    public const KONDISI = [
        'baik'         => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak'        => 'Rusak',
    ];

    // --- Data dan Informasi ---
    public const BENTUK = [
        'elektronik'       => 'Elektronik',
        'fisik'            => 'Fisik',
        'elektronik_fisik' => 'Elektronik dan Fisik',
    ];

    public const KLASIFIKASI_DATA = [
        'publik'   => 'Publik',
        'terbatas' => 'Terbatas',
        'rahasia'  => 'Rahasia',
    ];

    public const ENKRIPSI = [
        'ada'   => 'Ada',
        'tidak' => 'Tidak',
    ];

    // --- SDM ---
    public const TIPE_SDM = [
        'internal'     => 'Internal',
        'pihak_ketiga' => 'Pihak Ketiga',
    ];

    // --- Tab II: Kritikalitas (CIA) ---
    public const LEVEL = [
        'rendah' => 'Rendah',
        'sedang' => 'Sedang',
        'tinggi' => 'Tinggi',
    ];

    private const BOBOT = ['rendah' => 1, 'sedang' => 2, 'tinggi' => 3];

    public const CIA = [
        'c' => [
            'huruf' => 'C',
            'judul' => 'A. Confidentiality',
            'sub'   => 'Kerahasiaan',
            'opsi'  => [
                'rendah' => 'Tidak berdampak — informasi memang bersifat publik atau tidak sensitif',
                'sedang' => 'Menimbulkan kerugian reputasi atau gangguan operasional internal',
                'tinggi' => 'Menimbulkan kerugian serius — menyangkut data pribadi, keuangan, rahasia jabatan, atau keamanan negara',
            ],
        ],
        'i' => [
            'huruf' => 'I',
            'judul' => 'B. Integrity',
            'sub'   => 'Integritas',
            'opsi'  => [
                'rendah' => 'Tidak berdampak — kesalahan mudah dideteksi dan dikoreksi tanpa konsekuensi',
                'sedang' => 'Menyebabkan kesalahan proses atau data yang perlu upaya koreksi',
                'tinggi' => 'Menyebabkan kerugian besar, keputusan salah, atau gangguan layanan utama',
            ],
        ],
        'a' => [
            'huruf' => 'A',
            'judul' => 'C. Availability',
            'sub'   => 'Ketersediaan',
            'opsi'  => [
                'rendah' => 'Tidak berdampak — dapat ditunda atau digantikan sementara tanpa konsekuensi',
                'sedang' => 'Menghambat sebagian kegiatan namun masih ada alternatif atau solusi sementara',
                'tinggi' => 'Menghentikan layanan penting atau operasional utama pemerintahan',
            ],
        ],
    ];

    // --- Tab III: Infrastruktur Informasi Vital (IIV) ---
    public const LEVEL_IIV = [
        'kritis'   => 'Kritis',
        'terbatas' => 'Terbatas',
        'minor'    => 'Minor',
    ];

    public const STATUS_IIV = [
        'vital' => 'Termasuk INFRASTRUKTUR INFORMASI VITAL!!',
        'bukan' => 'Bukan termasuk Infrastruktur Informasi Vital',
    ];

    public const IIV = [
        'ops' => [
            'judul' => 'A. Dampak Operasional',
            'sub'   => 'Gangguan kelangsungan layanan',
            'opsi'  => [
                'kritis'   => 'Menimbulkan gangguan pada skala Nasional; layanan pemerintahan terhenti total atau tidak dapat diakses secara luas; pemulihan membutuhkan waktu lebih dari 24 jam.',
                'terbatas' => 'Menimbulkan gangguan terbatas pada layanan utama lingkup Provinsi Bali; sebagian fungsi layanan masih dapat berjalan; pemulihan dapat diselesaikan dalam waktu kurang dari 24 jam.',
                'minor'    => 'Gangguan sangat kecil atau tidak berdampak signifikan terhadap operasional; layanan tetap berjalan normal; tidak memerlukan eskalasi pemulihan khusus.',
            ],
        ],
        'data' => [
            'judul' => 'B. Dampak Data / Informasi',
            'sub'   => 'Kerugian atas data/informasi',
            'opsi'  => [
                'kritis'   => 'Berpotensi mengakibatkan kebocoran, kerusakan, atau hilangnya data strategis/rahasia negara berskala nasional; dapat mengancam keamanan nasional atau kedaulatan informasi.',
                'terbatas' => 'Berpotensi mengakibatkan kebocoran atau kerusakan data internal pemerintah lingkup provinsi; berdampak pada privasi individu atau integritas data layanan publik daerah.',
                'minor'    => 'Tidak berdampak signifikan terhadap kerahasiaan, integritas, atau ketersediaan data; data yang terpengaruh bersifat publik atau tidak sensitif.',
            ],
        ],
        'fin' => [
            'judul' => 'C. Dampak Finansial',
            'sub'   => 'Estimasi kerugian finansial',
            'opsi'  => [
                'kritis'   => 'Berpotensi mengakibatkan kerugian finansial yang sangat besar pada skala nasional (APBN), termasuk denda regulasi berat, tuntutan hukum massal, atau kerugian ekonomi yang meluas.',
                'terbatas' => 'Berpotensi mengakibatkan kerugian finansial yang signifikan pada skala daerah (APBD Provinsi/Kabupaten); memerlukan alokasi anggaran pemulihan atau kompensasi khusus.',
                'minor'    => 'Kerugian finansial yang dapat diabaikan atau sangat kecil; dapat diselesaikan melalui anggaran operasional rutin tanpa eskalasi khusus.',
            ],
        ],
        'umum' => [
            'judul' => 'D. Dampak Umum / Sosial',
            'sub'   => 'Potensi kegaduhan masyarakat',
            'opsi'  => [
                'kritis'   => 'Berpotensi menimbulkan kegaduhan massal berskala provinsi atau nasional; dapat memicu kepanikan publik, demonstrasi, atau keresahan sosial yang meluas; merusak kepercayaan masyarakat terhadap pemerintah secara luas.',
                'terbatas' => 'Berpotensi menimbulkan kegaduhan atau ketidaknyamanan terbatas pada kelompok/individu tertentu; tidak bersifat massal; dampak sosial lebih kecil dari skala provinsi; dapat dikelola melalui komunikasi publik biasa.',
                'minor'    => 'Tidak menimbulkan kegaduhan publik; masyarakat umum tidak terpengaruh atau dampak sangat terlokalisir dan dapat diselesaikan secara internal.',
            ],
        ],
        'ketergantungan' => [
            'judul' => 'E. Dampak Ketergantungan',
            'sub'   => 'Cascading failure sistem lain',
            'opsi'  => [
                'kritis'   => 'Aset ini menjadi tulang punggung bagi banyak sistem/layanan lain; kegagalan aset ini akan memicu kegagalan berantai (cascading failure) pada infrastruktur kritikal lainnya di tingkat nasional atau lintas sektor.',
                'terbatas' => 'Beberapa sistem/layanan di lingkup provinsi bergantung pada aset ini; kegagalan akan mengganggu layanan terkait tetapi tidak memicu kegagalan masif; pemulihan dapat dilakukan secara bertahap.',
                'minor'    => 'Sedikit atau tidak ada sistem lain yang bergantung pada aset ini; kegagalan bersifat terisolasi dan tidak berdampak pada infrastruktur lain.',
            ],
        ],
    ];

    public static function options(): array
    {
        return [
            'klasifikasi'      => self::KLASIFIKASI,
            'lisensi'          => self::LISENSI,
            'vendor'           => self::VENDOR,
            'platform'         => self::PLATFORM,
            'hosting'          => self::HOSTING,
            'kondisi'          => self::KONDISI,
            'bentuk'           => self::BENTUK,
            'klasifikasi_data' => self::KLASIFIKASI_DATA,
            'enkripsi'         => self::ENKRIPSI,
            'tipe_sdm'         => self::TIPE_SDM,
        ];
    }

    // =====================================================================
    //  Status tab
    // =====================================================================

    /** Tab yang berlaku untuk aset ini. Tab IV (KSE) hanya untuk klasifikasi Perangkat Lunak. */
    public static function tabsAktif(array $aset): array
    {
        $tabs = self::TABS;

        if (($aset['informasi']['klasifikasi'] ?? null) !== 'perangkat_lunak') {
            unset($tabs['kse']);
        }

        return $tabs;
    }

    /** Label tab yang berlaku tetapi belum pernah disimpan. */
    public static function tabBelumDisimpan(array $aset): array
    {
        return array_values(array_diff_key(self::tabsAktif($aset), $aset));
    }

    /** Lengkap = semua tab yang berlaku sudah disimpan. */
    public static function lengkap(array $aset): bool
    {
        return self::tabBelumDisimpan($aset) === [];
    }

    // =====================================================================
    //  Perhitungan
    // =====================================================================

    /** Kritikalitas = nilai tertinggi dari C, I, A. Null bila belum lengkap. */
    public static function hitungKritikalitas(array $d): ?string
    {
        $nilai = [];
        foreach (array_keys(self::CIA) as $k) {
            $v = $d[$k] ?? null;
            if (! isset(self::BOBOT[$v])) return null;
            $nilai[] = self::BOBOT[$v];
        }

        return array_search(max($nilai), self::BOBOT, true);
    }

    /** 'vital' bila minimal 1 Kritis atau minimal 3 Terbatas; selain itu 'bukan'. Null bila belum lengkap. */
    public static function hitungIiv(array $d): ?string
    {
        $hitung = ['kritis' => 0, 'terbatas' => 0, 'minor' => 0];
        foreach (array_keys(self::IIV) as $k) {
            $v = $d[$k] ?? null;
            if (! isset($hitung[$v])) return null;
            $hitung[$v]++;
        }

        return ($hitung['kritis'] >= 1 || $hitung['terbatas'] >= 3) ? 'vital' : 'bukan';
    }

    // =====================================================================
    //  Form & simpan
    // =====================================================================

    public function form(Request $request)
    {
        $aset = session('aset', []);
        $tabs = self::tabsAktif($aset);
        $tab  = $request->query('tab', array_key_first($tabs));
        abort_unless(array_key_exists($tab, self::TABS), 404);

        if (! array_key_exists($tab, $tabs)) {
            return redirect()->route('aset.form')
                ->withErrors(['aset' => self::TABS[$tab] . ' hanya berlaku untuk aset dengan klasifikasi Perangkat Lunak.']);
        }

        return view('aset.form', [
            'tabs'      => $tabs,
            'tab'       => $tab,
            'aset'      => $aset,
            'options'   => self::options(),
            'kelompok'  => self::KELOMPOK,
            'cia'       => self::CIA,
            'level'     => self::LEVEL,
            'iiv'       => self::IIV,
            'levelIiv'  => self::LEVEL_IIV,
            'statusIiv' => self::STATUS_IIV,
        ]);
    }

    public function save(Request $request, string $tab)
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $aset = session('aset', []);

        if (! array_key_exists($tab, self::tabsAktif($aset))) {
            return redirect()->route('aset.form')
                ->withErrors(['aset' => self::TABS[$tab] . ' hanya berlaku untuk aset dengan klasifikasi Perangkat Lunak.']);
        }

        $data = $this->dataTab($tab, $request);

        $aset['nomor'] ??= $this->generateNomor();
        $aset[$tab]      = $data;

        // Tab yang tidak berlaku lagi (mis. klasifikasi bukan Perangkat Lunak → Tab IV) dibuang isiannya
        $tabs = self::tabsAktif($aset);
        foreach (array_keys(array_diff_key(self::TABS, $tabs)) as $tidakBerlaku) {
            unset($aset[$tidakBerlaku]);
        }

        $aset['diperbarui'] = now()->toIso8601String();
        session(['aset' => $aset]);

        // Lanjut otomatis ke tab berikutnya (sesuai tab yang berlaku); tab terakhir tetap di tempat
        $urutan = array_keys($tabs);
        $next   = $urutan[array_search($tab, $urutan, true) + 1] ?? $tab;

        $pesan = self::TABS[$tab] . ' tersimpan.'
            . ($next !== $tab ? ' Silakan lanjutkan ' . self::TABS[$next] . '.' : '');

        return redirect()->route('aset.form', ['tab' => $next])->with('saved', $pesan);
    }

    /**
     * Bersihkan + validasi isian satu tab. Dipakai oleh tombol Simpan dan oleh Impor JSON,
     * sehingga keduanya memakai aturan yang sama. Melempar ValidationException bila gagal.
     */
    public function dataTab(string $tab, Request $request): array
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $data = Validator::make(
            $request->all(),
            $this->rules($tab, $request),
            $this->messages() + Kse::messages(),
            $this->attributes()
        )->validate();

        // Metode enkripsi hanya relevan bila enkripsi = ada
        if (($data['enkripsi'] ?? null) === 'tidak') {
            unset($data['metode_enkripsi']);
        }

        // Hasil turunan selalu dihitung di server, tidak pernah diambil dari input
        if ($tab === 'kritikalitas') {
            $data['kritikalitas'] = self::hitungKritikalitas($data);
        }
        if ($tab === 'iiv') {
            $data['status_iiv'] = self::hitungIiv($data);
        }
        if ($tab === 'kse') {
            $data += Kse::hitung($data);   // skor + kategori
        }

        return $data;
    }

    // =====================================================================
    //  Export PDF
    // =====================================================================

    /** [label => nilai tampilan] untuk bagian B Tab I, sesuai klasifikasi. */
    public static function kelengkapanPdf(array $info): array
    {
        $pilihan = [
            'lisensi'          => self::LISENSI,
            'vendor'           => self::VENDOR,
            'platform'         => self::PLATFORM,
            'lokasi_hosting'   => self::HOSTING,
            'kondisi'          => self::KONDISI,
            'bentuk_informasi' => self::BENTUK,
            'klasifikasi_data' => self::KLASIFIKASI_DATA,
            'enkripsi'         => self::ENKRIPSI,
            'tipe'             => self::TIPE_SDM,
        ];
        $label    = (new self)->attributes();
        $kelompok = self::KELOMPOK[$info['klasifikasi'] ?? ''] ?? null;
        $baris    = [];

        foreach (self::FIELD_KELOMPOK[$kelompok] ?? [] as $f) {
            if ($f === 'metode_enkripsi' && ($info['enkripsi'] ?? null) !== 'ada') continue;

            $v = $info[$f] ?? null;
            $baris[$label[$f]] = match (true) {
                blank($v)                   => '-',
                isset($pilihan[$f])         => $pilihan[$f][$v] ?? (string) $v,
                str_starts_with($f, 'tgl_') => Carbon::createFromFormat('!Y-m-d', $v)->locale('id')->translatedFormat('d F Y'),
                default                     => (string) $v,
            };
        }

        return $baris;
    }

    public function pdf(Request $request)
    {
        $aset = session('aset', []);

        if (! self::lengkap($aset)) {
            return redirect()->route('aset.form')
                ->withErrors(['aset' => 'Lengkapi dan simpan seluruh tab terlebih dahulu sebelum export PDF.']);
        }

        // Lokasi, tanggal penetapan & pilihan TTE: hanya untuk cetakan ini, tidak disimpan
        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte'), [
            'lokasi'  => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tte'     => ['nullable', 'array'],
            'tte.*'   => [Rule::in(['pj', 'pimpinan'])],
        ]);

        if ($v->fails()) {
            return redirect()->route('aset.form', ['tab' => array_key_last(self::tabsAktif($aset))])
                ->withErrors(['aset' => 'Lokasi dan tanggal penetapan wajib diisi dengan benar sebelum mencetak PDF.']);
        }

        $input = $v->validated();

        [$label, $galat] = LabelTte::ambil($request, array_values(array_unique($input['tte'] ?? [])));
        if ($galat) {
            return redirect()->route('aset.form', ['tab' => array_key_last(self::tabsAktif($aset))])
                ->withErrors(['aset' => $galat]);
        }


        $penetapan = [
            'lokasi'  => $input['lokasi'],
            'label'   => $label,
            'tanggal' => Carbon::createFromFormat('!Y-m-d', $input['tanggal'])->locale('id')->translatedFormat('d F Y'),
            'tte'     => array_values(array_unique($input['tte'] ?? [])),
        ];

        $pdf = Pdf::loadView('aset.pdf', [
            'aset'        => $aset,
            'kelengkapan' => self::kelengkapanPdf($aset['informasi']),
            'penetapan'   => $penetapan,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        PdfFooter::tambah($pdf, $aset['nomor']);

        return $pdf->stream('Aset-' . $aset['nomor'] . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    // =====================================================================
    //  Validasi
    // =====================================================================

    private function rules(string $tab, Request $request): array
    {
        return match ($tab) {
            'informasi'    => $this->rulesInformasi($request),
            'kritikalitas' => $this->rulesPilihan(array_keys(self::CIA), array_keys(self::LEVEL)),
            'iiv'          => $this->rulesPilihan(array_keys(self::IIV), array_keys(self::LEVEL_IIV)),
            'kse'          => Kse::rules(),
        };
    }

    /** Setiap aspek/dimensi wajib dipilih salah satu dari daftar level. */
    private function rulesPilihan(array $aspek, array $level): array
    {
        $rules = [];
        foreach ($aspek as $k) {
            $rules[$k] = ['required', Rule::in($level)];
        }

        return $rules;
    }

    private function rulesInformasi(Request $request): array
    {
        // A. Informasi Dasar
        $rules = [
            'nama_aset'        => ['required', 'string', 'max:255'],
            'klasifikasi'      => ['required', Rule::in(array_keys(self::KLASIFIKASI))],
            'pemilik_aset'     => ['required', 'string', 'max:255'],
            'pimpinan_nama'    => ['required', 'string', 'max:255'],
            'pimpinan_jabatan' => ['required', 'string', 'max:255'],
            'pj_nama'          => ['required', 'string', 'max:255'],
            'pj_jabatan'       => ['required', 'string', 'max:255'],
            'keterangan'       => ['required', 'string', 'max:2000'],
        ];

        // B. Kelengkapan Data: hanya kelompok sesuai klasifikasi yang divalidasi (dan disimpan)
        $kelompok = self::KELOMPOK[$request->input('klasifikasi')] ?? null;

        return $rules + match ($kelompok) {
            'pl' => [
                'url'                  => ['required', 'string', 'max:255', 'url:http,https'],
                'versi'                => ['required', 'string', 'max:50'],
                'lisensi'              => ['required', Rule::in(array_keys(self::LISENSI))],
                'tgl_lisensi_berakhir' => ['nullable', 'date_format:Y-m-d'],
                'vendor'               => ['required', Rule::in(array_keys(self::VENDOR))],
                'platform'             => ['required', Rule::in(array_keys(self::PLATFORM))],
                'lead_developer'       => ['required', 'string', 'max:255'],
                'lokasi_hosting'       => ['required', Rule::in(array_keys(self::HOSTING))],
            ],
            'pk' => [
                'merk'            => ['required', 'string', 'max:100'],
                'model'           => ['required', 'string', 'max:100'],
                'serial_number'   => ['required', 'string', 'max:100'],
                'kapasitas'       => ['required', 'string', 'max:100'],
                'tahun_perolehan' => ['required', 'integer', 'digits:4', 'min:1970', 'max:' . now()->year],
                'kondisi'         => ['required', Rule::in(array_keys(self::KONDISI))],
                'lokasi_fisik'    => ['required', 'string', 'max:255'],
                'spesifikasi'     => ['required', 'string', 'max:2000'],
                'ip_address'      => ['nullable', 'ip'],
            ],
            'data' => [
                'bentuk_informasi' => ['required', Rule::in(array_keys(self::BENTUK))],
                'format'           => ['required', 'string', 'max:100'],
                'klasifikasi_data' => ['required', Rule::in(array_keys(self::KLASIFIKASI_DATA))],
                'retensi'          => ['required', 'string', 'max:100'],
                'enkripsi'         => ['required', Rule::in(array_keys(self::ENKRIPSI))],
                'metode_enkripsi'  => ['nullable', 'required_if:enkripsi,ada', 'string', 'max:255'],
            ],
            'sdm' => [
                'jabatan'              => ['required', 'string', 'max:255'],
                'unit_kerja'           => ['required', 'string', 'max:255'],
                'no_hp'                => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
                'email'                => ['required', 'email:rfc', 'max:255'],
                'tipe'                 => ['required', Rule::in(array_keys(self::TIPE_SDM))],
                'tgl_kontrak_berakhir' => ['nullable', 'date_format:Y-m-d'],
                'akses_sistem'         => ['required', 'string', 'max:2000'],
            ],
            default => [],
        };
    }

    private function messages(): array
    {
        $pesan = [
            'required'    => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi bila Enkripsi = Ada.',
            'email'       => ':attribute harus berupa alamat email yang valid.',
            'max'         => ':attribute maksimal :max karakter.',
            'regex'       => 'Format :attribute tidak valid.',
            'in'          => 'Pilihan :attribute tidak valid.',
            'url'         => ':attribute harus berupa URL lengkap, diawali http:// atau https://',
            'ip'          => ':attribute harus berupa alamat IP yang valid (IPv4/IPv6).',
            'date_format' => ':attribute harus berupa tanggal yang valid.',
            'integer'     => ':attribute harus berupa angka bulat.',
            'digits'      => ':attribute harus :digits digit.',
            'tahun_perolehan.min' => 'Tahun Perolehan minimal :min.',
            'tahun_perolehan.max' => 'Tahun Perolehan tidak boleh melebihi tahun ini (:max).',
        ];

        // Tab II & III: "Pilih salah satu nilai <aspek>."
        foreach (array_keys(self::CIA + self::IIV) as $k) {
            $pesan["$k.required"] = 'Pilih salah satu nilai :attribute.';
        }

        return $pesan;
    }

    private function attributes(): array
    {
        return [
            // Tab I — A. Informasi Dasar
            'nama_aset'            => 'Nama Aset',
            'klasifikasi'          => 'Klasifikasi Aset',
            'pemilik_aset'         => 'Nama Instansi Pemilik Aset',
            'pimpinan_nama'        => 'Nama Pimpinan Instansi Pemilik Aset',
            'pimpinan_jabatan'     => 'Jabatan Pemilik Aset',
            'pj_nama'              => 'Penanggung Jawab Aset',
            'pj_jabatan'           => 'Jabatan Penanggung Jawab',
            'keterangan'           => 'Keterangan',
            // Tab I — B. Kelengkapan Data
            'url'                  => 'URL',
            'versi'                => 'Versi',
            'lisensi'              => 'Lisensi',
            'tgl_lisensi_berakhir' => 'Tanggal Lisensi Berakhir',
            'vendor'               => 'Vendor/Pengembang',
            'platform'             => 'Platform',
            'lead_developer'       => 'Lead Developer',
            'lokasi_hosting'       => 'Lokasi Hosting',
            'merk'                 => 'Merk',
            'model'                => 'Model',
            'serial_number'        => 'Serial Number',
            'kapasitas'            => 'Kapasitas',
            'tahun_perolehan'      => 'Tahun Perolehan',
            'kondisi'              => 'Kondisi',
            'lokasi_fisik'         => 'Lokasi Fisik',
            'spesifikasi'          => 'Spesifikasi',
            'ip_address'           => 'IP Address',
            'bentuk_informasi'     => 'Bentuk Informasi',
            'format'               => 'Format',
            'klasifikasi_data'     => 'Klasifikasi Data',
            'retensi'              => 'Retensi',
            'enkripsi'             => 'Enkripsi',
            'metode_enkripsi'      => 'Metode Enkripsi',
            'jabatan'              => 'Jabatan',
            'unit_kerja'           => 'Unit Kerja',
            'no_hp'                => 'No. HP',
            'email'                => 'Email',
            'tipe'                 => 'Tipe',
            'tgl_kontrak_berakhir' => 'Tanggal Kontrak Berakhir',
            'akses_sistem'         => 'Akses ke Sistem',
            // Tab II
            'c'                    => 'Confidentiality',
            'i'                    => 'Integrity',
            'a'                    => 'Availability',
            // Tab III
            'ops'                  => 'Dampak Operasional',
            'data'                 => 'Dampak Data / Informasi',
            'fin'                  => 'Dampak Finansial',
            'umum'                 => 'Dampak Umum / Sosial',
            'ketergantungan'       => 'Dampak Ketergantungan',
        ];
    }

    private function generateNomor(): string
    {
        return 'ASET-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
