@php
    $label = (array) ($penetapan['label'] ?? []);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Aset Register</title>
    <style>
@include('aset._pdf-css')

        h1.register {
            line-height: 1.4;
            margin-bottom: 14pt;
        }

        table.rekap {
            width: 100%;
            border-collapse: collapse;
        }

        .rekap th,
        .rekap td {
            border: 0.75pt solid #000;
            padding: 4pt 5pt;
            vertical-align: top;
            text-align: left;
        }

        .rekap thead th {
            background: #e5e5e5;
            text-align: center;
            vertical-align: middle;
        }

        .rekap thead {
            display: table-header-group;   /* judul kolom diulang di setiap halaman */
        }

        .rekap tr {
            page-break-inside: avoid;
        }

        .rekap td.tengah {
            text-align: center;
        }

        .blok-pengesahan {
            margin-top: 18pt;
        }

        table.ttd-tunggal {
            width: 100%;
            margin-top: 12pt;
            border-collapse: collapse;
        }

        .ttd-tunggal td {
            text-align: center;
            vertical-align: top;
            border: 0;
        }

        .ttd-tunggal .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-tunggal .ruang-ttd {
            height: 60pt;
        }

        .aset-baru {
            page-break-before: always;
        }

        .judul-lampiran {
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 0.75pt solid #000;
            padding-bottom: 3pt;
            margin-bottom: 10pt;
        }

        h2.rekap-judul {
            background: none;
            border: 0;
            padding: 0;
            margin: 12pt 0 4pt;
            font-size: 10pt;
        }

        table.rekap-angka {
            width: auto;
            border-collapse: collapse;
            margin-left: 14pt;
        }

        .rekap-angka td {
            border: 0;
            padding: 1.5pt 6pt 1.5pt 0;
        }

        .rekap-angka td:first-child {
            width: 150pt;   /* titik dua sejajar di semua rekap A–D */
        }

        .rekap-angka td.angka {
            text-align: right;
            min-width: 30pt;
        }

        .rekap-angka tr.total td {
            font-weight: bold;
            border-top: 0.5pt solid #000;
        }

        h1.tabel-aset {
            page-break-before: always;
        }

        .cakupan {
            text-align: center;
            margin: -8pt 0 12pt;
            font-size: 9pt;
        }
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    {{-- ================= REKAP ================= --}}
    <h1 class="register">ASET REGISTER<br>{{ $instansi }}</h1>
    @if ($cakupan)
        <div class="cakupan">Klasifikasi aset: {{ $cakupan }}</div>
    @endif

    @php
        $baris_rekap = function (array $daftar, string $satuan, ?int $total = null) {
            $h = '<table class="rekap-angka">';
            foreach ($daftar as $r) {
                $h .= '<tr><td>' . e($r['label']) . '</td><td>:</td><td class="angka">' . $r['jumlah'] . '</td><td>' . $satuan . '</td></tr>';
            }
            if ($total !== null) {
                $h .= '<tr class="total"><td>Jumlah</td><td>:</td><td class="angka">' . $total . '</td><td>' . $satuan . '</td></tr>';
            }
            return $h . '</table>';
        };
    @endphp

    <h2 class="rekap-judul">A. Jumlah Aset per Klasifikasi Aset</h2>
    {!! $baris_rekap($rekap['klasifikasi'], 'aset', $rekap['total']) !!}

    <h2 class="rekap-judul">B. Jumlah Aset per Kritikalitas Aset</h2>
    {!! $baris_rekap($rekap['kritikalitas'], 'aset', $rekap['total']) !!}

    <h2 class="rekap-judul">C. Jumlah Aset per Status Infrastruktur Informasi Vital (IIV)</h2>
    {!! $baris_rekap($rekap['iiv'], 'aset', $rekap['total']) !!}

    <h2 class="rekap-judul">D. Jumlah Sistem Elektronik per Kategori</h2>
    @if ($rekap['kse'] !== null)
        {!! $baris_rekap($rekap['kse'], 'sistem elektronik', $rekap['total_se']) !!}
    @else
        <div style="margin-left:14pt">- (klasifikasi Perangkat Lunak tidak termasuk dalam cetakan ini)</div>
    @endif

    <div class="blok-pengesahan">
        <div class="pengesahan">
            Tanggal Penetapan<br>
            {{ $penetapan['lokasi'] }}, {{ $penetapan['tanggal'] }}
        </div>

        <table class="ttd-tunggal">
            <tr>
                <td>{!! \App\Support\LabelTte::kolom(
                    'pimpinan',
                    $label,
                    210,
                    'Pimpinan Instansi Pemilik Aset',
                    $penetapan['jabatan'] ?? null,
                    $instansi,
                    $penetapan['nama'] ?? null,
                ) !!}</td>
            </tr>
        </table>
    </div>

    {{-- ================= TABEL ASET (halaman baru) ================= --}}
    <h1 class="tabel-aset">TABEL ASET</h1>

    <table class="rekap">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:15%">Klasifikasi Aset</th>
                <th style="width:12%">Kode Aset</th>
                <th>Nama Aset</th>
                <th style="width:11%">Kritikalitas Aset</th>
                <th style="width:10%">Status IIV</th>
                <th style="width:10%">Kategori SE</th>
                @if ($lampiran)
                    <th style="width:11%">Lampiran</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($baris as $i => $b)
                <tr>
                    <td class="tengah">{{ $i + 1 }}</td>
                    <td>{{ $b['klasifikasi'] }}</td>
                    <td class="tengah">{{ $b['kode'] }}</td>
                    <td>{{ $b['nama'] }}</td>
                    <td class="tengah">{{ $b['kritikalitas'] }}</td>
                    <td class="tengah">{{ $b['iiv'] }}</td>
                    <td class="tengah">{{ $b['kse'] }}</td>
                    @if ($lampiran)
                        <td class="tengah" style="white-space:nowrap">Lampiran {{ $i + 1 }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>


    {{-- ================= IDENTIFIKASI & PENILAIAN PER ASET (tanpa tanda tangan) ================= --}}
    @if ($lampiran)
        @foreach ($baris as $i => $b)
            <div class="aset-baru">
                <div class="judul-lampiran">Lampiran {{ $i + 1 }} — Aset Register {{ $instansi }}</div>
                @include('aset._pdf-isi', ['aset' => $b['aset'], 'kelengkapan' => $b['kelengkapan']])
            </div>
        @endforeach
    @endif

</body>

</html>
