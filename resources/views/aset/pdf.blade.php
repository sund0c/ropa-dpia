@php
    use App\Http\Controllers\AsetController as A;
    use App\Support\Kse;
    use Illuminate\Support\Str;

    $inf = $aset['informasi'];
    $kr = $aset['kritikalitas'];
    $iv = $aset['iiv'];
    $ks = $aset['kse'] ?? null;

    $ml = fn($t) => nl2br(e((string) $t));
    $tglIso = fn($iso) => \Carbon\Carbon::parse($iso)
        ->timezone(config('app.timezone'))
        ->locale('id')
        ->translatedFormat('d F Y, H:i') . ' WITA';

    $tte = (array) ($penetapan['tte'] ?? []);
    $ruang = fn(string $k) => in_array($k, $tte, true) ? '<span class="tte">Ditandatangani menggunakan TTE</span>' : '';

    $jumlahIiv = array_count_values(array_map(fn($k) => $iv[$k], array_keys(A::IIV)));
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Aset {{ $aset['nomor'] }}</title>
    <style>
        @page {
            margin: 1.8cm 1.6cm 2.2cm 1.6cm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.35;
        }

        header {
            position: fixed;
            top: -1.15cm;
            left: 0;
            right: 0;
            text-align: right;
        }

        header span {
            font-weight: bold;
            font-size: 9pt;
            letter-spacing: 1.5pt;
            border: 0.75pt solid #000;
            padding: 2pt 8pt;
        }

        h1 {
            font-size: 13pt;
            text-align: center;
            margin: 0 0 12pt;
            letter-spacing: .3pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td,
        .kv td,
        .grid th,
        .grid td {
            border: 0.75pt solid #000;
            padding: 4pt 6pt;
            vertical-align: top;
            text-align: left;
        }

        .meta td.k,
        .kv td.k {
            width: 32%;
            font-weight: bold;
        }

        h2 {
            font-size: 10.5pt;
            background: #e5e5e5;
            border: 0.75pt solid #000;
            border-bottom: 0;
            padding: 4pt 6pt;
            margin: 14pt 0 0;
        }

        h2.halaman-baru {
            page-break-before: always;
            margin-top: 0;
        }

        h3 {
            font-size: 9.5pt;
            margin: 8pt 0 3pt;
        }

        .grid th {
            background: #f2f2f2;
        }

        .grid td.no,
        .grid td.tengah {
            text-align: center;
        }

        .kesimpulan {
            margin-top: 6pt;
            border: 0.75pt solid #000;
            padding: 5pt 6pt;
            font-weight: bold;
        }

        .catatan {
            margin-top: 4pt;
            border: 0.75pt solid #000;
            padding: 5pt 6pt;
        }

        .tebal {
            font-weight: bold;
        }

        /* Tanda tangan */
        .blok-pengesahan {
            margin-top: 26pt;
            page-break-inside: avoid;
        }

        .pengesahan {
            text-align: center;
        }

        .ttd {
            margin-top: 16pt;
        }

        .ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10pt;
            border: 0;
        }

        .ttd td.ruang {
            height: 65pt;
            vertical-align: middle;
        }

        .ttd .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd .tte {
            font-size: 8pt;
            font-style: italic;
            color: #333;
        }
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    <h1>IDENTIFIKASI DAN PENILAIAN ASET TIK</h1>

    <table class="meta">
        <tr>
            <td class="k">Nomor Aset</td>
            <td>{{ $aset['nomor'] }}</td>
        </tr>
        <tr>
            <td class="k">Tanggal Terakhir Diperbarui</td>
            <td>{{ $tglIso($aset['diperbarui']) }}</td>
        </tr>
    </table>

    {{-- ================= I ================= --}}
    <h2>I. INFORMASI ASET</h2>
    <table class="kv">
        <tr>
            <td class="k">Nama Aset</td>
            <td>{{ $inf['nama_aset'] }}</td>
        </tr>
        <tr>
            <td class="k">Klasifikasi Aset</td>
            <td>{{ A::KLASIFIKASI[$inf['klasifikasi']] ?? $inf['klasifikasi'] }}</td>
        </tr>
        <tr>
            <td class="k">Nama Instansi Pemilik Aset</td>
            <td>{{ $inf['pemilik_aset'] }}</td>
        </tr>
        <tr>
            <td class="k">Pimpinan Instansi Pemilik Aset</td>
            <td>{{ $inf['pimpinan_nama'] ?? '-' }} — {{ $inf['pimpinan_jabatan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Penanggung Jawab Aset</td>
            <td>{{ $inf['pj_nama'] ?? '-' }} — {{ $inf['pj_jabatan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Keterangan</td>
            <td>{!! $ml($inf['keterangan']) !!}</td>
        </tr>
    </table>

    <h3>Kelengkapan Data ({{ A::KLASIFIKASI[$inf['klasifikasi']] ?? '' }})</h3>
    <table class="kv">
        @foreach ($kelengkapan as $label => $nilai)
            <tr>
                <td class="k">{{ $label }}</td>
                <td>{!! $ml($nilai) !!}</td>
            </tr>
        @endforeach
    </table>

    {{-- ================= II ================= --}}
    <h2 class="halaman-baru">II. KRITIKALITAS ASET (CIA)</h2>
    <table class="grid">
        <tr>
            <th style="width:24%">Aspek</th>
            <th style="width:12%">Nilai</th>
            <th>Uraian</th>
        </tr>
        @foreach (A::CIA as $k => $x)
            <tr>
                <td>{{ Str::after($x['judul'], '. ') }} ({{ $x['sub'] }})</td>
                <td class="tengah tebal">{{ A::LEVEL[$kr[$k]] }}</td>
                <td>{{ $x['opsi'][$kr[$k]] }}</td>
            </tr>
        @endforeach
    </table>
    <div class="kesimpulan">Kritikalitas Aset: {{ strtoupper(A::LEVEL[$kr['kritikalitas']]) }} <span
            style="font-weight:normal">(nilai tertinggi dari C, I, A)</span></div>
    @if (in_array($kr['kritikalitas'], ['sedang', 'tinggi'], true))
        <div class="catatan">
            <strong>Catatan:</strong> Aset dengan nilai kritikalitas Tinggi dan Sedang <strong>WAJIB</strong> dibuatkan
            dokumen manajemen risiko yang berkelanjutan.
        </div>
    @endif

    {{-- ================= III ================= --}}
    <h2 class="halaman-baru">III. INFRASTRUKTUR INFORMASI VITAL (IIV)</h2>
    <table class="grid">
        <tr>
            <th style="width:24%">Dimensi</th>
            <th style="width:12%">Nilai</th>
            <th>Uraian</th>
        </tr>
        @foreach (A::IIV as $k => $x)
            <tr>
                <td>{{ Str::after($x['judul'], '. ') }}</td>
                <td class="tengah tebal">{{ A::LEVEL_IIV[$iv[$k]] }}</td>
                <td>{{ $x['opsi'][$iv[$k]] }}</td>
            </tr>
        @endforeach
    </table>
    <div class="kesimpulan">
        Status: {{ A::STATUS_IIV[$iv['status_iiv']] }}
        <span style="font-weight:normal">
            (Kritis: {{ $jumlahIiv['kritis'] ?? 0 }}, Terbatas: {{ $jumlahIiv['terbatas'] ?? 0 }}, Minor:
            {{ $jumlahIiv['minor'] ?? 0 }})
        </span>
    </div>
    <div class="catatan" style="font-size:8.5pt">
        Ketentuan: aset termasuk IIV apabila minimal 1 dimensi bernilai Kritis atau minimal 3 dimensi bernilai Terbatas.
    </div>
    @if ($iv['status_iiv'] === 'vital')
        <div class="catatan">
            <strong>Catatan:</strong> Aset ini harus <strong>segera dilaporkan ke BSSN</strong> untuk dilakukan
            peninjauan dan
            penetapan secara resmi oleh BSSN. BSSN akan ikut melakukan upaya penanganan terhadap seluruh aset yang
            termasuk
            dalam Infrastruktur Informasi Vital.
        </div>
    @endif

    {{-- ================= IV (hanya Perangkat Lunak) ================= --}}
    @if ($ks)
        <h2 class="halaman-baru">IV. KATEGORISASI SISTEM ELEKTRONIK</h2>
        <table class="grid">
            <tr>
                <th style="width:5%">No</th>
                <th style="width:33%">Pertanyaan</th>
                <th style="width:30%">Jawaban</th>
                <th style="width:8%">Nilai</th>
                <th>Keterangan</th>
            </tr>
            @foreach (Kse::PERTANYAAN as $n => $p)
                @php $j = $ks["kse_$n"]; @endphp
                <tr>
                    <td class="no">{{ $n }}</td>
                    <td>{{ $p['tanya'] }}</td>
                    <td>{{ $p['opsi'][$j] }}</td>
                    <td class="tengah tebal">{{ Kse::NILAI[$j] }}</td>
                    <td>{!! filled($ks["ket_$n"] ?? null) ? $ml($ks["ket_$n"]) : '-' !!}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="tebal" style="text-align:right">Total Skor</td>
                <td class="tengah tebal">{{ $ks['skor'] }}</td>
                <td></td>
            </tr>
        </table>
        <div class="kesimpulan">
            Kategori Sistem Elektronik: {{ strtoupper(Kse::KATEGORI[$ks['kategori']]) }}
            <span style="font-weight:normal">(10–15 Rendah, 16–34 Tinggi, 35–50 Strategis)</span>
        </div>
        <div class="catatan"><strong>Catatan:</strong> {{ Kse::CATATAN[$ks['kategori']] }}</div>
    @endif

    {{-- ================= TANDA TANGAN ================= --}}
    <div class="blok-pengesahan">
        <div class="pengesahan">
            Tanggal Penetapan<br>
            {{ $penetapan['lokasi'] }}, {{ $penetapan['tanggal'] }}
        </div>

        <table class="ttd">
            <tr>
                <td>Penanggung Jawab Aset</td>
                <td>Pimpinan Instansi Pemilik Aset</td>
            </tr>
            <tr>
                <td>{{ $inf['pj_jabatan'] ?? '' }}</td>
                <td>{{ $inf['pimpinan_jabatan'] ?? '' }}</td>
            </tr>
            <tr>
                <td>{{ $inf['pemilik_aset'] }}</td>
                <td>{{ $inf['pemilik_aset'] }}</td>
            </tr>
            <tr>
                <td class="ruang">{!! $ruang('pj') !!}</td>
                <td class="ruang">{!! $ruang('pimpinan') !!}</td>
            </tr>
            <tr>
                <td class="nama">{{ $inf['pj_nama'] ?? '' }}</td>
                <td class="nama">{{ $inf['pimpinan_nama'] ?? '' }}</td>
            </tr>
        </table>
    </div>

</body>

</html>
