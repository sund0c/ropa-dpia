{{-- Isi Identifikasi & Penilaian Aset TIK (tanpa tanda tangan). Butuh: $aset, $kelengkapan. --}}
@php
    $inf = $aset['informasi'];
    $kr = $aset['kritikalitas'];
    $iv = $aset['iiv'];
    $ks = $aset['kse'] ?? null;

    $ml = fn($t) => nl2br(e((string) $t));
    $tglIso = fn($iso) => \Carbon\Carbon::parse($iso)
        ->timezone(config('app.timezone'))
        ->locale('id')
        ->translatedFormat('d F Y, H:i') . ' WITA';
    $jumlahIiv = array_count_values(array_map(fn($k) => $iv[$k], array_keys(\App\Http\Controllers\AsetController::IIV)));
    $A = \App\Http\Controllers\AsetController::class;
    $Kse = \App\Support\Kse::class;
    $Str = \Illuminate\Support\Str::class;
@endphp

    <h1>IDENTIFIKASI DAN PENILAIAN ASET TIK</h1>

    <table class="meta">
        <tr>
            <td class="k">Kode Aset</td>
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
            <td>{{ $A::KLASIFIKASI[$inf['klasifikasi']] ?? $inf['klasifikasi'] }}</td>
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

    <h3>Kelengkapan Data ({{ $A::KLASIFIKASI[$inf['klasifikasi']] ?? '' }})</h3>
    <table class="kv">
        @foreach ($kelengkapan as $lbl => $nilai)
            <tr>
                <td class="k">{{ $lbl }}</td>
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
        @foreach ($A::CIA as $k => $x)
            <tr>
                <td>{{ $Str::after($x['judul'], '. ') }} ({{ $x['sub'] }})</td>
                <td class="tengah tebal">{{ $A::LEVEL[$kr[$k]] }}</td>
                <td>{{ $x['opsi'][$kr[$k]] }}</td>
            </tr>
        @endforeach
    </table>
    <div class="kesimpulan">Kritikalitas Aset: {{ strtoupper($A::LEVEL[$kr['kritikalitas']]) }} <span
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
        @foreach ($A::IIV as $k => $x)
            <tr>
                <td>{{ $Str::after($x['judul'], '. ') }}</td>
                <td class="tengah tebal">{{ $A::LEVEL_IIV[$iv[$k]] }}</td>
                <td>{{ $x['opsi'][$iv[$k]] }}</td>
            </tr>
        @endforeach
    </table>
    <div class="kesimpulan">
        Status: {{ $A::STATUS_IIV[$iv['status_iiv']] }}
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
            @foreach ($Kse::PERTANYAAN as $n => $p)
                @php $j = $ks["kse_$n"]; @endphp
                <tr>
                    <td class="no">{{ $n }}</td>
                    <td>{{ $p['tanya'] }}</td>
                    <td>{{ $p['opsi'][$j] }}</td>
                    <td class="tengah tebal">{{ $Kse::NILAI[$j] }}</td>
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
            Kategori Sistem Elektronik: {{ strtoupper($Kse::KATEGORI[$ks['kategori']]) }}
            <span style="font-weight:normal">(10–15 Rendah, 16–34 Tinggi, 35–50 Strategis)</span>
        </div>
        <div class="catatan"><strong>Catatan:</strong> {{ $Kse::CATATAN[$ks['kategori']] }}</div>
    @endif

