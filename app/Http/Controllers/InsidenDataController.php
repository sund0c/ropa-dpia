<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use JsonException;

/** Ekspor / impor JSON untuk Modul D — Laporan Penanganan Insiden. Tidak ada yang disimpan di server. */
class InsidenDataController extends Controller
{
    private const FORMAT = 'penangananinsiden';
    private const VERSI  = 1;

    public function ekspor()
    {
        $insiden = session('insiden', []);

        if (empty($insiden['nomor'])) {
            return redirect()->route('insiden.form')
                ->withErrors(['insiden' => 'Belum ada data untuk diekspor. Simpan Tab I terlebih dahulu.']);
        }

        $isi = json_encode([
            'format'   => self::FORMAT,
            'versi'    => self::VERSI,
            'diekspor' => now()->toIso8601String(),
            'insiden'   => $insiden,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(fn() => print($isi), 'Insiden-' . InsidenController::namaBerkas($insiden['nomor']) . '.json', [
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

        $gagalFile = fn(string $pesan) => redirect()->route('insiden.form')->withErrors(['insiden' => $pesan]);

        try {
            $in = json_decode(file_get_contents($request->file('berkas')->getRealPath()), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $gagalFile('File tidak dapat dibaca sebagai JSON yang valid.');
        }

        if (! is_array($in) || ($in['format'] ?? null) !== self::FORMAT) {
            return $gagalFile('File ini bukan hasil ekspor modul Laporan Penanganan Insiden.');
        }
        if ((int) ($in['versi'] ?? 0) !== self::VERSI) {
            return $gagalFile('Versi format file tidak didukung.');
        }

        // Hanya data modul ini yang dikosongkan
        session()->forget('insiden');
        session()->regenerate();

        $gagal  = [];
        $ctl    = app(InsidenController::class);
        $insiden = [];

        foreach (InsidenController::TABS as $tab => $label) {
            $isiTab = $in['insiden'][$tab] ?? null;
            if (! is_array($isiTab)) continue;
            if (! array_key_exists($tab, InsidenController::tabsAktif($insiden))) continue;

            try {
                $insiden[$tab] = $ctl->dataTab($tab, $this->permintaan($isiTab));
            } catch (ValidationException) {
                $gagal[] = $label;
            }
            session(['insiden' => $insiden]);   // tab berikutnya bisa bergantung pada tab sebelumnya (mis. tab analisis → tab informasi)
        }

        // Nomor laporan selalu diambil dari Tab I yang sudah lolos validasi, bukan dari isi file
        $insiden['nomor']      = $insiden['informasi']['nomor_laporan'] ?? null;
        $insiden['diperbarui'] = $this->waktu($in['insiden']['diperbarui'] ?? null);
        session(['insiden' => $insiden]);

        $redirect = redirect()->route('insiden.form')
            ->with('saved', 'Data ' . ($insiden['nomor'] ?? 'laporan insiden') . ' berhasil diimpor.');

        return $gagal
            ? $redirect->withErrors(['insiden' => 'Sebagian data tidak dimuat karena tidak lolos validasi: ' . implode('; ', $gagal) . '. Silakan isi ulang bagian tersebut.'])
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
