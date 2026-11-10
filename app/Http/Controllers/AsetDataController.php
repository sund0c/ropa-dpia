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

    /** Contoh data per klasifikasi: resources/data/aset/contoh-{klasifikasi}.json */
    private const FOLDER_CONTOH = 'data/aset';

    /** Daftar contoh untuk popup: klasifikasi => nama aset contoh (hanya file yang ada). */
    public static function daftarContoh(): array
    {
        $daftar = [];
        foreach (AsetController::KLASIFIKASI as $k => $label) {
            $path = resource_path(self::FOLDER_CONTOH . "/contoh-$k.json");
            if (! is_file($path)) continue;
            $isi = json_decode((string) file_get_contents($path), true);
            $daftar[$k] = (string) ($isi['aset']['informasi']['nama_aset'] ?? '');
        }
        return $daftar;
    }

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
        // Pesan dikirim sebagai error 'aset' agar tampil di atas form (dialog impor tertutup setelah redirect)
        $gagalFile = fn(string $pesan) => redirect()->route('aset.form')->withErrors(['aset' => $pesan]);

        $berkas = $request->file('berkas');
        if (! $berkas) {
            return $gagalFile('Pilih file JSON yang akan diimpor.');
        }
        if (! $berkas->isValid()) {
            return $gagalFile('File gagal diunggah ke server: ' . $berkas->getErrorMessage());
        }
        if ($berkas->getSize() > 1024 * 1024) {
            return $gagalFile('Ukuran file maksimal 1 MB.');
        }
        if (strtolower($berkas->getClientOriginalExtension()) !== 'json') {
            return $gagalFile('File harus berekstensi .json.');
        }

        return $this->muat(file_get_contents($berkas->getRealPath()), 'Data %s berhasil diimpor.');
    }

    /** Muat contoh data bawaan untuk klasifikasi yang dipilih (menggantikan data aset di sesi ini). */
    public function imporContoh(Request $request)
    {
        $gagal = fn(string $pesan) => redirect()->route('aset.form')->withErrors(['aset' => $pesan]);

        $k = (string) $request->input('klasifikasi');
        if (! array_key_exists($k, AsetController::KLASIFIKASI)) {
            return $gagal('Pilih klasifikasi aset untuk contoh data yang akan dimuat.');
        }

        $label = AsetController::KLASIFIKASI[$k];
        $path  = resource_path(self::FOLDER_CONTOH . "/contoh-$k.json");
        if (! is_file($path)) {
            return $gagal("Contoh data untuk klasifikasi $label belum tersedia.");
        }

        return $this->muat(
            file_get_contents($path),
            "Contoh data $label (%s) berhasil dimuat. Silakan ubah sesuai aset Anda.",
            $k,
        );
    }

    /**
     * Validasi & muat isi JSON ke sesi. $pesanOk memakai %s untuk nomor aset.
     * $klasifikasi (contoh data): isi file harus sesuai klasifikasi yang dipilih.
     */
    private function muat(string $json, string $pesanOk, ?string $klasifikasi = null)
    {
        $gagalFile = fn(string $pesan) => redirect()->route('aset.form')->withErrors(['aset' => $pesan]);

        try {
            $in = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $gagalFile('File tidak dapat dibaca sebagai JSON yang valid.');
        }

        if (! is_array($in) || ($in['format'] ?? null) !== self::FORMAT) {
            return $gagalFile('File ini bukan hasil ekspor modul Identifikasi dan Penilaian Aset TIK.');
        }
        if ((int) ($in['versi'] ?? 0) !== self::VERSI) {
            return $gagalFile('Versi format file tidak didukung.');
        }
        if ($klasifikasi !== null && ($in['aset']['informasi']['klasifikasi'] ?? null) !== $klasifikasi) {
            return $gagalFile('Contoh data tidak sesuai dengan klasifikasi ' . AsetController::KLASIFIKASI[$klasifikasi] . '. Hubungi pengelola aplikasi.');
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

        $redirect = redirect()->route('aset.form')->with('saved', sprintf($pesanOk, $aset['nomor'] ?? 'aset'));

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
