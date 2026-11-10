<?php

namespace App\Http\Controllers;

use App\Support\Kse;
use App\Support\LabelTte;
use App\Support\PdfFooter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Aset Register: rekap banyak file JSON aset (hasil ekspor) menjadi satu tabel PDF, disambung
 * Identifikasi & Penilaian Aset TIK setiap aset (tanpa tanda tangan).
 * File dibaca di browser lalu dikirim sebagai satu field teks (tidak terkena batas max_file_uploads PHP).
 * Tidak ada yang disimpan di server maupun di sesi.
 */
class AsetRegisterController extends Controller
{
    private const FORMAT     = 'manajemenaset';
    private const VERSI      = 1;
    private const MAKS_FILE  = 1000;
    private const MAKS_GALAT = 10;   // jumlah nama file bermasalah yang disebutkan di pesan

    public const STATUS_IIV_SINGKAT = [
        'vital' => 'IIV',
        'bukan' => 'Bukan IIV',
    ];

    public function pdf(Request $request)
    {
        @set_time_limit(300);   // ratusan aset = ratusan halaman
        $gagal = fn(string $pesan) => redirect()->route('aset.form')->withErrors(['aset' => 'Aset Register: ' . $pesan]);

        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte', 'berkas_json', 'lampiran', 'klasifikasi'), [
            'lokasi'        => ['required', 'string', 'max:100'],
            'tanggal'       => ['required', 'date_format:Y-m-d'],
            'tte'           => ['nullable', 'array'],
            'tte.*'         => [Rule::in(['pimpinan'])],
            'berkas_json'   => ['required', 'string'],
            'lampiran'      => ['nullable', 'boolean'],
            'klasifikasi'   => ['required', 'array', 'min:1'],
            'klasifikasi.*' => [Rule::in(array_keys(AsetController::KLASIFIKASI))],
        ]);
        if ($v->fails()) {
            return $gagal($v->errors()->has('klasifikasi')
                ? 'pilih minimal satu klasifikasi aset yang dicetak.'
                : 'pilih file JSON aset, lalu isi lokasi dan tanggal penetapan dengan benar.');
        }
        $input    = $v->validated();
        $lampiran = (bool) ($input['lampiran'] ?? false);
        $dipilih  = array_values(array_unique($input['klasifikasi']));

        $berkas = json_decode($input['berkas_json'], true);
        if (! is_array($berkas) || $berkas === [] || ! array_is_list($berkas)) {
            return $gagal('tidak ada file JSON yang terbaca.');
        }
        if (count($berkas) > self::MAKS_FILE) {
            return $gagal('maksimal ' . self::MAKS_FILE . ' file sekaligus.');
        }

        // --- Baca & validasi setiap file (aturan sama dengan form/impor) ---
        $baris = [];
        $salah = [];
        foreach ($berkas as $i => $f) {
            $nama = is_array($f) && is_string($f['nama'] ?? null) ? mb_substr($f['nama'], 0, 120) : 'file #' . ($i + 1);
            $hasil = is_array($f) && is_string($f['isi'] ?? null) ? $this->baca($f['isi']) : 'isi file kosong';

            if (is_string($hasil)) {
                $salah[] = "$nama ($hasil)";
            } else {
                $baris[] = $hasil + ['file' => $nama];
            }
        }

        if ($salah) {
            return $gagal($this->daftar(count($salah) . ' file tidak dapat dipakai', $salah));
        }

        // --- Filter klasifikasi yang dipilih ---
        $baris = array_values(array_filter($baris, fn($b) => in_array($b['kode_klasifikasi'], $dipilih, true)));
        if ($baris === []) {
            return $gagal('tidak ada aset dengan klasifikasi yang dipilih di antara file yang diunggah.');
        }

        // --- Kode aset ganda ---
        $ganda = [];
        foreach (collect($baris)->groupBy('kode') as $kode => $grup) {
            if ($grup->count() > 1) $ganda[] = "$kode: " . $grup->pluck('file')->implode(', ');
        }
        if ($ganda) {
            return $gagal($this->daftar('kode aset ganda', $ganda));
        }

        // --- Satu register = satu instansi pemilik aset ---
        $pemilik = collect($baris)->pluck('pemilik')->map(fn($p) => trim($p))->unique(fn($p) => mb_strtolower($p))->values();
        if ($pemilik->count() > 1) {
            return $gagal('file berasal dari lebih dari satu pemilik aset (' . $pemilik->implode('; ') . '). Satu register hanya untuk satu instansi.');
        }

        // Pimpinan: yang paling banyak muncul (antisipasi salah ketik di sebagian file)
        $pimpinan = collect($baris)
            ->map(fn($b) => $b['pimpinan_nama'] . '|' . $b['pimpinan_jabatan'])
            ->countBy()->sortDesc()->keys()->first();
        [$pimpinanNama, $pimpinanJabatan] = explode('|', (string) $pimpinan, 2) + [null, null];

        // --- Urutkan: klasifikasi aset (A–Z), lalu kode aset ---
        usort($baris, fn($a, $b) => [mb_strtolower($a['klasifikasi']), $a['kode']] <=> [mb_strtolower($b['klasifikasi']), $b['kode']]);

        [$label, $galat] = LabelTte::ambil($request, array_values(array_unique($input['tte'] ?? [])));
        if ($galat) {
            return $gagal($galat);
        }

        $pdf = Pdf::loadView('aset.register', [
            'baris'     => $baris,
            'rekap'     => $this->rekap($baris, $dipilih),
            'instansi'  => $pemilik->first(),
            'lampiran'  => $lampiran,
            // Keterangan di bawah judul bila tidak semua klasifikasi dicetak
            'cakupan'   => count($dipilih) < count(AsetController::KLASIFIKASI)
                ? implode(', ', array_map(fn($k) => AsetController::KLASIFIKASI[$k], array_values(array_intersect(array_keys(AsetController::KLASIFIKASI), $dipilih))))
                : null,
            'penetapan' => [
                'lokasi'   => $input['lokasi'],
                'tanggal'  => Carbon::createFromFormat('!Y-m-d', $input['tanggal'])->locale('id')->translatedFormat('d F Y'),
                'label'    => $label,
                'jabatan'  => $pimpinanJabatan,
                'nama'     => $pimpinanNama,
            ],
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        PdfFooter::tambah($pdf);

        return $pdf->download('Aset-Register-' . now()->format('Ymd') . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    /** Satu baris register, atau string alasan bila file tidak dapat dipakai. */
    private function baca(string $json): array|string
    {
        $in = json_decode($json, true, 32);
        if (! is_array($in))                                  return 'bukan JSON yang valid';
        if (($in['format'] ?? null) !== self::FORMAT)         return 'bukan file ekspor aset';
        if ((int) ($in['versi'] ?? 0) !== self::VERSI)        return 'versi format tidak didukung';

        $ctl  = app(AsetController::class);
        $aset = [];
        foreach (AsetController::TABS as $tab => $labelTab) {
            if (! array_key_exists($tab, AsetController::tabsAktif($aset))) continue;   // Tab IV hanya Perangkat Lunak
            $isiTab = $in['aset'][$tab] ?? null;
            if (! is_array($isiTab))                           return "bagian $labelTab belum diisi";
            try {
                $aset[$tab] = $ctl->dataTab($tab, $this->permintaan($isiTab));
            } catch (ValidationException) {
                return "bagian $labelTab tidak valid";
            }
        }

        $inf  = $aset['informasi'];
        $k    = $inf['klasifikasi'];
        $kode = AsetController::kodeAset($inf);
        if (! $kode)                                           return 'nomor urut aset tidak valid';

        $aset['nomor']      = $kode;
        $aset['diperbarui'] = $this->waktu($in['aset']['diperbarui'] ?? null);

        $kritis = $aset['kritikalitas']['kritikalitas'];
        $iiv    = $aset['iiv']['status_iiv'];
        $kse    = $k === 'perangkat_lunak' ? Kse::KATEGORI[$aset['kse']['kategori']] : '-';

        return [
            'kode_klasifikasi' => $k,
            'klasifikasi'      => AsetController::KLASIFIKASI[$k],
            'kode'             => $kode,
            'nama'             => (string) $inf['nama_aset'],
            'kode_kritikalitas'=> $kritis,
            'kode_iiv'         => $iiv,
            'kode_kse'         => $k === 'perangkat_lunak' ? $aset['kse']['kategori'] : null,
            'kritikalitas'     => AsetController::LEVEL[$kritis],
            'iiv'              => self::STATUS_IIV_SINGKAT[$iiv],
            'kse'              => $kse,
            'aset'             => $aset,
            'kelengkapan'      => AsetController::kelengkapanPdf($inf),
            'pemilik'          => (string) $inf['pemilik_aset'],
            'pimpinan_nama'    => (string) ($inf['pimpinan_nama'] ?? ''),
            'pimpinan_jabatan' => (string) ($inf['pimpinan_jabatan'] ?? ''),
        ];
    }

    /** Rekap jumlah untuk halaman pertama (A–D). Kategori yang bernilai 0 tetap ditampilkan. */
    private function rekap(array $baris, array $dipilih): array
    {
        $hitung = fn(string $kunci) => array_count_values(array_filter(array_column($baris, $kunci), 'is_string'));

        $klas = $hitung('kode_klasifikasi');
        $kr   = $hitung('kode_kritikalitas');
        $iiv  = $hitung('kode_iiv');
        $kse  = $hitung('kode_kse');

        $susun = fn(array $label, array $jumlah) => array_map(
            fn($k) => ['label' => $label[$k], 'jumlah' => $jumlah[$k] ?? 0],
            array_keys($label)
        );

        // Klasifikasi: urutan A–Z sama dengan Tabel Aset
        $labelKlas = array_intersect_key(AsetController::KLASIFIKASI, array_flip($dipilih));
        uasort($labelKlas, fn($a, $b) => strcasecmp($a, $b));

        return [
            'klasifikasi'  => $susun($labelKlas, $klas),
            'kritikalitas' => $susun(['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'], $kr),
            'iiv'          => $susun(['vital' => 'Termasuk IIV', 'bukan' => 'Bukan IIV'], $iiv),
            'kse'          => in_array('perangkat_lunak', $dipilih, true)
                ? $susun(['strategis' => 'Strategis', 'tinggi' => 'Tinggi', 'rendah' => 'Rendah'], $kse)
                : null,
            'total'        => count($baris),
            'total_se'     => array_sum($kse),
        ];
    }

    /** Request sintetis dari isi satu tab, dinormalisasi seperti middleware web (trim, "" → null). */
    private function permintaan(array $isi): Request
    {
        $normal = function ($v) use (&$normal) {
            if (is_array($v))  return array_map($normal, $v);
            if (is_string($v)) {
                $v = trim($v);
                return $v === '' ? null : $v;
            }
            return $v;
        };

        return Request::create('/', 'POST', $normal($isi));
    }

    private function waktu($nilai): string
    {
        try {
            return is_string($nilai) ? Carbon::parse($nilai)->toIso8601String() : now()->toIso8601String();
        } catch (\Throwable) {
            return now()->toIso8601String();
        }
    }

    private function daftar(string $judul, array $isi): string
    {
        $tampil = array_slice($isi, 0, self::MAKS_GALAT);
        $sisa   = count($isi) - count($tampil);

        return $judul . ': ' . implode('; ', $tampil) . ($sisa > 0 ? "; dan $sisa lainnya" : '') . '.';
    }
}
