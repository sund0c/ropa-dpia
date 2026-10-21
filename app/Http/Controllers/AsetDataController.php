<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use JsonException;

class AsetDataController extends Controller
{
    private const FORMAT = 'manajemenaset';
    private const VERSI  = 1;

    /** Unduh isian aset di sesi ini sebagai JSON. Tidak ada yang disimpan di server. */
    public function ekspor()
    {
        $aset = session('aset', []);

        if (empty($aset['nomor'])) {
            return redirect()->route('aset.form')
                ->withErrors(['aset' => 'Belum ada data untuk diekspor. Simpan minimal satu tab.']);
        }

        $isi = json_encode([
            'format'   => self::FORMAT,
            'versi'    => self::VERSI,
            'diekspor' => now()->toIso8601String(),
            'aset'     => $aset,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(fn() => print($isi), $aset['nomor'] . '.json', [
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

        $gagalFile = fn(string $pesan) => redirect()->route('aset.form')->withErrors(['aset' => $pesan]);

        try {
            $in = json_decode(file_get_contents($request->file('berkas')->getRealPath()), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $gagalFile('File tidak dapat dibaca sebagai JSON yang valid.');
        }

        if (! is_array($in) || ($in['format'] ?? null) !== self::FORMAT) {
            return $gagalFile('File ini bukan hasil ekspor modul Identifikasi dan Penilaian Aset TIK.');
        }
        if ((int) ($in['versi'] ?? 0) !== self::VERSI) {
            return $gagalFile('Versi format file tidak didukung.');
        }

        // Hanya data aset yang dikosongkan; data RoPA & DPIA di sesi yang sama tetap
        session()->forget('aset');
        session()->regenerate();

        $gagal = [];
        $ctl   = app(AsetController::class);
        $aset  = [];

        foreach (AsetController::TABS as $tab => $label) {
            $isiTab = $in['aset'][$tab] ?? null;
            if (! is_array($isiTab)) continue;
            if (! array_key_exists($tab, AsetController::tabsAktif($aset))) continue;   // Tab IV hanya untuk Perangkat Lunak

            try {
                $aset[$tab] = $ctl->dataTab($tab, $this->permintaan($isiTab));
            } catch (ValidationException) {
                $gagal[] = $label;
            }
        }
        $aset['nomor'] = AsetController::kodeAset($aset['informasi'] ?? []);
        $aset['diperbarui'] = $this->waktu($in['aset']['diperbarui'] ?? null);
        session(['aset' => $aset]);

        $redirect = redirect()->route('aset.form')->with('saved', 'Data ' . ($aset['nomor'] ?? 'aset') . ' berhasil diimpor.');

        return $gagal
            ? $redirect->withErrors(['aset' => 'Sebagian data tidak dimuat karena tidak lolos validasi: ' . implode('; ', $gagal) . '. Silakan isi ulang bagian tersebut.'])
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
