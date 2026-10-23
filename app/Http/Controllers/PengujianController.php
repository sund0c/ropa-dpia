<?php

namespace App\Http\Controllers;

use App\Support\LabelTte;
use App\Support\PdfFooter;
use App\Support\RunbookOwasp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Modul E — Pengecekan Keamanan Aplikasi Web (OWASP Top 10:2025, pengujian manual).
 * Data hanya di session ('pengujian'), satu aplikasi per sesi.
 */
class PengujianController extends Controller
{
    /** Daftar tab. Tab A01–A10 dan Ringkasan ditambahkan di sini + di rules(). */
    public const TABS = [
        'informasi' => 'I. Informasi',
        'a01' => 'A01', 'a02' => 'A02', 'a03' => 'A03', 'a04' => 'A04', 'a05' => 'A05',
        'a06' => 'A06', 'a07' => 'A07', 'a08' => 'A08', 'a09' => 'A09', 'a10' => 'A10',
        'ringkasan' => 'XII. Ringkasan',
    ];

    public const PERNYATAAN_BAKU = 'Ruang lingkup pengujian dibatasi pada endpoint dan peran yang disebutkan dalam laporan ini, diuji secara manual pada tanggal pengujian. Endpoint, peran, dan alur lain tidak termasuk cakupan dan memerlukan pengujian terpisah. Tidak ditemukannya kerentanan di luar cakupan tidak boleh diartikan sebagai bebas kerentanan.';

    public const HASIL = [
        'aman'          => 'Aman',
        'rentan'        => 'Kerentanan',
        'tinjau'        => 'Perlu tinjauan',
        'tidak_diuji'   => 'Tidak diuji',
        'tidak_berlaku' => 'Tidak berlaku',
    ];

    public const MAKS_ENTRI = 20;

    /** Tingkat risiko temuan, urut dari paling berat. */
    public const RISIKO = [
        'kritis' => 'Kritis',
        'tinggi' => 'Tinggi',
        'sedang' => 'Sedang',
        'rendah' => 'Rendah',
    ];

    public static function namaTab(string $tab): string
    {
        $k = RunbookOwasp::kategori($tab);

        return $k ? $k['kode'] . ' ' . $k['nama'] : (self::TABS[$tab] ?? $tab);
    }

    /** Entri satu butir: daftar entri tersimpan, atau satu entri kosong bila belum ada. */
    public static function entri(array $uji, string $k, int $no): array
    {
        $e = $uji[$k]['butir'][$no]['entri'] ?? [];

        return $e === [] ? [[]] : array_values($e);
    }

    /** Seluruh entri berstatus Kerentanan, urut menurut tingkat risiko lalu kode butir. */
    public static function daftarTemuan(array $uji): array
    {
        $urut = array_flip(array_keys(self::RISIKO));
        $temuan = [];
        foreach (RunbookOwasp::KATEGORI as $k => $kat) {
            foreach ($uji[$k]['butir'] ?? [] as $no => $butir) {
                foreach ($butir['entri'] ?? [] as $h) {
                    if (($h['hasil'] ?? null) !== 'rentan') continue;
                    $temuan[] = $h + [
                        'kode'  => $kat['kode'] . '.' . $no,
                        'judul' => $kat['butir'][$no]['judul'] ?? '',
                        'kat'   => $kat['kode'] . ' ' . $kat['nama'],
                    ];
                }
            }
        }
        usort($temuan, fn ($a, $b) => [$urut[$a['risiko']] ?? 9, $a['kode']] <=> [$urut[$b['risiko']] ?? 9, $b['kode']]);

        return $temuan;
    }

    /** Rekap jumlah entri per hasil, per kategori. Total = jumlah entri yang dicatat. */
    public static function rekap(array $uji): array
    {
        $rekap = [];
        foreach (RunbookOwasp::KATEGORI as $k => $kat) {
            $baris = array_fill_keys(array_keys(self::HASIL), 0);
            $total = 0;
            foreach ($uji[$k]['butir'] ?? [] as $butir) {
                foreach ($butir['entri'] ?? [] as $h) {
                    if (isset($baris[$h['hasil'] ?? ''])) { $baris[$h['hasil']]++; $total++; }
                }
            }
            $rekap[$k] = $baris + ['total' => $total];
        }

        return $rekap;
    }

