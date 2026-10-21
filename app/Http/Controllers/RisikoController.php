<?php

namespace App\Http\Controllers;

use App\Support\KatalogRisiko;
use App\Support\LabelTte;
use App\Support\PdfFooter;
use App\Support\PetaRisiko;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Modul C — Manajemen Risiko Aset. Data hanya di session ('risiko'), satu aset per sesi. */
class RisikoController extends Controller
{
    /** Daftar tab. Tab berikutnya cukup ditambahkan di sini + di rules(). */
    public const TABS = [
        'informasi'    => 'I. Informasi Aset',
        'peta'         => 'II. Peta Risiko',
        'identifikasi' => 'III. Identifikasi Risiko',
        'nilai'        => 'IV. Nilai Risiko Saat Ini',
        'penanganan'   => 'V. Rencana Penanganan Risiko',
    ];

    public const OPSI = [
        'eskalasi'    => 'Eskalasi',
        'mitigasi'    => 'Mitigasi',
        'transfer'    => 'Transfer',
        'menghindari' => 'Menghindari',
        'menerima'    => 'Menerima',
    ];

    public const STATUS_RESIDUAL = [
        'terima'   => 'Terima',
        'mitigasi' => 'Mitigasi',
    ];

    public const KEPUTUSAN = [
        'ya'    => 'Ya',
        'tidak' => 'Tidak',
    ];

    // =====================================================================
    //  Status tab
    // =====================================================================

    public static function tabsAktif(array $risiko): array
    {
        return self::TABS;
    }

    /** Label tab yang belum pernah disimpan. */
    public static function tabBelumDisimpan(array $risiko): array
    {
        $belum = array_diff_key(self::tabsAktif($risiko), $risiko);

        // Tab IV dianggap belum lengkap bila ada risiko Tab III yang belum dinilai
        if (isset($risiko['nilai']) && self::risikoBelumDinilai($risiko) !== []) {
            $belum['nilai'] = self::TABS['nilai'];
        }

        // Tab V: hanya wajib bila ada risiko yang diputuskan ditangani; semua risiko itu wajib punya rencana
        if (self::daftarDitangani($risiko) === []) {
            unset($belum['penanganan']);
        } elseif (isset($risiko['penanganan']) && self::belumDitangani($risiko) !== []) {
            $belum['penanganan'] = self::TABS['penanganan'];
        }

        return array_values($belum);
    }

    /**
     * Risiko yang di Tab IV diputuskan untuk ditangani (keputusan = Ya), urut prioritas.
     * [id => risiko Tab III + 'nilai' => set Tab IV].
     */
    public static function daftarDitangani(array $risiko): array
    {
        $daftar = self::daftarRisiko($risiko);
        $hasil  = [];

        foreach ((array) ($risiko['nilai']['set'] ?? []) as $s) {
            $id = $s['risiko_id'] ?? null;
            if (($s['keputusan'] ?? null) === 'ya' && isset($daftar[$id])) {
                $hasil[$id] = $daftar[$id] + ['nilai' => $s];
            }
        }

        uasort($hasil, fn ($a, $b) => (int) $a['nilai']['prioritas'] <=> (int) $b['nilai']['prioritas']);

        return $hasil;
    }

    /** Kode risiko yang perlu ditangani tetapi belum punya rencana di Tab V. */
    public static function belumDitangani(array $risiko): array
    {
        $ada = array_column((array) ($risiko['penanganan']['set'] ?? []), 'risiko_id');

        return array_values(array_map(
            fn ($r) => $r['kode'],
            array_diff_key(self::daftarDitangani($risiko), array_flip($ada))
        ));
    }

    /** Kode risiko Tab III yang belum punya nilai di Tab IV. */
    public static function risikoBelumDinilai(array $risiko): array
    {
        $dinilai = array_column((array) ($risiko['nilai']['set'] ?? []), 'risiko_id');

        return array_values(array_map(
            fn ($r) => $r['kode'],
            array_diff_key(self::daftarRisiko($risiko), array_flip($dinilai))
        ));
    }

    public static function lengkap(array $risiko): bool
    {
        return self::tabBelumDisimpan($risiko) === [];
    }

    // =====================================================================
    //  Form & simpan
    // =====================================================================

