<?php

namespace App\Http\Controllers;

use App\Support\RopaStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use JsonException;

class DataController extends Controller
{
    private const FORMAT = 'ropa-dpia';
    private const VERSI  = 1;

    /** Unduh seluruh isian sesi (RoPA + DPIA) sebagai JSON. Tidak ada yang disimpan di server. */
    public function ekspor()
    {
        $ropa = session('ropa', []);

        if (empty($ropa['nomor'])) {
            return redirect()->route('ropa.form')
                ->withErrors(['ropa' => 'Belum ada data untuk diekspor. Simpan minimal satu tab RoPA.']);
        }

        $isi = json_encode([
            'format'   => self::FORMAT,
            'versi'    => self::VERSI,
            'diekspor' => now()->toIso8601String(),
            'ropa'     => $ropa,
            'dpia'     => session('dpia', []),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(fn() => print($isi), $ropa['nomor'] . '.json', [
            'Content-Type'  => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /** Muat kembali file JSON hasil ekspor. Setiap tab divalidasi ulang dengan aturan form. */
    public function impor(Request $request)
    {
        // --- 1. Periksa file (sesi yang sedang berjalan belum disentuh) ---
        $request->validate(
            ['berkas' => ['required', 'file', 'max:1024', 'extensions:json']],
            [
                'berkas.required'   => 'Pilih file JSON yang akan diimpor.',
                'berkas.max'        => 'Ukuran file maksimal 1 MB.',
                'berkas.extensions' => 'File harus berekstensi .json.',
            ]
        );

        $gagalFile = fn(string $pesan) => redirect()->route('ropa.form')->withErrors(['ropa' => $pesan]);

        try {
            $in = json_decode(file_get_contents($request->file('berkas')->getRealPath()), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $gagalFile('File tidak dapat dibaca sebagai JSON yang valid.');
        }

        if (! is_array($in) || ($in['format'] ?? null) !== self::FORMAT) {
            return $gagalFile('File ini bukan hasil ekspor aplikasi RoPA & DPIA.');
        }
        if ((int) ($in['versi'] ?? 0) !== self::VERSI) {
            return $gagalFile('Versi format file tidak didukung.');
        }

        $nomor = $in['ropa']['nomor'] ?? null;
        if (! is_string($nomor) || ! preg_match('/^ROPA-\d{8}-[A-Z0-9]{6}$/', $nomor)) {
            return $gagalFile('Nomor RoPA di dalam file tidak valid.');
        }

        // --- 2. Kosongkan sesi (persetujuan penggunaan dibawa), lalu muat tab satu per satu ---
        session()->invalidate();
        session()->regenerateToken();
        session(['persetujuan' => now()->toIso8601String()]);

        $gagal = [];

        // RoPA: berurutan, karena tab tertentu bergantung pada tab sebelumnya
        $ropaCtl = app(RopaController::class);
        $ropa    = ['nomor' => $nomor];
        foreach (RopaController::TABS as $tab => $label) {
            $isiTab = $in['ropa'][$tab] ?? null;
            if (! is_array($isiTab)) continue;

            try {
                $ropa[$tab] = $ropaCtl->dataTab($tab, $this->permintaan($isiTab));
            } catch (ValidationException) {
                $gagal[] = "RoPA $label";
            }
            session(['ropa' => $ropa]);
        }
        $ropa['diperbarui'] = $this->waktu($in['ropa']['diperbarui'] ?? null);
        session(['ropa' => $ropa]);

        // DPIA: hanya bila RoPA hasil impor lengkap dan memang wajib DPIA
        $adaDpia = ! empty(array_diff_key((array) ($in['dpia'] ?? []), ['diperbarui' => 1]));
        if ($adaDpia && RopaStatus::lengkap($ropa) && RopaStatus::wajibDpia($ropa)) {
            $dpiaCtl = app(DpiaController::class);
            $dpia    = [];
            foreach (DpiaController::TABS as $tab => $label) {
                if (in_array($tab, DpiaController::TABS_OTOMATIS, true)) continue;
                $isiTab = $in['dpia'][$tab] ?? null;
                if (! is_array($isiTab)) continue;

                try {
                    $dpia[$tab] = $dpiaCtl->dataTab($tab, $this->permintaan($isiTab));
                } catch (ValidationException) {
                    $gagal[] = "DPIA $label";
                }
                session(['dpia' => $dpia]);   // Tab VII bergantung pada Tab VI yang sudah dimuat
            }
            if ($dpia) {
                $dpia['diperbarui'] = $this->waktu($in['dpia']['diperbarui'] ?? null);
                session(['dpia' => $dpia]);
            }
        } elseif ($adaDpia) {
            $gagal[] = 'DPIA (RoPA hasil impor belum lengkap atau tidak mewajibkan DPIA)';
        }

        $redirect = redirect()->route('ropa.form')
            ->with('saved', "Data $nomor berhasil diimpor.");

        return $gagal
            ? $redirect->withErrors(['ropa' => 'Sebagian data tidak dimuat karena tidak lolos validasi: ' . implode('; ', $gagal) . '. Silakan isi ulang bagian tersebut.'])
            : $redirect;
    }

    /** Buat request sintetis dari isi satu tab, dengan normalisasi seperti middleware web (trim, "" → null). */
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

    /** Waktu "terakhir diperbarui" dari file bila valid; selain itu waktu sekarang. */
    private function waktu($nilai): string
    {
        try {
            return is_string($nilai) ? Carbon::parse($nilai)->toIso8601String() : now()->toIso8601String();
        } catch (\Throwable) {
            return now()->toIso8601String();
        }
    }
}
