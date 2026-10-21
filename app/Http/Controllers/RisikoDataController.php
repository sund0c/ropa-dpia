<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use JsonException;

/** Ekspor / impor JSON untuk Modul C — Manajemen Risiko. Tidak ada yang disimpan di server. */
class RisikoDataController extends Controller
{
    private const FORMAT = 'manajemenrisiko';
    private const VERSI  = 1;

    public function ekspor()
    {
        $risiko = session('risiko', []);

        if (empty($risiko['nomor'])) {
            return redirect()->route('risiko.form')
                ->withErrors(['risiko' => 'Belum ada data untuk diekspor. Simpan Tab I terlebih dahulu.']);
        }

        $isi = json_encode([
            'format'   => self::FORMAT,
            'versi'    => self::VERSI,
            'diekspor' => now()->toIso8601String(),
            'risiko'   => $risiko,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(fn() => print($isi), 'Risiko-' . $risiko['nomor'] . '.json', [
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

        $gagalFile = fn(string $pesan) => redirect()->route('risiko.form')->withErrors(['risiko' => $pesan]);

        try {
            $in = json_decode(file_get_contents($request->file('berkas')->getRealPath()), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $gagalFile('File tidak dapat dibaca sebagai JSON yang valid.');
        }

        if (! is_array($in) || ($in['format'] ?? null) !== self::FORMAT) {
            return $gagalFile('File ini bukan hasil ekspor modul Manajemen Risiko.');
        }
        if ((int) ($in['versi'] ?? 0) !== self::VERSI) {
            return $gagalFile('Versi format file tidak didukung.');
        }

        // Hanya data modul ini yang dikosongkan
        session()->forget('risiko');
        session()->regenerate();

        $gagal  = [];
        $ctl    = app(RisikoController::class);
        $risiko = [];

        foreach (RisikoController::TABS as $tab => $label) {
            $isiTab = $in['risiko'][$tab] ?? null;
            if (! is_array($isiTab)) continue;
            if (! array_key_exists($tab, RisikoController::tabsAktif($risiko))) continue;

            try {
                $risiko[$tab] = $ctl->dataTab($tab, $this->permintaan($isiTab));
            } catch (ValidationException) {
                $gagal[] = $label;
            }
            session(['risiko' => $risiko]);   // tab berikutnya bisa bergantung pada tab sebelumnya (mis. IV → III)
        }

        // Kode aset selalu diambil dari Tab I yang sudah lolos validasi, bukan dari isi file
        $risiko['nomor']      = $risiko['informasi']['kode_aset'] ?? null;
        $risiko['diperbarui'] = $this->waktu($in['risiko']['diperbarui'] ?? null);
        session(['risiko' => $risiko]);

        $redirect = redirect()->route('risiko.form')
            ->with('saved', 'Data ' . ($risiko['nomor'] ?? 'manajemen risiko') . ' berhasil diimpor.');

        return $gagal
            ? $redirect->withErrors(['risiko' => 'Sebagian data tidak dimuat karena tidak lolos validasi: ' . implode('; ', $gagal) . '. Silakan isi ulang bagian tersebut.'])
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
