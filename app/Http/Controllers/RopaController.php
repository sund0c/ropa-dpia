<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\RopaStatus;
use Barryvdh\DomPDF\Facade\Pdf;

class RopaController extends Controller
{
    /** Daftar tab RoPA. Tab berikutnya cukup ditambahkan di sini + di rules(). */
    public const TABS = [
        'organisasi' => 'I. Informasi Organisasi & Penanggung Jawab',
        'aktivitas'  => 'II. Aktivitas & Legalitas Pemrosesan',
        'pemetaan'   => 'III. Pemetaan Aliran Data Pribadi',
        'hak'        => 'IV. Pemenuhan Hak Subjek Data Pribadi',
        'siklus'     => 'V. Siklus Hidup & Keamanan Data',
        'risiko'     => 'VI. Indikator Risiko Tinggi',

    ];

    public const DASAR_PEMROSESAN = [
        'consent'             => 'Persetujuan eksplisit subjek data (Consent)',
        'contract'            => 'Pemenuhan kewajiban perjanjian (Contractual)',
        'legal_obligation'    => 'Kewajiban hukum pengendali (Legal Obligation)',
        'vital_interest'      => 'Perlindungan kepentingan vital subjek (Vital Interests)',
        'public_interest'     => 'Kepentingan umum / pelayanan publik (Public Interests)',
        'legitimate_interest' => 'Kepentingan sah lainnya (Legitimate Interests)',
        'balancing'           => 'Keseimbangan kepentingan Pengendali dan hak Subjek Data Pribadi',
    ];

    /** Pasal 4 ayat (3) UU PDP */
    public const JENIS_DATA_UMUM = [
        'nama'              => 'Nama lengkap',
        'jenis_kelamin'     => 'Jenis kelamin',
        'kewarganegaraan'   => 'Kewarganegaraan',
        'agama'             => 'Agama',
        'status_perkawinan' => 'Status perkawinan',
        'kombinasi'         => 'Data Pribadi yang dikombinasikan untuk mengidentifikasi seseorang',
    ];

    /** Pasal 4 ayat (2) UU PDP */
    public const JENIS_DATA_SPESIFIK = [
        'kesehatan' => 'Data dan informasi kesehatan',
        'biometrik' => 'Data biometrik',
        'genetika'  => 'Data genetika',
        'kejahatan' => 'Catatan kejahatan',
        'anak'      => 'Data anak',
        'keuangan'  => 'Data keuangan pribadi',
        'lainnya'   => 'Data lainnya sesuai dengan ketentuan peraturan perundang-undangan',
    ];

    public const PERAN_PENERIMA = [
        'pengendali'         => 'Pengendali',
        'pengendali_bersama' => 'Pengendali Bersama',
        'prosesor'           => 'Prosesor',
    ];

    /** Hak Subjek Data Pribadi, Pasal 5–13 UU PDP */
    public const HAK_SUBJEK = [
        'informasi'           => 'Hak mendapatkan Informasi tentang kejelasan identitas, dasar kepentingan hukum, tujuan permintaan dan penggunaan Data Pribadi, dan akuntabilitas pihak yang meminta Data Pribadi. (Pasal 5 UU PDP)',
        'perbaikan'           => 'Hak melengkapi, memperbarui, dan/atau memperbaiki kesalahan dan/atau ketidakakuratan Data Pribadi tentang dirinya sesuai dengan tujuan pemrosesan Data Pribadi. (Pasal 6 UU PDP)',
        'akses'               => 'Hak mendapatkan akses dan memperoleh salinan Data Pribadi tentang dirinya sesuai dengan ketentuan peraturan perundang-undangan. (Pasal 7 UU PDP)',
        'penghapusan'         => 'Hak untuk mengakhiri pemrosesan, menghapus, dan/atau memusnahkan Data Pribadi tentang dirinya sesuai dengan ketentuan peraturan perundang-undangan. (Pasal 8 UU PDP)',
        'tarik_persetujuan'   => 'Hak menarik kembali persetujuan pemrosesan Data Pribadi tentang dirinya yang telah diberikan kepada Pengendali Data Pribadi. (Pasal 9 UU PDP)',
        'keberatan_otomatis'  => 'Hak untuk mengajukan keberatan atas tindakan pengambilan keputusan yang hanya didasarkan pada pemrosesan secara otomatis, termasuk pemrofilan, yang menimbulkan akibat hukum atau berdampak signifikan pada Subjek Data Pribadi. (Pasal 10 ayat 1 UU PDP)',
        'pembatasan'          => 'Hak menunda atau membatasi pemrosesan Data Pribadi secara proporsional sesuai dengan tujuan pemrosesan Data Pribadi. (Pasal 11 UU PDP)',
        'ganti_rugi'          => 'Hak menggugat dan menerima ganti rugi atas pelanggaran pemrosesan Data Pribadi tentang dirinya sesuai dengan ketentuan peraturan perundang-undangan. (Pasal 12 ayat 1 UU PDP)',
        'portabilitas_format' => 'Subjek Data Pribadi berhak mendapatkan dan/atau menggunakan Data Pribadi tentang dirinya dari Pengendali Data Pribadi dalam bentuk yang sesuai dengan struktur dan/atau format yang lazim digunakan atau dapat dibaca oleh sistem elektronik. (Pasal 13 ayat 1 UU PDP)',
        'portabilitas_kirim'  => 'Subjek Data Pribadi berhak menggunakan dan mengirimkan Data Pribadi tentang dirinya ke Pengendali Data Pribadi lainnya, sepanjang sistem yang digunakan dapat saling berkomunikasi secara aman sesuai dengan prinsip Pelindungan Data Pribadi berdasarkan UU PDP. (Pasal 13 ayat 2 UU PDP)',
    ];

