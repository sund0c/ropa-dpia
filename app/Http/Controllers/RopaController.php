<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\RopaStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\LabelTte;
use App\Support\PdfFooter;

class RopaController extends Controller
{
    /** Daftar tab RoPA. Tab berikutnya cukup ditambahkan di sini + di rules(). */
    public const TABS = [
        'dokumen'    => 'I. Informasi Dokumen',
        'organisasi' => 'II. Informasi Organisasi',
        'aktivitas'  => 'III. Aktivitas & Legalitas Pemrosesan',
        'pemetaan'   => 'IV. Pemetaan Aliran Data Pribadi',
        'hak'        => 'V. Pemenuhan Hak Subjek Data Pribadi',
        'siklus'     => 'VI. Siklus Hidup & Keamanan Data',
        'risiko'     => 'VII. Indikator Risiko Tinggi',

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

    /** Rujukan Pasal 20 ayat (2) UU PDP untuk setiap dasar pemrosesan. */
    public const PASAL_DASAR = [
        'consent'             => 'huruf a',
        'contract'            => 'huruf b',
        'legal_obligation'    => 'huruf c',
        'vital_interest'      => 'huruf d',
        'public_interest'     => 'huruf e',
        'legitimate_interest' => 'huruf f',
        'balancing'           => 'huruf f',
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

    /** Indikator yang otomatis Ya bila ada hak subjek (Tab IV) yang tidak difasilitasi. */
    public const INDIKATOR_HAK = 'pembatasan_hak';

    /** Nama singkat hak subjek, untuk keterangan otomatis di DPIA. */
    public const HAK_SINGKAT = [
        'informasi'           => 'hak mendapatkan informasi (Pasal 5)',
        'perbaikan'           => 'hak memperbaiki Data Pribadi (Pasal 6)',
        'akses'               => 'hak akses dan memperoleh salinan Data Pribadi (Pasal 7)',
        'penghapusan'         => 'hak mengakhiri pemrosesan, menghapus, dan/atau memusnahkan Data Pribadi (Pasal 8)',
        'tarik_persetujuan'   => 'hak menarik kembali persetujuan (Pasal 9)',
        'keberatan_otomatis'  => 'hak mengajukan keberatan atas keputusan otomatis (Pasal 10 ayat 1)',
        'pembatasan'          => 'hak menunda atau membatasi pemrosesan (Pasal 11)',
        'ganti_rugi'          => 'hak menggugat dan menerima ganti rugi (Pasal 12 ayat 1)',
        'portabilitas_format' => 'hak mendapatkan Data Pribadi dalam format yang dapat dibaca sistem elektronik (Pasal 13 ayat 1)',
        'portabilitas_kirim'  => 'hak mengirimkan Data Pribadi ke Pengendali lain (Pasal 13 ayat 2)',
    ];

    /** Daftar satu kolom (name[]). Baris kosong dibuang sebelum validasi. */
    private const LIST_FIELDS = ['tahapan', 'referensi_hukum', 'kategori_subjek',
        'pengamanan_organisasi', 'pengamanan_operasional', 'pengamanan_teknis'];

    /** Kelompok pengamanan Tab V (semuanya boleh kosong). */
    public const KELOMPOK_PENGAMANAN = [
        'organisasi'  => 'Pengamanan Organisasional',
        'operasional' => 'Pengamanan Operasional',
        'teknis'      => 'Pengamanan Teknis',
    ];

    /** Daftar multi-kolom (name[i][kolom]). Baris yang semua kolomnya kosong dibuang. */
    private const ROW_FIELDS = [
        'pengumpulan' => ['sumber', 'lokasi'],
        'riwayat'     => ['versi', 'tanggal', 'deskripsi', 'oleh'],
        'transfer'     => ['organisasi', 'negara', 'kontak', 'tujuan', 'mekanisme', 'data'],
        'pengungkapan' => ['organisasi', 'kontak', 'tujuan', 'mekanisme', 'data'],
    ];

    /** Nilai default untuk field opsional yang bisa tidak terkirim sama sekali. */
    private const DEFAULTS = [
        'pemetaan' => ['jenis_umum' => [], 'jenis_spesifik' => [], 'transfer' => [], 'pengungkapan' => []],
        'risiko'   => ['indikator' => []],
        'siklus'   => ['pengamanan_organisasi' => [], 'pengamanan_operasional' => [], 'pengamanan_teknis' => []],
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
                'status'         => DpiaController::STATUS_DOKUMEN,
            ],
        ]);
    }

    public function save(Request $request, string $tab)
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $data = $this->dataTab($tab, $request);

        $ropa = session('ropa', []);
        $ropa['nomor']    ??= self::nomorBaru();
        $ropa[$tab]         = $data;
        $ropa['diperbarui'] = now()->toIso8601String();

        session(['ropa' => $ropa]);

        // Lanjut otomatis ke tab berikutnya; tab terakhir tetap di tempat
        $urutan = array_keys(self::TABS);
        $next   = $urutan[array_search($tab, $urutan, true) + 1] ?? $tab;

        $pesan = self::TABS[$tab] . ' tersimpan.'
            . ($next !== $tab ? ' Silakan lanjutkan ' . self::TABS[$next] . '.' : '');

        return redirect()->route('ropa.form', ['tab' => $next])->with('saved', $pesan);
    }

    /**
     * Versi terakhir RoPA: baris riwayat dengan tanggal paling akhir (bila sama, baris paling bawah).
     * Null bila Informasi Dokumen belum diisi.
     */
    public static function versiTerakhir(array $ropa): ?string
    {
        $akhir = null;
        foreach ((array) ($ropa['dokumen']['riwayat'] ?? []) as $rw) {
            if (! is_array($rw) || blank($rw['versi'] ?? null)) continue;
            if ($akhir === null || (string) ($rw['tanggal'] ?? '') >= (string) ($akhir['tanggal'] ?? '')) {
                $akhir = $rw;
            }
        }
        return $akhir['versi'] ?? null;
    }

    /** Kode referensi RoPA untuk DPIA, misalnya "ROPA-20261008-YVPPBM (Versi 1.1)". */
    public static function referensi(array $ropa): string
    {
        $versi = self::versiTerakhir($ropa);
        return ($ropa['nomor'] ?? '-') . ($versi !== null ? " (Versi $versi)" : '');
    }

    /**
     * Informasi Dokumen pengganti untuk file ekspor lama (sebelum tab ini ada): versi 1.0,
     * tanggal dari nomor RoPA, review satu tahun kemudian. Pengguna dapat mengubahnya di Tab I.
     */
    public static function dokumenDefault(array $ropaJson): array
    {
        $nomor = (string) ($ropaJson['nomor'] ?? '');
        try {
            $tgl = preg_match('/^ROPA-(\d{8})-/', $nomor, $m)
                ? Carbon::createFromFormat('!Ymd', $m[1])
                : now()->startOfDay();
        } catch (\Throwable) {
            $tgl = now()->startOfDay();
        }
        $o = (array) ($ropaJson['organisasi'] ?? []);
        $oleh = $o['koordinator_nama'] ?? $o['nama_ppdp'] ?? null;

        return [
            'tanggal_penyusunan' => $tgl->format('Y-m-d'),
            'tanggal_review'     => $tgl->copy()->addYear()->format('Y-m-d'),
            'status_dokumen'     => 'draf',
            'riwayat'            => [[
                'versi'     => '1.0',
                'tanggal'   => $tgl->format('Y-m-d'),
                'deskripsi' => 'Penyusunan awal',
                'oleh'      => filled($oleh) ? $oleh : 'Penyusun',
            ]],
        ];
    }

    /** Field Koordinator PPDP (Tab II). Boleh kosong pada impor file lama, wajib saat disimpan dari form. */
    public const FIELD_KOORDINATOR = ['koordinator_nama', 'koordinator_jabatan', 'koordinator_email', 'koordinator_hp'];

    /**
     * Bersihkan + validasi isian satu tab. Dipakai oleh tombol Simpan dan oleh Impor JSON,
     * sehingga keduanya selalu memakai aturan yang sama. Melempar ValidationException bila gagal.
     * $imporLama: file ekspor sebelum ada Koordinator PPDP tetap bisa dimuat; datanya dilengkapi kemudian di Tab II.
     */
    public function dataTab(string $tab, Request $request, bool $imporLama = false): array
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        if ($tab === 'pemetaan') $this->pindahTransferLama($request);
        $this->cleanLists($request);
        if ($tab === 'pemetaan') $this->syncDataAnak($request);
        if ($tab === 'siklus')   $this->pecahPengamananLama($request);
        if ($tab === 'risiko') {
            // Indikator otomatis tidak disimpan dari pilihan manual (data lama/impor bisa memuatnya)
            $request->merge(['indikator' => array_values(array_diff(
                (array) $request->input('indikator', []),
                [self::INDIKATOR_OTOMATIS, self::INDIKATOR_HAK]
            ))]);
        }

        $rules = $this->rules($tab, $request);
        if ($tab === 'organisasi' && $imporLama && blank($request->input('koordinator_nama'))) {
            foreach (self::FIELD_KOORDINATOR as $f) {
                $rules[$f] = ['nullable', ...array_filter($rules[$f], fn($r) => ! in_array($r, ['nullable'], true) && ! (is_string($r) && str_starts_with($r, 'required')))];
            }
        }

        $data = Validator::make(
            $request->all(),
            $rules,
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
            'status_dokumen' => DpiaController::STATUS_DOKUMEN,
        ];
    }

    public function pdf(Request $request)
    {
        $ropa = session('ropa', []);

        if (! RopaStatus::lengkap($ropa)) {
            return redirect()->route('ropa.form')
                ->withErrors(['ropa' => 'Lengkapi seluruh tab RoPA terlebih dahulu.']);
        }
        if (! RopaStatus::koordinatorLengkap($ropa)) {
            return redirect()->route('ropa.form', ['tab' => 'organisasi'])
                ->withErrors(['ropa' => 'Lengkapi data Koordinator PPDP di Tab II terlebih dahulu.']);
        }

        // Lokasi & tanggal pengesahan: hanya untuk cetakan ini, tidak disimpan
        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte'), [
            'lokasi'  => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tte'     => ['nullable', 'array'],
            'tte.*'   => [Rule::in(['koordinator', 'ppdp', 'pengendali'])],
        ]);

        if ($v->fails()) {
            return redirect()->route('ropa.form', ['tab' => 'risiko'])
                ->withErrors(['ropa' => 'Lokasi dan tanggal pengesahan wajib diisi dengan benar sebelum mencetak PDF.']);
        }

        $input = $v->validated();

        [$label, $galat] = LabelTte::ambil($request, array_values(array_unique($input['tte'] ?? [])));
        if ($galat) {
            return redirect()->route('ropa.form', ['tab' => 'risiko'])->withErrors(['ropa' => $galat]);
        }



        $pengesahan = [
            'lokasi'  => $input['lokasi'],
            'label'   => $label,
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

        // Nomor halaman di atas area footer TTE (lihat PdfFooter)
        PdfFooter::tambah($pdf);

        return $pdf->download('RoPA-' . $ropa['nomor'] . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Pengamanan per kelompok untuk tampilan/PDF. Data lama (satu daftar "pengamanan",
     * sebelum dipecah) dikelompokkan otomatis menurut kata kuncinya.
     */
    public static function pengamanan(array $siklus): array
    {
        $ada = false;
        $hasil = [];
        foreach (array_keys(self::KELOMPOK_PENGAMANAN) as $k) {
            $ada = $ada || array_key_exists("pengamanan_$k", $siklus);
            $hasil[$k] = array_values((array) ($siklus["pengamanan_$k"] ?? []));
        }

        return $ada ? $hasil : self::kelompokkan((array) ($siklus['pengamanan'] ?? []));
    }

    /** Tebakan kelompok untuk daftar pengamanan lama; pengguna dapat memindahkannya di Tab V. */
    private static function kelompokkan(array $daftar): array
    {
        $kunci = [
            'organisasi'  => ['sop', 'kebijakan', 'surat edaran', 'keputusan', 'sk ', 'pernyataan', 'nda',
                              'perjanjian', 'kerahasiaan', 'pedoman', 'prosedur', 'penunjukan', 'regulasi'],
            'operasional' => ['bimtek', 'pelatihan', 'sosialisasi', 'backup', 'cadangan', 'filing', 'filling',
                              'lemari', 'kunci', 'arsip', 'jadwal', 'penghancur', 'pemusnahan', 'peninjauan'],
        ];

        $hasil = ['organisasi' => [], 'operasional' => [], 'teknis' => []];
        foreach ($daftar as $x) {
            if (! is_string($x) || trim($x) === '') continue;
            $t = mb_strtolower($x);
            $kel = 'teknis';
            foreach ($kunci as $k => $kata) {
                foreach ($kata as $w) {
                    if (str_contains($t, $w)) { $kel = $k; break 2; }
                }
            }
            $hasil[$kel][] = trim($x);
        }
        return $hasil;
    }

    /** Impor file lama: "pengamanan" (satu daftar) dipecah ke tiga kelompok. */
    private function pecahPengamananLama(Request $request): void
    {
        $baru = array_map(fn($k) => "pengamanan_$k", array_keys(self::KELOMPOK_PENGAMANAN));
        if (! $request->has('pengamanan') || $request->hasAny($baru)) return;

        $hasil = self::kelompokkan((array) $request->input('pengamanan'));
        $request->merge(array_combine($baru, array_values($hasil)));
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

    /**
     * Penerima Data Pribadi untuk tampilan/PDF. Data lama (sebelum dipisah) menyimpan semua penerima
     * di "transfer" dengan kolom "peran" tanpa "negara": baris seperti itu dipindah ke "pengungkapan".
     */
    public static function penerima(array $p): array
    {
        $transfer = [];
        $pengungkapan = array_values((array) ($p['pengungkapan'] ?? []));
        foreach ((array) ($p['transfer'] ?? []) as $row) {
            if (! is_array($row)) continue;
            if (array_key_exists('negara', $row)) {
                $transfer[] = $row;
            } else {
                unset($row['peran']);
                $pengungkapan[] = $row;
            }
        }

        return ['transfer' => $transfer, 'pengungkapan' => $pengungkapan];
    }

    /** Impor file lama: penerima tanpa "negara" dipindah ke Pengungkapan (lihat penerima()). */
    private function pindahTransferLama(Request $request): void
    {
        if (! $request->has('transfer')) return;
        $request->merge(self::penerima([
            'transfer'     => $request->input('transfer'),
            'pengungkapan' => $request->input('pengungkapan', []),
        ]));
    }

    /** "Data anak" (data spesifik) selalu mengikuti isian "Pemrosesan melibatkan Data Pribadi Anak". */
    private function syncDataAnak(Request $request): void
    {
        $spesifik = array_values(array_diff((array) $request->input('jenis_spesifik', []), ['anak']));

        if ($request->boolean('data_anak')) {
            $spesifik[] = 'anak';
        }

        $request->merge(['jenis_spesifik' => $spesifik]);
    }

    private function rules(string $tab, Request $request): array
    {
        return match ($tab) {
            'dokumen' => [
                'tanggal_penyusunan'  => ['required', 'date_format:Y-m-d'],
                'tanggal_review'      => ['required', 'date_format:Y-m-d', 'after:tanggal_penyusunan'],
                'status_dokumen'      => ['required', Rule::in(array_keys(DpiaController::STATUS_DOKUMEN))],
                'riwayat'             => ['required', 'array', 'max:30'],
                'riwayat.*.versi'     => ['required', 'string', 'max:20'],
                'riwayat.*.tanggal'   => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_penyusunan'],
                'riwayat.*.deskripsi' => ['required', 'string', 'max:1000'],
                'riwayat.*.oleh'      => ['required', 'string', 'max:255'],
            ],
            'organisasi' => [
                'nama_instansi'   => ['required', 'string', 'max:255'],
                'alamat'          => ['required', 'string', 'max:1000'],
                'telepon'         => ['nullable', 'required_without:email', 'regex:/^[0-9+()\-\s]{6,30}$/'],
                'email'           => ['nullable', 'required_without:telepon', 'email:rfc', 'max:255'],
                'nama_pengendali' => ['required', 'string', 'max:255'],
                'nama_ppdp'       => ['required', 'string', 'max:255'],
                'jabatan_pengendali' => ['required', 'string', 'max:255'],
                'jabatan_ppdp'       => ['required', 'string', 'max:255'],
                'ppdp_email'      => ['nullable', 'required_without:ppdp_hp', 'email:rfc', 'max:255'],
                'ppdp_hp'         => ['nullable', 'required_without:ppdp_email', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
                'koordinator_nama'    => ['required', 'string', 'max:255'],
                'koordinator_jabatan' => ['required', 'string', 'max:255'],
                'koordinator_email'   => ['nullable', 'required_without:koordinator_hp', 'email:rfc', 'max:255'],
                'koordinator_hp'      => ['nullable', 'required_without:koordinator_email', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            ],
            'aktivitas' => [
                'nama_aktivitas'    => ['required', 'string', 'max:255'],
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
                'pengamanan_organisasi'    => ['nullable', 'array', 'max:50'],
                'pengamanan_organisasi.*'  => ['required', 'string', 'max:500'],
                'pengamanan_operasional'   => ['nullable', 'array', 'max:50'],
                'pengamanan_operasional.*' => ['required', 'string', 'max:500'],
                'pengamanan_teknis'        => ['nullable', 'array', 'max:50'],
                'pengamanan_teknis.*'      => ['required', 'string', 'max:500'],
                'status_siklus'     => ['required', Rule::in(array_keys(self::STATUS_SIKLUS))],
            ],
            'risiko' => [
                'indikator'   => ['nullable', 'array'],
                'indikator.*' => ['string', Rule::in(array_keys(
                    array_diff_key(self::INDIKATOR_RISIKO, [self::INDIKATOR_OTOMATIS => true, self::INDIKATOR_HAK => true])
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
            // Keterangan hanya disimpan bila kotaknya dicentang
            'kelompok_rentan_keterangan' => ['exclude_unless:kelompok_rentan,1', 'nullable', 'string', 'max:2000'],
            'data_anak_keterangan'       => ['exclude_unless:data_anak,1', 'nullable', 'string', 'max:2000'],
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
            'transfer.*.negara'       => ['required', Rule::in(array_keys(\App\Support\Negara::DAFTAR))],
            'transfer.*.kontak'       => ['required', 'string', 'max:255'],
            'transfer.*.tujuan'       => ['required', 'string', 'max:2000'],
            'transfer.*.mekanisme'    => ['required', 'string', 'max:2000'],
            'transfer.*.data'         => ['required', 'array'],
            'transfer.*.data.*'       => ['string', Rule::in($dipilih)],
            'pengungkapan'                => ['nullable', 'array', 'max:30'],
            'pengungkapan.*.organisasi'   => ['required', 'string', 'max:255'],
            'pengungkapan.*.kontak'       => ['required', 'string', 'max:255'],
            'pengungkapan.*.tujuan'       => ['required', 'string', 'max:2000'],
            'pengungkapan.*.mekanisme'    => ['required', 'string', 'max:2000'],
            'pengungkapan.*.data'         => ['required', 'array'],
            'pengungkapan.*.data.*'       => ['string', Rule::in($dipilih)],
        ];
    }

    private function messages(): array
    {
        return [
            'tanggal_review.after'             => 'Tanggal review berikutnya harus setelah tanggal penyusunan.',
            'status_dokumen.required'          => 'Pilih status dokumen.',
            'riwayat.required'                 => 'Isi minimal satu riwayat perubahan dokumen.',
            'riwayat.max'                      => 'Maksimal :max riwayat perubahan.',
            'riwayat.*.tanggal.after_or_equal' => 'Tanggal riwayat tidak boleh sebelum Tanggal Penyusunan.',
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
            'pengungkapan.max'               => 'Maksimal :max penerima.',
            'pengungkapan.*.data.required'   => 'Pilih minimal satu data pribadi yang dikirim ke penerima ini.',
            'pengungkapan.*.data.*.in'       => 'Data yang dikirim harus termasuk jenis data pribadi yang dicentang di atas.',
            'pengamanan_organisasi.max'  => 'Maksimal :max langkah pengamanan organisasional.',
            'pengamanan_operasional.max' => 'Maksimal :max langkah pengamanan operasional.',
            'pengamanan_teknis.max'      => 'Maksimal :max langkah pengamanan teknis.',
            'status_siklus.required'     => 'Pilih status siklus hidup.',
        ];
    }

    private function attributes(): array
    {
        return [
            'tanggal_penyusunan'    => 'Tanggal penyusunan',
            'tanggal_review'        => 'Tanggal review berikutnya',
            'status_dokumen'        => 'Status dokumen',
            'riwayat.*.versi'       => 'Versi',
            'riwayat.*.tanggal'     => 'Tanggal perubahan',
            'riwayat.*.deskripsi'   => 'Deskripsi perubahan',
            'riwayat.*.oleh'        => 'Disusun/direvisi oleh',
            'nama_instansi'         => 'Nama Pengendali Data Pribadi (instansi)',
            'alamat'                => 'Alamat kantor',
            'telepon'               => 'Nomor telepon resmi',
            'email'                 => 'Email resmi',
            'nama_pengendali'       => 'Pimpinan Pengendali Data Pribadi',
            'koordinator_nama'      => 'Nama Koordinator PPDP',
            'koordinator_jabatan'   => 'Jabatan Koordinator PPDP',
            'koordinator_email'     => 'Email Koordinator PPDP',
            'koordinator_hp'        => 'No. HP Koordinator PPDP',
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
            'transfer.*.negara'    => 'Negara tujuan',
            'kelompok_rentan_keterangan' => 'Mekanisme/Keterangan kelompok rentan',
            'data_anak_keterangan'       => 'Mekanisme/Keterangan data anak',
            'pengungkapan.*.organisasi' => 'Organisasi penerima',
            'pengungkapan.*.kontak'     => 'Kontak/PIC penerima',
            'pengungkapan.*.tujuan'     => 'Tujuan pengiriman',
            'pengungkapan.*.mekanisme'  => 'Mekanisme pengiriman',
            'transfer.*.kontak'     => 'Kontak/PIC penerima',
            'transfer.*.tujuan'     => 'Tujuan pengiriman',
            'transfer.*.mekanisme'  => 'Mekanisme pengiriman',
            'hak.required'          => 'Pilih minimal satu hak yang difasilitasi.',
            'masa_retensi'          => 'Masa retensi data',
            'metode_pemusnahan'     => 'Metode pemusnahan data',
            'pengamanan_organisasi.*'  => 'Langkah pengamanan organisasional',
            'pengamanan_operasional.*' => 'Langkah pengamanan operasional',
            'pengamanan_teknis.*'      => 'Langkah pengamanan teknis',
            'status_siklus'         => 'Status siklus hidup',
            'indikator.*'           => 'Indikator risiko',
            'jabatan_pengendali' => 'Jabatan Pimpinan Pengendali Data Pribadi',
            'jabatan_ppdp'       => 'Jabatan PPDP',
        ];
    }

    /**
     * Nomor RoPA baru: ROPA-YYYYMMDD-XXXXXX. Enam karakter acak kriptografis dari 32 karakter
     * (tanpa 0/O dan 1/I agar tidak tertukar saat dibaca), sekitar 1 miliar kemungkinan per hari,
     * sehingga praktis tidak akan sama antarpengguna/komputer walau tanpa basis data.
     */
    public static function nomorBaru(): string
    {
        $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $acak  = '';
        for ($i = 0; $i < 6; $i++) {
            $acak .= $abjad[random_int(0, strlen($abjad) - 1)];
        }

        return 'ROPA-' . now()->format('Ymd') . '-' . $acak;
    }
}