    public const LINGKUNGAN = [
        'staging'     => 'Staging / uji',
        'produksi'    => 'Produksi',
        'pengembangan'=> 'Pengembangan (development)',
    ];

    public const ALAT = [
        'burp'    => 'Burp Suite (proxy manual)',
        'zap'     => 'OWASP ZAP (proxy manual)',
        'devtools'=> 'Browser DevTools',
        'curl'    => 'curl',
        'tls'     => 'Alat uji TLS (mis. testssl.sh)',
    ];

    /** Prinsip pengujian dari runbook; seluruhnya wajib disetujui sebelum pengujian. */
    public const PRINSIP = [
        'otorisasi'   => 'Otorisasi dulu: aplikasi ini hanya diuji berdasarkan izin tertulis yang tercatat di tab ini.',
        'keselamatan' => 'Gerbang keselamatan: request yang mengubah data (POST/PUT/DELETE/PATCH) tidak di-replay ke data produksi nyata; dipakai staging, akun uji, atau objek uji.',
        'isi_respons' => 'Hasil dinilai dari isi respons, bukan status HTTP mentah (200 bisa berisi penolakan; 302 bisa mengarah ke login).',
        'sanitasi'    => 'Bukti disanitasi sebelum dicatat: token sesi, kata sandi, dan data pribadi dibuang atau disamarkan.',
    ];

    // =====================================================================
    //  Status tab
    // =====================================================================

    public static function tabsAktif(array $uji): array
    {
        return self::TABS;
    }

    public static function tabBelumDisimpan(array $uji): array
    {
        return array_values(array_diff_key(self::tabsAktif($uji), $uji));
    }

    public static function lengkap(array $uji): bool
    {
        return self::tabBelumDisimpan($uji) === [];
    }

    public static function tanggal(?string $ymd): string
    {
        if (blank($ymd)) return '-';
        try {
            return Carbon::createFromFormat('!Y-m-d', $ymd)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return $ymd;
        }
    }

    // =====================================================================
    //  Form & simpan
    // =====================================================================

    public function form(Request $request)
    {
        $uji  = session('pengujian', []);
        $tabs = self::tabsAktif($uji);
        $tab  = $request->query('tab', array_key_first($tabs));
        abort_unless(array_key_exists($tab, $tabs), 404);

        return view('pengujian.form', ['tabs' => $tabs, 'tab' => $tab, 'uji' => $uji]);
    }

    public function save(Request $request, string $tab)
    {
        abort_unless(array_key_exists($tab, self::tabsAktif(session('pengujian', []))), 404);

        $data = $this->dataTab($tab, $request);

        $uji               = session('pengujian', []);
        $uji[$tab]         = $data;
        $uji['nomor']      = $uji['informasi']['nomor_laporan'] ?? null;
        $uji['diperbarui'] = now()->toIso8601String();
        session(['pengujian' => $uji]);

        $urutan = array_keys(self::tabsAktif($uji));
        $next   = $urutan[array_search($tab, $urutan, true) + 1] ?? $tab;

        $pesan = self::namaTab($tab) . ' tersimpan.'
            . ($next !== $tab ? ' Silakan lanjutkan ' . self::namaTab($next) . '.' : '');

        return redirect()->route('pengujian.form', ['tab' => $next])->with('saved', $pesan);
    }

    /** Bersihkan + validasi isian satu tab (dipakai Simpan dan Impor JSON). */
    public function dataTab(string $tab, Request $request): array
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        if ($tab === 'informasi') {
            foreach (['nomor_laporan', 'kode_aset'] as $f) {
                if ($request->filled($f)) {
                    $request->merge([$f => strtoupper(preg_replace('/\s+/', '', (string) $request->input($f)))]);
                }
            }
            $request->merge(['alat' => array_values(array_intersect(array_keys(self::ALAT), (array) $request->input('alat', [])))]);
        }