    public const STATUS_SIKLUS = [
        'aktif'     => 'Aktif',
        'non_aktif' => 'Non Aktif',
    ];

    /** Pasal 34 ayat (2) UU PDP. */
    public const INDIKATOR_RISIKO = [
        'keputusan_otomatis'    => 'Pengambilan keputusan secara otomatis yang memiliki akibat hukum atau dampak yang signifikan terhadap Subjek Data Pribadi.',
        'data_spesifik'         => 'Pemrosesan atas Data Pribadi yang bersifat spesifik.',
        'skala_besar'           => 'Pemrosesan Data Pribadi dalam skala besar.',
        'pemantauan_sistematis' => 'Pemrosesan Data Pribadi untuk kegiatan evaluasi, penskoran, atau pemantauan yang sistematis terhadap Subjek Data Pribadi.',
        'pencocokan_data'       => 'Pemrosesan Data Pribadi untuk kegiatan pencocokan atau penggabungan sekelompok data.',
        'teknologi_baru'        => 'Penggunaan teknologi baru dalam pemrosesan Data Pribadi.',
        'pembatasan_hak'        => 'Pemrosesan Data Pribadi yang membatasi pelaksanaan hak Subjek Data Pribadi.',
    ];

    /** Indikator yang nilainya diturunkan dari Tab III, bukan dipilih manual. */
    public const INDIKATOR_OTOMATIS = 'data_spesifik';

    /** Daftar satu kolom (name[]). Baris kosong dibuang sebelum validasi. */
    private const LIST_FIELDS = ['tahapan', 'referensi_hukum', 'kategori_subjek', 'pengamanan'];

    /** Daftar multi-kolom (name[i][kolom]). Baris yang semua kolomnya kosong dibuang. */
    private const ROW_FIELDS = [
        'pengumpulan' => ['sumber', 'lokasi'],
        'transfer'    => ['organisasi', 'peran', 'kontak', 'tujuan', 'mekanisme', 'data'],
    ];

    /** Nilai default untuk field opsional yang bisa tidak terkirim sama sekali. */
    private const DEFAULTS = [
        'pemetaan' => ['jenis_umum' => [], 'jenis_spesifik' => [], 'transfer' => []],
        'risiko'   => ['indikator' => []],
    ];

    public function form(Request $request)
    {
        $tab = $request->query('tab', array_key_first(self::TABS));
        abort_unless(array_key_exists($tab, self::TABS), 404);

        return view('ropa.form', [
            'tabs'    => self::TABS,
            'tab'     => $tab,
            'ropa'    => session('ropa', []),
            'options' => [
                'dasar'          => self::DASAR_PEMROSESAN,
                'jenis_umum'     => self::JENIS_DATA_UMUM,
                'jenis_spesifik' => self::JENIS_DATA_SPESIFIK,
                'peran'          => self::PERAN_PENERIMA,
                'hak'            => self::HAK_SUBJEK,
                'status_siklus'  => self::STATUS_SIKLUS,
                'risiko'         => self::INDIKATOR_RISIKO,
            ],
        ]);
    }

    public function save(Request $request, string $tab)
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $data = $this->dataTab($tab, $request);

        $ropa = session('ropa', []);
        $ropa['nomor']    ??= $this->generateNomor();
        $ropa[$tab]         = $data;
        $ropa['diperbarui'] = now()->toIso8601String();