    public function form(Request $request)
    {
        $risiko = session('risiko', []);
        $tabs   = self::tabsAktif($risiko);
        $tab    = $request->query('tab', array_key_first($tabs));
        abort_unless(array_key_exists($tab, $tabs), 404);

        return view('risiko.form', [
            'tabs'            => $tabs,
            'tab'             => $tab,
            'risiko'          => $risiko,
            'klasifikasi'     => AsetController::KLASIFIKASI,
            'kodeKlasifikasi' => AsetController::KODE_KLASIFIKASI,
        ]);
    }

    public function save(Request $request, string $tab)
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $data = $this->dataTab($tab, $request);

        $risiko               = session('risiko', []);
        $risiko[$tab]         = $data;
        $risiko['nomor']      = $risiko['informasi']['kode_aset'] ?? null;

        // Risiko dihapus di Tab III → set di Tab IV yang merujuk risiko itu ikut dibuang
        if ($tab === 'identifikasi' && isset($risiko['nilai'])) {
            $ada = array_column($data['risiko'], 'id');
            $risiko['nilai']['set'] = array_values(array_filter(
                (array) ($risiko['nilai']['set'] ?? []),
                fn ($s) => in_array($s['risiko_id'] ?? null, $ada, true)
            ));
            if ($risiko['nilai']['set'] === []) unset($risiko['nilai']);
        }

        // Rencana penanganan hanya untuk risiko yang (masih) diputuskan ditangani di Tab IV
        if (isset($risiko['penanganan'])) {
            $ditangani = array_keys(self::daftarDitangani($risiko));
            $risiko['penanganan']['set'] = array_values(array_filter(
                (array) ($risiko['penanganan']['set'] ?? []),
                fn ($s) => in_array($s['risiko_id'] ?? null, $ditangani, true)
            ));
            if ($risiko['penanganan']['set'] === []) unset($risiko['penanganan']);
        }
        $risiko['diperbarui'] = now()->toIso8601String();
        session(['risiko' => $risiko]);

        // Lanjut otomatis ke tab berikutnya; tab terakhir tetap di tempat
        $urutan = array_keys(self::tabsAktif($risiko));
        $next   = $urutan[array_search($tab, $urutan, true) + 1] ?? $tab;

        $pesan = self::TABS[$tab] . ' tersimpan.'
            . ($next !== $tab ? ' Silakan lanjutkan ' . self::TABS[$next] . '.' : '');

