@php
    use App\Http\Controllers\InsidenController as IC;

    $jenisLaporan = $insiden['informasi']['jenis_laporan'] ?? 'akhir';

    $pil = fn (string $f) => (string) old($f, $d[$f] ?? '');
    $dmp = fn (string $dim, string $f) => (string) old("dampak.$dim.$f", $d['dampak'][$dim][$f] ?? '');
    $err = fn (string $f) => $errors->first($f);

    $opsiKeparahan = collect(IC::KEPARAHAN)->mapWithKeys(fn ($k, $i) => [$i => "$i – {$k['nama']}"])->all();

    // Contoh kasus deface WordPress, menyesuaikan jenis laporan di Tab I
    $contoh = [
        'awal' => [
            'kategori' => 'defacement', 'kategori_lainnya' => '',
            'vektor' => 'belum', 'vektor_lainnya' => '',
            'dampak' => [
                'kerahasiaan'  => [2, 'Dasbor administrator dapat diakses penyerang; akses ke basis data belum dapat dipastikan'],
                'integritas'   => [3, 'Halaman utama diganti; konten resmi pemerintah dipalsukan'],
                'ketersediaan' => [2, 'Situs dialihkan ke halaman pemeliharaan sampai pembersihan selesai'],
                'reputasi'     => [3, 'Tangkapan layar beredar di media sosial'],
                'cakupan'      => [1, 'Sementara 1 situs; server lain di segmen yang sama sedang diperiksa'],
            ],
            'data_pribadi' => 'diduga',
            'jenis_data' => 'Data akun pengguna WordPress (nama pengguna, email dinas, hash kata sandi)',
            'jumlah_subjek' => '± 4 orang (akun pengelola situs)',
            'notifikasi' => 'menunggu', 'tanggal_notifikasi' => '',
            'keparahan' => 3, 'alasan_keparahan' => '',
        ],
        'perkembangan' => [
            'kategori' => 'defacement', 'kategori_lainnya' => '',
            'vektor' => 'kredensial', 'vektor_lainnya' => '',
            'dampak' => [
                'kerahasiaan'  => [1, 'Belum ditemukan bukti data diunduh; pemeriksaan log basis data masih berlangsung'],
                'integritas'   => [3, 'Halaman utama diganti; konten resmi pemerintah dipalsukan'],
                'ketersediaan' => [2, 'Konten asli tidak tersedia ± 3,5 jam'],
                'reputasi'     => [3, 'Tangkapan layar tersebar di media sosial'],
                'cakupan'      => [1, '1 situs; server lain di segmen yang sama tidak terdampak'],
            ],
            'data_pribadi' => 'diduga',
            'jenis_data' => 'Data akun pengguna WordPress (nama pengguna, email dinas, hash kata sandi)',
            'jumlah_subjek' => '4 orang (akun pengelola situs)',
            'notifikasi' => 'menunggu', 'tanggal_notifikasi' => '',
            'keparahan' => 2, 'alasan_keparahan' => '',
        ],
        'akhir' => [
            'kategori' => 'defacement', 'kategori_lainnya' => '',
            'vektor' => 'kredensial', 'vektor_lainnya' => '',
            'dampak' => [
                'kerahasiaan'  => [1, 'Tidak ada bukti data diakses atau diunduh; dasbor hanya memuat konten publik dan daftar 4 akun pengguna'],
                'integritas'   => [3, 'Halaman utama diganti; konten resmi pemerintah dipalsukan'],
                'ketersediaan' => [2, 'Konten asli tidak tersedia ± 3,5 jam pada jam kerja'],
                'reputasi'     => [3, 'Tangkapan layar tersebar di media sosial (± 120 kali dibagikan)'],
                'cakupan'      => [1, '1 situs; server lain di segmen yang sama tidak terdampak'],
            ],
            'data_pribadi' => 'tidak',
            'jenis_data' => '', 'jumlah_subjek' => '',
            'notifikasi' => 'tidak_perlu', 'tanggal_notifikasi' => '',
            'keparahan' => 2, 'alasan_keparahan' => '',
        ],
    ];

    $saranJs = ['keparahan' => collect(IC::KEPARAHAN)->map(fn ($k) => $k['nama'])->all()];
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .dm { border: 1px solid #e5e7eb; border-radius: 8px; padding: .7rem .8rem .1rem; margin-bottom: .75rem; }
    .dm-head { display: grid; grid-template-columns: 1fr 200px; gap: 1rem; align-items: start; }
    .dm-nama { font-weight: bold; color: #1e3a8a; }
    .dm-panduan { font-size: .8rem; color: #6b7280; margin: .2rem 0 .5rem; line-height: 1.4; }
    .dm select, .dm input { width: 100%; box-sizing: border-box; }
    .saran { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: 1rem; padding: .6rem .8rem;
             background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; }
    .saran b { padding: .2rem .7rem; border-radius: 999px; background: #e5e7eb; }
    .saran b[data-lv="1"] { background: #dcfce7; color: #166534; }
    .saran b[data-lv="2"] { background: #fef3c7; color: #92400e; }
    .saran b[data-lv="3"] { background: #fed7aa; color: #9a3412; }
    .saran b[data-lv="4"] { background: #b91c1c; color: #fff; }
    table.kriteria { width: 100%; border-collapse: collapse; font-size: .85rem; margin-bottom: 1rem; }
    table.kriteria th, table.kriteria td { border: 1px solid #e5e7eb; padding: .35rem .5rem; text-align: left; vertical-align: top; }
    table.kriteria th { background: #f3f4f6; }
    @media (max-width: 640px) { .dm-head { grid-template-columns: 1fr; gap: 0; } }
</style>

<div class="contoh-bar">
    <span>Contoh kasus <em>defacement</em> WordPress untuk
        <strong>{{ IC::JENIS_LAPORAN[$jenisLaporan] ?? 'Laporan Akhir' }}</strong> (mengikuti Jenis Laporan di Tab I).</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

{{-- ================= A ================= --}}
<section class="panel">
    <h2 class="panel-head">A. Kategori &amp; Vektor Serangan</h2>

    <div class="row">
        <x-aset-field name="kategori" label="Kategori Insiden" type="select" :options="IC::KATEGORI"
            :value="$d['kategori'] ?? null" required data-kategori />
        <x-aset-field name="vektor" label="Vektor Serangan (cara masuk)" type="select" :options="IC::VEKTOR"
            :value="$d['vektor'] ?? null" required data-vektor
            hint="Pilih “Belum diketahui” bila masih diselidiki (umum pada Laporan Awal)." />
    </div>

    <div class="row">
        <div data-blok-kategori @if ($pil('kategori') !== 'lainnya') hidden @endif>
            <x-aset-field name="kategori_lainnya" label="Kategori Lainnya" :value="$d['kategori_lainnya'] ?? null" maxlength="255" />
        </div>
        <div data-blok-vektor @if ($pil('vektor') !== 'lainnya') hidden @endif>
            <x-aset-field name="vektor_lainnya" label="Vektor Serangan Lainnya" :value="$d['vektor_lainnya'] ?? null" maxlength="255" />
        </div>
    </div>
</section>

{{-- ================= B ================= --}}
<section class="panel">
    <h2 class="panel-head">B. Penilaian Dampak</h2>

    <p class="hint" style="margin-top:0">
        Nilai dampak insiden pada setiap dimensi. Keterangan berisi fakta singkat yang mendasari penilaian.
    </p>

    @foreach (IC::DIMENSI as $dim => $info)
        <div class="dm">
            <div class="dm-head">
                <div>
                    <div class="dm-nama">{{ $info['nama'] }} <span class="req">*</span></div>
                    <div class="dm-panduan">{{ $info['panduan'] }}</div>
                </div>
                <div class="field" style="margin-bottom:.5rem">
                    <select name="dampak[{{ $dim }}][tingkat]" data-dampak="{{ $dim }}" aria-label="Tingkat dampak {{ $info['nama'] }}">
                        <option value="">— Pilih tingkat —</option>
                        @foreach (IC::TINGKAT_DAMPAK as $k => $t)
                            <option value="{{ $k }}" @selected($dmp($dim, 'tingkat') === (string) $k)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @if ($err("dampak.$dim.tingkat")) <small class="error">{{ $err("dampak.$dim.tingkat") }}</small> @endif
                </div>
            </div>
            <div class="field">
                <input name="dampak[{{ $dim }}][keterangan]" value="{{ $dmp($dim, 'keterangan') }}" maxlength="500"
                       placeholder="Keterangan dampak {{ strtolower($info['nama']) }}" aria-label="Keterangan dampak {{ $info['nama'] }}">
                @if ($err("dampak.$dim.keterangan")) <small class="error">{{ $err("dampak.$dim.keterangan") }}</small> @endif
            </div>
        </div>
    @endforeach
</section>

{{-- ================= C ================= --}}
<section class="panel">
    <h2 class="panel-head">C. Data Pribadi &amp; Pemberitahuan (UU PDP)</h2>

    <x-aset-field name="data_pribadi" label="Status Data Pribadi" type="select" :options="IC::DATA_PRIBADI"
        :value="$d['data_pribadi'] ?? null" required data-dp
        hint="Bila terkonfirmasi, pemberitahuan tertulis kepada subjek data dan lembaga wajib paling lambat 3 × 24 jam (UU 27/2022 Pasal 46)." />

    <div data-blok-dp @if (! in_array($pil('data_pribadi'), ['diduga', 'ya'], true)) hidden @endif>
        <div class="row">
            <x-aset-field name="jenis_data" label="Jenis Data Pribadi" :value="$d['jenis_data'] ?? null" maxlength="1000"
                hint="Contoh: Data akun pengguna (nama pengguna, email dinas, hash kata sandi)" />
            <x-aset-field name="jumlah_subjek" label="Perkiraan Jumlah Subjek Data" :value="$d['jumlah_subjek'] ?? null" maxlength="255"
                hint="Opsional. Contoh: ± 4 orang" />
        </div>

        <div class="row">
            <x-aset-field name="notifikasi" label="Status Pemberitahuan" type="select" :options="IC::NOTIFIKASI_PDP"
                :value="$d['notifikasi'] ?? null" data-notif />
            <div data-blok-tgl @if ($pil('notifikasi') !== 'sudah') hidden @endif>
                <x-aset-field name="tanggal_notifikasi" label="Tanggal Pemberitahuan" type="date" :value="$d['tanggal_notifikasi'] ?? null" />
            </div>
        </div>
    </div>
</section>

{{-- ================= D ================= --}}
<section class="panel">
    <h2 class="panel-head">D. Tingkat Keparahan</h2>

    <table class="kriteria">
        <tr><th style="width:18%">Level</th><th>Kriteria</th></tr>
        @foreach (IC::KEPARAHAN as $i => $k)
            <tr><td>{{ $i }} – {{ $k['nama'] }}</td><td>{{ $k['kriteria'] }}</td></tr>
        @endforeach
    </table>

    <div class="saran">
        <span>Saran berdasarkan penilaian dampak dan status data pribadi:</span>
        <b data-saran data-lv="">–</b>
    </div>

    <div class="row">
        <x-aset-field name="keparahan" label="Tingkat Keparahan Ditetapkan" type="select" :options="$opsiKeparahan"
            :value="$d['keparahan'] ?? null" required data-keparahan
            hint="Umumnya sama dengan saran. Bila berbeda, alasan wajib diisi." />
        <div></div>
    </div>

    <div data-blok-alasan hidden>
        <x-aset-field name="alasan_keparahan" label="Alasan Penetapan Keparahan" type="textarea" rows="2"
            :value="$d['alasan_keparahan'] ?? null" maxlength="1000"
            hint="Contoh: Diturunkan ke Sedang karena aset bukan layanan vital dan tidak ada indikasi akses ke basis data." />
    </div>
</section>

<script>
(function () {
    const contoh = @json($contoh);
    const jenis  = @json($jenisLaporan);
    const nama   = @json($saranJs['keparahan']);
    const form   = document.querySelector('[data-kategori]').form;
    const el     = (s) => form.querySelector(s);

    const tingkat = (dim) => parseInt(el(`[data-dampak="${dim}"]`).value, 10) || 0;

    // Sama dengan InsidenController::saranKeparahan()
    function saran() {
        const c = tingkat('kerahasiaan'), i = tingkat('integritas'), a = tingkat('ketersediaan'),
              r = tingkat('reputasi'),   k = tingkat('cakupan'),   dp = el('[data-dp]').value;
        if ((c >= 3 && dp === 'ya') || (a >= 3 && k >= 3)) return 4;
        if (c >= 2 || dp === 'ya' || k >= 2) return 3;
        if (i >= 2 || a >= 2 || r >= 2) return 2;
        return 1;
    }

    const lengkap = () => [...form.querySelectorAll('[data-dampak]')].every(s => s.value !== '') && el('[data-dp]').value !== '';

    function segarkan() {
        const dp = el('[data-dp]').value;
        el('[data-blok-kategori]').hidden = el('[data-kategori]').value !== 'lainnya';
        el('[data-blok-vektor]').hidden   = el('[data-vektor]').value !== 'lainnya';
        el('[data-blok-dp]').hidden       = !['diduga', 'ya'].includes(dp);
        el('[data-blok-tgl]').hidden      = el('[data-notif]').value !== 'sudah';

        const b = el('[data-saran]');
        if (!lengkap()) {
            b.textContent = 'lengkapi penilaian dampak & data pribadi'; b.dataset.lv = '';
            el('[data-blok-alasan]').hidden = true;
            return;
        }
        const s = saran();
        b.textContent = s + ' – ' + nama[s]; b.dataset.lv = s;
        const kp = el('[data-keparahan]').value;
        el('[data-blok-alasan]').hidden = kp === '' || parseInt(kp, 10) === s;
    }

    el('[data-isi-contoh]').addEventListener('click', () => {
        const ada = [...form.querySelectorAll('select, input:not([type=hidden]), textarea')].some(x => x.value.trim() !== '');
        if (ada && !confirm('Isian Tab II yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        const c = contoh[jenis] || contoh.akhir;
        for (const [n, v] of Object.entries(c)) {
            if (n === 'dampak') {
                for (const [dim, [t, ket]] of Object.entries(v)) {
                    form.elements[`dampak[${dim}][tingkat]`].value = t;
                    form.elements[`dampak[${dim}][keterangan]`].value = ket;
                }
            } else if (form.elements[n]) {
                form.elements[n].value = v;
            }
        }
        segarkan();
    });

    form.addEventListener('change', segarkan);
    window.addEventListener('pageshow', segarkan);
    segarkan();
})();
</script>
