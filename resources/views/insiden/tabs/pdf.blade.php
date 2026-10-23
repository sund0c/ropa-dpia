@php
    use App\Http\Controllers\InsidenController as IC;
    use App\Support\LabelTte;

    $inf = $insiden['informasi'];

    // Helvetica bawaan DomPDF tidak memiliki ≤ ≥; karakter itu dicetak dengan DejaVu Sans
    $u  = fn ($t) => str_replace(['≤', '≥'], ['<span class="dv">≤</span>', '<span class="dv">≥</span>'], e((string) $t));
    $ml = fn ($t) => nl2br($u($t));
    $w  = fn (?string $f) => IC::tampilWaktu($inf[$f] ?? null);

    $sumber = ($inf['sumber_deteksi'] ?? null) === 'lainnya'
        ? ($inf['sumber_lainnya'] ?? 'Lainnya')
        : (IC::SUMBER_DETEKSI[$inf['sumber_deteksi']] ?? $inf['sumber_deteksi']);

    $kls = $insiden['klasifikasi'];
    $kategori = $kls['kategori'] === 'lainnya' ? $kls['kategori_lainnya'] : (IC::KATEGORI[$kls['kategori']] ?? $kls['kategori']);
    $vektor   = $kls['vektor'] === 'lainnya' ? $kls['vektor_lainnya'] : (IC::VEKTOR[$kls['vektor']] ?? $kls['vektor']);
    $kp       = IC::KEPARAHAN[$kls['keparahan']] ?? ['nama' => '-', 'kriteria' => ''];

    $durasiGangguan = IC::durasi($inf['waktu_deteksi'] ?? null, $inf['waktu_pulih'] ?? null);

    $label = (array) ($penetapan['label'] ?? []);

    // Laporan awal / perkembangan: isi masih sementara
    $sementara = ($inf['jenis_laporan'] ?? null) !== 'akhir';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Penanganan Insiden {{ $insiden['nomor'] }}</title>
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
        .tebal { font-weight: bold; }
        .dv { font-family: "DejaVu Sans", sans-serif; }

        /* Tanda tangan */
        .blok-pengesahan { margin-top: 26pt; page-break-inside: avoid; }
        .pengesahan { text-align: center; }
        .ttd { margin-top: 16pt; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 10pt; border: 0; }
        .ttd tr.peran td { vertical-align: bottom; padding-bottom: 6pt; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .ttd .ruang-ttd { height: 60pt; }

        .kronologi th, .kronologi td { font-size: 8.5pt; padding: 3pt 4pt; }
        .kronologi tr { page-break-inside: avoid; }

        .hash { font-family: 'DejaVu Sans Mono', monospace; font-size: 6.5pt; }

        .catatan-klas { margin-top: 18pt; font-size: 7.5pt; border-top: 0.5pt solid #000; padding-top: 4pt; }
    </style>
</head>

<body>

    <header><span>TERBATAS</span></header>

    <h1>LAPORAN PENANGANAN INSIDEN SIBER</h1>
    <p class="subjudul">{{ $inf['judul'] }}<br><b>{{ IC::JENIS_LAPORAN[$inf['jenis_laporan']] ?? '' }}</b></p>

    {{-- ================= 1 ================= --}}
    <h2>1. IDENTITAS LAPORAN</h2>
    <table class="kv">
        <tr><td class="k">Nomor Laporan</td><td>{{ $inf['nomor_laporan'] }}</td></tr>
        @if (filled($inf['nomor_tiket'] ?? null))
            <tr><td class="k">Nomor Tiket Insiden</td><td>{{ $inf['nomor_tiket'] }}</td></tr>
        @endif
        <tr><td class="k">Judul</td><td>{{ $inf['judul'] }}</td></tr>
        <tr>
            <td class="k">Klasifikasi Dokumen</td>
            <td><b>TERBATAS</b> — hanya untuk Pimpinan Instansi Pemilik Aset, Tim Penanganan Insiden, dan pihak yang berwenang</td>
        </tr>
        <tr><td class="k">Jenis Laporan</td><td>{{ IC::JENIS_LAPORAN[$inf['jenis_laporan']] ?? $inf['jenis_laporan'] }}</td></tr>
        <tr><td class="k">Status Insiden</td><td>{{ IC::STATUS[$inf['status']] ?? $inf['status'] }}</td></tr>
        <tr><td class="k">Disusun oleh</td><td>{{ $inf['tim_nama'] }} — {{ $inf['ketua_instansi'] }}</td></tr>
        <tr><td class="k">Pemilik Aset</td><td>{{ $inf['pemilik_aset'] }}</td></tr>
        <tr><td class="k">Tanggal Laporan</td><td>{{ $penetapan['tanggal'] }}</td></tr>
    </table>

    {{-- ================= 2 ================= --}}
    <h2 class="lepas">2. RINGKASAN EKSEKUTIF</h2>
    @if ($sementara)
        <p class="paragraf"><i>Laporan ini disusun saat penanganan masih berlangsung. Informasi di dalamnya bersifat
            sementara dan dapat berubah; kesimpulan akhir disampaikan dalam Laporan Akhir.</i></p>
    @endif
    <p class="paragraf">{!! $ml($inf['ringkasan']) !!}</p>

    <table class="kv">
        <tr><td class="k">Kategori</td><td>{{ $kategori }}</td></tr>
        <tr><td class="k">Tingkat Keparahan</td><td><b>{{ $kp['nama'] }} (Level {{ $kls['keparahan'] }} dari 4)</b></td></tr>
        <tr><td class="k">Aset Terdampak</td><td>{{ $inf['nama_aset'] }}@if (filled($inf['kode_aset'] ?? null)) ({{ $inf['kode_aset'] }})@endif</td></tr>
        <tr><td class="k">Waktu Terdeteksi</td><td>{{ $w('waktu_deteksi') }}</td></tr>
        @if ($durasiGangguan)
            <tr><td class="k">Lama Gangguan</td><td>{{ $durasiGangguan }} (terdeteksi hingga layanan pulih)</td></tr>
        @endif
        <tr><td class="k">Data Pribadi</td><td>{{ IC::DATA_PRIBADI[$kls['data_pribadi']] ?? $kls['data_pribadi'] }}</td></tr>
        <tr><td class="k">Status</td><td>{{ IC::STATUS[$inf['status']] ?? $inf['status'] }}</td></tr>
    </table>

    {{-- ================= 3 ================= --}}
    <h2>3. INFORMASI INSIDEN</h2>
    <table class="kv">
        <tr><td class="k">Waktu Kejadian (perkiraan)</td><td>{{ $w('waktu_kejadian') }}</td></tr>
        <tr><td class="k">Waktu Terdeteksi</td><td>{{ $w('waktu_deteksi') }}</td></tr>
        <tr><td class="k">Waktu Dilaporkan ke Tim</td><td>{{ $w('waktu_lapor') }}</td></tr>
        <tr><td class="k">Waktu Layanan Pulih</td><td>{{ filled($inf['waktu_pulih'] ?? null) ? $w('waktu_pulih') : 'Belum pulih' }}</td></tr>
        @if (($inf['status'] ?? null) === 'ditutup')
            <tr><td class="k">Waktu Insiden Ditutup</td><td>{{ $w('waktu_tutup') }}</td></tr>
        @endif
        <tr><td class="k">Sumber Deteksi</td><td>{{ $sumber }}</td></tr>
        <tr><td class="k">Pelapor</td><td>{{ $inf['pelapor'] }}</td></tr>
        <tr><td class="k">Tim Penanganan Insiden</td><td>{{ $inf['tim_nama'] }}</td></tr>
        <tr><td class="k">Ketua Tim</td><td>{{ $inf['ketua_nama'] }} — {{ $inf['ketua_jabatan'] }}, {{ $inf['ketua_instansi'] }}</td></tr>
        <tr><td class="k">Pimpinan Pemilik Aset</td><td>{{ $inf['pimpinan_nama'] }} — {{ $inf['pimpinan_jabatan'] }}, {{ $inf['pemilik_aset'] }}</td></tr>
        <tr><td class="k">Lokasi &amp; Spesifikasi Aset</td><td>{!! $ml($inf['lokasi_aset']) !!}</td></tr>
        @if (filled($inf['pihak_diberitahu'] ?? null))
            <tr><td class="k">Pihak yang Diberi Tahu</td><td>{!! $ml($inf['pihak_diberitahu']) !!}</td></tr>
        @endif
    </table>

    @php $ukuran = IC::ukuranWaktu($inf); @endphp
    @if ($ukuran)
        <h3>Ukuran Waktu Penanganan</h3>
        <table class="grid">
            <tr><th>Ukuran</th><th style="width:30%">Durasi</th></tr>
            @foreach ($ukuran as $nama => $nilai)
                <tr><td>{{ $nama }}</td><td>{{ $nilai }}</td></tr>
            @endforeach
        </table>
    @endif

    {{-- ================= 4 ================= --}}
    <h2 class="lepas">4. KLASIFIKASI DAN TINGKAT KEPARAHAN</h2>
    <table class="kv">
        <tr><td class="k">Kategori Insiden</td><td>{{ $kategori }}</td></tr>
        <tr><td class="k">Vektor Serangan</td><td>{{ $vektor }}</td></tr>
        <tr><td class="k">Tingkat Keparahan</td><td><b>{{ $kp['nama'] }} (Level {{ $kls['keparahan'] }} dari 4)</b> — {{ $kp['kriteria'] }}</td></tr>
        @if (filled($kls['alasan_keparahan'] ?? null))
            <tr>
                <td class="k">Alasan Penetapan</td>
                <td>{!! $ml($kls['alasan_keparahan']) !!}<br>
                    <i>Saran perhitungan: {{ IC::KEPARAHAN[$kls['saran']]['nama'] ?? '-' }}</i></td>
            </tr>
        @endif
    </table>

    <h3>Penilaian Dampak</h3>
    <table class="grid">
        <tr><th style="width:22%">Dimensi</th><th style="width:16%">Penilaian</th><th>Keterangan</th></tr>
        @foreach (IC::DIMENSI as $dim => $info)
            <tr>
                <td>{{ $info['nama'] }}</td>
                <td>{{ IC::TINGKAT_DAMPAK[$kls['dampak'][$dim]['tingkat']] ?? '-' }}</td>
                <td>{!! $u($kls['dampak'][$dim]['keterangan']) !!}</td>
            </tr>
        @endforeach
    </table>

    <h3>Data Pribadi dan Pemberitahuan</h3>
    <table class="kv">
        <tr><td class="k">Status Data Pribadi</td><td>{{ IC::DATA_PRIBADI[$kls['data_pribadi']] ?? $kls['data_pribadi'] }}</td></tr>
        @if ($kls['data_pribadi'] !== 'tidak')
            <tr><td class="k">Jenis Data Pribadi</td><td>{!! $ml($kls['jenis_data']) !!}</td></tr>
            @if (filled($kls['jumlah_subjek'] ?? null))
                <tr><td class="k">Perkiraan Jumlah Subjek Data</td><td>{{ $kls['jumlah_subjek'] }}</td></tr>
            @endif
        @endif
        <tr>
            <td class="k">Pemberitahuan (UU 27/2022 Pasal 46)</td>
            <td>
                {{ IC::NOTIFIKASI_PDP[$kls['notifikasi']] ?? $kls['notifikasi'] }}@if ($kls['notifikasi'] === 'sudah' && filled($kls['tanggal_notifikasi'] ?? null)), {{ \Carbon\Carbon::parse($kls['tanggal_notifikasi'])->locale('id')->translatedFormat('d F Y') }}@endif
                @if ($kls['data_pribadi'] === 'tidak')
                    — tidak terjadi kegagalan pelindungan data pribadi.
                @endif
            </td>
        </tr>
    </table>

    <h3>Acuan Tingkat Keparahan</h3>
    <table class="grid">
        <tr><th style="width:8%">Level</th><th style="width:14%">Nama</th><th>Kriteria</th></tr>
        @foreach (IC::KEPARAHAN as $i => $k)
            <tr @if ($i === (int) $kls['keparahan']) style="background:#eeeeee; font-weight:bold" @endif>
                <td>{{ $i }}</td><td>{{ $k['nama'] }}</td><td>{{ $k['kriteria'] }}</td>
            </tr>
        @endforeach
    </table>

    {{-- ================= 5 ================= --}}
    <h2 class="halaman-baru lepas">5. KRONOLOGI</h2>
    <p class="paragraf">Waktu dalam WITA (UTC+8), disusun dari log sistem, catatan tim, dan laporan pihak terkait.</p>
    <table class="grid kronologi">
        <tr>
            <th style="width:17%">Tanggal &amp; Waktu</th>
            <th style="width:14%">Fase</th>
            <th>Kejadian</th>
            <th style="width:20%">Sumber Bukti</th>
        </tr>
        @foreach ($insiden['kronologi']['kronologi'] as $r)
            @php $wr = IC::waktu($r['waktu']); @endphp
            <tr>
                <td>{{ $wr ? $wr->locale('id')->translatedFormat('d M Y, H.i') : $r['waktu'] }}</td>
                <td>{{ IC::FASE[$r['fase']] ?? $r['fase'] }}</td>
                <td>{!! $ml($r['kejadian']) !!}</td>
                <td>{{ $r['bukti'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>

    {{-- ================= 6 ================= --}}
    @if (isset($insiden['analisis']) && array_key_exists('analisis', IC::tabsAktif($insiden)))
        @php $an = $insiden['analisis']; @endphp
        <h2 class="lepas">6. ANALISIS TEKNIS{{ $sementara ? ' (SEMENTARA)' : '' }}</h2>
        <p class="paragraf">{!! $ml($an['ringkasan_teknis']) !!}</p>

        @if (! empty($an['rantai']))
            <h3>6.1 Rantai Serangan (pemetaan MITRE ATT&amp;CK)</h3>
            <table class="grid kronologi">
                <tr><th style="width:24%">Tahap</th><th style="width:32%">Teknik</th><th>Temuan</th></tr>
                @foreach ($an['rantai'] as $r)
                    <tr>
                        <td>{{ IC::TAKTIK[$r['taktik']] ?? $r['taktik'] }}</td>
                        <td>{{ $r['teknik'] ?? '' }}</td>
                        <td>{!! $u($r['temuan']) !!}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if (! empty($an['ioc']))
            <h3>6.2 Indikator Kompromi (IoC)</h3>
            <table class="grid kronologi">
                <tr><th style="width:16%">Jenis</th><th style="width:38%">Nilai</th><th>Keterangan</th></tr>
                @foreach ($an['ioc'] as $r)
                    <tr>
                        <td>{{ IC::JENIS_IOC[$r['jenis']] ?? $r['jenis'] }}</td>
                        <td style="font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5pt">{{ $r['nilai'] }}</td>
                        <td>{!! $u($r['keterangan'] ?? '') !!}</td>
                    </tr>
                @endforeach
            </table>
            @if (filled($an['ioc_dibagikan'] ?? null))
                <p class="paragraf" style="margin-top:4pt">{!! $ml($an['ioc_dibagikan']) !!}</p>
            @endif
        @endif

        @if (! empty($an['whys']) || filled($an['akar_masalah'] ?? null))
            <h3>6.3 Akar Masalah (5 Whys)</h3>
            @if (! empty($an['whys']))
                <table class="grid kronologi">
                    @foreach ($an['whys'] as $n => $r)
                        <tr>
                            <td style="width:4%">{{ $n + 1 }}.</td>
                            <td style="width:38%"><b>{{ $r['tanya'] }}</b></td>
                            <td>{!! $u($r['jawab']) !!}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
            @if (filled($an['akar_masalah'] ?? null))
                <p class="paragraf" style="margin-top:6pt"><b>Akar masalah{{ $sementara ? ' (sementara)' : '' }}:</b> {!! $ml($an['akar_masalah']) !!}</p>
            @endif
            @if (filled($an['kontrol_terkait'] ?? null))
                <p class="paragraf"><b>Kontrol ISO/IEC 27001:2022 terkait:</b> {{ $an['kontrol_terkait'] }}</p>
            @endif
        @endif
    @endif

    {{-- ================= 7 ================= --}}
    @php
        $pn = (isset($insiden['penanganan']) && array_key_exists('penanganan', IC::tabsAktif($insiden)))
            ? $insiden['penanganan'] : null;
        $adaBukti = $pn && ! empty($pn['bukti']);
    @endphp
    @if ($pn)
        <h2 class="lepas">7. TINDAKAN PENANGANAN</h2>
        <p class="paragraf">Penanganan mengikuti fase ISO/IEC 27035: penanggulangan, pemberantasan, dan pemulihan.
            Bukti diamankan sebelum sistem dibersihkan.</p>

        <h3>7.1 Penanggulangan (Containment)</h3>
        <p class="paragraf">{!! $ml($pn['penanggulangan']) !!}</p>

        @if ($adaBukti || filled($pn['catatan_bukti'] ?? null))
            <h3>7.2 Pengamanan Bukti</h3>
            <p class="paragraf">
                @if (filled($pn['catatan_bukti'] ?? null)){!! $ml($pn['catatan_bukti']) !!}@endif
                @if ($adaBukti) {{ count($pn['bukti']) }} bukti tercatat pada Lampiran A (Daftar Bukti).@endif
            </p>
        @endif

        @if (filled($pn['pemberantasan'] ?? null))
            <h3>7.3 Pemberantasan (Eradication)</h3>
            <p class="paragraf">{!! $ml($pn['pemberantasan']) !!}</p>
        @endif

        @if (filled($pn['pemulihan'] ?? null))
            <h3>7.4 Pemulihan (Recovery)</h3>
            <p class="paragraf">{!! $ml($pn['pemulihan']) !!}</p>
        @endif
    @endif

    {{-- Bagian berikutnya (Pelajaran & Tindak Lanjut) ditambahkan di sini --}}

    {{-- ================= PENGESAHAN ================= --}}
    <div class="blok-pengesahan">
        <div class="pengesahan">
            {{ $penetapan['lokasi'] }}, {{ $penetapan['tanggal'] }}
        </div>

        <table class="ttd">
            <tr class="peran">
                <td>Disusun oleh,<br>Ketua Tim Penanganan Insiden</td>
                <td>Disetujui oleh,<br>Pimpinan Instansi Pemilik Aset</td>
            </tr>
            <tr>
                <td>{!! LabelTte::kolom('ketua', $label, 210, '', $inf['ketua_jabatan'] ?? null, $inf['ketua_instansi'] ?? null, $inf['ketua_nama'] ?? null) !!}</td>
                <td>{!! LabelTte::kolom('pimpinan', $label, 210, '', $inf['pimpinan_jabatan'] ?? null, $inf['pemilik_aset'] ?? null, $inf['pimpinan_nama'] ?? null) !!}</td>
            </tr>
        </table>
    </div>

    {{-- ================= LAMPIRAN ================= --}}
    @if ($adaBukti)
        <h2 class="halaman-baru lepas">LAMPIRAN A. DAFTAR BUKTI (CHAIN OF CUSTODY)</h2>
        <p class="paragraf">Bukti disimpan di luar dokumen ini. Keaslian setiap bukti dapat diperiksa dengan mencocokkan
            nilai hash SHA-256 berikut.</p>
        <table class="grid kronologi">
            <tr>
                <th style="width:4%">No</th>
                <th style="width:24%">Bukti</th>
                <th style="width:14%">Waktu Diambil</th>
                <th style="width:16%">Pengambil</th>
                <th style="width:18%">Lokasi Penyimpanan</th>
                <th>Hash SHA-256</th>
            </tr>
            @foreach ($pn['bukti'] as $n => $r)
                @php $wb = IC::waktu($r['waktu']); @endphp
                <tr>
                    <td>{{ $n + 1 }}</td>
                    <td>{{ $r['nama'] }}</td>
                    <td>{{ $wb ? $wb->locale('id')->translatedFormat('d M Y, H.i') : $r['waktu'] }}</td>
                    <td>{{ $r['pengambil'] }}</td>
                    <td>{{ $r['lokasi'] }}</td>
                    <td class="hash">{{ $r['hash'] ? implode(' ', str_split($r['hash'], 16)) : '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="catatan-klas">
        Dokumen ini berklasifikasi <b>TERBATAS</b>. Dilarang menggandakan atau menyebarluaskan sebagian maupun seluruh isi
        dokumen kepada pihak yang tidak berwenang.
    </div>

</body>

</html>