        return redirect()->route('risiko.form', ['tab' => $next])->with('saved', $pesan);
    }

    /** Kembalikan Peta Risiko ke nilai default (bila tab ini pernah disimpan, nilainya ikut diganti). */
    public function resetPeta()
    {
        $risiko = session('risiko', []);

        if (isset($risiko['peta'])) {
            $risiko['peta']       = PetaRisiko::bawaan();
            $risiko['diperbarui'] = now()->toIso8601String();
            session(['risiko' => $risiko]);
        }

        return redirect()->route('risiko.form', ['tab' => 'peta'])
            ->with('saved', 'Peta Risiko dikembalikan ke nilai default.');
    }

    /**
     * Bersihkan + validasi isian satu tab. Dipakai oleh tombol Simpan dan oleh Impor JSON,
     * sehingga keduanya memakai aturan yang sama. Melempar ValidationException bila gagal.
     */
    public function dataTab(string $tab, Request $request): array
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        // Kode aset: huruf besar, tanpa spasi
        if ($tab === 'informasi' && $request->filled('kode_aset')) {
            $request->merge(['kode_aset' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('kode_aset')))]);
        }

        // Identifikasi: urutan baris dirapikan (indeks 0..n) sebelum divalidasi
        if ($tab === 'identifikasi') {
            $request->merge(['risiko' => array_values(array_filter((array) $request->input('risiko', []), 'is_array'))]);
        }
        if ($tab === 'nilai') {
            $request->merge(['set' => array_values(array_filter((array) $request->input('set', []), 'is_array'))]);
        }
        // Penanganan: isian residual dibuang bila tidak ada residual; kontrol tambahan hanya bila status Mitigasi
        if ($tab === 'penanganan') {
            $set = array_values(array_filter((array) $request->input('set', []), 'is_array'));
            foreach ($set as &$s) {
                if (($s['residual'] ?? null) !== 'ya') {
                    unset($s['res_dampak'], $s['res_kemungkinan'], $s['res_status'], $s['kontrol_tambahan']);
                } elseif (($s['res_status'] ?? null) !== 'mitigasi') {
                    unset($s['kontrol_tambahan']);
                }
            }
            unset($s);
            $request->merge(['set' => $set]);
        }

        $v = Validator::make(
            $request->all(),
            $this->rules($tab),
            $this->messages() + PetaRisiko::messages(),
            $this->attributes() + PetaRisiko::attributes()
        );

        // Awalan Kode Aset harus sesuai klasifikasi (mis. Perangkat Lunak → PL-)
        $v->after(function ($v) use ($request, $tab) {
            if ($tab !== 'informasi') return;
            $awalan = AsetController::KODE_KLASIFIKASI[$request->input('klasifikasi')] ?? null;
            $kode   = (string) $request->input('kode_aset');
            if ($awalan && preg_match('/^[A-Z]{2}-\d{6}$/', $kode) && ! str_starts_with($kode, $awalan . '-')) {
                $v->errors()->add('kode_aset', "Kode Aset untuk klasifikasi ini harus diawali {$awalan}-.");
            }
        });

        // Tab IV: setiap risiko Tab III wajib dinilai
        $v->after(function ($v) use ($request, $tab) {
            if ($tab !== 'nilai') return;
            $ada   = array_column((array) $request->input('set', []), 'risiko_id');
            $kurang = array_diff_key(self::daftarRisiko(session('risiko', [])), array_flip(array_filter($ada, 'is_string')));
            if ($kurang !== []) {
                $v->errors()->add('set', 'Risiko berikut belum dinilai: ' . implode(', ', array_column($kurang, 'kode')) . '. Muat ulang halaman lalu isi kembali.');
            }
        });

        // Tab V: setiap risiko yang diputuskan ditangani wajib punya rencana
        $v->after(function ($v) use ($request, $tab) {
            if ($tab !== 'penanganan') return;
            $ada    = array_column((array) $request->input('set', []), 'risiko_id');
            $kurang = array_diff_key(self::daftarDitangani(session('risiko', [])), array_flip(array_filter($ada, 'is_string')));
            if ($kurang !== []) {
                $v->errors()->add('set', 'Risiko berikut belum memiliki rencana penanganan: ' . implode(', ', array_column($kurang, 'kode')) . '. Muat ulang halaman lalu isi kembali.');
            }
        });

        $data = $v->validate();

        // Nilai Risiko Saat Ini: IR = Dampak × Kemungkinan, selalu dihitung di server
        if ($tab === 'nilai') {
            $peta = PetaRisiko::nilai(session('risiko.peta', []));
            foreach ($data['set'] as &$s) {
                $s['ir']    = (int) $s['dampak'] * (int) $s['kemungkinan'];
                $s['level'] = PetaRisiko::level($s['ir'], $peta);
            }
            unset($s);
        }

        // Risiko residual: RR = Dampak × Kemungkinan residual, selalu dihitung di server
        if ($tab === 'penanganan') {
            $peta = PetaRisiko::nilai(session('risiko.peta', []));
            foreach ($data['set'] as &$s) {
                if (($s['residual'] ?? null) === 'ya') {
                    $s['rr']       = (int) $s['res_dampak'] * (int) $s['res_kemungkinan'];
                    $s['rr_level'] = PetaRisiko::level($s['rr'], $peta);
                }
            }
            unset($s);
        }

        return $data;
    }

    /** Risiko dari Tab III: [id => ['kode' => 'R01', 'ancaman', 'kerentanan', 'dampak', ...]]. */
    public static function daftarRisiko(array $risiko): array
    {
        $hasil = [];
        foreach (array_values((array) ($risiko['identifikasi']['risiko'] ?? [])) as $n => $r) {
            $hasil[$r['id']] = ['kode' => 'R' . str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT)] + $r;
        }

        return $hasil;
    }

    // =====================================================================
    //  Export PDF
    // =====================================================================

    public function pdf(Request $request)
    {
        $risiko = session('risiko', []);

        if (! self::lengkap($risiko)) {
            return redirect()->route('risiko.form')
                ->withErrors(['risiko' => 'Lengkapi dan simpan seluruh tab terlebih dahulu sebelum export PDF.']);
        }

        // Lokasi, tanggal penetapan & pilihan TTE: hanya untuk cetakan ini, tidak disimpan
        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte'), [
            'lokasi'  => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tte'     => ['nullable', 'array'],
            'tte.*'   => [Rule::in(['pj', 'pimpinan'])],
        ]);

        $kembali = fn(string $pesan) => redirect()
            ->route('risiko.form', ['tab' => array_key_last(self::tabsAktif($risiko))])
            ->withErrors(['risiko' => $pesan]);

        if ($v->fails()) {
            return $kembali('Lokasi dan tanggal penetapan wajib diisi dengan benar sebelum mencetak PDF.');
        }

        $input = $v->validated();

        [$label, $galat] = LabelTte::ambil($request, array_values(array_unique($input['tte'] ?? [])));
        if ($galat) return $kembali($galat);

        $penetapan = [
            'lokasi'  => $input['lokasi'],
            'tanggal' => Carbon::createFromFormat('!Y-m-d', $input['tanggal'])->locale('id')->translatedFormat('d F Y'),
            'tte'     => array_values(array_unique($input['tte'] ?? [])),
            'label'   => $label,
        ];

        $pdf = Pdf::loadView('risiko.pdf', [
            'risiko'      => $risiko,
            'klasifikasi' => AsetController::KLASIFIKASI,
            'peta'        => PetaRisiko::nilai($risiko['peta'] ?? []),
            'kategori'    => KatalogRisiko::KATEGORI,
            'penetapan'   => $penetapan,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        PdfFooter::tambah($pdf, 'Manajemen Risiko ' . $risiko['nomor']);

        return $pdf->download('Risiko-' . $risiko['nomor'] . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    // =====================================================================
    //  Validasi
    // =====================================================================

    private function rules(string $tab): array
    {
        return match ($tab) {
            'informasi' => [
                'nama_aset'        => ['required', 'string', 'max:255'],
                'klasifikasi'      => ['required', Rule::in(array_keys(AsetController::KLASIFIKASI))],
                'kode_aset'        => ['required', 'regex:/^(DI|PL|PK|SP|PS)-\d{6}$/', 'not_regex:/-0{6}$/'],
                'pemilik_aset'     => ['required', 'string', 'max:255'],
                'pimpinan_nama'    => ['required', 'string', 'max:255'],
                'pimpinan_jabatan' => ['required', 'string', 'max:255'],
                'pj_nama'          => ['required', 'string', 'max:255'],
                'pj_jabatan'       => ['required', 'string', 'max:255'],
            ],
            'peta' => PetaRisiko::rules(),
            'identifikasi' => [
                'risiko'              => ['required', 'array', 'min:1', 'max:50'],
                'risiko.*.id'         => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', 'distinct'],
                'risiko.*.ancaman'    => ['required', 'string', 'max:500'],
                'risiko.*.kerentanan' => ['required', 'string', 'max:1000'],
                'risiko.*.kategori'   => ['required', Rule::in(array_keys(KatalogRisiko::KATEGORI))],
                'risiko.*.area'       => ['required', Rule::in(array_keys(PetaRisiko::DIMENSI))],
                'risiko.*.dampak'     => ['required', 'integer', 'between:1,5'],
            ],
            'nilai' => [
                'set'               => ['required', 'array', 'min:1', 'max:100'],
                'set.*.risiko_id'   => ['required', 'distinct', Rule::in(array_keys(self::daftarRisiko(session('risiko', []))))],
                'set.*.kontrol'     => ['required', 'string', 'max:2000'],
                'set.*.dampak'      => ['required', 'integer', 'between:1,5'],
                'set.*.kemungkinan' => ['required', 'integer', 'between:1,5'],
                'set.*.keputusan'   => ['required', Rule::in(array_keys(self::KEPUTUSAN))],
                'set.*.prioritas'   => ['required', 'integer', 'between:1,999'],
            ],
            'penanganan' => [
                'set'                    => ['required', 'array', 'min:1'],
                'set.*.risiko_id'        => ['required', 'distinct', Rule::in(array_keys(self::daftarDitangani(session('risiko', []))))],
                'set.*.opsi'             => ['required', Rule::in(array_keys(self::OPSI))],
                'set.*.rencana_aksi'     => ['required', 'string', 'max:3000'],
                'set.*.keluaran'         => ['required', 'string', 'max:3000'],
                'set.*.target'           => ['required', 'date_format:Y-m-d'],
                'set.*.residual'         => ['required', Rule::in(['ya', 'tidak'])],
                'set.*.res_dampak'       => ['nullable', 'required_if:set.*.residual,ya', 'integer', 'between:1,5'],
                'set.*.res_kemungkinan'  => ['nullable', 'required_if:set.*.residual,ya', 'integer', 'between:1,5'],
                'set.*.res_status'       => ['nullable', 'required_if:set.*.residual,ya', Rule::in(array_keys(self::STATUS_RESIDUAL))],
                'set.*.kontrol_tambahan' => ['nullable', 'required_if:set.*.res_status,mitigasi', 'string', 'max:3000'],
            ],
        };
    }

    private function messages(): array
    {
        return [
            'required'            => ':attribute wajib diisi.',
            'max'                 => ':attribute maksimal :max karakter.',
            'in'                  => 'Pilihan :attribute tidak valid.',
            'kode_aset.regex'     => 'Format Kode Aset: kode klasifikasi + 6 digit, contoh PL-000001.',
            'kode_aset.not_regex' => 'Nomor pada Kode Aset minimal 000001.',
            'risiko.required'     => 'Identifikasi minimal satu risiko.',
            'risiko.min'          => 'Identifikasi minimal satu risiko.',
            'risiko.max'          => 'Maksimal :max risiko.',
            'risiko.*.id.required'     => 'Baris risiko tidak valid. Muat ulang halaman lalu coba lagi.',
            'risiko.*.id.regex'        => 'Baris risiko tidak valid. Muat ulang halaman lalu coba lagi.',
            'risiko.*.id.distinct'     => 'Baris risiko ganda. Muat ulang halaman lalu coba lagi.',
            'risiko.*.dampak.required' => 'Pilih level dampak.',
            'risiko.*.dampak.between'  => 'Pilih level dampak.',
            'set.required'                 => 'Isi minimal satu nilai risiko.',
            'set.min'                      => 'Isi minimal satu nilai risiko.',
            'set.max'                      => 'Maksimal :max nilai risiko.',
            'set.*.risiko_id.required'     => 'Risiko tidak valid. Muat ulang halaman lalu coba lagi.',
            'set.*.risiko_id.distinct'     => 'Risiko ganda. Muat ulang halaman lalu coba lagi.',
            'set.*.risiko_id.in'           => 'Risiko ini sudah tidak ada di Tab III. Muat ulang halaman.',
            'set.*.dampak.required'        => 'Pilih nilai dampak.',
            'set.*.dampak.between'         => 'Pilih nilai dampak.',
            'set.*.kemungkinan.required'   => 'Pilih nilai kemungkinan.',
            'set.*.kemungkinan.between'    => 'Pilih nilai kemungkinan.',
            'set.*.prioritas.integer'      => 'Prioritas harus berupa angka bulat.',
            'set.*.prioritas.between'      => 'Prioritas antara :min dan :max.',
            'set.*.target.date_format'     => 'Target implementasi harus berupa tanggal yang valid.',
            'set.*.res_dampak.required_if'      => 'Pilih nilai dampak residual.',
            'set.*.res_kemungkinan.required_if' => 'Pilih nilai kemungkinan residual.',
            'set.*.res_status.required_if'      => 'Pilih status risiko residual.',
            'set.*.kontrol_tambahan.required_if' => 'Rencana kontrol tambahan wajib diisi bila status Mitigasi.',
        ];
    }

    private function attributes(): array
    {
        return [
            'nama_aset'        => 'Nama Aset',
            'klasifikasi'      => 'Klasifikasi Aset',
            'kode_aset'        => 'Kode Aset',
            'pemilik_aset'     => 'Nama Instansi Pemilik Aset',
            'pimpinan_nama'    => 'Nama Pimpinan Instansi Pemilik Aset',
            'pimpinan_jabatan' => 'Jabatan Pemilik Aset',
            'pj_nama'          => 'Penanggung Jawab Aset',
            'pj_jabatan'       => 'Jabatan Penanggung Jawab',
            'risiko.*.ancaman'    => 'Ancaman',
            'risiko.*.kerentanan' => 'Kerentanan',
            'risiko.*.kategori'   => 'Kategori risiko',
            'risiko.*.area'       => 'Area dampak',
            'set.*.kontrol'       => 'Kontrol saat ini',
            'set.*.keputusan'     => 'Keputusan penanganan risiko',
            'set.*.prioritas'     => 'Prioritas risiko',
            'set.*.opsi'          => 'Opsi penanganan',
            'set.*.rencana_aksi'  => 'Rencana aksi penanganan risiko',
            'set.*.keluaran'      => 'Keluaran',
            'set.*.target'        => 'Target implementasi',
            'set.*.residual'      => 'Terdapat residual',
        ];
    }
}