        $v = Validator::make($request->all(), $this->rules($tab), $this->messages(), $this->attributes($tab));

        $v->after(function ($v) use ($request, $tab) {
            if ($tab !== 'informasi') return;

            $tgl = fn (string $f) => rescue(fn () => filled($request->input($f))
                ? Carbon::createFromFormat('!Y-m-d', $request->input($f)) : null, null, false);

            [$surat, $mulai, $selesai] = [$tgl('tanggal_surat'), $tgl('periode_mulai'), $tgl('periode_selesai')];

            if ($surat && $mulai && $mulai->lt($surat)) {
                $v->errors()->add('periode_mulai', 'Pengujian tidak boleh dimulai sebelum tanggal surat otorisasi.');
            }
            if ($mulai && $selesai && $selesai->lt($mulai)) {
                $v->errors()->add('periode_selesai', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
            }
            if (empty($request->input('alat')) && blank($request->input('alat_lainnya'))) {
                $v->errors()->add('alat', 'Pilih minimal satu alat yang dipakai.');
            }
        });

        $data = $v->validate();

        if ($kat = RunbookOwasp::kategori($tab)) {
            $butir = [];
            foreach (array_keys($kat['butir']) as $no) {
                $entri = [];
                foreach (array_values((array) ($data['butir'][$no]['entri'] ?? [])) as $h) {
                    $hasil = $h['hasil'];
                    $diuji = in_array($hasil, ['aman', 'rentan', 'tinjau'], true);
                    $entri[] = [
                        'hasil'       => $hasil,
                        'objek'       => $diuji ? ($h['objek'] ?? null) : null,
                        'peran'       => $diuji ? ($h['peran'] ?? null) : null,
                        'bukti'       => $diuji ? ($h['bukti'] ?? null) : null,
                        'risiko'      => $hasil === 'rentan' ? $h['risiko'] : null,
                        'rekomendasi' => in_array($hasil, ['rentan', 'tinjau'], true) ? ($h['rekomendasi'] ?? null) : null,
                        'alasan'      => $diuji ? null : ($h['alasan'] ?? null),
                    ];
                }
                $butir[$no] = ['entri' => $entri];
            }
            return ['butir' => $butir];
        }

        if ($tab === 'informasi') {
            $data['alat'] = array_values($data['alat'] ?? []);
            foreach (array_keys(self::PRINSIP) as $p) $data['prinsip'][$p] = true;
        }

        return $data;
    }

    // =====================================================================
    //  Export PDF
    // =====================================================================

