<?php

namespace App\Http\Controllers;

use App\Support\MetodologiRisiko;
use App\Support\RopaStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DpiaController extends Controller
{
    public const TABS = [
        'dokumen'      => 'I. Informasi Dokumen',
        'organisasi'   => 'II. Organisasi dan Penanggung Jawab',
        'risiko'       => 'III. Analisis Potensi Risiko Tinggi',
        'deskripsi'    => 'IV. Deskripsi Pemrosesan Data Pribadi',
        'kebutuhan'    => 'V. Penilaian Kebutuhan dan Proporsionalitas',
        'penilaian'    => 'VI. Penilaian Risiko',
        'pengendalian' => 'VII. Langkah Pengendalian dan Penanganan Risiko',
        'rekomendasi'  => 'VIII. Rekomendasi dengan PPDP dan Lembaga',
        'kesimpulan'   => 'IX. Kesimpulan dan Keputusan',
    ];

    /** Tab yang seluruh isinya diambil dari RoPA: tanpa input, tanpa simpan, otomatis lengkap. */
    public const TABS_OTOMATIS = ['organisasi'];

    public const STATUS_DOKUMEN = [
        'draf'      => 'Draf',
        'direviu'   => 'Direviu',
        'disetujui' => 'Disetujui',
    ];

    public const LOKASI_PIHAK = [
        'dalam_negeri' => 'Dalam Negeri',
        'luar_negeri'  => 'Luar Negeri',
    ];

    /** Pertanyaan penilaian kebutuhan & proporsionalitas. Jawaban yang diharapkan: Ya. */
    public const PENILAIAN = [
        'kebutuhan' => [
            'judul' => '1. Penilaian Kebutuhan',
            'items' => [
                'diperlukan' => 'Apakah pemrosesan data pribadi ini sangat diperlukan untuk mencapai tujuan yang ditetapkan?',
                'tanpa_data' => 'Apakah tujuan yang ditetapkan tidak dapat dicapai tanpa memproses data pribadi?',
            ],
        ],
        'proporsionalitas' => [
            'judul' => '2. Penilaian Proporsionalitas',
            'items' => [
                'dampak_hak'   => 'Apakah dampak potensial terhadap hak-hak subjek data pribadi telah dipertimbangkan?',
                'transparansi' => 'Apakah subjek data pribadi mendapatkan informasi yang cukup mengenai pemrosesan (transparansi)?',
            ],
        ],
    ];

    public const JAWABAN = ['ya' => 'Ya', 'tidak' => 'Tidak'];

    public const STATUS_KONTROL = ['aktif' => 'Aktif', 'belum_aktif' => 'Belum Aktif'];

    public const JENIS_KONTROL = ['organisasi' => 'Organisasi', 'operasional' => 'Operasional', 'teknis' => 'Teknis'];

    public const KEPUTUSAN = [
        'mitigasi' => 'Mitigasi',
        'transfer' => 'Transfer',
        'diterima' => 'Diterima',
        'ditolak'  => 'Ditolak',
    ];

    public const STATUS_PENANGANAN = ['belum' => 'Belum dimulai', 'proses' => 'Sedang proses', 'selesai' => 'Selesai'];

    public const EFEKTIVITAS = [
        'efektif' => 'Efektif',
        'kurang'  => 'Kurang Efektif',
        'tidak'   => 'Tidak Efektif',
    ];

    public const KEPUTUSAN_AKHIR = [
        'lanjut'        => 'Pemrosesan data pribadi dapat dilanjutkan sesuai rencana dengan risiko residual berada pada tingkat yang dapat diterima',
        'lanjut_syarat' => 'Pemrosesan data pribadi dapat dilanjutkan dengan syarat penerapan langkah mitigasi tambahan pada Bagian VII sebelum pemrosesan dimulai',
        'tunda'         => 'Pemrosesan data pribadi perlu ditunda sampai dengan hasil konsultasi dengan Lembaga (Bagian VIII) diperoleh',
        'tidak_lanjut'  => 'Pemrosesan data pribadi tidak direkomendasikan untuk dilanjutkan pada kondisi saat ini',
    ];

    /** Daftar multi-kolom sederhana. Baris yang semua kolomnya kosong dibuang sebelum validasi. */
    private const ROW_FIELDS = [
        'riwayat' => ['versi', 'tanggal', 'deskripsi', 'oleh'],
        'pihak'   => ['nama', 'peran', 'lokasi', 'data', 'dasar', 'keterangan'],
    ];

    // =====================================================================
    //  Aksi
    // =====================================================================

    public function form(Request $request)
    {
        if ($redirect = $this->guard()) return $redirect;

        $tab = $request->query('tab', array_key_first(self::TABS));
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $ropa = session('ropa');

        return view('dpia.form', [
            'tabs'     => self::TABS,
            'tab'      => $tab,
            'ropa'     => $ropa,
            'dpia'     => session('dpia', []),
            'kode'     => self::kodeDokumen($ropa),
            'otomatis' => in_array($tab, self::TABS_OTOMATIS, true),
            'options'  => [
                'status'            => self::STATUS_DOKUMEN,
                'risiko'            => RopaController::INDIKATOR_RISIKO,
                'peran'             => RopaController::PERAN_PENERIMA,
                'lokasi'            => self::LOKASI_PIHAK,
                'jenis_umum'        => RopaController::JENIS_DATA_UMUM,
                'jenis_spesifik'    => RopaController::JENIS_DATA_SPESIFIK,
                'penilaian'         => self::PENILAIAN,
                'jawaban'           => self::JAWABAN,
                'status_kontrol'    => self::STATUS_KONTROL,
                'jenis_kontrol'     => self::JENIS_KONTROL,
                'keputusan'         => self::KEPUTUSAN,
                'status_penanganan' => self::STATUS_PENANGANAN,
                'efektivitas'       => self::EFEKTIVITAS,
                'keputusan_akhir'   => self::KEPUTUSAN_AKHIR,
            ],
        ]);
    }

    public function save(Request $request, string $tab)
    {
        if ($redirect = $this->guard()) return $redirect;

        abort_unless(
            array_key_exists($tab, self::TABS) && ! in_array($tab, self::TABS_OTOMATIS, true),
            404
        );

        $this->cleanRows($request);
        if ($tab === 'penilaian')    $this->cleanInheren($request);
        if ($tab === 'pengendalian') $this->cleanPengendalian($request);

        $data = $request->validate($this->rules($tab), $this->messages(), $this->attributes());

        // Konsultasi Lembaga tidak dilakukan: buang isian Lembaga yang terlanjur diketik
        if ($tab === 'rekomendasi' && $data['lembaga_konsultasi'] === 'tidak') {
            $data['lembaga_tanggal'] = $data['lembaga_saran'] = $data['lembaga_tindak_lanjut'] = null;
        }

        $dpia = session('dpia', []);
        $dpia[$tab]         = $data;
        $dpia['diperbarui'] = now()->toIso8601String();

        session(['dpia' => $dpia]);

        return redirect()
            ->route('dpia.form', ['tab' => $tab])
            ->with('saved', self::TABS[$tab] . ' tersimpan.');
    }

    /** Kembalikan metodologi penilaian risiko ke nilai default (risiko inheren tidak disentuh). */
    public function resetMetodologi()
    {
        if ($redirect = $this->guard()) return $redirect;

        $dpia = session('dpia', []);
        unset($dpia['penilaian']['metodologi']);
        session(['dpia' => $dpia]);

        return redirect()
            ->route('dpia.form', ['tab' => 'penilaian'])
            ->with('saved', 'Metodologi penilaian risiko dikembalikan ke nilai default.');
    }

    /** Kembalikan daftar risiko inheren ke contoh default (metodologi tidak disentuh). */
    public function resetInheren()
    {
        if ($redirect = $this->guard()) return $redirect;

        $dpia = session('dpia', []);
        unset($dpia['penilaian']['inheren']);
        session(['dpia' => $dpia]);

        return redirect()
            ->route('dpia.form', ['tab' => 'penilaian'])
            ->with('saved', 'Daftar risiko inheren dikembalikan ke nilai default.');
    }

    /** Kode DPIA = akhiran Nomor RoPA dengan awalan DPIA. */
    public static function kodeDokumen(array $ropa): string
    {
        return 'DPIA-' . Str::after($ropa['nomor'], 'ROPA-');
    }

    // =====================================================================
    //  Pembersihan input
    // =====================================================================

    private function cleanRows(Request $request): void
    {
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

    /** Risiko inheren: hanya siklus & kolom yang dikenal, baris kosong dibuang, ID dijaga. */
    private function cleanInheren(Request $request): void
    {
        $bersih  = [];
        $dipakai = [];

        foreach (array_keys(MetodologiRisiko::SIKLUS) as $s) {
            $rows = [];
            foreach ((array) $request->input("inheren.$s", []) as $r) {
                if (! is_array($r)) continue;

                $row = [
                    'id'          => $r['id'] ?? null,
                    'risiko'      => $r['risiko'] ?? null,
                    'kemungkinan' => $r['kemungkinan'] ?? null,
                    'dampak'      => $r['dampak'] ?? null,
                ];
                if (! filled($row['risiko']) && ! filled($row['kemungkinan']) && ! filled($row['dampak'])) continue;

                if (! is_string($row['id']) || ! preg_match('/^[a-z0-9-]{1,40}$/', $row['id']) || isset($dipakai[$row['id']])) {
                    $row['id'] = strtolower(Str::random(10));
                }
                $dipakai[$row['id']] = true;
                $rows[] = $row;
            }
            if ($rows) $bersih[$s] = $rows;
        }

        $request->merge(['inheren' => $bersih]);
    }

    /**
     * Pengendalian per risiko: hanya ID risiko dari Tab VI yang diterima, hanya kolom yang dikenal,
     * baris kosong dibuang, dan langkah penanganan dibuang bila keputusan bukan Mitigasi.
     */
    private function cleanPengendalian(Request $request): void
    {
        $ids    = $this->idRisiko();
        $ambil  = fn($r, array $keys) => collect($keys)->mapWithKeys(fn($k) => [$k => is_array($r) ? ($r[$k] ?? null) : null])->all();
        $adaIsi = fn(array $row) => collect($row)->contains(fn($v) => filled($v));

        $kontrol  = [];
        $residual = [];

        foreach ($ids as $id) {
            $kontrol[$id] = collect((array) $request->input("kontrol.$id", []))
                ->map(fn($r) => $ambil($r, ['langkah', 'status', 'bukti', 'jenis']))
                ->filter($adaIsi)
                ->values()->all();

            $residual[$id] = collect((array) $request->input("residual.$id", []))
                ->map(function ($r) use ($ambil, $adaIsi) {
                    $row = $ambil($r, ['risiko', 'langkah', 'kemungkinan', 'dampak', 'keputusan']);
                    $row['penanganan'] = $row['keputusan'] === 'mitigasi'
                        ? collect((array) (is_array($r) ? ($r['penanganan'] ?? []) : []))
                        ->map(fn($p) => $ambil($p, ['langkah', 'pj', 'bulan', 'tahun', 'status']))
                        ->filter($adaIsi)
                        ->values()->all()
                        : [];
                    return $row;
                })
                ->filter(fn($row) => $adaIsi(Arr::except($row, 'penanganan')) || ! empty($row['penanganan']))
                ->values()->all();
        }

        $request->merge(['kontrol' => $kontrol, 'residual' => $residual]);
    }

    /** ID risiko inheren yang berlaku saat ini (tersimpan di Tab VI, atau default). */
    private function idRisiko(): array
    {
        return array_map('strval', array_keys(MetodologiRisiko::daftarRisiko(session('dpia.penilaian', []))));
    }

    // =====================================================================
    //  Validasi
    // =====================================================================

    private function rules(string $tab): array
    {
        return match ($tab) {
            'dokumen' => [
                'unit_kerja'          => ['required', 'string', 'max:255'],
                'tanggal_penyusunan'  => ['required', 'date_format:Y-m-d'],
                'tanggal_review'      => ['required', 'date_format:Y-m-d', 'after:tanggal_penyusunan'],
                'status_dokumen'      => ['required', Rule::in(array_keys(self::STATUS_DOKUMEN))],
                'riwayat'             => ['required', 'array', 'max:30'],
                'riwayat.*.versi'     => ['required', 'string', 'max:20'],
                'riwayat.*.tanggal'   => ['required', 'date_format:Y-m-d'],
                'riwayat.*.deskripsi' => ['nullable', 'string', 'max:1000'],
                'riwayat.*.oleh'      => ['nullable', 'string', 'max:255'],
            ],
            'risiko'       => $this->rulesRisiko(),
            'deskripsi'    => $this->rulesDeskripsi(),
            'kebutuhan'    => $this->rulesKebutuhan(),
            'penilaian'    => $this->rulesPenilaian(),
            'pengendalian' => $this->rulesPengendalian(),
            'rekomendasi'  => [
                'ppdp_tanggal'          => ['required', 'date_format:Y-m-d'],
                'ppdp_saran'            => ['required', 'string', 'max:5000'],
                'ppdp_tindak_lanjut'    => ['required', 'string', 'max:5000'],
                'lembaga_konsultasi'    => ['required', Rule::in(['ya', 'tidak'])],
                'lembaga_tanggal'       => ['nullable', 'required_if:lembaga_konsultasi,ya', 'date_format:Y-m-d'],
                'lembaga_saran'         => ['nullable', 'required_if:lembaga_konsultasi,ya', 'string', 'max:5000'],
                'lembaga_tindak_lanjut' => ['nullable', 'required_if:lembaga_konsultasi,ya', 'string', 'max:5000'],
            ],
            'kesimpulan'   => [
                'ringkasan.kebutuhan' => ['required', 'string', 'max:2000'],
                'ringkasan.inheren'   => ['required', 'string', 'max:2000'],
                'ringkasan.residual'  => ['required', 'string', 'max:2000'],
                'efektivitas'         => ['required', Rule::in(array_keys(self::EFEKTIVITAS))],
                'keputusan_akhir'     => ['required', Rule::in(array_keys(self::KEPUTUSAN_AKHIR))],
                'catatan'             => ['nullable', 'string', 'max:3000'],
            ],
        };
    }

    /** Keterangan hanya diterima (dan wajib) untuk indikator yang bernilai Ya menurut RoPA. */
    private function rulesRisiko(): array
    {
        $rules = [];
        foreach (RopaStatus::indikatorAktif(session('ropa', [])) as $key) {
            $rules["keterangan.$key"] = ['required', 'string', 'max:2000'];
        }
        return $rules;
    }

    private function rulesDeskripsi(): array
    {
        $ropa  = session('ropa', []);
        $jenis = array_merge(
            (array) ($ropa['pemetaan']['jenis_umum'] ?? []),
            (array) ($ropa['pemetaan']['jenis_spesifik'] ?? []),
        );

        return [
            'latar_belakang'     => ['required', 'string', 'max:5000'],
            'pihak'              => ['required', 'array', 'max:30'],
            'pihak.*.nama'       => ['required', 'string', 'max:255'],
            'pihak.*.peran'      => ['required', Rule::in(array_keys(RopaController::PERAN_PENERIMA))],
            'pihak.*.lokasi'     => ['required', Rule::in(array_keys(self::LOKASI_PIHAK))],
            'pihak.*.data'       => ['required', 'array'],
            'pihak.*.data.*'     => ['string', Rule::in($jenis)],
            'pihak.*.dasar'      => ['required', 'string', 'max:2000'],
            'pihak.*.keterangan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function rulesKebutuhan(): array
    {
        $rules = [];
        foreach (self::PENILAIAN as $grup) {
            foreach (array_keys($grup['items']) as $key) {
                $rules["jawaban.$key"] = ['required', Rule::in(array_keys(self::JAWABAN))];
                $rules["alasan.$key"]  = ['required', 'string', 'max:2000'];
            }
        }
        return $rules;
    }

    private function rulesPenilaian(): array
    {
        $r = [];

        // 1–4. Metodologi (nama level tidak bisa diubah, jadi tidak divalidasi)
        foreach (range(1, 5) as $i) {
            $r["metodologi.kemungkinan.$i.periode"] = ['required', 'string', 'max:255'];
            $r["metodologi.dampak.$i.finansial"]    = ['required', 'string', 'max:100'];
            $r["metodologi.dampak.$i.reputasi"]     = ['required', 'string', 'max:1000'];
            $r["metodologi.dampak.$i.kepatuhan"]    = ['required', 'string', 'max:1000'];
            $r["metodologi.dampak.$i.hukum"]        = ['required', 'string', 'max:1000'];
        }

        foreach (range(1, 4) as $i) {
            $r["metodologi.kategori_batas.$i"] = ['required', 'integer', 'between:1,24'];
            if ($i > 1) {
                $r["metodologi.kategori_batas.$i"][] = 'gt:metodologi.kategori_batas.' . ($i - 1);
            }
        }

        // 5. Risiko inheren
        $r['inheren'] = ['required', 'array'];
        foreach (array_keys(MetodologiRisiko::SIKLUS) as $s) {
            $r["inheren.$s"]               = ['nullable', 'array', 'max:20'];
            $r["inheren.$s.*.id"]          = ['required', 'string', 'max:40'];
            $r["inheren.$s.*.risiko"]      = ['required', 'string', 'max:500'];
            $r["inheren.$s.*.kemungkinan"] = ['required', 'integer', 'between:1,5'];
            $r["inheren.$s.*.dampak"]      = ['required', 'integer', 'between:1,5'];
        }

        return $r;
    }

    private function rulesPengendalian(): array
    {
        $tahun = (int) now()->year;

        $r = [
            'kontrol'  => ['array'],
            'residual' => ['required', 'array'],   // kosong = belum ada risiko di Tab VI
        ];

        // Setiap risiko: pengendalian boleh kosong, residual minimal satu
        foreach ($this->idRisiko() as $id) {
            $r["kontrol.$id"]  = ['array', 'max:20'];
            $r["residual.$id"] = ['required', 'array', 'max:20'];
        }

        return $r + [
            'kontrol.*.*.langkah'      => ['required', 'string', 'max:1000'],
            'kontrol.*.*.status'       => ['required', Rule::in(array_keys(self::STATUS_KONTROL))],
            'kontrol.*.*.bukti'        => ['nullable', 'required_if:kontrol.*.*.status,aktif', 'string', 'max:500'],
            'kontrol.*.*.jenis'        => ['required', Rule::in(array_keys(self::JENIS_KONTROL))],

            'residual.*.*.risiko'      => ['required', 'string', 'max:500'],
            'residual.*.*.langkah'     => ['required', 'string', 'max:1000'],
            'residual.*.*.kemungkinan' => ['required', 'integer', 'between:1,5'],
            'residual.*.*.dampak'      => ['required', 'integer', 'between:1,5'],
            'residual.*.*.keputusan'   => ['required', Rule::in(array_keys(self::KEPUTUSAN))],
            'residual.*.*.penanganan'  => ['array', 'max:20', 'required_if:residual.*.*.keputusan,mitigasi'],

            'residual.*.*.penanganan.*.langkah' => ['required', 'string', 'max:1000'],
            'residual.*.*.penanganan.*.pj'      => ['required', 'string', 'max:255'],
            'residual.*.*.penanganan.*.bulan'   => ['required', 'integer', 'between:1,12'],
            'residual.*.*.penanganan.*.tahun'   => ['required', 'integer', 'between:' . ($tahun - 1) . ',' . ($tahun + 10)],
            'residual.*.*.penanganan.*.status'  => ['required', Rule::in(array_keys(self::STATUS_PENANGANAN))],
        ];
    }

    private function messages(): array
    {
        return [
            // Umum
            'required'    => ':attribute wajib diisi.',
            'max'         => ':attribute maksimal :max karakter.',
            'in'          => 'Pilihan :attribute tidak valid.',
            'date_format' => ':attribute harus berupa tanggal yang valid.',
            'integer'     => ':attribute harus berupa angka bulat.',

            // I
            'tanggal_review.after'    => 'Tanggal review berikutnya harus setelah tanggal penyusunan.',
            'status_dokumen.required' => 'Pilih status dokumen.',
            'riwayat.required'        => 'Isi minimal satu riwayat perubahan dokumen.',
            'riwayat.max'             => 'Maksimal :max riwayat perubahan.',

            // III
            'keterangan.*.required' => 'Keterangan wajib diisi untuk potensi risiko yang bernilai Ya.',
            'keterangan.*.max'      => 'Keterangan maksimal :max karakter.',

            // IV
            'pihak.required'        => 'Isi minimal satu pihak yang terlibat dalam pemrosesan.',
            'pihak.max'             => 'Maksimal :max pihak.',
            'pihak.*.data.required' => 'Pilih minimal satu data yang diakses pihak ini.',
            'pihak.*.data.*.in'     => 'Data yang diakses harus termasuk jenis data pribadi di RoPA.',

            // V
            'jawaban.*.required' => 'Pilih Ya atau Tidak.',
            'alasan.*.required'  => 'Keterangan wajib diisi.',
            'alasan.*.max'       => 'Keterangan maksimal :max karakter.',

            // VI
            'metodologi.kategori_batas.*.integer' => 'Batas harus berupa angka bulat.',
            'metodologi.kategori_batas.*.between' => 'Batas harus antara :min dan :max.',
            'metodologi.kategori_batas.*.gt'      => 'Batas harus lebih besar dari batas level sebelumnya.',
            'inheren.required'                    => 'Identifikasi minimal satu risiko inheren.',
            'inheren.*.max'                       => 'Maksimal :max risiko per siklus.',
            'inheren.*.*.kemungkinan.required'    => 'Pilih nilai kemungkinan.',
            'inheren.*.*.dampak.required'         => 'Pilih nilai dampak.',

            // VII
            'kontrol.*.max'                          => 'Maksimal :max langkah pengendalian per risiko.',
            'kontrol.*.*.bukti.required_if'          => 'Bukti dukung wajib diisi untuk langkah yang berstatus Aktif.',
            'residual.required'                      => 'Belum ada risiko inheren di Tab VI. Isi dan simpan Tab VI terlebih dahulu.',
            'residual.*.required'                    => 'Setiap risiko wajib memiliki minimal satu risiko residual beserta keputusannya.',
            'residual.*.max'                         => 'Maksimal :max risiko residual per risiko.',
            'residual.*.*.penanganan.required_if'    => 'Keputusan Mitigasi wajib disertai minimal satu langkah penanganan.',
            'residual.*.*.penanganan.max'            => 'Maksimal :max langkah penanganan.',

            // VIII
            'lembaga_konsultasi.required'       => 'Pilih apakah konsultasi dengan Lembaga dilakukan.',
            'lembaga_tanggal.required_if'       => 'Tanggal rekomendasi Lembaga wajib diisi bila konsultasi dilakukan.',
            'lembaga_saran.required_if'         => 'Saran/rekomendasi Lembaga wajib diisi bila konsultasi dilakukan.',
            'lembaga_tindak_lanjut.required_if' => 'Tindak lanjut atas saran Lembaga wajib diisi bila konsultasi dilakukan.',

            // IX
            'ringkasan.*.required'     => 'Kesimpulan wajib diisi.',
            'efektivitas.required'     => 'Pilih efektivitas langkah mitigasi.',
            'keputusan_akhir.required' => 'Pilih keputusan akhir pemrosesan.',
        ];
    }

    private function attributes(): array
    {
        return [
            // I
            'unit_kerja'          => 'Unit kerja',
            'tanggal_penyusunan'  => 'Tanggal penyusunan',
            'tanggal_review'      => 'Tanggal review berikutnya',
            'status_dokumen'      => 'Status dokumen',
            'riwayat.*.versi'     => 'Versi',
            'riwayat.*.tanggal'   => 'Tanggal perubahan',
            'riwayat.*.deskripsi' => 'Deskripsi perubahan',
            'riwayat.*.oleh'      => 'Disusun/direvisi oleh',

            // III
            'keterangan.*' => 'Keterangan',

            // IV
            'latar_belakang'     => 'Latar belakang pemrosesan',
            'pihak.*.nama'       => 'Nama pihak',
            'pihak.*.peran'      => 'Peran',
            'pihak.*.lokasi'     => 'Dalam/Luar Negeri',
            'pihak.*.dasar'      => 'Dasar keterlibatan',
            'pihak.*.keterangan' => 'Keterangan',

            // V
            'jawaban.*' => 'Jawaban',
            'alasan.*'  => 'Keterangan',

            // VI
            'metodologi.kemungkinan.*.periode' => 'Periode kejadian',
            'metodologi.dampak.*.finansial'    => 'Dampak finansial',
            'metodologi.dampak.*.reputasi'     => 'Dampak reputasi',
            'metodologi.dampak.*.kepatuhan'    => 'Dampak kepatuhan regulasi',
            'metodologi.dampak.*.hukum'        => 'Dampak hukum',
            'inheren.*.*.risiko'               => 'Risiko',
            'inheren.*.*.kemungkinan'          => 'Kemungkinan',
            'inheren.*.*.dampak'               => 'Dampak',

            // VII
            'kontrol.*.*.langkah'      => 'Langkah mitigasi',
            'kontrol.*.*.status'       => 'Status',
            'kontrol.*.*.bukti'        => 'Bukti dukung',
            'kontrol.*.*.jenis'        => 'Keterangan',
            'residual.*.*.risiko'      => 'Risiko residual',
            'residual.*.*.langkah'     => 'Langkah mitigasi',
            'residual.*.*.kemungkinan' => 'Kemungkinan residual',
            'residual.*.*.dampak'      => 'Dampak residual',
            'residual.*.*.keputusan'   => 'Keputusan',
            'residual.*.*.penanganan.*.langkah' => 'Langkah mitigasi yang direncanakan',
            'residual.*.*.penanganan.*.pj'      => 'Penanggung jawab',
            'residual.*.*.penanganan.*.bulan'   => 'Bulan target',
            'residual.*.*.penanganan.*.tahun'   => 'Tahun target',
            'residual.*.*.penanganan.*.status'  => 'Status',

            // VIII
            'ppdp_tanggal'          => 'Tanggal rekomendasi PPDP',
            'ppdp_saran'            => 'Saran/rekomendasi PPDP',
            'ppdp_tindak_lanjut'    => 'Tindak lanjut atas saran PPDP',
            'lembaga_tanggal'       => 'Tanggal rekomendasi Lembaga',
            'lembaga_saran'         => 'Saran/rekomendasi Lembaga',
            'lembaga_tindak_lanjut' => 'Tindak lanjut atas saran Lembaga',

            // IX
            'ringkasan.*'     => 'Kesimpulan',
            'efektivitas'     => 'Efektivitas langkah mitigasi',
            'keputusan_akhir' => 'Keputusan akhir',
            'catatan'         => 'Catatan tambahan',
        ];
    }

    private function guard(): ?RedirectResponse
    {
        $ropa = session('ropa', []);

        if (! RopaStatus::lengkap($ropa)) {
            return redirect()->route('ropa.form')
                ->withErrors(['ropa' => 'Lengkapi seluruh tab RoPA terlebih dahulu.']);
        }

        if (! RopaStatus::wajibDpia($ropa)) {
            return redirect()->route('ropa.form', ['tab' => 'risiko'])
                ->withErrors(['ropa' => 'Tidak ada indikator risiko tinggi, DPIA tidak diwajibkan untuk aktivitas ini.']);
        }

        return null;
    }
}
