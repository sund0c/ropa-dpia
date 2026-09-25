@php
    $o = $ropa['organisasi'];
    $a = $ropa['aktivitas'];
    $p = $ropa['pemetaan'];
    $h = $ropa['hak'];
    $s = $ropa['siklus'];

    $yaTidak   = fn ($v) => $v ? 'Ya' : 'Tidak';
    $multiline = fn ($t) => nl2br(e($t));   // aman: di-escape dulu, baru baris baru jadi <br>
    $tgl       = fn ($iso) => \Carbon\Carbon::parse($iso)->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') . ' WITA';
    $kontakInstansi = implode(' / ', array_filter([$o['telepon'] ?? null, $o['email'] ?? null]));
    $kontakPpdp     = implode(' / ', array_filter([$o['ppdp_email'] ?? null, $o['ppdp_hp'] ?? null]));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>RoPA {{ $ropa['nomor'] }}</title>
<style>
    @page { margin: 1.8cm 1.6cm 2.2cm 1.6cm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #000; line-height: 1.35; }
    h1 { font-size: 13pt; text-align: center; margin: 0 0 12pt; letter-spacing: .3pt; }
    table { width: 100%; border-collapse: collapse; }
    .meta td, .kv td, .grid th, .grid td { border: 0.75pt solid #000; padding: 4pt 6pt; vertical-align: top; text-align: left; }
    .meta td.k, .kv td.k { width: 32%; font-weight: bold; }
    h2 { font-size: 10.5pt; background: #e5e5e5; border: 0.75pt solid #000; border-bottom: 0; padding: 4pt 6pt; margin: 14pt 0 0; }
    h3 { font-size: 9.5pt; margin: 8pt 0 3pt; }
    .grid th { background: #f2f2f2; }
    .grid td.no { width: 5%; text-align: center; }
    ol, ul { margin: 0; padding-left: 14pt; }
    li { margin-bottom: 2pt; }
    .penerima { margin-top: 6pt; page-break-inside: avoid; }
    .kesimpulan { margin-top: 6pt; border: 0.75pt solid #000; padding: 5pt 6pt; font-weight: bold; }
    .ttd { margin-top: 18pt; page-break-inside: avoid; }
    .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 12pt; border: 0; }
    .ttd .ruang { height: 65pt; }
    .ttd .nama { font-weight: bold; text-decoration: underline; }

    h2.halaman-baru { page-break-before: always; margin-top: 0; }
    header { position: fixed; top: -1.15cm; left: 0; right: 0; text-align: right; }
    header span { font-weight: bold; font-size: 9pt; letter-spacing: 1.5pt; border: 0.75pt solid #000; padding: 2pt 8pt; }

    .blok-pengesahan { margin-top: 26pt; page-break-inside: avoid; }
    .pengesahan { text-align: center; }

</style>
</head>
<body>

<header><span>TERBATAS</span></header>
<h1>RECORD OF PROCESSING ACTIVITIES (RoPA)</h1>

<table class="meta">
    <tr><td class="k">Nomor RoPA</td><td>{{ $ropa['nomor'] }}</td></tr>
    <tr><td class="k">Tanggal Terakhir Diperbarui</td><td>{{ $tgl($ropa['diperbarui']) }}</td></tr>
</table>

{{-- ============ I ============ --}}
<h2>I. INFORMASI ORGANISASI &amp; PENANGGUNG JAWAB</h2>
<table class="kv">
    <tr><td class="k">Nama Instansi / Organisasi / Perusahaan</td><td>{{ $o['nama_instansi'] }}</td></tr>
    <tr><td class="k">Alamat Kantor Resmi</td><td>{!! $multiline($o['alamat']) !!}</td></tr>
    <tr><td class="k">Nomor Telepon / Email Resmi</td><td>{{ $kontakInstansi }}</td></tr>
    <tr><td class="k">Nama Pengendali Data Pribadi</td><td>{{ $o['nama_pengendali'] }}</td></tr>
    <tr><td class="k">Nama Pejabat Pelindung Data Pribadi (PPDP)</td><td>{{ $o['nama_ppdp'] }}</td></tr>
    <tr><td class="k">Kontak PPDP</td><td>{{ $kontakPpdp }}</td></tr>
</table>

{{-- ============ II ============ --}}
<h2 class="halaman-baru">II. AKTIVITAS &amp; LEGALITAS PEMROSESAN</h2>
<table class="kv">
    <tr><td class="k">Nama Aktivitas Pemrosesan</td><td>{{ $a['nama_aktivitas'] }}</td></tr>
    <tr><td class="k">Deskripsi Aktivitas Pemrosesan</td>
        <td><ol>@foreach ($a['tahapan'] as $t)<li>{{ $t }}</li>@endforeach</ol></td></tr>
    <tr><td class="k">Tujuan Pemrosesan</td><td>{!! $multiline($a['tujuan']) !!}</td></tr>
    <tr><td class="k">Dasar Pemrosesan</td>
        <td><ul>@foreach ($a['dasar'] as $k)<li>{{ $labels['dasar'][$k] ?? $k }}</li>@endforeach</ul></td></tr>
    <tr><td class="k">Referensi Dasar Hukum</td>
        <td><ol>@foreach ($a['referensi_hukum'] as $r)<li>{{ $r }}</li>@endforeach</ol></td></tr>
</table>

{{-- ============ III ============ --}}
<h2 class="halaman-baru">III. PEMETAAN ALIRAN DATA PRIBADI</h2>
<table class="kv">
    <tr><td class="k">Kategori Subjek Data</td>
        <td><ul>@foreach ($p['kategori_subjek'] as $k)<li>{{ $k }}</li>@endforeach</ul></td></tr>
    <tr><td class="k">Melibatkan kelompok rentan (disabilitas, lansia, minoritas)</td><td>{{ $yaTidak($p['kelompok_rentan']) }}</td></tr>
    <tr><td class="k">Melibatkan Data Pribadi Anak</td><td>{{ $yaTidak($p['data_anak']) }}</td></tr>
    <tr><td class="k">Estimasi Jumlah Subjek Data</td><td>{{ number_format($p['estimasi_subjek'], 0, ',', '.') }} orang</td></tr>
    <tr><td class="k">Jenis Data Pribadi Umum<br><span style="font-weight:normal">(Pasal 4 ayat (3) UU PDP)</span></td>
        <td>
            @if (empty($p['jenis_umum'])) - @else
                <ul>@foreach ($p['jenis_umum'] as $k)<li>{{ $labels['jenis_umum'][$k] ?? $k }}</li>@endforeach</ul>
            @endif
        </td></tr>
    <tr><td class="k">Jenis Data Pribadi Spesifik<br><span style="font-weight:normal">(Pasal 4 ayat (2) UU PDP)</span></td>
        <td>
            @if (empty($p['jenis_spesifik'])) Tidak ada @else
                <ul>@foreach ($p['jenis_spesifik'] as $k)<li>{{ $labels['jenis_spesifik'][$k] ?? $k }}</li>@endforeach</ul>
            @endif
        </td></tr>
</table>

<h3 class="halaman-baru">Sumber Pengumpulan &amp; Lokasi Penyimpanan Data</h3>
<table class="grid">
    <tr><th style="width:5%">No</th><th>Sumber Pengumpulan</th><th>Lokasi Penyimpanan</th></tr>
    @foreach ($p['pengumpulan'] as $i => $row)
        <tr><td class="no">{{ $i + 1 }}</td><td>{{ $row['sumber'] }}</td><td>{{ $row['lokasi'] }}</td></tr>
    @endforeach
</table>

<h3 class="halaman-baru">Transfer Data Pribadi</h3>
@forelse ($p['transfer'] as $i => $t)
    <table class="kv penerima">
        <tr><td class="k" colspan="2" style="background:#f2f2f2">Penerima #{{ $i + 1 }}</td></tr>
        <tr><td class="k">Organisasi Penerima</td><td>{{ $t['organisasi'] }}</td></tr>
        <tr><td class="k">Peran</td><td>{{ $labels['peran'][$t['peran']] ?? $t['peran'] }}</td></tr>
        <tr><td class="k">Kontak / PIC Penerima</td><td>{{ $t['kontak'] }}</td></tr>
        <tr><td class="k">Tujuan Pengiriman</td><td>{!! $multiline($t['tujuan']) !!}</td></tr>
        <tr><td class="k">Mekanisme Pengiriman</td><td>{!! $multiline($t['mekanisme']) !!}</td></tr>
        <tr><td class="k">Data Pribadi yang Dikirim</td>
            <td><ul>@foreach ($t['data'] as $k)<li>{{ $labels['jenis'][$k] ?? $k }}</li>@endforeach</ul></td></tr>
    </table>
@empty
    <table class="kv"><tr><td>Tidak ada transfer Data Pribadi ke pihak lain.</td></tr></table>
@endforelse

{{-- ============ IV ============ --}}
<h2 class="halaman-baru">IV. PEMENUHAN HAK SUBJEK DATA PRIBADI</h2>
<table class="kv">
    <tr><td class="k">Jenis Hak yang Difasilitasi</td>
        <td><ol>@foreach ($h['hak'] as $k)<li>{{ $labels['hak'][$k] ?? $k }}</li>@endforeach</ol></td></tr>
    <tr><td class="k">Mekanisme / Kanal Permohonan Hak Subjek Data</td><td>{!! $multiline($h['mekanisme_hak']) !!}</td></tr>
</table>

{{-- ============ V ============ --}}
<h2 class="halaman-baru">V. SIKLUS HIDUP &amp; KEAMANAN DATA</h2>
<table class="kv">
    <tr><td class="k">Masa Retensi Data</td><td>{{ $s['masa_retensi'] }}</td></tr>
    <tr><td class="k">Metode Pemusnahan Data</td><td>{!! $multiline($s['metode_pemusnahan']) !!}</td></tr>
    <tr><td class="k">Pengamanan Teknis &amp; Organisasional</td>
        <td><ol>@foreach ($s['pengamanan'] as $x)<li>{{ $x }}</li>@endforeach</ol></td></tr>
    <tr><td class="k">Status Siklus Hidup</td><td>{{ $labels['status_siklus'][$s['status_siklus']] ?? $s['status_siklus'] }}</td></tr>
</table>

{{-- ============ VI ============ --}}
<h2 class="halaman-baru">VI. INDIKATOR RISIKO TINGGI</h2>
<table class="kv">
    <tr><td class="k">Indikator yang Terpenuhi<br><span style="font-weight:normal">(Pasal 34 ayat (2) UU PDP)</span></td>
        <td>
            @if (empty($indikator)) Tidak ada indikator risiko tinggi yang terpenuhi. @else
                <ul>
                    @foreach ($indikator as $k)
                        <li>{{ $labels['risiko'][$k] ?? $k }}
                            @if ($k === \App\Http\Controllers\RopaController::INDIKATOR_OTOMATIS)
                                <em>(berdasarkan jenis data pada bagian III)</em>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </td></tr>
</table>
<div class="kesimpulan">
    Kesimpulan:
    {{ $wajibDpia
        ? 'Aktivitas pemrosesan ini WAJIB dilengkapi dengan Penilaian Dampak Pelindungan Data Pribadi (DPIA).'
        : 'Aktivitas pemrosesan ini tidak memenuhi indikator risiko tinggi; DPIA tidak diwajibkan.' }}
</div>


{{-- ============ PENGESAHAN & TANDA TANGAN ============ --}}
<div class="blok-pengesahan">
    <div class="pengesahan">
        Tanggal Pengesahan<br>
        {{ $pengesahan['lokasi'] }}, {{ $pengesahan['tanggal'] }}
    </div>

    <table class="ttd">
        <tr>
            <td>Pengendali Data Pribadi</td>
            <td>Pejabat Pelindung Data Pribadi</td>
        </tr>
        <tr>
            <td class="ruang"></td>
            <td class="ruang"></td>
        </tr>
        <tr>
            <td class="nama">{{ $o['nama_pengendali'] }}</td>
            <td class="nama">{{ $o['nama_ppdp'] }}</td>
        </tr>
    </table>
</div>

</body>
</html>