        session(['ropa' => $ropa]);

        return redirect()
            ->route('ropa.form', ['tab' => $tab])
            ->with('saved', self::TABS[$tab] . ' tersimpan.');
    }

    /**
     * Bersihkan + validasi isian satu tab. Dipakai oleh tombol Simpan dan oleh Impor JSON,
     * sehingga keduanya selalu memakai aturan yang sama. Melempar ValidationException bila gagal.
     */
    public function dataTab(string $tab, Request $request): array
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $this->cleanLists($request);

        $data = Validator::make(
            $request->all(),
            $this->rules($tab, $request),
            $this->messages(),
            $this->attributes()
        )->validate();

        return array_merge(self::DEFAULTS[$tab] ?? [], $data);
    }


    /** Label untuk PDF RoPA (dipakai juga oleh PDF gabungan DPIA). */
    public static function labelsPdf(): array
    {
        return [
            'dasar'          => self::DASAR_PEMROSESAN,
            'jenis_umum'     => self::JENIS_DATA_UMUM,
            'jenis_spesifik' => self::JENIS_DATA_SPESIFIK,
            'jenis'          => self::JENIS_DATA_UMUM + self::JENIS_DATA_SPESIFIK,
            'peran'          => self::PERAN_PENERIMA,
            'hak'            => self::HAK_SUBJEK,
            'status_siklus'  => self::STATUS_SIKLUS,
            'risiko'         => self::INDIKATOR_RISIKO,
        ];
    }

    public function pdf(Request $request)
    {
        $ropa = session('ropa', []);

        if (! RopaStatus::lengkap($ropa)) {
            return redirect()->route('ropa.form')
                ->withErrors(['ropa' => 'Lengkapi seluruh tab RoPA terlebih dahulu.']);
        }

        // Lokasi & tanggal pengesahan: hanya untuk cetakan ini, tidak disimpan
        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte'), [
            'lokasi'  => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tte'     => ['nullable', 'array'],
            'tte.*'   => [Rule::in(['pj', 'ppdp', 'pengendali'])],
        ]);

        if ($v->fails()) {
            return redirect()->route('ropa.form', ['tab' => 'risiko'])
                ->withErrors(['ropa' => 'Lokasi dan tanggal pengesahan wajib diisi dengan benar sebelum mencetak PDF.']);
        }

        $input = $v->validated();


        $pengesahan = [
            'lokasi'  => $input['lokasi'],
            'tanggal' => Carbon::createFromFormat('!Y-m-d', $input['tanggal'])->locale('id')->translatedFormat('d F Y'),
            'tte'     => array_values(array_unique($input['tte'] ?? [])),
        ];

        $pdf = Pdf::loadView('ropa.pdf', [
            'ropa'       => $ropa,
            'labels'     => self::labelsPdf(),
            'indikator'  => RopaStatus::indikatorAktif($ropa),
            'wajibDpia'  => RopaStatus::wajibDpia($ropa),
            'pengesahan' => $pengesahan,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        // Render dulu agar jumlah halaman diketahui, lalu gambar footer di setiap halaman
        $pdf->render();

        $dompdf  = $pdf->getDomPDF();
        $canvas  = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->getFont('Helvetica');
        $size    = 7.5;
        $hitam   = [0, 0, 0];

        $kiri   = 45;
        $kanan  = $canvas->get_width() - 45;
        $yGaris = $canvas->get_height() - 50;

        $canvas->page_line($kiri, $yGaris, $kanan, $yGaris, $hitam, 0.75);
        $canvas->page_text(
            $kiri,
            $yGaris + 4,
            $ropa['nomor'] . ' - Dicetak ' . now()->translatedFormat('d F Y, H:i') . ' WITA',
            $font,
            $size,
            $hitam
        );

        $lebar = $metrics->getTextWidth('Halaman 99 dari 99', $font, $size);
        $canvas->page_text($kanan - $lebar, $yGaris + 4, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, $size, $hitam);

        return $pdf->stream('RoPA-' . $ropa['nomor'] . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    private function cleanLists(Request $request): void
    {
        foreach (self::LIST_FIELDS as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => array_values(array_filter((array) $request->input($field), 'filled')),
                ]);
            }
        }

        foreach (self::ROW_FIELDS as $field => $keys) {
            if ($request->has($field)) {
                $rows = array_filter((array) $request->input($field), function ($row) use ($keys) {
                    if (! is_array($row)) return false;
                    foreach ($keys as $k) {
                        if (filled($row[$k] ?? null)) return true;
                    }
                    return false;
                });
                $request->merge([$field => array_values($rows)]);
            }
        }
    }

    private function rules(string $tab, Request $request): array
    {
        return match ($tab) {
            'organisasi' => [
                'nama_instansi'   => ['required', 'string', 'max:255'],
                'alamat'          => ['required', 'string', 'max:1000'],
                'telepon'         => ['nullable', 'required_without:email', 'regex:/^[0-9+()\-\s]{6,30}$/'],
                'email'           => ['nullable', 'required_without:telepon', 'email:rfc', 'max:255'],
                'nama_pengendali' => ['required', 'string', 'max:255'],
                'nama_ppdp'       => ['required', 'string', 'max:255'],
                'ppdp_email'      => ['nullable', 'required_without:ppdp_hp', 'email:rfc', 'max:255'],
                'ppdp_hp'         => ['nullable', 'required_without:ppdp_email', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            ],
            'aktivitas' => [
                'nama_aktivitas'    => ['required', 'string', 'max:255'],
                'penanggung_jawab'  => ['required', 'string', 'max:255'],
                'tahapan'           => ['required', 'array', 'max:50'],
                'tahapan.*'         => ['required', 'string', 'max:1000'],
                'tujuan'            => ['required', 'string', 'max:2000'],
                'dasar'             => ['required', 'array'],
                'dasar.*'           => ['string', Rule::in(array_keys(self::DASAR_PEMROSESAN))],
                'referensi_hukum'   => ['required', 'array', 'max:30'],
                'referensi_hukum.*' => ['required', 'string', 'max:500'],
            ],
            'pemetaan' => $this->rulesPemetaan($request),
            'hak' => [
                'hak'           => ['required', 'array'],
                'hak.*'         => ['string', Rule::in(array_keys(self::HAK_SUBJEK))],
                'mekanisme_hak' => ['required', 'string', 'max:2000'],
            ],
            'siklus' => [
                'masa_retensi'      => ['required', 'string', 'max:255'],
                'metode_pemusnahan' => ['required', 'string', 'max:2000'],
                'pengamanan'        => ['required', 'array', 'max:50'],
                'pengamanan.*'      => ['required', 'string', 'max:500'],
                'status_siklus'     => ['required', Rule::in(array_keys(self::STATUS_SIKLUS))],
            ],
            'risiko' => [
                'indikator'   => ['nullable', 'array'],
                'indikator.*' => ['string', Rule::in(array_keys(
                    array_diff_key(self::INDIKATOR_RISIKO, [self::INDIKATOR_OTOMATIS => true])
                ))],
            ],
        };
    }

    private function rulesPemetaan(Request $request): array
    {
        // Data yang boleh dikirim ke penerima = jenis data yang dicentang di tab ini
        $dipilih = array_merge(
            (array) $request->input('jenis_umum', []),
            (array) $request->input('jenis_spesifik', []),
        );

        return [
            'kategori_subjek'         => ['required', 'array', 'max:30'],
            'kategori_subjek.*'       => ['required', 'string', 'max:255'],
            'kelompok_rentan'         => ['required', 'boolean'],
            'data_anak'               => ['required', 'boolean'],
            'estimasi_subjek'         => ['required', 'integer', 'min:0', 'max:999999999'],
            'jenis_umum'              => ['nullable', 'array', 'required_without:jenis_spesifik'],
            'jenis_umum.*'            => ['string', Rule::in(array_keys(self::JENIS_DATA_UMUM))],
            'jenis_spesifik'          => ['nullable', 'array', 'required_without:jenis_umum'],
            'jenis_spesifik.*'        => ['string', Rule::in(array_keys(self::JENIS_DATA_SPESIFIK))],
            'pengumpulan'             => ['required', 'array', 'max:30'],
            'pengumpulan.*.sumber'    => ['required', 'string', 'max:255'],
            'pengumpulan.*.lokasi'    => ['required', 'string', 'max:255'],
            'transfer'                => ['nullable', 'array', 'max:30'],
            'transfer.*.organisasi'   => ['required', 'string', 'max:255'],
            'transfer.*.peran'        => ['required', Rule::in(array_keys(self::PERAN_PENERIMA))],
            'transfer.*.kontak'       => ['required', 'string', 'max:255'],
            'transfer.*.tujuan'       => ['required', 'string', 'max:2000'],
            'transfer.*.mekanisme'    => ['required', 'string', 'max:2000'],
            'transfer.*.data'         => ['required', 'array'],
            'transfer.*.data.*'       => ['string', Rule::in($dipilih)],
        ];
    }

    private function messages(): array
    {
        return [
            'required'                   => ':attribute wajib diisi.',
            'required_without'           => 'Isi :attribute atau :values (minimal salah satu).',
            'email'                      => ':attribute harus berupa alamat email yang valid.',
            'max'                        => ':attribute maksimal :max karakter.',
            'regex'                      => 'Format :attribute tidak valid.',
            'in'                         => 'Pilihan :attribute tidak valid.',
            'integer'                    => ':attribute harus berupa angka bulat.',
            'boolean'                    => ':attribute tidak valid.',
            'tahapan.required'           => 'Isi minimal satu tahapan aktivitas.',
            'tahapan.max'                => 'Maksimal :max tahapan.',
            'dasar.required'             => 'Pilih minimal satu dasar pemrosesan.',
            'referensi_hukum.required'   => 'Isi minimal satu referensi dasar hukum.',
            'referensi_hukum.max'        => 'Maksimal :max referensi.',
            'kategori_subjek.required'   => 'Isi minimal satu kategori subjek data.',
            'kategori_subjek.max'        => 'Maksimal :max kategori.',
            'estimasi_subjek.min'        => 'Estimasi jumlah subjek data tidak boleh negatif.',
            'estimasi_subjek.max'        => 'Estimasi jumlah subjek data terlalu besar.',
            'jenis_umum.required_without'     => 'Pilih minimal satu jenis data pribadi (umum atau spesifik).',
            'jenis_spesifik.required_without' => 'Pilih minimal satu jenis data pribadi (umum atau spesifik).',
            'pengumpulan.required'       => 'Isi minimal satu sumber pengumpulan beserta lokasi penyimpanannya.',
            'pengumpulan.max'            => 'Maksimal :max sumber.',
            'transfer.max'               => 'Maksimal :max penerima.',
            'transfer.*.data.required'   => 'Pilih minimal satu data pribadi yang dikirim ke penerima ini.',
            'transfer.*.data.*.in'       => 'Data yang dikirim harus termasuk jenis data pribadi yang dicentang di atas.',
            'pengamanan.required'        => 'Isi minimal satu langkah pengamanan.',
            'pengamanan.max'             => 'Maksimal :max langkah pengamanan.',
            'status_siklus.required'     => 'Pilih status siklus hidup.',
        ];
    }

    private function attributes(): array
    {
        return [
            'nama_instansi'         => 'Nama instansi',
            'alamat'                => 'Alamat kantor',
            'telepon'               => 'Nomor telepon resmi',
            'email'                 => 'Email resmi',
            'nama_pengendali'       => 'Nama Pengendali Data Pribadi',
            'nama_ppdp'             => 'Nama PPDP',
            'ppdp_email'            => 'Email PPDP',
            'ppdp_hp'               => 'No. HP PPDP',
            'nama_aktivitas'        => 'Nama aktivitas pemrosesan',
            'tahapan.*'             => 'Tahapan',
            'tujuan'                => 'Tujuan pemrosesan',
            'dasar.*'               => 'Dasar pemrosesan',
            'referensi_hukum.*'     => 'Referensi',
            'kategori_subjek.*'     => 'Kategori subjek',
            'estimasi_subjek'       => 'Estimasi jumlah subjek data',
            'jenis_umum.*'          => 'Jenis data umum',
            'jenis_spesifik.*'      => 'Jenis data spesifik',
            'pengumpulan.*.sumber'  => 'Sumber pengumpulan',
            'pengumpulan.*.lokasi'  => 'Lokasi penyimpanan',
            'transfer.*.organisasi' => 'Organisasi penerima',
            'transfer.*.peran'      => 'Peran penerima',
            'transfer.*.kontak'     => 'Kontak/PIC penerima',
            'transfer.*.tujuan'     => 'Tujuan pengiriman',
            'transfer.*.mekanisme'  => 'Mekanisme pengiriman',
            'hak.required'          => 'Pilih minimal satu hak yang difasilitasi.',
            'masa_retensi'          => 'Masa retensi data',
            'metode_pemusnahan'     => 'Metode pemusnahan data',
            'pengamanan.*'          => 'Langkah pengamanan',
            'status_siklus'         => 'Status siklus hidup',
            'indikator.*'           => 'Indikator risiko',
            'penanggung_jawab'  => 'Nama penanggung jawab layanan/aktivitas',
        ];
    }

    private function generateNomor(): string
    {
        return 'ROPA-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
