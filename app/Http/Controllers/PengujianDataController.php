<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use JsonException;

/** Ekspor / impor JSON untuk Modul E — Pengecekan Keamanan Aplikasi Web. Tidak ada yang disimpan di server. */
class PengujianDataController extends Controller
{
    private const FORMAT = 'pengujianweb';
    private const VERSI  = 1;

    public function ekspor()
    {
        $uji = session('pengujian', []);

        if (empty($uji['nomor'])) {
            return redirect()->route('pengujian.form')
                ->withErrors(['pengujian' => 'Belum ada data untuk diekspor. Simpan Tab I terlebih dahulu.']);
        }

        $isi = json_encode([
            'format'   => self::FORMAT,
            'versi'    => self::VERSI,
            'diekspor' => now()->toIso8601String(),
            'pengujian'   => $uji,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(fn() => print($isi), 'Pengujian-' . PengujianController::namaBerkas($uji['nomor']) . '.json', [
            'Content-Type'  => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /** Muat kembali file JSON hasil ekspor. Setiap tab divalidasi ulang dengan aturan form. */
    public function impor(Request $request)
    {
        $request->validate(
            ['berkas' => ['required', 'file', 'max:1024', 'extensions:json']],
            [
                'berkas.required'   => 'Pilih file JSON yang akan diimpor.',
                'berkas.max'        => 'Ukuran file maksimal 1 MB.',
                'berkas.extensions' => 'File harus berekstensi .json.',
            ]
        );

        $gagalFile = fn(string $pesan) => redirect()->route('pengujian.form')->withErrors(['pengujian' => $pesan]);

        try {
            $in = json_decode(file_get_contents($request->file('berkas')->getRealPath()), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $gagalFile('File tidak dapat dibaca sebagai JSON yang valid.');
        }

        if (! is_array($in) || ($in['format'] ?? null) !== self::FORMAT) {
            return $gagalFile('File ini bukan hasil ekspor modul Pengecekan Keamanan Aplikasi Web.');
        }
        if ((int) ($in['versi'] ?? 0) !== self::VERSI) {
            return $gagalFile('Versi format file tidak didukung.');
        }

        // Hanya data modul ini yang dikosongkan
        session()->forget('pengujian');
        session()->regenerate();

        $gagal  = [];
        $ctl    = app(PengujianController::class);
        $uji = [];

        foreach (PengujianController::TABS as $tab => $label) {
            $isiTab = $in['pengujian'][$tab] ?? null;
            if (! is_array($isiTab)) continue;
            if (! array_key_exists($tab, PengujianController::tabsAktif($uji))) continue;

            try {
                $uji[$tab] = $ctl->dataTab($tab, $this->permintaan($isiTab));
            } catch (ValidationException) {
                $gagal[] = $label;
            }
            session(['pengujian' => $uji]);   // tab berikutnya bisa bergantung pada tab sebelumnya (mis. tab kategori → tab informasi)
        }

        // Nomor laporan selalu diambil dari Tab I yang sudah lolos validasi, bukan dari isi file
        $uji['nomor']      = $uji['informasi']['nomor_laporan'] ?? null;
        $uji['diperbarui'] = $this->waktu($in['pengujian']['diperbarui'] ?? null);
        session(['pengujian' => $uji]);

        $redirect = redirect()->route('pengujian.form')
            ->with('saved', 'Data ' . ($uji['nomor'] ?? 'laporan pengujian') . ' berhasil diimpor.');

        return $gagal
            ? $redirect->withErrors(['pengujian' => 'Sebagian data tidak dimuat karena tidak lolos validasi: ' . implode('; ', $gagal) . '. Silakan isi ulang bagian tersebut.'])
            : $redirect;
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
}
