@php
    $inf = $aset['informasi'];
    $label = (array) ($penetapan['label'] ?? []);
    $kolom = fn(string $k, string $peran, ?string $jabatan, ?string $nama) => \App\Support\LabelTte::kolom(
        $k,
        $label,
        210,
        $peran,
        $jabatan,
        $inf['pemilik_aset'] ?? '',
        $nama,
    );
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Aset {{ $aset['nomor'] }}</title>
    <style>
@include('aset._pdf-css')
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    @include('aset._pdf-isi')

    {{-- ================= TANDA TANGAN ================= --}}
    <div class="blok-pengesahan">
        <div class="pengesahan">
            Tanggal Penetapan<br>
            {{ $penetapan['lokasi'] }}, {{ $penetapan['tanggal'] }}
        </div>

        <table class="ttd">
            <tr>
                <td>{!! $kolom('pj', 'Penanggung Jawab Aset', $inf['pj_jabatan'] ?? null, $inf['pj_nama'] ?? null) !!}</td>
                <td>{!! $kolom(
                    'pimpinan',
                    'Pimpinan Instansi Pemilik Aset',
                    $inf['pimpinan_jabatan'] ?? null,
                    $inf['pimpinan_nama'] ?? null,
                ) !!}</td>
            </tr>
        </table>
    </div>

</body>

</html>
