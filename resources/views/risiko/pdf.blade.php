@php
    use App\Support\MetodologiRisiko as MR;
    use App\Support\PetaRisiko as PR;

    $inf = $risiko['informasi'];

    $tglIso = fn($iso) => \Carbon\Carbon::parse($iso)
        ->timezone(config('app.timezone'))
        ->locale('id')
        ->translatedFormat('d F Y, H:i') . ' WITA';

    $kat = MR::KATEGORI;
    $teks = fn(int $lv) => in_array($lv, MR::TEKS_PUTIH, true) ? '#fff' : '#000';
    // Helvetica bawaan DomPDF tidak memiliki ≤ ≥; karakter itu dicetak dengan DejaVu Sans
    $u = fn($t) => str_replace(['≤', '≥'], ['<span class="dv">≤</span>', '<span class="dv">≥</span>'], e((string) $t));
    $ml = fn($t) => nl2br($u($t));

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
    <title>Manajemen Risiko {{ $risiko['nomor'] }}</title>
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

        h2.lepas {
            border-bottom: 0.75pt solid #000;
            margin-bottom: 8pt;
        }

        h3 {
            font-size: 9.5pt;
            margin: 10pt 0 4pt;
        }

        .grid th {
            background: #f2f2f2;
        }

        .tengah {
            text-align: center !important;
        }

        .tebal {
            font-weight: bold;
        }

        .dv {
            font-family: "DejaVu Sans", sans-serif;
        }

        /* Matriks */
        .matriks {
            width: auto;
            margin: 0;
        }

        .matriks th,
        .matriks td {
            border: 0.75pt solid #000;
            padding: 3pt 5pt;
            text-align: center;
            font-size: 8pt;
        }

        .matriks th {
            font-weight: normal;
        }

        .matriks td {
            width: 48pt;
            height: 22pt;
            font-weight: bold;
            font-size: 9pt;
        }

        /* Dampak */
        .dampak th,
        .dampak td {
            font-size: 7.5pt;
            padding: 3pt 4pt;
            line-height: 1.3;
        }

        .dampak th.lvh {
            text-align: center;
        }

        .dampak td.dim {
            font-weight: bold;
            background: #f7f7f7;
        }

        .dampak tr.grup td {
            background: #e5e5e5;
            font-weight: bold;
        }

        /* Penanganan */
        .penanganan {
            margin-top: 8pt;
            page-break-inside: avoid;
        }

        .penanganan th,
        .penanganan td {
            font-size: 8pt;
            padding: 3pt 4pt;
        }

        .penanganan td.k {
            width: 18%;
            font-weight: bold;
            background: #f7f7f7;
        }

        .penanganan .lvl {
            display: inline-block;
            padding: 1pt 4pt;
            font-weight: bold;
        }

        /* Nilai risiko */
        .nilai th,
        .nilai td {
            font-size: 7.5pt;
            padding: 3pt 3pt;
        }

        /* Identifikasi */
        .ident th,
        .ident td {
            font-size: 8pt;
            padding: 3pt 4pt;
        }

        .ident .lvl {
            display: inline-block;
            padding: 1pt 4pt;
            font-weight: bold;
            font-size: 7.5pt;
        }

        .ident .kriteria {
            margin-top: 2pt;
            font-size: 7pt;
            color: #333;
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

        .ttd tr.peran td {
            vertical-align: bottom;
            padding-bottom: 6pt;
        }

        .ttd .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd .ruang-ttd {
            height: 60pt;
        }
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    <h1>DOKUMEN MANAJEMEN RISIKO ASET TIK</h1>

    {{-- ================= I ================= --}}
    <h2>I. INFORMASI ASET</h2>
    <table class="kv">
        <tr>
            <td class="k">Nama Aset</td>
            <td>{{ $inf['nama_aset'] }}</td>
        </tr>
        <tr>
            <td class="k">Klasifikasi Aset</td>
            <td>{{ $klasifikasi[$inf['klasifikasi']] ?? $inf['klasifikasi'] }}</td>
        </tr>
        <tr>
            <td class="k">Kode Aset</td>
            <td>{{ $inf['kode_aset'] }}</td>
        </tr>
        <tr>
            <td class="k">Nama Instansi Pemilik Aset</td>
            <td>{{ $inf['pemilik_aset'] }}</td>
        </tr>
        <tr>
            <td class="k">Pimpinan Instansi Pemilik Aset</td>
            <td>{{ $inf['pimpinan_nama'] }} — {{ $inf['pimpinan_jabatan'] }}</td>
        </tr>
        <tr>
            <td class="k">Penanggung Jawab Aset</td>
            <td>{{ $inf['pj_nama'] }} — {{ $inf['pj_jabatan'] }}</td>
        </tr>
    </table>

    {{-- ================= II ================= --}}
    <h2 class="halaman-baru lepas">II. PETA RISIKO</h2>

    <h3>1. Matriks Risiko</h3>
    <table class="matriks">
        <tr>
            <th colspan="2" rowspan="2">Kemungkinan \ Dampak</th>
            @foreach (range(1, 5) as $dp)
                <th>{{ $dp }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach (range(1, 5) as $dp)
                <th>{{ $peta['dampak'][$dp]['nama'] }}</th>
            @endforeach
        </tr>
        @foreach (range(1, 5) as $k)
            <tr>
                <th>{{ $k }}</th>
                <th style="text-align:left">{{ $peta['kemungkinan'][$k]['nama'] }}</th>
                @foreach (range(1, 5) as $dp)
                    @php
                        $skor = $k * $dp;
                        $lv = PR::level($skor, $peta);
                    @endphp
                    <td style="background:{{ $kat[$lv]['warna'] }}; color:{{ $teks($lv) }}">{{ $skor }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <h3>2. Kategori Tingkat Risiko</h3>
    <table class="grid" style="width:60%">
        <tr>
            <th colspan="2">Level Risiko</th>
            <th>Rentang Besaran Risiko</th>
            <th style="width:18%">Warna</th>
        </tr>
        @foreach ($kat as $i => $k)
            @php
                $bawah = $i === 1 ? 1 : (int) $peta['kategori_batas'][$i - 1] + 1;
                $atas = $i === 5 ? 25 : (int) $peta['kategori_batas'][$i];
            @endphp
            <tr>
                <td class="tengah" style="width:8%">{{ $i }}</td>
                <td>{{ $k['nama'] }}</td>
                <td class="tengah">{{ $bawah }}–{{ $atas }}</td>
                <td style="background:{{ $k['warna'] }}"></td>
            </tr>
        @endforeach
    </table>

    <h3>3. Skala Kemungkinan Terjadi (Likelihood)</h3>
    <table class="grid">
        <tr>
            <th style="width:8%">Skor</th>
            <th style="width:30%">Level Kemungkinan</th>
            <th>Periode Kejadian</th>
        </tr>
        @foreach (range(1, 5) as $k)
            <tr>
                <td class="tengah tebal" style="background:{{ $kat[$k]['warna'] }}; color:{{ $teks($k) }}">
                    {{ $k }}</td>
                <td class="tebal" style="background:{{ $kat[$k]['warna'] }}; color:{{ $teks($k) }}">
                    {{ $peta['kemungkinan'][$k]['nama'] }}</td>
                <td>{!! $u($peta['kemungkinan'][$k]['periode']) !!}</td>
            </tr>
        @endforeach
    </table>

    <h3 style="page-break-before: always; margin-top: 0">4. Skala Dampak Kejadian (Impact)</h3>
    <table class="grid dampak">
        <tr>
            <th style="width:14%">Dimensi Dampak</th>
            @foreach (range(1, 5) as $dp)
                <th class="lvh" style="background:{{ $kat[$dp]['warna'] }}; color:{{ $teks($dp) }}">
                    {{ $dp }}<br>{{ $peta['dampak'][$dp]['nama'] }}
                </th>
            @endforeach
        </tr>
        @foreach (['finansial' => 'Dampak Finansial', 'non' => 'Dampak Non-Finansial'] as $grup => $judulGrup)
            <tr class="grup">
                <td colspan="6">{{ $judulGrup }}</td>
            </tr>
            @foreach (PR::DIMENSI as $kunci => $dim)
                @continue($dim['kelompok'] !== $grup)
                <tr>
                    <td class="dim">{{ $dim['label'] }}</td>
                    @foreach (range(1, 5) as $dp)
                        <td>{!! $ml($peta['dampak'][$dp][$kunci] ?? '') !!}</td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </table>

    {{-- ================= III ================= --}}
    <h2 class="halaman-baru">III. IDENTIFIKASI RISIKO</h2>
    <table class="grid ident">
        <tr>
            <th style="width:6%">Kode</th>
            <th style="width:22%">Ancaman</th>
            <th style="width:24%">Kerentanan</th>
            <th style="width:18%">Kategori Risiko</th>
            <th style="width:13%">Area Dampak</th>
            <th>Dampak</th>
        </tr>
        @foreach ((array) ($risiko['identifikasi']['risiko'] ?? []) as $n => $r)
            @php $lv = (int) $r['dampak']; @endphp
            <tr>
                <td class="tengah tebal">R{{ str_pad($n + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td>{!! $ml($r['ancaman']) !!}</td>
                <td>{!! $ml($r['kerentanan']) !!}</td>
                <td>{{ $kategori[$r['kategori']] ?? $r['kategori'] }}</td>
                <td>{{ PR::DIMENSI[$r['area']]['label'] ?? $r['area'] }}</td>
                <td>
                    <span class="lvl"
                        style="background:{{ $kat[$lv]['warna'] }}; color:{{ $teks($lv) }}">{{ $lv }} –
                        {{ $peta['dampak'][$lv]['nama'] }}</span>
                    <div class="kriteria">{!! $ml($peta['dampak'][$lv][$r['area']] ?? '') !!}</div>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- ================= IV ================= --}}
    @php
        $daftarRisiko = \App\Http\Controllers\RisikoController::daftarRisiko($risiko);
        $setNilai = collect((array) ($risiko['nilai']['set'] ?? []))
            ->filter(fn($s) => isset($daftarRisiko[$s['risiko_id']]))
            ->sortBy(fn($s) => (int) $s['prioritas'])
            ->values();
    @endphp
    <h2 class="halaman-baru">IV. NILAI RISIKO SAAT INI</h2>
    <table class="grid nilai">
        <tr>
            <th style="width:6%">Prio&shy;ritas</th>
            <th style="width:6%">Kode</th>
            <th style="width:27%">Risiko (Ancaman / Kerentanan)</th>
            <th style="width:23%">Kontrol Saat Ini</th>
            <th style="width:5%">D</th>
            <th style="width:5%">K</th>
            <th style="width:6%">IR</th>
            <th style="width:11%">Level Risiko</th>
            <th>Ditangani</th>
        </tr>
        @foreach ($setNilai as $s)
            @php
                $r = $daftarRisiko[$s['risiko_id']];
                $ir = (int) $s['dampak'] * (int) $s['kemungkinan'];
                $lv = PR::level($ir, $peta);
            @endphp
            <tr>
                <td class="tengah tebal">{{ $s['prioritas'] }}</td>
                <td class="tengah">{{ $r['kode'] }}</td>
                <td><b>{{ $r['ancaman'] }}</b><br>{{ $r['kerentanan'] }}</td>
                <td>{!! $ml($s['kontrol']) !!}</td>
                <td class="tengah">{{ $s['dampak'] }}</td>
                <td class="tengah">{{ $s['kemungkinan'] }}</td>
                <td class="tengah tebal" style="background:{{ $kat[$lv]['warna'] }}; color:{{ $teks($lv) }}">
                    {{ $ir }}</td>
                <td class="tengah" style="background:{{ $kat[$lv]['warna'] }}; color:{{ $teks($lv) }}">
                    {{ $kat[$lv]['nama'] }}</td>
                <td class="tengah">
                    {{ \App\Http\Controllers\RisikoController::KEPUTUSAN[$s['keputusan']] ?? $s['keputusan'] }}</td>
            </tr>
        @endforeach
    </table>
    <p style="font-size:7.5pt; margin-top:3pt">D = Nilai Dampak, K = Nilai Kemungkinan, IR = D × K. Diurutkan
        berdasarkan prioritas.</p>

    {{-- ================= V ================= --}}
    @php
        $RCls = \App\Http\Controllers\RisikoController::class;
        $ditangani = $RCls::daftarDitangani($risiko);
        $rencana = collect((array) ($risiko['penanganan']['set'] ?? []))->keyBy('risiko_id');
        $tglId = fn($t) => \Carbon\Carbon::createFromFormat('!Y-m-d', $t)->locale('id')->translatedFormat('d F Y');
    @endphp
    <h2 class="halaman-baru lepas">V. RENCANA PENANGANAN RISIKO</h2>
    @if ($ditangani === [])
        <table class="kv">
            <tr>
                <td>Tidak ada risiko yang diputuskan untuk ditangani.</td>
            </tr>
        </table>
    @endif
    @foreach ($ditangani as $rid => $r)
        @php
            $p = $rencana[$rid] ?? null;
            $ir = (int) $r['nilai']['dampak'] * (int) $r['nilai']['kemungkinan'];
            $lvI = PR::level($ir, $peta);
        @endphp
        @continue(!$p)
        <table class="grid penanganan">
            <tr>
                <th colspan="4" style="background:#e5e5e5">
                    {{ $r['kode'] }} — {{ $r['ancaman'] }}
                    <span style="font-weight:normal">(Prioritas {{ $r['nilai']['prioritas'] }}; IR {{ $ir }}
                        {{ $kat[$lvI]['nama'] }})</span>
                </th>
            </tr>
            <tr>
                <td class="k">Opsi Penanganan</td>
                <td>{{ $RCls::OPSI[$p['opsi']] ?? $p['opsi'] }}</td>
                <td class="k">Target Implementasi</td>
                <td>{{ $tglId($p['target']) }}</td>
            </tr>
            <tr>
                <td class="k">Rencana Aksi</td>
                <td colspan="3">{!! $ml($p['rencana_aksi']) !!}</td>
            </tr>
            <tr>
                <td class="k">Keluaran</td>
                <td colspan="3">{!! $ml($p['keluaran']) !!}</td>
            </tr>
            <tr>
                <td class="k">Risiko Residual</td>
                <td colspan="3">
                    @if (($p['residual'] ?? null) === 'ya')
                        @php
                            $rr = (int) $p['res_dampak'] * (int) $p['res_kemungkinan'];
                            $lvR = PR::level($rr, $peta);
                        @endphp
                        Dampak {{ $p['res_dampak'] }} × Kemungkinan {{ $p['res_kemungkinan'] }} =
                        <span class="lvl"
                            style="background:{{ $kat[$lvR]['warna'] }}; color:{{ $teks($lvR) }}">RR
                            {{ $rr }} – {{ $kat[$lvR]['nama'] }}</span>;
                        Status: <b>{{ $RCls::STATUS_RESIDUAL[$p['res_status']] ?? $p['res_status'] }}</b>
                    @else
                        Tidak ada
                    @endif
                </td>
            </tr>
            @if (($p['residual'] ?? null) === 'ya' && ($p['res_status'] ?? null) === 'mitigasi')
                <tr>
                    <td class="k">Rencana Kontrol Tambahan</td>
                    <td colspan="3">{!! $ml($p['kontrol_tambahan'] ?? '') !!}</td>
                </tr>
            @endif
        </table>
    @endforeach

    {{-- Tab berikutnya ditambahkan di sini, masing-masing diawali <h2 class="halaman-baru"> --}}

    {{-- ================= TANDA TANGAN ================= --}}
    <div class="blok-pengesahan">
        <div class="pengesahan">
            Tanggal Penetapan<br>
            {{ $penetapan['lokasi'] }}, {{ $penetapan['tanggal'] }}
        </div>

        <table class="ttd">
            <tr class="peran">
                <td>Penanggung Jawab Aset</td>
                <td>Pimpinan Instansi Pemilik Aset</td>
            </tr>
            <tr>
                <td>{!! $kolom('pj', '', $inf['pj_jabatan'] ?? null, $inf['pj_nama'] ?? null) !!}</td>
                <td>{!! $kolom('pimpinan', '', $inf['pimpinan_jabatan'] ?? null, $inf['pimpinan_nama'] ?? null) !!}</td>
            </tr>
        </table>
    </div>

</body>

</html>
