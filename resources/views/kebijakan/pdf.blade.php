@php
    $o = $ropa['organisasi'];
    $a = $ropa['aktivitas'];
    $p = $ropa['pemetaan'];
    $h = $ropa['hak'];
    $s = $ropa['siklus'];

    $ml  = fn($t) => nl2br(e((string) $t));
    $no  = 0;
    $bab = function (string $judul) use (&$no) { return '<h2>' . (++$no) . '. ' . e($judul) . '</h2>'; };

    $dasar        = (array) ($a['dasar'] ?? []);
    $persetujuan  = in_array('consent', $dasar, true);
    $umum         = (array) ($p['jenis_umum'] ?? []);
    $spesifik     = (array) ($p['jenis_spesifik'] ?? []);
    $transfer     = (array) ($p['transfer'] ?? []);
    $anak         = (bool) ($p['data_anak'] ?? false);
    $rentan       = (bool) ($p['kelompok_rentan'] ?? false);
    $kontak       = array_filter(['Telepon' => $o['telepon'] ?? null, 'Surel' => $o['email'] ?? null]);
    $estimasi     = number_format((int) ($p['estimasi_subjek'] ?? 0), 0, ',', '.');
    $layanan      = $a['nama_aktivitas'];
    $instansi     = $o['nama_instansi'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kebijakan Privasi — {{ $layanan }}</title>
<style>
    @page { margin: 1.8cm 1.8cm 2.2cm 1.8cm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color: #000; line-height: 1.45; }
    header { position: fixed; top: -1.15cm; left: 0; right: 0; text-align: right; }
    header span { font-weight: bold; font-size: 9pt; letter-spacing: 1.5pt; border: 0.75pt solid #000; padding: 2pt 8pt; }

    .judul { text-align: center; margin-bottom: 18pt; }
    .judul .draft { font-size: 11pt; font-weight: bold; letter-spacing: 3pt; margin-bottom: 6pt; }
    .judul h1 { font-size: 15pt; margin: 0 0 6pt; letter-spacing: .5pt; }
    .judul .layanan { font-size: 12pt; font-weight: bold; margin-bottom: 3pt; }
    .judul .instansi { font-size: 11pt; }

    h2 { font-size: 11pt; margin: 16pt 0 6pt; padding-bottom: 3pt; border-bottom: 0.75pt solid #000; page-break-after: avoid; }
    p { margin: 0 0 6pt; text-align: justify; }
    ul, ol { margin: 0 0 6pt; padding-left: 16pt; }
    li { margin-bottom: 2pt; }
    table { width: 100%; border-collapse: collapse; margin: 4pt 0 8pt; }
    th, td { border: 0.75pt solid #000; padding: 4pt 6pt; vertical-align: top; text-align: left; }
    th { background: #f2f2f2; }
</style>
</head>
<body>

<header><span>DRAFT</span></header>

<div class="judul">
    <div class="draft">DRAFT</div>
    <h1>KEBIJAKAN PRIVASI</h1>
    <div class="layanan">{{ $layanan }}</div>
    <div class="instansi">{{ $instansi }}</div>
</div>

{{-- ============ TENTANG ============ --}}
{!! $bab('Tentang Kebijakan Ini') !!}
<p>
    {{ $instansi }} ("kami") menyelenggarakan <strong>{{ $layanan }}</strong> ("Layanan"). Kebijakan ini menjelaskan
    data pribadi apa yang kami proses dalam Layanan, untuk apa, siapa yang dapat mengaksesnya, berapa lama disimpan,
    bagaimana kami melindunginya, dan hak Anda atas data tersebut.
</p>
<p>
    Kebijakan ini hanya berlaku untuk Layanan tersebut. Kebijakan ini tidak mengatur layanan lain, maupun pemanfaatan
    data oleh pihak penerima yang menjadi tanggung jawab pihak penerima tersebut.
</p>
<p>
    <strong>Pengendali Data Pribadi</strong> untuk Layanan ini adalah {{ $instansi }}, {{ $o['alamat'] }}.
    Kami telah menunjuk Pejabat Pelindung Data Pribadi yang dapat dihubungi melalui kanal pada bagian Hubungi Kami.
    Kebijakan ini disusun berdasarkan Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).
</p>

{{-- ============ DATA ============ --}}
{!! $bab('Data Pribadi yang Kami Proses') !!}
<p>
        Layanan memproses data pribadi <strong>{{ implode(', ', (array) $p['kategori_subjek']) }}</strong>{{ $anak || $rentan ? ', termasuk' : '' }}
    {{ $anak ? 'anak' : '' }}{{ $anak && $rentan ? ' serta' : '' }}{{ $rentan ? ' kelompok rentan seperti penyandang disabilitas dan lansia' : '' }}.
</p>
<table>
    <tr><th style="width:62%">Jenis data</th><th>Kategori menurut UU PDP</th></tr>
    @foreach ($umum as $k)
        <tr><td>{{ $labels['jenis_umum'][$k] ?? $k }}</td><td>Umum (Pasal 4 ayat 3)</td></tr>
    @endforeach
    @foreach ($spesifik as $k)
        <tr><td>{{ $labels['jenis_spesifik'][$k] ?? $k }}</td><td>Spesifik (Pasal 4 ayat 2)</td></tr>
    @endforeach
</table>
<p><strong>Sumber data.</strong> Data diperoleh dari:</p>
<ul>
    @foreach ((array) $p['pengumpulan'] as $row)
        <li>{{ $row['sumber'] }}</li>
    @endforeach
</ul>

{{-- ============ TUJUAN & DASAR ============ --}}
{!! $bab('Tujuan dan Dasar Hukum Pemrosesan') !!}
<p><strong>Tujuan.</strong> {!! $ml($a['tujuan']) !!}</p>
<p>Data tidak digunakan untuk tujuan lain di luar tujuan tersebut.</p>
<p><strong>Dasar hukum</strong> (Pasal 20 ayat 2 UU PDP):</p>
<ul>
    @foreach ($dasar as $d)
        <li>{{ $labels['dasar'][$d] ?? $d }} ({{ $pasal[$d] ?? '' }})</li>
    @endforeach
</ul>
@if ($persetujuan)
    <p>
        Untuk pemrosesan yang didasarkan pada persetujuan, Anda dapat menarik kembali persetujuan Anda sewaktu-waktu
        melalui kanal pada bagian Hubungi Kami. Penarikan persetujuan tidak memengaruhi keabsahan pemrosesan yang telah
        dilakukan sebelumnya.
    </p>
@endif
@if (! empty($a['referensi_hukum']))
    <p><strong>Peraturan yang menjadi rujukan:</strong></p>
    <ol>
        @foreach ((array) $a['referensi_hukum'] as $ref)
            <li>{{ $ref }}</li>
        @endforeach
    </ol>
@endif

{{-- ============ PROSES ============ --}}
{!! $bab('Bagaimana Data Diproses') !!}
<p>Pemrosesan data dalam Layanan berlangsung melalui tahapan berikut:</p>
<ol>
    @foreach ((array) $a['tahapan'] as $t)
        <li>{{ $t }}</li>
    @endforeach
</ol>

{{-- ============ PENERIMA ============ --}}
{!! $bab('Siapa yang Dapat Mengakses Data') !!}
@if ($transfer)
    <p>Data hanya dapat diakses oleh pihak berikut, sebatas yang diperlukan untuk tujuannya:</p>
    <table>
        <tr><th style="width:26%">Penerima</th><th style="width:14%">Peran</th><th style="width:32%">Tujuan</th><th>Data yang diterima</th></tr>
        @foreach ($transfer as $t)
            <tr>
                <td>{{ $t['organisasi'] }}</td>
                <td>{{ $labels['peran'][$t['peran']] ?? $t['peran'] }}</td>
                <td>{!! $ml($t['tujuan']) !!}</td>
                <td>{{ implode(', ', array_map(fn($k) => $labels['jenis'][$k] ?? $k, (array) $t['data'])) }}</td>
            </tr>
        @endforeach
    </table>
@else
    <p>Data tidak dibagikan kepada pihak lain di luar {{ $instansi }}.</p>
@endif
<ul>
    <li>Kami <strong>tidak menjual, menyewakan, atau memperdagangkan</strong> data pribadi Anda.</li>
    <li>Data hanya dapat diungkapkan kepada aparat penegak hukum atau lembaga berwenang jika diwajibkan peraturan
        perundang-undangan, dan hanya sebatas yang diwajibkan.</li>
</ul>

{{-- ============ RETENSI ============ --}}
{!! $bab('Penyimpanan, Retensi, dan Pemusnahan') !!}
<p><strong>Masa penyimpanan:</strong> {!! $ml($s['masa_retensi']) !!}</p>
<p><strong>Pemusnahan:</strong> {!! $ml($s['metode_pemusnahan']) !!}</p>
<p><strong>Lokasi penyimpanan:</strong></p>
<ul>
    @foreach ((array) $p['pengumpulan'] as $row)
        <li>{{ $row['lokasi'] }}</li>
    @endforeach
</ul>

{{-- ============ KEAMANAN ============ --}}
{!! $bab('Keamanan Data') !!}
<p>
    Kami menerapkan pengamanan teknis dan organisasi untuk melindungi data dari akses, pengungkapan, perubahan, dan
    penghapusan yang tidak sah. Pengamanan tersebut meliputi pembatasan akses berdasarkan peran dan kebutuhan tugas,
    enkripsi, pengamanan perangkat dan jaringan, pencatatan jejak audit, perjanjian kerahasiaan bagi petugas, serta
    pemantauan dan penanganan insiden keamanan.
</p>
@if ($risikoTinggi)
    <p>
        Karena pemrosesan ini tergolong berisiko tinggi menurut Pasal 34 UU PDP, kami telah melakukan Penilaian Dampak
        Pelindungan Data Pribadi sebelum Layanan dijalankan, dan meninjaunya secara berkala.
    </p>
@endif
<p>
    Rincian teknis pengamanan tidak kami publikasikan untuk menjaga keamanan Layanan. Tidak ada sistem yang sepenuhnya
    bebas risiko, sehingga pengamanan kami tinjau dan perbarui secara berkala.
</p>

{{-- ============ HAK ============ --}}
{!! $bab('Hak Anda') !!}
<p>Sebagai Subjek Data Pribadi, Anda memiliki hak berikut:</p>
<ol>
    @foreach ((array) $h['hak'] as $k)
        <li>{{ $labels['hak'][$k] ?? $k }}</li>
    @endforeach
</ol>
<p><strong>Cara mengajukan permintaan.</strong> {!! $ml($h['mekanisme_hak']) !!}</p>
<ul>
    <li>Kami dapat meminta verifikasi identitas sebelum menindaklanjuti permintaan, agar data Anda tidak diberikan kepada orang lain.</li>
    <li>Permintaan ditanggapi dalam jangka waktu yang ditetapkan UU PDP.</li>
    <li>Pengajuan permintaan tidak dipungut biaya.</li>
    <li>Sebagian hak dapat dibatasi dalam keadaan tertentu sebagaimana diatur dalam Pasal 15 UU PDP. Jika permintaan
        Anda ditolak atau dibatasi, kami sampaikan alasannya secara tertulis.</li>
</ul>

{{-- ============ DATA ANAK / SPESIFIK (bila ada) ============ --}}
@if ($anak || $spesifik || $rentan)
    {!! $bab('Data Anak, Data Spesifik, dan Kelompok Rentan') !!}
    <p>
        Layanan memproses
        {{ implode(', ', array_filter([
            $anak ? 'data anak' : null,
            $spesifik ? 'data pribadi yang bersifat spesifik' : null,
            $rentan ? 'data kelompok rentan' : null,
        ])) }}.
        Data tersebut hanya digunakan untuk tujuan yang dinyatakan dalam kebijakan ini, dengan akses yang lebih
        terbatas dan pengawasan yang lebih ketat.
    </p>
    @if ($anak && $persetujuan)
        <p>Pemrosesan data anak yang didasarkan pada persetujuan dilakukan dengan persetujuan orang tua atau wali,
            sesuai Pasal 25 UU PDP.</p>
    @endif
@endif

{{-- ============ INSIDEN ============ --}}
{!! $bab('Jika Terjadi Kegagalan Pelindungan Data') !!}
<p>
    Jika terjadi kegagalan pelindungan data pribadi dalam Layanan, kami memberitahukannya secara tertulis paling
    lambat 3 x 24 jam kepada Subjek Data Pribadi yang terdampak dan kepada lembaga penyelenggara pelindungan data
    pribadi, sesuai Pasal 46 UU PDP. Pemberitahuan memuat data yang terungkap, kapan dan bagaimana data terungkap,
    serta upaya penanganan dan pemulihan.
</p>
<p>Jika Anda menemukan atau menduga adanya penyalahgunaan data dari Layanan ini, segera laporkan kepada kami.</p>

{{-- ============ KONTAK ============ --}}
{!! $bab('Hubungi Kami') !!}
<p>Pertanyaan, permintaan hak, dan pengaduan terkait Layanan dapat disampaikan kepada Pejabat Pelindung Data Pribadi {{ $instansi }}:</p>
<table>
    @foreach ($kontak as $jenis => $nilai)
        <tr><td style="width:26%"><strong>{{ $jenis }}</strong></td><td>{{ $nilai }}</td></tr>
    @endforeach
    <tr><td style="width:26%"><strong>Alamat</strong></td><td>{{ $instansi }}, {{ $o['alamat'] }}</td></tr>
</table>
<p>
    Jika Anda tidak puas dengan tanggapan kami, Anda dapat menyampaikan pengaduan kepada lembaga penyelenggara
    pelindungan data pribadi sesuai UU PDP.
</p>

{{-- ============ PERUBAHAN ============ --}}
{!! $bab('Perubahan Kebijakan') !!}
<p>
    Kami meninjau kebijakan ini secara berkala, dan setiap kali terjadi perubahan jenis data, penerima data, atau cara
    pemrosesan. Perubahan penting akan diumumkan bersama tanggal pembaruannya.
</p>
<p><strong>Terakhir diperbarui:</strong> {{ $tanggal }}</p>

</body>
</html>
