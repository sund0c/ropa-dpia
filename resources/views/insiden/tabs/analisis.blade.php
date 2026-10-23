@php
    use App\Http\Controllers\InsidenController as IC;

    $jenisLaporan = $insiden['informasi']['jenis_laporan'] ?? 'akhir';
    $akhir        = $jenisLaporan === 'akhir';

    $lama  = session()->hasOldInput();
    $daftar = function (string $grup) use ($lama, $d) {
        $rows = $lama ? array_values(array_filter((array) old($grup, []), 'is_array')) : ($d[$grup] ?? []);
        return $rows === [] ? [[]] : $rows;
    };
    $rantai = $daftar('rantai');
    $ioc    = $daftar('ioc');
    $whys   = $lama ? array_values((array) old('whys', [])) : ($d['whys'] ?? []);

    // ---------------- Contoh kasus deface WordPress ----------------
    $r = fn ($t, $k, $m) => ['taktik' => $t, 'teknik' => $k, 'temuan' => $m];
    $o = fn ($j, $n, $k) => ['jenis' => $j, 'nilai' => $n, 'keterangan' => $k];
    $w = fn ($q, $a) => ['tanya' => $q, 'jawab' => $a];

    $rantaiLengkap = [
        $r('pengintaian', 'T1589.001 Gather Victim Identity Information: Credentials', 'Enumerasi nama pengguna melalui /?author=1 dan REST API /wp-json/wp/v2/users'),
        $r('akses_awal', 'T1110.001 Brute Force: Password Guessing', '1.412 percobaan login; berhasil dengan akun bawaan admin berkata sandi lemah'),
        $r('akses_awal', 'T1078 Valid Accounts', 'Login ke dasbor WordPress memakai akun administrator yang sah'),
        $r('persistensi', 'T1505.003 Server Software Component: Web Shell', 'Berkas x.php di folder wp-content/uploads'),
        $r('penemuan', 'T1082 System Information Discovery', 'Perintah id dan uname -a melalui web shell'),
        $r('dampak', 'T1491.002 Defacement: External Defacement', 'index.php dan header.php tema diganti halaman defacement'),
    ];
    $iocLengkap = [
        $o('ip', '203.0.113.45', 'Login berhasil dan akses web shell'),
        $o('ip', '198.51.100.17, 198.51.100.88', 'Percobaan login massal'),
        $o('berkas', 'wp-content/uploads/2026/10/x.php', 'Web shell PHP (hash SHA-256 pada lampiran bukti)'),
        $o('useragent', 'Mozilla/5.0 (compatible; MSIE 9.0)', 'Dipakai pada seluruh permintaan penyerang'),
        $o('url', 'POST /wp-login.php beruntun; GET /?author=', 'Pola serangan kredensial'),
    ];

    $contoh = [
        'perkembangan' => [
            'ringkasan_teknis' => 'Analisis sementara menunjukkan penyerang tidak memakai celah perangkat lunak. Penyerang menebak kredensial akun bawaan WordPress, lalu memakai Theme File Editor untuk mengganti halaman dan menanam web shell. Pemeriksaan log basis data masih berlangsung.',
            'rantai' => array_slice($rantaiLengkap, 1),
            'ioc'    => array_slice($iocLengkap, 0, 3),
            'ioc_dibagikan' => 'IoC diteruskan ke tim SOC untuk dipantau di seluruh server.',
            'whys'   => [
                $w('Mengapa halaman berubah?', 'Penyerang mengedit berkas tema melalui dasbor WordPress.'),
                $w('Mengapa penyerang bisa masuk dasbor?', 'Login berhasil sebagai admin dengan kata sandi yang sangat lemah.'),
                $w('Mengapa kata sandi itu berhasil?', 'Akun bawaan tidak pernah diganti, tanpa 2FA dan tanpa pembatasan percobaan login.'),
            ],
            'akar_masalah'    => 'Sementara: kredensial bawaan yang lemah tanpa 2FA. Penyebab tidak terdeteksinya serangan masih dianalisis.',
            'kontrol_terkait' => 'A.5.17, A.8.5',
        ],
        'akhir' => [
            'ringkasan_teknis' => 'Penyerang tidak memakai celah perangkat lunak. Penyerang cukup menebak kredensial bawaan, lalu memanfaatkan fitur bawaan WordPress (Theme File Editor dan unggah berkas) untuk mengganti halaman dan menanam web shell. Akses melalui web shell terbatas pada akun layanan web (www-data) tanpa eskalasi hak akses.',
            'rantai' => $rantaiLengkap,
            'ioc'    => $iocLengkap,
            'ioc_dibagikan' => 'IoC dibagikan ke tim SOC untuk dipantau di seluruh server Pemprov Bali dan dilaporkan ke BSSN.',
            'whys'   => [
                $w('Mengapa halaman berubah?', 'Penyerang mengedit berkas tema melalui dasbor WordPress.'),
                $w('Mengapa penyerang bisa masuk dasbor?', 'Ia login sebagai admin dengan kata sandi admin.'),
                $w('Mengapa kata sandi itu berhasil?', 'Akun bawaan dari instalasi awal (2021) tidak pernah diganti; tidak ada 2FA maupun pembatasan percobaan login.'),
                $w('Mengapa tidak terdeteksi lebih awal?', 'Log situs belum dikirim ke SIEM/SOC, sehingga 1.412 kegagalan login tidak memicu peringatan.'),
                $w('Mengapa kontrol dasar ini terlewat?', 'Belum ada standar konfigurasi aman (hardening baseline) CMS, dan situs tidak masuk siklus penilaian keamanan berkala.'),
            ],
            'akar_masalah'    => 'Tidak adanya standar dan penegakan manajemen kredensial untuk aplikasi CMS, diperparah oleh pemantauan log yang belum terintegrasi.',
            'kontrol_terkait' => 'A.5.17 Authentication information; A.8.5 Secure authentication; A.8.15 Logging; A.8.16 Monitoring activities',
        ],
    ];
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .an-list { display: flex; flex-direction: column; gap: .6rem; }
    .an { border: 1px solid #e5e7eb; border-left: 4px solid #93c5fd; border-radius: 8px; padding: .6rem .8rem 0; }
    .an .field { margin-bottom: .6rem; }
    .an .field input, .an .field select { width: 100%; box-sizing: border-box; }
    .an-grid { display: grid; grid-template-columns: 1fr 1.3fr auto; gap: 0 .75rem; align-items: start; }
    button.an-hapus { background: transparent; color: #b91c1c; padding: .3rem .5rem; margin-top: 1.4rem; font-size: 1rem; }
    .an-tambah { margin: .75rem 0 1rem; }
    .why { display: grid; grid-template-columns: 2rem 1fr 1.4fr; gap: 0 .75rem; align-items: start; }
    .why-no { font-weight: bold; color: #1e3a8a; padding-top: .55rem; }
    .why input { width: 100%; box-sizing: border-box; }
    .why .field { margin-bottom: .5rem; }
    @media (max-width: 700px) { .an-grid, .why { grid-template-columns: 1fr; } button.an-hapus { margin-top: 0; justify-self: end; } .why-no { padding: 0; } }
</style>

@if ($jenisLaporan !== 'akhir')
    <div class="alert warn">
        <strong>Laporan Perkembangan:</strong> analisis boleh bersifat sementara. Rantai serangan, IoC, dan akar masalah
        dapat diisi sejauh yang sudah diketahui; kesimpulan akhir disampaikan pada Laporan Akhir.
    </div>
@endif

<div class="contoh-bar">
    <span>Contoh analisis kasus <em>defacement</em> WordPress untuk
        <strong>{{ IC::JENIS_LAPORAN[$jenisLaporan] ?? 'Laporan Akhir' }}</strong>.</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

<datalist id="daftar-teknik">
    @foreach (IC::TEKNIK as $t) <option value="{{ $t }}"></option> @endforeach
</datalist>

<div id="analisis">
    {{-- ================= A ================= --}}
    <section class="panel">
        <h2 class="panel-head">A. Ringkasan Analisis Teknis</h2>
        <x-aset-field name="ringkasan_teknis" label="Ringkasan Analisis Teknis" type="textarea" rows="4"
            :value="$d['ringkasan_teknis'] ?? null" required maxlength="3000"
            hint="Bagaimana serangan terjadi secara teknis, dalam 2–4 kalimat. Contoh: Penyerang tidak memakai celah perangkat lunak; ia menebak kredensial bawaan lalu memakai fitur bawaan WordPress untuk mengganti halaman." />
    </section>

    {{-- ================= B ================= --}}
    <section class="panel">
        <h2 class="panel-head">B. Rantai Serangan <small>MITRE ATT&amp;CK · opsional</small></h2>
        <p class="hint" style="margin-top:0">
            Urutkan dari tahap paling awal. Teknik dapat dipilih dari saran (ketik "T1" atau nama teknik) atau ditulis sendiri.
            Kosongkan bila belum dianalisis.
        </p>
        @error('rantai') <div class="alert err">{{ $message }}</div> @enderror
        <div class="an-list" data-an-list="rantai">
            @foreach ($rantai as $i => $row)
                @include('insiden.tabs._analisis-row', ['grup' => 'rantai', 'i' => $i, 'row' => $row])
            @endforeach
        </div>
        <template data-an-template="rantai">
            @include('insiden.tabs._analisis-row', ['grup' => 'rantai', 'i' => '__i__', 'row' => []])
        </template>
        <button type="button" class="secondary an-tambah" data-an-tambah="rantai">+ Tambah tahap</button>
    </section>

    {{-- ================= C ================= --}}
    <section class="panel">
        <h2 class="panel-head">C. Indikator Kompromi (IoC) <small>opsional</small></h2>
        <p class="hint" style="margin-top:0">
            Jejak penyerang yang dapat dipakai untuk mendeteksi serangan serupa di sistem lain: alamat IP, berkas, hash, pola URL, dan sebagainya.
        </p>
        @error('ioc') <div class="alert err">{{ $message }}</div> @enderror
        <div class="an-list" data-an-list="ioc">
            @foreach ($ioc as $i => $row)
                @include('insiden.tabs._analisis-row', ['grup' => 'ioc', 'i' => $i, 'row' => $row])
            @endforeach
        </div>
        <template data-an-template="ioc">
            @include('insiden.tabs._analisis-row', ['grup' => 'ioc', 'i' => '__i__', 'row' => []])
        </template>
        <button type="button" class="secondary an-tambah" data-an-tambah="ioc">+ Tambah IoC</button>

        <x-aset-field name="ioc_dibagikan" label="Tindak Lanjut IoC" type="textarea" rows="2"
            :value="$d['ioc_dibagikan'] ?? null" maxlength="1000"
            hint="Opsional. Kepada siapa IoC dibagikan. Contoh: Dibagikan ke tim SOC untuk dipantau di seluruh server dan dilaporkan ke BSSN." />
    </section>

    {{-- ================= D ================= --}}
    <section class="panel">
        <h2 class="panel-head">D. Akar Masalah (5 Whys) @if ($akhir) <small>wajib untuk Laporan Akhir</small> @endif</h2>
        <p class="hint" style="margin-top:0">
            Mulai dari dampak yang terlihat, lalu tanyakan "mengapa" berulang kali sampai ditemukan penyebab mendasar
            (biasanya proses, kebijakan, atau kontrol yang tidak ada), bukan sekadar penyebab teknis.
        </p>
        @error('whys') <div class="alert err">{{ $message }}</div> @enderror

        @for ($n = 0; $n < IC::JUMLAH_WHY; $n++)
            <div class="why">
                <span class="why-no">{{ $n + 1 }}.</span>
                <div class="field">
                    <input type="text" name="whys[{{ $n }}][tanya]" value="{{ $whys[$n]['tanya'] ?? '' }}" maxlength="500"
                           placeholder="{{ $n === 0 ? 'Mengapa halaman berubah?' : 'Mengapa …?' }}" aria-label="Pertanyaan {{ $n + 1 }}">
                    @error("whys.$n.tanya") <small class="error">{{ $message }}</small> @enderror
                </div>
                <div class="field">
                    <input type="text" name="whys[{{ $n }}][jawab]" value="{{ $whys[$n]['jawab'] ?? '' }}" maxlength="1000"
                           placeholder="Jawaban" aria-label="Jawaban {{ $n + 1 }}">
                    @error("whys.$n.jawab") <small class="error">{{ $message }}</small> @enderror
                </div>
            </div>
        @endfor

        <x-aset-field name="akar_masalah" label="Kesimpulan Akar Masalah" type="textarea" rows="3"
            :value="$d['akar_masalah'] ?? null" :required="$akhir" maxlength="2000"
            hint="Contoh: Tidak adanya standar dan penegakan manajemen kredensial untuk aplikasi CMS, diperparah oleh pemantauan log yang belum terintegrasi." />

        <x-aset-field name="kontrol_terkait" label="Kontrol ISO/IEC 27001:2022 Terkait" :value="$d['kontrol_terkait'] ?? null" maxlength="500"
            hint="Opsional. Kontrol Annex A yang tidak berjalan. Contoh: A.5.17, A.8.5, A.8.15, A.8.16" />
    </section>
</div>

<script>
(function () {
    const root   = document.getElementById('analisis');
    const form   = root.closest('form');
    const contoh = @json($contoh);
    const jenis  = @json($jenisLaporan);
    const MAKS   = {{ IC::MAKS_BARIS }};
    let seq = Date.now();

    const list = (g) => root.querySelector(`[data-an-list="${g}"]`);

    function tambah(g, isi) {
        const tpl = root.querySelector(`[data-an-template="${g}"]`);
        list(g).insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', String(seq++)));
        const baris = list(g).lastElementChild;
        if (isi) for (const [k, v] of Object.entries(isi)) {
            const el = baris.querySelector(`[name$="[${k}]"]`);
            if (el) el.value = v ?? '';
        }
        return baris;
    }

    function segarkan() {
        ['rantai', 'ioc'].forEach(g => {
            root.querySelector(`[data-an-tambah="${g}"]`).disabled = list(g).children.length >= MAKS;
        });
    }

    root.addEventListener('click', (e) => {
        const t = e.target.closest('[data-an-tambah]');
        if (t) { tambah(t.dataset.anTambah).querySelector('select, input').focus(); segarkan(); return; }

        const h = e.target.closest('[data-an-hapus]');
        if (h) {
            const baris = h.closest('[data-an]');
            const daftar = baris.parentElement;
            // Baris terakhir tidak dihapus, cukup dikosongkan (bagian ini opsional)
            if (daftar.children.length > 1) baris.remove();
            else baris.querySelectorAll('input, select').forEach(x => x.value = '');
            segarkan();
        }
    });

    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        const ada = [...root.querySelectorAll('input, select, textarea')].some(x => x.value.trim() !== '');
        if (ada && !confirm('Isian Tab IV yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        const c = contoh[jenis] || contoh.akhir;

        ['rantai', 'ioc'].forEach(g => { list(g).innerHTML = ''; c[g].forEach(r => tambah(g, r)); });
        for (let n = 0; n < {{ IC::JUMLAH_WHY }}; n++) {
            form.elements[`whys[${n}][tanya]`].value = c.whys[n]?.tanya ?? '';
            form.elements[`whys[${n}][jawab]`].value = c.whys[n]?.jawab ?? '';
        }
        ['ringkasan_teknis', 'ioc_dibagikan', 'akar_masalah', 'kontrol_terkait'].forEach(f => form.elements[f].value = c[f] ?? '');
        segarkan();
    });

    segarkan();
})();
</script>
