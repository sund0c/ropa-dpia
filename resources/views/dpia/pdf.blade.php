@php
    $MR = \App\Support\MetodologiRisiko::class;
    $C = \Illuminate\Support\Carbon::class;

    $dok = $dpia['dokumen'];
    $o = $ropa['organisasi'];
    $a = $ropa['aktivitas'];
    $p = $ropa['pemetaan'];
    $rs = $dpia['risiko'] ?? [];
    $ds = $dpia['deskripsi'];
    $kb = $dpia['kebutuhan'];
    $pn = $dpia['penilaian'];
    $pg = $dpia['pengendalian'];
    $rk = $dpia['rekomendasi'];
    $ks = $dpia['kesimpulan'];

    $m = $MR::nilai($pn['metodologi'] ?? []);
    $inheren = $pn['inheren'] ?? $MR::DEFAULT_INHEREN;
    $kat = $MR::KATEGORI;

    $warnaSel = fn(int $lv) => 'background:' .
        $kat[$lv]['warna'] .
        ';color:' .
        (in_array($lv, $MR::TEKS_PUTIH, true) ? '#fff' : '#000');
    $tgl = fn($ymd) => $ymd ? $C::createFromFormat('!Y-m-d', $ymd)->locale('id')->translatedFormat('d F Y') : '-';
    $bulanTh = fn($b, $t) => $C::create((int) $t, (int) $b, 1)->locale('id')->translatedFormat('F Y');
    $ml = fn($t) => nl2br(e((string) $t));
    $kotak = fn(bool $on) => '<span class="kotak">
