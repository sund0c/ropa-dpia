@php
    use App\Http\Controllers\PengujianController as PC;
    use App\Support\LabelTte;

    $inf = $uji['informasi'];

    // Helvetica bawaan DomPDF tidak memiliki ≤ ≥; karakter itu dicetak dengan DejaVu Sans
    $u  = fn ($t) => str_replace(['≤', '≥'], ['<span class="dv">≤</span>', '<span class="dv">≥</span>'], e((string) $t));
    $ml = fn ($t) => nl2br($u($t));

    $alat = array_map(fn ($k) => PC::ALAT[$k] ?? $k, $inf['alat'] ?? []);
    if (filled($inf['alat_lainnya'] ?? null)) $alat[] = $inf['alat_lainnya'];

    $label = (array) ($penetapan['label'] ?? []);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Pengujian Keamanan Aplikasi Web {{ $uji['nomor'] }}</title>
    <style>
        @page { margin: 1.8cm 1.6cm 2.2cm 1.6cm; }

        body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #000; line-height: 1.35; }

        header { position: fixed; top: -1.15cm; left: 0; right: 0; text-align: right; }
        header span { font-weight: bold; font-size: 9pt; letter-spacing: 1.5pt; border: 0.75pt solid #000; padding: 2pt 8pt; }

        h1 { font-size: 13pt; text-align: center; margin: 0 0 3pt; letter-spacing: .3pt; }
        .subjudul { text-align: center; font-size: 10pt; margin: 0 0 14pt; }

        table { width: 100%; border-collapse: collapse; }
        .kv td, .grid th, .grid td { border: 0.75pt solid #000; padding: 4pt 6pt; vertical-align: top; text-align: left; }
        .kv td.k { width: 32%; font-weight: bold; }
        .grid th { background: #f2f2f2; }

        h2 { font-size: 10.5pt; background: #e5e5e5; border: 0.75pt solid #000; border-bottom: 0; padding: 4pt 6pt; margin: 14pt 0 0; }
        h2.halaman-baru { page-break-before: always; margin-top: 0; }
        h2.lepas { border-bottom: 0.75pt solid #000; margin-bottom: 8pt; }
        h3 { font-size: 9.5pt; margin: 10pt 0 4pt; }

        .paragraf { margin: 0 0 8pt; text-align: justify; }
        .dv { font-family: "DejaVu Sans", sans-serif; }
        .mono { font-family: 'DejaVu Sans Mono', monospace; font-size: 8pt; }
        .tengah { text-align: center !important; }
        .lampiran { width: auto; margin: 0 0 4pt 12pt; }
        .lampiran td { border: 0; padding: 2pt 6pt 2pt 0; vertical-align: top; }
        .kronologi th, .kronologi td { font-size: 8pt; padding: 3pt 4pt; }
        .kronologi tr { page-break-inside: avoid; }

        .blok-pengesahan { margin-top: 26pt; page-break-inside: avoid; }
        .pengesahan { text-align: center; }
        .ttd { margin-top: 16pt; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 10pt; border: 0; }
        .ttd tr.peran td { vertical-align: bottom; padding-bottom: 6pt; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .ttd .ruang-ttd { height: 60pt; }

        .catatan-klas { margin-top: 18pt; font-size: 7.5pt; border-top: 0.5pt solid #000; padding-top: 4pt; }
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    <h1>LAPORAN PENGUJIAN KEAMANAN APLIKASI WEB</h1>
    <p class="subjudul">{{ $inf['nama_aplikasi'] }}<br>Pengujian Manual Berdasarkan OWASP Top 10:2025</p>

    {{-- ================= 1 ================= --}}
    <h2>1. IDENTITAS LAPORAN</h2>
    <table class="kv">
        <tr><td class="k">Nomor Laporan</td><td>{{ $inf['nomor_laporan'] }}</td></tr>
        <tr>
            <td class="k">Klasifikasi Dokumen</td>
            <td><b>TERBATAS</b> — hanya untuk Pimpinan Instansi Pemilik Aplikasi, Tim Penguji, dan pengembang yang menindaklanjuti</td>
        </tr>
        <tr><td class="k">Acuan</td><td>OWASP Top 10:2025; Runbook Pengujian Keamanan Manual, Bidang Persandian Diskominfos Provinsi Bali</td></tr>
        <tr><td class="k">Disusun oleh</td><td>{{ $inf['tim_nama'] }} — {{ $inf['ketua_instansi'] }}</td></tr>
        <tr><td class="k">Tanggal Laporan</td><td>{{ $penetapan['tanggal'] }}</td></tr>
    </table>

    {{-- ================= 2 ================= --}}
    <h2>2. APLIKASI YANG DIUJI</h2>
    <table class="kv">
        <tr><td class="k">Nama Aplikasi</td><td>{{ $inf['nama_aplikasi'] }}@if (filled($inf['kode_aset'] ?? null)) ({{ $inf['kode_aset'] }})@endif</td></tr>
        <tr><td class="k">URL</td><td class="mono">{{ $inf['url'] }}</td></tr>
        <tr><td class="k">Lingkungan Pengujian</td><td>{{ PC::LINGKUNGAN[$inf['lingkungan']] ?? $inf['lingkungan'] }}</td></tr>
        @if (filled($inf['teknologi'] ?? null))
            <tr><td class="k">Teknologi</td><td>{{ $inf['teknologi'] }}</td></tr>
        @endif
        <tr><td class="k">Instansi Pemilik</td><td>{{ $inf['pemilik_aset'] }}</td></tr>
        <tr><td class="k">Pimpinan</td><td>{{ $inf['pimpinan_nama'] }} — {{ $inf['pimpinan_jabatan'] }}</td></tr>
    </table>

    {{-- ================= 3 ================= --}}
    <h2 class="halaman-baru">3. OTORISASI DAN RUANG LINGKUP</h2>
    <table class="kv">
        <tr>
            <td class="k">Surat Otorisasi</td>
            <td>Nomor {{ $inf['nomor_surat'] }}, tanggal {{ PC::tanggal($inf['tanggal_surat']) }}<br>dari {{ $inf['pemberi_izin'] }}</td>
        </tr>
        <tr><td class="k">Periode Pengujian</td><td>{{ PC::tanggal($inf['periode_mulai']) }} s.d. {{ PC::tanggal($inf['periode_selesai']) }}</td></tr>
        <tr>
            <td class="k">Tim Penguji</td>
            <td>{{ $inf['tim_nama'] }}<br>Ketua: {{ $inf['ketua_nama'] }} — {{ $inf['ketua_jabatan'] }}@if (filled($inf['anggota'] ?? null))<br>Anggota:<br>{!! $ml($inf['anggota']) !!}@endif</td>
        </tr>
        <tr><td class="k">Peran / Akun yang Diuji</td><td>{!! $ml($inf['peran']) !!}</td></tr>
        <tr><td class="k">Cakupan</td><td>{!! $ml($inf['cakupan']) !!}</td></tr>
        @if (filled($inf['luar_cakupan'] ?? null))
            <tr><td class="k">Di Luar Cakupan</td><td>{!! $ml($inf['luar_cakupan']) !!}</td></tr>
        @endif
        <tr><td class="k">Alat</td><td>{{ implode('; ', $alat) }} (seluruhnya dioperasikan manual)</td></tr>
    </table>

    <h3>Prinsip Pengujian yang Diterapkan</h3>
    <table class="grid">
        @foreach (PC::PRINSIP as $k => $t)
            <tr><td style="width:5%" class="dv">✓</td><td>{{ $t }}</td></tr>
        @endforeach
    </table>

    @php
        use App\Support\RunbookOwasp;
        $rekap  = PC::rekap($uji);
        $temuan = PC::daftarTemuan($uji);
        $rk     = $uji['ringkasan'] ?? [];
        $warnaR = ['kritis' => '#f3c4c4', 'tinggi' => '#f6d4bf', 'sedang' => '#f6e3bf', 'rendah' => '#cdeadd'];
    @endphp

    {{-- ================= 4 ================= --}}
    <h2 class="halaman-baru lepas">4. RINGKASAN HASIL</h2>
    <table class="grid kronologi">
        <tr>
            <th style="width:30%">Kategori</th>
            <th>Aman</th><th>Rentan</th><th>Tinjau</th><th>Tdk diuji</th><th>Tdk berlaku</th><th>Total</th>
        </tr>
        @foreach (RunbookOwasp::KATEGORI as $k => $kat)
            @php $r = $rekap[$k]; @endphp
            <tr>
                <td>{{ $kat['kode'] }} — {{ $kat['nama'] }}</td>
                <td class="tengah">{{ $r['aman'] }}</td>
                <td class="tengah"><b>{{ $r['rentan'] ?: '' }}</b></td>
                <td class="tengah">{{ $r['tinjau'] }}</td>
                <td class="tengah">{{ $r['tidak_diuji'] }}</td>
                <td class="tengah">{{ $r['tidak_berlaku'] }}</td>
                <td class="tengah">{{ $r['total'] }}</td>
            </tr>
        @endforeach
    </table>

    @if (filled($rk['kesimpulan'] ?? null))
        <p class="paragraf" style="margin-top:8pt"><b>Kesimpulan:</b> {!! $ml($rk['kesimpulan']) !!}</p>
    @endif

    {{-- ================= 5 ================= --}}
    <h2 class="halaman-baru lepas">5. DAFTAR TEMUAN</h2>
    @if ($temuan === [])
        <p class="paragraf">Tidak ada butir yang ditandai sebagai kerentanan dalam cakupan pengujian ini.</p>
    @else
        <table class="grid kronologi">
            <tr><th style="width:10%">Risiko</th><th style="width:10%">Butir</th><th>Temuan</th><th style="width:30%">Rekomendasi</th></tr>
            @foreach ($temuan as $t)
                <tr>
                    <td style="background:{{ $warnaR[$t['risiko']] ?? '#eee' }}"><b>{{ PC::RISIKO[$t['risiko']] ?? $t['risiko'] }}</b></td>
                    <td>{{ $t['kode'] }}</td>
                    <td><b>{{ $t['judul'] }}</b><br>{!! $u($t['objek'] ?? '') !!}@if (filled($t['bukti'] ?? null))<br><span style="font-size:7.5pt">{!! $u($t['bukti']) !!}</span>@endif</td>
                    <td>{!! $ml($t['rekomendasi'] ?? '') !!}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- ================= 6 ================= --}}
    <h2 class="halaman-baru lepas">6. HASIL RINCI PER KATEGORI</h2>
    @foreach (RunbookOwasp::KATEGORI as $k => $kat)
        <h3>{{ $kat['kode'] }} — {{ $kat['nama'] }}</h3>
        <table class="grid kronologi">
            <tr><th style="width:6%">No</th><th style="width:24%">Butir Uji</th><th style="width:14%">Hasil</th><th>Catatan</th></tr>
            @foreach ($kat['butir'] as $no => $butir)
                @php $entri = PC::entri($uji, $k, $no); $n = count($entri); @endphp
                @foreach ($entri as $i => $h)
                    <tr>
                        @if ($i === 0)
                            <td class="tengah" rowspan="{{ $n }}">{{ $kat['kode'] }}.{{ $no }}</td>
                            <td rowspan="{{ $n }}">{{ $butir['judul'] }}</td>
                        @endif
                        <td>{{ PC::HASIL[$h['hasil'] ?? ''] ?? '—' }}@if (($h['hasil'] ?? null) === 'rentan' && filled($h['risiko'] ?? null)) <br><span style="font-size:7.5pt">({{ PC::RISIKO[$h['risiko']] ?? '' }})</span>@endif</td>
                        <td>
                            @if (in_array($h['hasil'] ?? null, ['aman', 'rentan', 'tinjau'], true))
                                {!! $u($h['objek'] ?? '') !!}
                                @if (filled($h['peran'] ?? null)) <span style="font-size:7.5pt">[{{ $h['peran'] }}]</span> @endif
                                @if (filled($h['bukti'] ?? null)) — {!! $u($h['bukti']) !!} @endif
                                @if (filled($h['rekomendasi'] ?? null))<br><i>Rekomendasi: {!! $u($h['rekomendasi']) !!}</i>@endif
                            @elseif (filled($h['alasan'] ?? null))
                                {!! $u($h['alasan']) !!}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    @endforeach

    {{-- ================= 7 ================= --}}
    <h2 class="halaman-baru lepas">7. PERNYATAAN RUANG LINGKUP</h2>
    <p class="paragraf">{!! $ml($rk['pernyataan'] ?? PC::PERNYATAAN_BAKU) !!}</p>

    {{-- ================= 8 ================= --}}
    <h2 class="lepas">8. LAMPIRAN</h2>
    <p class="paragraf">Lampiran berikut merupakan bagian yang tidak terpisahkan dari laporan ini:</p>
    <table class="lampiran">
        <tr><td style="width:6%">A.</td><td>Surat Perintah Tugas</td></tr>
        <tr><td>B.</td><td>Non-Disclosure Agreement (Pernyataan Menjaga Kerahasiaan)</td></tr>
        <tr><td>C.</td><td>Bukti Dukung</td></tr>
    </table>


    {{-- ================= PENGESAHAN ================= --}}
    <div class="blok-pengesahan">
        <div class="pengesahan">{{ $penetapan['lokasi'] }}, {{ $penetapan['tanggal'] }}</div>

        <table class="ttd">
            <tr class="peran">
                <td>Disusun oleh,<br>Ketua Tim Penguji</td>
                <td>Mengetahui,<br>Pimpinan Instansi Pemilik Aplikasi</td>
            </tr>
            <tr>
                <td>{!! LabelTte::kolom('ketua', $label, 210, '', $inf['ketua_jabatan'] ?? null, $inf['ketua_instansi'] ?? null, $inf['ketua_nama'] ?? null) !!}</td>
                <td>{!! LabelTte::kolom('pimpinan', $label, 210, '', $inf['pimpinan_jabatan'] ?? null, $inf['pemilik_aset'] ?? null, $inf['pimpinan_nama'] ?? null) !!}</td>
            </tr>
        </table>
    </div>

    <div class="catatan-klas">
        Dokumen ini berklasifikasi <b>TERBATAS</b> karena memuat informasi kerentanan. Dilarang menggandakan atau
        menyebarluaskan sebagian maupun seluruh isi dokumen kepada pihak yang tidak berwenang.
    </div>

</body>

</html>
