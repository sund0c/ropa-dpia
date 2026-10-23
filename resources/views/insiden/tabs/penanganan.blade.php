@php
    use App\Http\Controllers\InsidenController as IC;

    $jenisLaporan = $insiden['informasi']['jenis_laporan'] ?? 'akhir';
    $akhir        = $jenisLaporan === 'akhir';

    $bukti = session()->hasOldInput()
        ? array_values(array_filter((array) old('bukti', []), 'is_array'))
        : ($d['bukti'] ?? []);
    if ($bukti === []) $bukti = [[]];

    // ---------------- Contoh kasus deface WordPress ----------------
    $b = fn ($n, $w, $p, $l, $h) => ['nama' => $n, 'waktu' => $w, 'pengambil' => $p, 'lokasi' => $l, 'hash' => $h];
    $daftarBukti = [
        $b('Tangkapan layar halaman defacement', '2026-10-14T07:50', 'Operator Situs Dinas XYZ', 'Folder tiket INC-20261014-001 (penyimpanan internal TTIS)', '8df48fabaeca42472877a257d59d4310e122e847f07493d5567a02dc7187b94c'),
        $b('Snapshot VM web-dinasxyz', '2026-10-14T08:25', 'Anggota TTIS (I Kadek Contoh)', 'Storage forensik Pusat Data (terenkripsi)', '45d884aa5d097ba97da09d46abe42f8366bf532d420648c2d5507db3c7cdaf4c'),
        $b('access.log dan error.log Nginx (11–14 Okt 2026)', '2026-10-14T08:25', 'Anggota TTIS (I Kadek Contoh)', 'Storage forensik Pusat Data (terenkripsi)', '7f78fc8f99bd1f1d0f329b76d2c02342448622ebe08feed47494ceb18881f84b'),
        $b('Ekspor log aktivitas WordPress', '2026-10-14T08:30', 'Anggota TTIS (I Kadek Contoh)', 'Storage forensik Pusat Data (terenkripsi)', '6f234932f1d7f6ec62f7d624fa82ba6920e92a7468b98eaa0b1be060abfc4d7f'),
        $b('Berkas web shell x.php (salinan, dikemas zip berkata sandi)', '2026-10-14T09:25', 'Anggota TTIS (I Kadek Contoh)', 'Storage forensik Pusat Data (terenkripsi)', '2a995ae3c123935eaabaecfcb02c3a9629a2bc1d91e53a4c62131aa9a9bf26ad'),
    ];

    $penanggulangan = "1. Situs dialihkan ke halaman pemeliharaan agar konten defacement tidak lagi tampil.\n2. Akses /wp-admin dan /wp-login.php diblokir dari internet; tiga IP penyerang diblokir di firewall.\n3. Seluruh sesi login WordPress diputus (kunci salt di wp-config.php diganti).\n4. Kata sandi 4 akun WordPress, akun basis data, dan akun SFTP diganti.";
    $catatanBukti   = 'Bukti diamankan sebelum sistem dibersihkan. Setiap bukti dicatat nilai hash SHA-256-nya sesuai ISO/IEC 27037.';

    $contoh = [
        'perkembangan' => [
            'penanggulangan' => $penanggulangan,
            'catatan_bukti'  => $catatanBukti,
            'bukti'          => $daftarBukti,
            'pemberantasan'  => "1. Web shell x.php dihapus; folder uploads dipindai untuk mencari berkas .php lain.\n2. Integritas inti WordPress diverifikasi dengan wp core verify-checksums.\n3. Pemeriksaan plugin pihak ketiga masih berlangsung.",
            'pemulihan'      => "1. Konten dipulihkan dari cadangan 13 Oktober 2026 pukul 00.00.\n2. Situs kembali daring pukul 11.20 WITA dan dipantau intensif.",
        ],
        'akhir' => [
            'penanggulangan' => $penanggulangan,
            'catatan_bukti'  => $catatanBukti,
            'bukti'          => $daftarBukti,
            'pemberantasan'  => "1. Web shell x.php dihapus; seluruh folder uploads dipindai untuk mencari berkas .php lain (tidak ditemukan).\n2. Integritas inti WordPress diverifikasi dengan wp core verify-checksums; tema dan plugin dipasang ulang dari sumber resmi.\n3. Akun admin dihapus dan diganti akun bernama unik; akun yang tidak aktif dinonaktifkan.\n4. Eksekusi PHP di folder uploads dimatikan melalui konfigurasi Nginx.\n5. Theme/Plugin File Editor dimatikan (DISALLOW_FILE_EDIT = true).",
            'pemulihan'      => "1. Konten dipulihkan dari cadangan 13 Oktober 2026 pukul 00.00, lalu diperiksa operator Dinas XYZ.\n2. Autentikasi dua faktor (TOTP) dan pembatasan percobaan login dipasang dan diwajibkan untuk semua akun.\n3. Enumerasi pengguna melalui ?author= dan REST API /wp/v2/users ditutup.\n4. Situs kembali daring pukul 11.20 WITA, dipantau intensif selama 7 hari tanpa temuan baru.",
        ],
    ];
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .bk-list { display: flex; flex-direction: column; gap: .6rem; }
    .bk { border: 1px solid #e5e7eb; border-left: 4px solid #a7f3d0; border-radius: 8px; padding: .6rem .8rem 0; }
    .bk .field { margin-bottom: .6rem; }
    .bk .field input { width: 100%; box-sizing: border-box; }
    .bk .row { gap: 0 .75rem; }
    .bk-grid { display: grid; grid-template-columns: 1.6fr 1fr auto; gap: 0 .75rem; align-items: start; }
    button.bk-hapus { background: transparent; color: #b91c1c; padding: .3rem .5rem; margin-top: 1.4rem; font-size: 1rem; }
    .bk-tambah { margin: .75rem 0 1rem; }
    @media (max-width: 700px) { .bk-grid { grid-template-columns: 1fr; } button.bk-hapus { margin-top: 0; justify-self: end; } }
</style>

@if (! $akhir)
    <div class="alert warn">
        <strong>Laporan Perkembangan:</strong> cukup isi penanggulangan dan bukti yang sudah diamankan.
        Pemberantasan dan pemulihan diisi sejauh yang sudah dilakukan.
    </div>
@endif

<div class="contoh-bar">
    <span>Contoh penanganan kasus <em>defacement</em> WordPress untuk
        <strong>{{ IC::JENIS_LAPORAN[$jenisLaporan] ?? 'Laporan Akhir' }}</strong>.</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

<p class="hint" style="margin-top:0">
    Tulis tindakan yang <strong>sudah dilakukan</strong>, satu tindakan per baris. Rencana perbaikan jangka panjang
    dicatat di tab Pelajaran &amp; Tindak Lanjut.
</p>

<div id="penanganan">
    {{-- ================= A ================= --}}
    <section class="panel">
        <h2 class="panel-head">A. Penanggulangan <small>Containment</small></h2>
        <x-aset-field name="penanggulangan" label="Tindakan Penanggulangan" type="textarea" rows="5"
            :value="$d['penanggulangan'] ?? null" required maxlength="3000"
            hint="Membatasi dampak dan menghentikan serangan. Contoh: 1. Situs dialihkan ke halaman pemeliharaan  2. Akses /wp-admin diblokir dari internet  3. Seluruh sesi login diputus dan kata sandi diganti" />
    </section>

    {{-- ================= B ================= --}}
    <section class="panel">
        <h2 class="panel-head">B. Pengamanan Bukti <small>dicetak sebagai Lampiran · opsional</small></h2>
        <x-aset-field name="catatan_bukti" label="Catatan Pengamanan Bukti" type="textarea" rows="2"
            :value="$d['catatan_bukti'] ?? null" maxlength="1000"
            hint="Opsional. Contoh: Bukti diamankan sebelum sistem dibersihkan; setiap bukti dicatat nilai hash SHA-256-nya sesuai ISO/IEC 27037." />

        <p class="hint">
            Daftar bukti (<em>chain of custody</em>). File bukti tidak diunggah ke aplikasi ini; cukup catat di mana file disimpan
            dan nilai hash-nya. Contoh menghitung hash: <code>shasum -a 256 access.log</code> (macOS) atau <code>sha256sum access.log</code> (Linux).
        </p>

        @error('bukti') <div class="alert err">{{ $message }}</div> @enderror
        <div class="bk-list" data-bk-list>
            @foreach ($bukti as $i => $row)
                @include('insiden.tabs._bukti-row', ['i' => $i, 'row' => $row])
            @endforeach
        </div>
        <template data-bk-template>
            @include('insiden.tabs._bukti-row', ['i' => '__i__', 'row' => []])
        </template>
        <button type="button" class="secondary bk-tambah" data-bk-tambah>+ Tambah bukti</button>
    </section>

    {{-- ================= C ================= --}}
    <section class="panel">
        <h2 class="panel-head">C. Pemberantasan <small>Eradication{{ $akhir ? '' : ' · opsional' }}</small></h2>
        <x-aset-field name="pemberantasan" label="Tindakan Pemberantasan" type="textarea" rows="5"
            :value="$d['pemberantasan'] ?? null" :required="$akhir" maxlength="3000"
            hint="Menghapus penyebab dan jejak penyerang. Contoh: 1. Web shell dihapus  2. Integritas inti WordPress diverifikasi  3. Akun admin bawaan dihapus" />
    </section>

    {{-- ================= D ================= --}}
    <section class="panel">
        <h2 class="panel-head">D. Pemulihan <small>Recovery{{ $akhir ? '' : ' · opsional' }}</small></h2>
        <x-aset-field name="pemulihan" label="Tindakan Pemulihan" type="textarea" rows="5"
            :value="$d['pemulihan'] ?? null" :required="$akhir" maxlength="3000"
            hint="Mengembalikan layanan dengan aman. Contoh: 1. Konten dipulihkan dari cadangan  2. 2FA diwajibkan  3. Situs kembali daring dan dipantau 7 hari" />
    </section>
</div>

<script>
(function () {
    const root   = document.getElementById('penanganan');
    const form   = root.closest('form');
    const list   = root.querySelector('[data-bk-list]');
    const tpl    = root.querySelector('[data-bk-template]');
    const contoh = @json($contoh);
    const jenis  = @json($jenisLaporan);
    const MAKS   = {{ IC::MAKS_BUKTI }};
    let seq = Date.now();

    function tambah(isi) {
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', String(seq++)));
        const baris = list.lastElementChild;
        if (isi) for (const [k, v] of Object.entries(isi)) {
            const el = baris.querySelector(`[name$="[${k}]"]`);
            if (el) el.value = v ?? '';
        }
        return baris;
    }

    const segarkan = () => { root.querySelector('[data-bk-tambah]').disabled = list.children.length >= MAKS; };

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-bk-tambah]')) { tambah().querySelector('input').focus(); segarkan(); return; }
        const h = e.target.closest('[data-bk-hapus]');
        if (h) {
            const baris = h.closest('[data-bk]');
            // Baris terakhir tidak dihapus, cukup dikosongkan (daftar bukti opsional)
            if (list.children.length > 1) baris.remove();
            else baris.querySelectorAll('input').forEach(x => x.value = '');
            segarkan();
        }
    });

    root.addEventListener('input', (e) => {
        if (e.target.matches('[name$="[hash]"]')) e.target.value = e.target.value.trim().toLowerCase();
    });

    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        const ada = [...root.querySelectorAll('input, textarea')].some(x => x.value.trim() !== '');
        if (ada && !confirm('Isian Tab V yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        const c = contoh[jenis] || contoh.akhir;
        list.innerHTML = '';
        c.bukti.forEach(r => tambah(r));
        ['penanggulangan', 'catatan_bukti', 'pemberantasan', 'pemulihan'].forEach(f => form.elements[f].value = c[f] ?? '');
        segarkan();
    });

    segarkan();
})();
</script>