    public function pdf(Request $request)
    {
        $uji = session('pengujian', []);

        if (! self::lengkap($uji)) {
            return redirect()->route('pengujian.form')
                ->withErrors(['pengujian' => 'Lengkapi dan simpan seluruh tab terlebih dahulu sebelum export PDF.']);
        }

        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte'), [
            'lokasi'  => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tte'     => ['nullable', 'array'],
            'tte.*'   => [Rule::in(['ketua', 'pimpinan'])],
        ]);

        $kembali = fn (string $pesan) => redirect()
            ->route('pengujian.form', ['tab' => array_key_last(self::tabsAktif($uji))])
            ->withErrors(['pengujian' => $pesan]);

        if ($v->fails()) {
            return $kembali('Lokasi dan tanggal laporan wajib diisi dengan benar sebelum mencetak PDF.');
        }

        $input = $v->validated();
        $tte   = array_values(array_unique($input['tte'] ?? []));

        [$label, $galat] = LabelTte::ambil($request, $tte);
        if ($galat) return $kembali($galat);

        $penetapan = [
            'lokasi'  => $input['lokasi'],
            'tanggal' => Carbon::createFromFormat('!Y-m-d', $input['tanggal'])->locale('id')->translatedFormat('d F Y'),
            'tte'     => $tte,
            'label'   => $label,
        ];

        $pdf = Pdf::loadView('pengujian.pdf', ['uji' => $uji, 'penetapan' => $penetapan])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        PdfFooter::tambah($pdf, 'TERBATAS — Laporan Pengujian Keamanan Aplikasi Web ' . $uji['nomor']);

        return $pdf->download('Pengujian-' . self::namaBerkas($uji['nomor']) . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    public static function namaBerkas(?string $nomor): string
    {
        $aman = trim(preg_replace('/[^A-Za-z0-9.-]+/', '-', (string) $nomor), '-');

        return $aman !== '' ? $aman : 'laporan';
    }

    // =====================================================================
    //  Validasi
    // =====================================================================

    private function rules(string $tab): array
    {
        if ($kat = RunbookOwasp::kategori($tab)) {
            $r = ['butir' => ['required', 'array']];
            foreach (array_keys($kat['butir']) as $no) {
                $p = "butir.$no";
                $e = "$p.entri";
                $r[$e]            = ['required', 'array', 'min:1', 'max:' . self::MAKS_ENTRI];
                $r["$e.*.hasil"]       = ['required', Rule::in(array_keys(self::HASIL))];
                $r["$e.*.objek"]       = ['nullable', "required_if:$e.*.hasil,aman,rentan,tinjau", 'string', 'max:500'];
                $r["$e.*.peran"]       = ['nullable', 'string', 'max:255'];
                $r["$e.*.bukti"]       = ['nullable', "required_if:$e.*.hasil,aman,rentan,tinjau", 'string', 'max:3000'];
                $r["$e.*.risiko"]      = ['nullable', "required_if:$e.*.hasil,rentan", Rule::in(array_keys(self::RISIKO))];
                $r["$e.*.rekomendasi"] = ['nullable', "required_if:$e.*.hasil,rentan", 'string', 'max:2000'];
                $r["$e.*.alasan"]      = ['nullable', "required_if:$e.*.hasil,tidak_diuji,tidak_berlaku", 'string', 'max:500'];
            }
            return $r;
        }

        if ($tab === 'ringkasan') {
            return [
                'kesimpulan'  => ['required', 'string', 'max:3000'],
                'pernyataan'  => ['required', 'string', 'max:3000'],
            ];
        }

        return match ($tab) {
            'informasi' => array_merge([
                // A. Aplikasi yang diuji
                'nomor_laporan'    => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9\/._-]*$/'],
                'nama_aplikasi'    => ['required', 'string', 'max:255'],
                'url'              => ['required', 'string', 'max:255', 'url:http,https'],
                'lingkungan'       => ['required', Rule::in(array_keys(self::LINGKUNGAN))],
                'kode_aset'        => ['nullable', 'regex:/^(DI|PL|PK|SP|PS)-\d{6}$/', 'not_regex:/-0{6}$/'],
                'teknologi'        => ['nullable', 'string', 'max:255'],
                'pemilik_aset'     => ['required', 'string', 'max:255'],
                'pimpinan_nama'    => ['required', 'string', 'max:255'],
                'pimpinan_jabatan' => ['required', 'string', 'max:255'],

                // B. Otorisasi
                'nomor_surat'      => ['required', 'string', 'max:255'],
                'tanggal_surat'    => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
                'pemberi_izin'     => ['required', 'string', 'max:255'],
                'periode_mulai'    => ['required', 'date_format:Y-m-d'],
                'periode_selesai'  => ['required', 'date_format:Y-m-d'],

                // C. Tim penguji
                'tim_nama'         => ['required', 'string', 'max:255'],
                'ketua_nama'       => ['required', 'string', 'max:255'],
                'ketua_jabatan'    => ['required', 'string', 'max:255'],
                'ketua_instansi'   => ['required', 'string', 'max:255'],
                'anggota'          => ['nullable', 'string', 'max:1000'],

                // D. Ruang lingkup
                'peran'            => ['required', 'string', 'max:1000'],
                'cakupan'          => ['required', 'string', 'max:3000'],
                'luar_cakupan'     => ['nullable', 'string', 'max:2000'],
                'alat'             => ['nullable', 'array'],
                'alat.*'           => [Rule::in(array_keys(self::ALAT))],
                'alat_lainnya'     => ['nullable', 'string', 'max:255'],
            ], collect(array_keys(self::PRINSIP))
                ->mapWithKeys(fn ($p) => ["prinsip.$p" => ['accepted']])->all()),
        };
    }

    private function messages(): array
    {
        return [
            'required'                => ':attribute wajib diisi.',
            'max'                     => ':attribute maksimal :max karakter.',
            'in'                      => 'Pilihan :attribute tidak valid.',
            'date_format'             => 'Format :attribute tidak valid.',
            'url'                     => ':attribute harus berupa alamat lengkap, diawali http:// atau https://',
            'accepted'                => 'Prinsip ini wajib disetujui sebelum pengujian.',
            'required_if'             => ':attribute wajib diisi.',
            'butir.*.entri.required'      => 'Isi minimal satu entri untuk butir ini.',
            'butir.*.entri.min'          => 'Isi minimal satu entri untuk butir ini.',
            'butir.*.entri.*.hasil.required' => 'Pilih hasil pengujian entri ini.',
            'nomor_laporan.regex'     => 'Nomor Laporan hanya boleh berisi huruf, angka, dan tanda / . _ -',
            'kode_aset.regex'         => 'Format Kode Aset: kode klasifikasi + 6 digit, contoh PL-000001.',
            'kode_aset.not_regex'     => 'Nomor pada Kode Aset minimal 000001.',
            'tanggal_surat.before_or_equal' => 'Tanggal surat otorisasi tidak boleh di masa depan.',
        ];
    }

    private function attributes(string $tab = 'informasi'): array
    {
        if ($kat = RunbookOwasp::kategori($tab)) {
            $a = [];
            foreach (array_keys($kat['butir']) as $no) {
                $e = "butir.$no.entri.*";
                $a["$e.hasil"]       = 'Hasil';
                $a["$e.objek"]       = 'Endpoint / objek yang diuji';
                $a["$e.peran"]       = 'Peran';
                $a["$e.bukti"]       = 'Catatan bukti';
                $a["$e.risiko"]      = 'Tingkat risiko';
                $a["$e.rekomendasi"] = 'Rekomendasi perbaikan';
                $a["$e.alasan"]      = 'Alasan';
            }
            return $a;
        }

        return [
            'nomor_laporan'    => 'Nomor Laporan',
            'nama_aplikasi'    => 'Nama Aplikasi',
            'url'              => 'URL Aplikasi',
            'lingkungan'       => 'Lingkungan Pengujian',
            'kode_aset'        => 'Kode Aset',
            'teknologi'        => 'Teknologi',
            'pemilik_aset'     => 'Nama Instansi Pemilik Aplikasi',
            'pimpinan_nama'    => 'Nama Pimpinan Instansi Pemilik Aplikasi',
            'pimpinan_jabatan' => 'Jabatan Pimpinan',
            'nomor_surat'      => 'Nomor Surat Otorisasi',
            'tanggal_surat'    => 'Tanggal Surat Otorisasi',
            'pemberi_izin'     => 'Pemberi Izin',
            'periode_mulai'    => 'Tanggal Mulai Pengujian',
            'periode_selesai'  => 'Tanggal Selesai Pengujian',
            'tim_nama'         => 'Nama Tim Penguji',
            'ketua_nama'       => 'Nama Ketua Tim Penguji',
            'ketua_jabatan'    => 'Jabatan Ketua Tim',
            'ketua_instansi'   => 'Instansi Ketua Tim',
            'anggota'          => 'Anggota Tim',
            'peran'            => 'Peran / Akun yang Diuji',
            'cakupan'          => 'Cakupan Pengujian',
            'luar_cakupan'     => 'Di Luar Cakupan',
            'alat'             => 'Alat',
            'alat_lainnya'     => 'Alat Lainnya',
            'kesimpulan'       => 'Kesimpulan',
            'pernyataan'       => 'Pernyataan Ruang Lingkup',
        ];
    }
}