' .
        ($on ? '&#9746;' : '&#9744;') .
        '</span>';
    $jenisLbl = $labels['jenis_umum'] + $labels['jenis_spesifik'];
    $bawah = fn(int $i) => $i === 1 ? 1 : (int) $m['kategori_batas'][$i - 1] + 1;
    $atas = fn(int $i) => $i === 5 ? 25 : (int) $m['kategori_batas'][$i];
    $bagian = fn(string $t) => strtr($t, ['Bagian VIII' => 'Bagian G', 'Bagian VII' => 'Bagian F']);

    // Bagian F: data per risiko diratakan menjadi baris tabel
    $kontrolRows = $residualRows = $penangananRows = [];
    foreach ($risikoList as $rid => $r) {
        foreach ((array) ($pg['kontrol'][$rid] ?? []) as $k) {
            $kontrolRows[] = ['kode' => $r['kode']] + $k;
        }
        foreach ((array) ($pg['residual'][$rid] ?? []) as $res) {
            $skor = (int) $res['kemungkinan'] * (int) $res['dampak'];
            $residualRows[] =
                [
                    'kode' => $r['kode'],
                    'inh' => $r['skor'],
                    'inh_lv' => $r['level'],
                    'skor' => $skor,
                    'lv' => $MR::level($skor, $m),
                ] + $res;
            foreach ((array) ($res['penanganan'] ?? []) as $pp) {
                $penangananRows[] = ['kode' => $r['kode']] + $pp;
            }
        }
    }

    $tahun = substr($dok['tanggal_penyusunan'], 0, 4);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $kode }}</title>
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

        .sampul {
            text-align: center;
            margin: 30pt 0 22pt;
        }

        .sampul .judul {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: .3pt;
        }

        .sampul .instansi {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 6pt;
        }

        .sampul .tahun {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 4pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td,
        .grid th,
        .grid td {
            border: 0.75pt solid #000;
            padding: 4pt 5pt;
            vertical-align: top;
            text-align: left;
        }

        .kv td.k {
            width: 34%;
            font-weight: bold;
        }

        .grid th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .grid.kecil th,
        .grid.kecil td {
            font-size: 7.5pt;
            padding: 3pt 4pt;
        }

        .c {
            text-align: center !important;
        }

        .muted {
            color: #555;
            font-style: italic;
        }

        h2 {
            font-size: 10.5pt;
            background: #e5e5e5;
            border: 0.75pt solid #000;
            padding: 4pt 6pt;
            margin: 0 0 8pt;
        }

        h2.halaman-baru {
            page-break-before: always;
        }

        h3.halaman-baru {
            page-break-before: always;
            margin-top: 0;
        }

        h3 {
            font-size: 9.5pt;
            margin: 10pt 0 4pt;
        }

        ol,
        ul {
            margin: 0;
            padding-left: 14pt;
        }

        li {
            margin-bottom: 2pt;
        }

        .kotak {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
        }

        .pilihan div {
            margin-bottom: 4pt;
        }

        .matriks th,
        .matriks td {
            border: 0.75pt solid #000;
            padding: 4pt;
            text-align: center;
            font-size: 8pt;
        }

        .matriks td {
            font-weight: bold;
            width: 44pt;
            height: 20pt;
        }

        .catatan {
            border: 0.75pt solid #000;
            padding: 6pt;
            min-height: 30pt;
        }

        .blok-ttd {
            margin-top: 26pt;
            page-break-inside: avoid;
        }

        .blok-ttd .tempat {
            text-align: right;
            margin-bottom: 10pt;
        }

        .ttd td {
            border: 0;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 0 6pt;
        }

        .ttd .ruang {
            height: 60pt;
        }

        .ttd .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd .kosong {
            font-weight: normal;
            text-decoration: none;
        }

        .ttd .ruang {
            vertical-align: middle;
        }

        .ttd .tte {
            font-size: 8pt;
            font-style: italic;
            color: #333;
        }

        @include('ropa._css')
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    {{-- ================= SAMPUL & INFORMASI DOKUMEN ================= --}}
    <div class="sampul">
        <div class="judul">DATA PROTECTION IMPACT ASSESSMENT (DPIA)</div>
        <div class="instansi">{{ mb_strtoupper($o['nama_instansi']) }}</div>
        <div class="tahun">TAHUN {{ $tahun }}</div>
    </div>

    <table class="kv">
        <tr>
            <td class="k">Kode Dokumen</td>
            <td>{{ $kode }}</td>
        </tr>
        <tr>
            <td class="k">Nama Aktivitas Proses</td>
            <td>{{ $a['nama_aktivitas'] }}</td>
        </tr>
        <tr>
            <td class="k">Unit Kerja</td>
            <td>{{ $dok['unit_kerja'] }}</td>
        </tr>
        <tr>
            <td class="k">Penanggung Jawab Layanan/Aktivitas</td>
            <td>{{ $a['penanggung_jawab'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Kode Referensi RoPA</td>
            <td>{{ $ropa['nomor'] }}</td>
        </tr>
        <tr>
            <td class="k">Tanggal Penyusunan</td>
            <td>{{ $tgl($dok['tanggal_penyusunan']) }}</td>
        </tr>
        <tr>
            <td class="k">Tanggal Review Berikutnya</td>
            <td>{{ $tgl($dok['tanggal_review']) }}</td>
        </tr>
        <tr>
            <td class="k">Status Dokumen</td>
            <td>
                @foreach ($labels['status'] as $key => $label)
                    {!! $kotak($dok['status_dokumen'] === $key) !!} {{ $label }}&nbsp;&nbsp;&nbsp;
                @endforeach
            </td>
        </tr>
    </table>

    <h3>Riwayat Perubahan Dokumen</h3>
    <table class="grid">
        <tr>
            <th style="width:12%">Versi</th>
            <th style="width:22%">Tanggal</th>
            <th>Deskripsi Perubahan</th>
            <th style="width:26%">Disusun/Direvisi oleh</th>
        </tr>
        @foreach ($dok['riwayat'] as $rw)
            <tr>
                <td class="c">{{ $rw['versi'] }}</td>
                <td>{{ $tgl($rw['tanggal']) }}</td>
                <td>{{ filled($rw['deskripsi'] ?? null) ? $rw['deskripsi'] : '-' }}</td>
                <td>{{ filled($rw['oleh'] ?? null) ? $rw['oleh'] : '-' }}</td>
            </tr>
        @endforeach
    </table>

    {{-- ================= A ================= --}}
    <h2 class="halaman-baru">A. INFORMASI ORGANISASI DAN PENANGGUNG JAWAB</h2>
    <table class="kv">
        <tr>
            <td class="k">Nama Instansi / OPD</td>
            <td>{{ $o['nama_instansi'] }}</td>
        </tr>
        <tr>
            <td class="k">Alamat Kantor Resmi</td>
            <td>{!! $ml($o['alamat']) !!}</td>
        </tr>
        <tr>
            <td class="k">Nomor Telepon / Email Resmi</td>
            <td>{{ implode(' / ', array_filter([$o['telepon'] ?? null, $o['email'] ?? null])) }}</td>
        </tr>
        <tr>
            <td class="k">Nama Pengendali Data Pribadi</td>
            <td>{{ $o['nama_pengendali'] }}</td>
        </tr>
        <tr>
            <td class="k">Nama PPDP</td>
            <td>{{ $o['nama_ppdp'] }}</td>
        </tr>
        <tr>
            <td class="k">Kontak PPDP (Email/Telepon)</td>
            <td>{{ implode(' / ', array_filter([$o['ppdp_email'] ?? null, $o['ppdp_hp'] ?? null])) }}</td>
        </tr>
    </table>

    {{-- ================= B ================= --}}
    <h2 class="halaman-baru">B. ANALISIS POTENSI RISIKO TINGGI</h2>
    <table class="grid">
        <tr>
            <th>Potensi Risiko Tinggi</th>
            <th class="c" style="width:12%">Ya/Tidak</th>
            <th style="width:38%">Keterangan</th>
        </tr>
        @foreach ($labels['risiko'] as $key => $label)
            @php $ya = in_array($key, $indikator, true); @endphp
            <tr>
                <td>{{ $label }}</td>
                <td class="c">{{ $ya ? 'Ya' : 'Tidak' }}</td>
                <td>{!! $ya ? $ml($rs['keterangan'][$key] ?? '-') : '-' !!}</td>
            </tr>
        @endforeach
    </table>

    {{-- ================= C ================= --}}
    <h2 class="halaman-baru">C. DESKRIPSI PEMROSESAN DATA PRIBADI</h2>

    <h3>1. Deskripsi Singkat Aktivitas Pemrosesan Data Pribadi</h3>
    <div>{{ $a['nama_aktivitas'] }}, meliputi:</div>
    <ol>
        @foreach ($a['tahapan'] as $t)
            <li>{{ $t }}</li>
        @endforeach
    </ol>
    <h3>2. Tujuan Pemrosesan Data Pribadi</h3>
    <div>{!! $ml($a['tujuan']) !!}</div>

    <h3>3. Latar Belakang Pemrosesan Data Pribadi</h3>
    <div>{!! $ml($ds['latar_belakang']) !!}</div>

    <h3>4. Data Pribadi siapa yang diproses dalam aktivitas ini dan jenis Data Pribadi yang diproses</h3>
    <table class="kv">
        <tr>
            <td class="k">Kategori Subjek Data Pribadi</td>
            <td>{{ implode(', ', $p['kategori_subjek']) }}</td>
        </tr>
        <tr>
            <td class="k">Estimasi jumlah Subjek Data Pribadi</td>
            <td>{{ number_format($p['estimasi_subjek'], 0, ',', '.') }} orang</td>
        </tr>
        <tr>
            <td class="k">Pemrosesan melibatkan Data Pribadi Anak</td>
            <td>{!! $kotak((bool) $p['data_anak']) !!} Ya &nbsp;&nbsp; {!! $kotak(!$p['data_anak']) !!} Tidak</td>
        </tr>
        <tr>
            <td class="k">Pemrosesan melibatkan kelompok rentan (disabilitas, lansia, minoritas)</td>
            <td>{!! $kotak((bool) $p['kelompok_rentan']) !!} Ya &nbsp;&nbsp; {!! $kotak(!$p['kelompok_rentan']) !!} Tidak</td>
        </tr>
    </table>

    <div style="margin-top:6pt"><strong>a. Data Pribadi yang bersifat spesifik:</strong></div>
    @if (empty($p['jenis_spesifik']))
        <div class="muted">Tidak ada.</div>
    @else
        <ul>
            @foreach ($p['jenis_spesifik'] as $k)
                <li>{{ $labels['jenis_spesifik'][$k] ?? $k }}</li>
            @endforeach
        </ul>
    @endif

    <div style="margin-top:6pt"><strong>b. Data Pribadi yang bersifat umum:</strong></div>
    @if (empty($p['jenis_umum']))
        <div class="muted">Tidak ada.</div>
    @else
        <ul>
            @foreach ($p['jenis_umum'] as $k)
                <li>{{ $labels['jenis_umum'][$k] ?? $k }}</li>
            @endforeach
        </ul>
    @endif

    <h3>5. Pihak yang Terlibat dalam Pemrosesan</h3>
    <table class="grid kecil">
        <tr>
            <th>Pihak</th>
            <th>Peran (Pengendali/Pengendali Bersama/Prosesor)</th>
            <th>Dalam Negeri/Luar Negeri</th>
            <th>Data yang Diakses</th>
            <th>Dasar Keterlibatan</th>
            <th>Keterangan</th>
        </tr>
        @foreach ($ds['pihak'] as $ph)
            <tr>
                <td>{{ $ph['nama'] }}</td>
                <td>{{ $labels['peran'][$ph['peran']] ?? $ph['peran'] }}</td>
                <td>{{ $labels['lokasi'][$ph['lokasi']] ?? $ph['lokasi'] }}</td>
                <td>{{ implode(', ', array_map(fn($k) => $jenisLbl[$k] ?? $k, (array) $ph['data'])) }}</td>
                <td>{!! $ml($ph['dasar']) !!}</td>
                <td>{!! filled($ph['keterangan'] ?? null) ? $ml($ph['keterangan']) : '-' !!}</td>
            </tr>
        @endforeach
    </table>

    {{-- ================= D ================= --}}
    <h2 class="halaman-baru">D. PENILAIAN KEBUTUHAN DAN PROPORSIONALITAS</h2>
    @foreach ($labels['penilaian'] as $grup)
        <h3>{{ $grup['judul'] }}</h3>
        <table class="grid">
            <tr>
                <th>Pertanyaan</th>
                <th class="c" style="width:12%">Ya/Tidak</th>
                <th style="width:40%">Keterangan</th>
            </tr>
            @foreach ($grup['items'] as $key => $pertanyaan)
                <tr>
                    <td>{{ $pertanyaan }}</td>
                    <td class="c">{{ $labels['jawaban'][$kb['jawaban'][$key]] ?? '-' }}</td>
                    <td>{!! $ml($kb['alasan'][$key] ?? '-') !!}</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    {{-- ================= E ================= --}}
    <h2 class="halaman-baru">E. PENILAIAN RISIKO</h2>

    <h3>1. Metodologi Penilaian Risiko dengan Matriks Risiko</h3>
    <table class="matriks">
        <tr>
            <th colspan="3" rowspan="3">Matriks Risiko 5X5</th>
            <th colspan="5">Level Dampak</th>
        </tr>
        <tr>
            @foreach (range(1, 5) as $x)
                <th>{{ $x }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach (range(1, 5) as $x)
                <th>{{ $m['dampak'][$x]['nama'] }}</th>
            @endforeach
        </tr>
        @foreach (range(1, 5) as $k)
            <tr>
                @if ($k === 1)
                    <th rowspan="5" style="width:40pt">Level Kemungkinan</th>
                @endif
                <th style="width:14pt">{{ $k }}</th>
                <th>{{ $m['kemungkinan'][$k]['nama'] }}</th>
                @foreach (range(1, 5) as $x)
                    @php $s = $k * $x; @endphp
                    <td style="{{ $warnaSel($MR::level($s, $m)) }}">{{ $s }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <h3>2. Kategori Tingkat Risiko</h3>
    <table class="grid" style="width:75%">
        <tr>
            <th colspan="2">Level Risiko</th>
            <th class="c">Rentang Besaran Risiko</th>
            <th style="width:22%">Keterangan Warna</th>
        </tr>
        @foreach ($kat as $i => $kt)
            <tr>
                <td class="c" style="width:8%">{{ $i }}</td>
                <td>{{ $kt['nama'] }}</td>
                <td class="c">{{ $bawah($i) }}–{{ $atas($i) }}</td>
                <td style="background:{{ $kt['warna'] }}"></td>
            </tr>
        @endforeach
    </table>

    <h3>3. Skala Kemungkinan Terjadi (<em>Likelihood</em>)</h3>
    <table class="grid">
        <tr>
            <th class="c" style="width:10%">Skor</th>
            <th style="width:30%">Level Kemungkinan</th>
            <th>Periode Kejadian</th>
        </tr>
        @foreach (range(1, 5) as $k)
            <tr>
                <td class="c" style="{{ $warnaSel($k) }}">{{ $k }}</td>
                <td style="{{ $warnaSel($k) }}">{{ $m['kemungkinan'][$k]['nama'] }}</td>
                <td>{{ $m['kemungkinan'][$k]['periode'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3 class="halaman-baru">4. Skala Dampak Kejadian (<em>Impact</em>)</h3>
    <table class="grid kecil">
        <tr>
            <th rowspan="2" style="width:13%">Level Dampak</th>
            <th rowspan="2" class="c" style="width:6%">Skor</th>
            <th>Dampak Finansial</th>
            <th colspan="3" class="c">Dampak Non-Finansial</th>
        </tr>
        <tr>
            <th style="width:12%">Rupiah</th>
            <th>Reputasi</th>
            <th>Kepatuhan Regulasi</th>
            <th>Hukum</th>
        </tr>
        @foreach (range(1, 5) as $x)
            <tr>
                <td style="{{ $warnaSel($x) }}">{{ $m['dampak'][$x]['nama'] }}</td>
                <td class="c">{{ $x }}</td>
                <td>{{ $m['dampak'][$x]['finansial'] }}</td>
                <td>{{ $m['dampak'][$x]['reputasi'] }}</td>
                <td>{{ $m['dampak'][$x]['kepatuhan'] }}</td>
                <td>{{ $m['dampak'][$x]['hukum'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3 class="halaman-baru">5. Identifikasi dan Analisis Risiko Inheren</h3>
    <table class="grid kecil">
        <tr>
            <th class="c" style="width:5%">No.</th>
            <th style="width:20%">Siklus Pemrosesan Data</th>
            <th>Risiko</th>
            <th class="c" style="width:11%">Kemungkinan</th>
            <th class="c" style="width:9%">Dampak</th>
            <th class="c" style="width:11%">Skor Risiko Inheren</th>
        </tr>
        @foreach ($MR::SIKLUS as $s => $labelSiklus)
            @php
                $rows = array_values((array) ($inheren[$s] ?? []));
                $no = $loop->iteration;
            @endphp
            @if (empty($rows))
                <tr>
                    <td class="c">{{ $no }}</td>
                    <td>{{ $labelSiklus }}</td>
                    <td colspan="4" class="muted">Tidak ada risiko teridentifikasi.</td>
                </tr>
            @else
                @foreach ($rows as $j => $r)
                    @php $skor = (int) $r['kemungkinan'] * (int) $r['dampak']; @endphp
                    <tr>
                        @if ($j === 0)
                            <td class="c" rowspan="{{ count($rows) }}">{{ $no }}</td>
                            <td rowspan="{{ count($rows) }}">{{ $labelSiklus }}</td>
                        @endif
                        <td><strong>R{{ $no }}.{{ $j + 1 }}</strong> {{ $r['risiko'] }}</td>
                        <td class="c">{{ $r['kemungkinan'] }}</td>
                        <td class="c">{{ $r['dampak'] }}</td>
                        <td class="c" style="{{ $warnaSel($MR::level($skor, $m)) }}">
                            <strong>{{ $skor }}</strong>
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    </table>

    {{-- ================= F ================= --}}
    <h2 class="halaman-baru">F. LANGKAH PENGENDALIAN DAN PENANGANAN RISIKO</h2>

    <h3>1. Langkah Pengendalian</h3>
    @if (empty($kontrolRows))
        <div class="muted">Belum ada langkah pengendalian yang tercatat.</div>
    @else
        <table class="grid kecil">
            <tr>
                <th class="c" style="width:5%">No.</th>
                <th class="c" style="width:8%">No. Risiko</th>
                <th>Langkah Mitigasi</th>
                <th style="width:10%">Status</th>
                <th style="width:22%">Bukti Dukung</th>
                <th style="width:16%">Keterangan (Organisasi/ Operasional/ Teknis)</th>
            </tr>
            @foreach ($kontrolRows as $n => $k)
                <tr>
                    <td class="c">{{ $n + 1 }}</td>
                    <td class="c">{{ $k['kode'] }}</td>
                    <td>{!! $ml($k['langkah']) !!}</td>
                    <td>{{ $labels['status_kontrol'][$k['status']] ?? $k['status'] }}</td>
                    <td>{{ filled($k['bukti'] ?? null) ? $k['bukti'] : '-' }}</td>
                    <td>{{ $labels['jenis_kontrol'][$k['jenis']] ?? $k['jenis'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <h3>2. Identifikasi Risiko Residual dan Keputusan Penanganan</h3>
    <table class="grid kecil">
        <tr>
            <th class="c" style="width:7%">No.</th>
            <th>Risiko</th>
            <th class="c" style="width:8%">Skor Risiko Inheren</th>
            <th>Langkah Mitigasi</th>
            <th class="c" style="width:9%">Kemungkinan (Residual)</th>
            <th class="c" style="width:8%">Dampak (Residual)</th>
            <th class="c" style="width:8%">Skor Risiko Residual</th>
            <th style="width:10%">Keputusan</th>
        </tr>
        @foreach ($residualRows as $res)
            <tr>
                <td class="c">{{ $res['kode'] }}</td>
                <td>{!! $ml($res['risiko']) !!}</td>
                <td class="c" style="{{ $warnaSel($res['inh_lv']) }}">{{ $res['inh'] }}</td>
                <td>{!! $ml($res['langkah']) !!}</td>
                <td class="c">{{ $res['kemungkinan'] }}</td>
                <td class="c">{{ $res['dampak'] }}</td>
                <td class="c" style="{{ $warnaSel($res['lv']) }}"><strong>{{ $res['skor'] }}</strong></td>
                <td>{{ $labels['keputusan'][$res['keputusan']] ?? $res['keputusan'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3>3. Langkah Penanganan Risiko</h3>
    @if (empty($penangananRows))
        <div class="muted">Tidak ada risiko dengan keputusan Mitigasi.</div>
    @else
        <table class="grid kecil">
            <tr>
                <th class="c" style="width:8%">No. Risiko</th>
                <th>Langkah Mitigasi yang Direncanakan</th>
                <th style="width:20%">Penanggung Jawab</th>
                <th style="width:15%">Target Waktu Penyelesaian</th>
                <th style="width:13%">Status</th>
            </tr>
            @foreach ($penangananRows as $pp)
                <tr>
                    <td class="c">{{ $pp['kode'] }}</td>
                    <td>{!! $ml($pp['langkah']) !!}</td>
                    <td>{{ $pp['pj'] }}</td>
                    <td>{{ $bulanTh($pp['bulan'], $pp['tahun']) }}</td>
                    <td>{{ $labels['status_penanganan'][$pp['status']] ?? $pp['status'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- ================= G ================= --}}
    <h2 class="halaman-baru">G. REKOMENDASI DENGAN PPDP DAN LEMBAGA</h2>
    <table class="kv">
        <tr>
            <td class="k">Tanggal rekomendasi PPDP</td>
            <td>{{ $tgl($rk['ppdp_tanggal']) }}</td>
        </tr>
        <tr>
            <td class="k">Saran/rekomendasi PPDP</td>
            <td>{!! $ml($rk['ppdp_saran']) !!}</td>
        </tr>
        <tr>
            <td class="k">Tindak lanjut atas saran PPDP</td>
            <td>{!! $ml($rk['ppdp_tindak_lanjut']) !!}</td>
        </tr>
        <tr>
            <td class="k">Jika salah satu kondisi berikut terpenuhi, dipertimbangkan untuk konsultasi dengan
                Lembaga sebelum pemrosesan data pribadi dilanjutkan</td>
            <td>
                <ul>
                    <li>Pemrosesan berpotensi menimbulkan kerugian materiil dan/atau imateriil signifikan pada Subjek
                        Data Pribadi yang belum sepenuhnya dapat dimitigasi</li>
                    <li>Tidak tersedia langkah mitigasi yang memadai terhadap risiko yang teridentifikasi</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td class="k">Konsultasi dengan Lembaga</td>
            <td>
                {!! $kotak($rk['lembaga_konsultasi'] === 'ya') !!} Dilakukan &nbsp;&nbsp;
                {!! $kotak($rk['lembaga_konsultasi'] === 'tidak') !!} Tidak dilakukan
            </td>
        </tr>
        @if ($rk['lembaga_konsultasi'] === 'ya')
            <tr>
                <td class="k">Tanggal rekomendasi Lembaga</td>
                <td>{{ $tgl($rk['lembaga_tanggal']) }}</td>
            </tr>
            <tr>
                <td class="k">Saran/rekomendasi Lembaga</td>
                <td>{!! $ml($rk['lembaga_saran']) !!}</td>
            </tr>
            <tr>
                <td class="k">Tindak lanjut atas saran Lembaga</td>
                <td>{!! $ml($rk['lembaga_tindak_lanjut']) !!}</td>
            </tr>
        @endif
    </table>

    {{-- ================= H ================= --}}
    <h2 class="halaman-baru">H. KESIMPULAN DAN KEPUTUSAN</h2>

    <h3>1. Ringkasan Penilaian</h3>
    <table class="grid">
        <tr>
            <th style="width:34%">Aspek</th>
            <th>Kesimpulan</th>
        </tr>
        <tr>
            <td>Kebutuhan dan Proporsionalitas</td>
            <td>{!! $ml($ks['ringkasan']['kebutuhan']) !!}</td>
        </tr>
        <tr>
            <td>Risiko Inheren (Sebelum Mitigasi) Tertinggi</td>
            <td>{!! $ml($ks['ringkasan']['inheren']) !!}</td>
        </tr>
        <tr>
            <td>Risiko Residual (Setelah Mitigasi) Tertinggi</td>
            <td>{!! $ml($ks['ringkasan']['residual']) !!}</td>
        </tr>
        <tr>
            <td>Efektivitas Langkah Mitigasi</td>
            <td>
                @foreach ($labels['efektivitas'] as $key => $label)
                    {!! $kotak($ks['efektivitas'] === $key) !!} {{ $label }}&nbsp;&nbsp;&nbsp;
                @endforeach
            </td>
        </tr>
    </table>

    <h3>2. Keputusan Akhir Pemrosesan</h3>
    <div class="pilihan">
        @foreach ($labels['keputusan_akhir'] as $key => $label)
            <div>{!! $kotak($ks['keputusan_akhir'] === $key) !!} {{ $bagian($label) }}</div>
        @endforeach
    </div>

    <div style="margin-top:8pt"><strong>Catatan tambahan:</strong></div>
    <div class="catatan">{!! filled($ks['catatan'] ?? null) ? $ml($ks['catatan']) : '&nbsp;' !!}</div>

    {{-- ================= TANDA TANGAN ================= --}}
    @php
        $pj = $a['penanggung_jawab'] ?? null;
        $tte = (array) ($pengesahan['tte'] ?? []);
        $ruang = fn(string $k) => in_array($k, $tte, true)
            ? '<span class="tte">Ditandatangani menggunakan TTE</span>'
            : '';
    @endphp
    <div class="blok-ttd">
        <div class="tempat">{{ $pengesahan['lokasi'] }}, {{ $pengesahan['tanggal'] }}</div>
        <table class="ttd">
            <tr>
                <td>Penanggung Jawab Layanan/Aktivitas</td>
                <td></td>
                <td>Pejabat Pelindung Data Pribadi</td>
            </tr>
            <tr>
                <td class="ruang">{!! $ruang('pj') !!}</td>
                <td></td>
                <td class="ruang">{!! $ruang('ppdp') !!}</td>
            </tr>
            <tr>
                <td class="nama {{ $pj ? '' : 'kosong' }}">{{ $pj ?: '(....................................)' }}</td>
                <td></td>
                <td class="nama">{{ $o['nama_ppdp'] }}</td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-top:18pt">Pengendali Data Pribadi</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td class="ruang">{!! $ruang('pengendali') !!}</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td class="nama">{{ $o['nama_pengendali'] }}</td>
                <td></td>
            </tr>
        </table>
    </div>

    {{-- ================= DOKUMEN RoPA (halaman baru) ================= --}}
    <div class="dok-ropa" style="page-break-before: always;">
        @include('ropa._isi', [
            'ropa' => $ropa,
            'labels' => $labelsRopa,
            'indikator' => $indikator,
            'wajibDpia' => true,
            'pengesahan' => $pengesahan,
        ])
    </div>

</body>

</html>
