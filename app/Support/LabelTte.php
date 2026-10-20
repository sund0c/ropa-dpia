<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

/** Gambar label TTE untuk kolom tanda tangan PDF. Hanya dipakai untuk satu cetakan, tidak disimpan. */
class LabelTte
{
    public const MAKS_KB      = 512;
    private const MAKS_PIKSEL = 4000 * 2000;

    /**
     * Periksa & olah label untuk setiap penandatangan yang dicentang TTE.
     * Mengembalikan [label, null] bila berhasil, atau [null, pesan galat].
     */
    public static function ambil(Request $request, array $tte): array
    {
        if ($tte === []) return [[], null];

        $rules = [];
        foreach ($tte as $k) {
            $rules["tte_label.$k"] = ['required', 'file', 'mimes:png,jpg,jpeg', 'max:' . self::MAKS_KB];
        }

        $v = Validator::make($request->allFiles(), $rules, [
            'required' => 'Unggah gambar label TTE untuk setiap penandatangan yang dicentang TTE.',
            'file'     => 'Gambar label TTE gagal diunggah. Silakan coba lagi.',
            'mimes'    => 'Label TTE harus berupa gambar PNG atau JPG.',
            'max'      => 'Ukuran gambar label TTE maksimal ' . self::MAKS_KB . ' KB.',
        ]);

        if ($v->fails()) return [null, $v->errors()->first()];

        $label = [];
        foreach ($tte as $k) {
            $label[$k] = self::olah($request->file("tte_label.$k"));
            if ($label[$k] === null) {
                return [null, 'Gambar label TTE tidak dapat dibaca. Gunakan file PNG atau JPG yang valid (maks. 4000×2000 piksel).'];
            }
        }

        return [$label, null];
    }

    /** Pastikan isinya benar-benar gambar PNG/JPG, lalu encode ulang ke PNG (membuang metadata & muatan tersembunyi). */
    private static function olah(?UploadedFile $file): ?array
    {
        $path = $file?->getRealPath();
        $info = $path ? @getimagesize($path) : false;

        if (! $info || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) return null;
        if ($info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::MAKS_PIKSEL) return null;

        $img = @imagecreatefromstring((string) file_get_contents($path));
        if ($img === false) return null;

        imagesavealpha($img, true);
        ob_start();
        imagepng($img, null, 9);
        $png = (string) ob_get_clean();

        return ['src' => 'data:image/png;base64,' . base64_encode($png), 'w' => $info[0], 'h' => $info[1]];
    }

    /** Tag <img> dengan ukuran tetap (pt) agar pas di kotak tanda tangan; rasio gambar dijaga. */
    public static function img(array $label, float $maksLebar, float $maksTinggi): string
    {
        $skala = min($maksLebar / $label['w'], $maksTinggi / $label['h']);

        return '<img src="' . $label['src'] . '" alt="Label TTE" style="width:'
            . round($label['w'] * $skala, 1) . 'pt;height:' . round($label['h'] * $skala, 1) . 'pt">';
    }

    /**
     * Isi satu kolom tanda tangan.
     *  - TTE (ada label): judul peran + gambar label saja.
     *  - Tanda tangan basah: judul peran, jabatan, instansi, ruang kosong, nama.
     */
    public static function kolom(
        string $kunci, array $label, float $lebarLabel,
        string $peran, ?string $jabatan, ?string $instansi, ?string $nama
    ): string {
        $html = '<div>' . e($peran) . '</div>';

        if (isset($label[$kunci])) {
            return $html . '<div style="margin-top:6pt">' . self::img($label[$kunci], $lebarLabel, 70) . '</div>';
        }

        return $html
            . '<div>' . e((string) $jabatan) . '</div>'
            . '<div>' . e((string) $instansi) . '</div>'
            . '<div class="ruang-ttd"></div>'
            . (filled($nama)
                ? '<div class="nama">' . e($nama) . '</div>'
                : '<div>(....................................)</div>');
    }
}
