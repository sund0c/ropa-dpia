@php
    use App\Http\Controllers\InsidenController as IC;

    $status = (string) old('status', $d['status'] ?? '');
    $sumber = (string) old('sumber_deteksi', $d['sumber_deteksi'] ?? '');

    // Contoh kasus: defacement situs WordPress akibat kredensial bawaan (admin:admin) tanpa 2FA.
    // Isi contoh menyesuaikan jenis laporan: makin awal laporan, makin sedikit yang sudah diketahui.
    $contohDasar = [
        'nomor_tiket'      => 'INC-20261014-001',
        'judul'            => 'Perubahan Tampilan (Defacement) Situs Web dinasxyz.baliprov.go.id',
        'nama_aset'        => 'Situs Web Dinas XYZ (dinasxyz.baliprov.go.id)',
        'kode_aset'        => 'PL-000012',
        'lokasi_aset'      => "Pusat Data Pemprov Bali, VM web-dinasxyz\nUbuntu 22.04, Nginx, PHP 8.2, WordPress 6.x, MariaDB",
        'pemilik_aset'     => 'Dinas XYZ Provinsi Bali',
        'pimpinan_nama'    => 'I Made Contoh',
        'pimpinan_jabatan' => 'Kepala Dinas',
        'waktu_deteksi'    => '2026-10-14T07:42',
        'waktu_lapor'      => '2026-10-14T07:55',
        'sumber_deteksi'   => 'masyarakat',
        'sumber_lainnya'   => '',
        'pelapor'          => 'Operator Situs Dinas XYZ',
        'tim_nama'         => 'Tim Tanggap Insiden Siber (TTIS) Pemerintah Provinsi Bali',
        'ketua_nama'       => 'I Putu Contoh',
        'ketua_jabatan'    => 'Ketua TTIS',
        'ketua_instansi'   => 'Dinas Kominfos Provinsi Bali',
    ];

    $contoh = [
        // Beberapa jam setelah terdeteksi: penyebab belum pasti, situs belum pulih
        'awal' => $contohDasar + [
            'nomor_laporan'    => 'LPI/2026/10/001-A',
            'jenis_laporan'    => 'awal',
            'status'           => 'ditangani',
            'waktu_kejadian'   => '2026-10-14T07:42',
            'waktu_pulih'      => '',
            'waktu_tutup'      => '',
            'pihak_diberitahu' => "1. Kepala Dinas XYZ Provinsi Bali\n2. Kepala Dinas Kominfos Provinsi Bali",
            'ringkasan'        => 'Pada 14 Oktober 2026 pukul 07.42 WITA diterima laporan masyarakat bahwa halaman utama situs web dinasxyz.baliprov.go.id berubah menjadi pesan defacement. Dugaan sementara, pihak tidak berwenang masuk melalui dasbor administrator WordPress. Sebagai tindakan awal, situs dialihkan ke halaman pemeliharaan dan akses ke halaman login administrator diblokir dari internet. Penyebab pasti, cakupan, dan dampak terhadap data masih dalam penyelidikan.',
        ],
        // Situs sudah pulih dan dipantau; analisis belum tuntas
        'perkembangan' => $contohDasar + [
            'nomor_laporan'    => 'LPI/2026/10/001-P1',
            'jenis_laporan'    => 'perkembangan',
            'status'           => 'pulih',
            'waktu_kejadian'   => '2026-10-14T02:17',
            'waktu_pulih'      => '2026-10-14T11:20',
            'waktu_tutup'      => '',
            'pihak_diberitahu' => "1. Kepala Dinas XYZ Provinsi Bali\n2. Kepala Dinas Kominfos Provinsi Bali\n3. BSSN melalui kanal aduan siber (sebagai laporan)",
            'ringkasan'        => 'Pada 14 Oktober 2026 pukul 07.42 WITA, halaman utama situs web dinasxyz.baliprov.go.id diketahui telah diganti dengan pesan defacement. Hasil analisis sementara menunjukkan penyerang login ke dasbor WordPress memakai akun bawaan "admin" dengan kata sandi sangat lemah dan tanpa autentikasi dua faktor, lalu mengubah berkas tema dan menanam web shell yang telah dihapus. Situs dipulihkan pukul 11.20 WITA dan dalam pemantauan intensif. Pemeriksaan kemungkinan akses ke basis data masih berlangsung.',
        ],
        // Penanganan selesai, akar masalah dan dampak sudah disimpulkan
        'akhir' => $contohDasar + [
            'nomor_laporan'    => 'LPI/2026/10/001',
            'jenis_laporan'    => 'akhir',
            'status'           => 'ditutup',
            'waktu_kejadian'   => '2026-10-14T02:17',
            'waktu_pulih'      => '2026-10-14T11:20',
            'waktu_tutup'      => '2026-10-21T15:00',
            'pihak_diberitahu' => "1. Kepala Dinas XYZ Provinsi Bali\n2. Kepala Dinas Kominfos Provinsi Bali\n3. BSSN melalui kanal aduan siber (sebagai laporan)",
            'ringkasan'        => 'Pada 14 Oktober 2026 pukul 07.42 WITA, halaman utama situs web dinasxyz.baliprov.go.id diganti oleh pihak tidak berwenang dengan pesan defacement. Penyerang masuk ke dasbor WordPress memakai akun bawaan "admin" dengan kata sandi yang sangat lemah, tanpa autentikasi dua faktor (2FA/OTP) dan tanpa pembatasan percobaan login, lalu mengubah berkas tema dan menanam web shell. Situs dipulihkan pukul 11.20 WITA. Tidak ditemukan bukti kebocoran data pribadi. Akar masalah adalah tidak adanya standar pengelolaan kredensial akun administrator CMS.',
        ],
    ];

    $hintRingkasan = [
        ''             => '3–5 kalimat untuk pimpinan. Pilih Jenis Laporan untuk melihat panduan isinya.',
        'awal'         => 'Laporan Awal: apa yang diketahui sejauh ini, dugaan sementara, dan tindakan awal. Tulis yang belum pasti sebagai "dugaan" atau "masih diselidiki"; kesimpulan belum diperlukan.',
        'perkembangan' => 'Laporan Perkembangan: temuan sementara, tindakan yang sudah dilakukan, status pemulihan, dan hal yang masih diselidiki.',
        'akhir'        => 'Laporan Akhir: apa yang terjadi, cara penyerang masuk, dampak, waktu pemulihan, dan akar masalah. Dicetak sebagai pembuka Ringkasan Eksekutif.',
    ];
    $jenis = (string) old('jenis_laporan', $d['jenis_laporan'] ?? '');
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .durasi { margin: -.25rem 0 1rem; padding: .55rem .8rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .88rem; }
    .durasi div { display: flex; justify-content: space-between; gap: 1rem; padding: .1rem 0; }
    .durasi b { white-space: nowrap; }
    .label-klas { display: inline-block; padding: .1rem .5rem; border: 1px solid #b91c1c; color: #b91c1c; font-weight: bold;
                  font-size: .75rem; letter-spacing: 1px; border-radius: 4px; }
</style>

<div class="contoh-bar">
    <span>Laporan dicetak dengan klasifikasi <span class="label-klas">TERBATAS</span>. Belum pernah menyusun laporan insiden?
        Pilih <strong>Jenis Laporan</strong>, lalu muat contoh kasus <em>defacement</em> situs WordPress sesuai jenis laporan tersebut.</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

{{-- ================= A ================= --}}
<section class="panel">
    <h2 class="panel-head">A. Identitas Laporan</h2>

    <div class="row">
        <x-aset-field name="nomor_laporan" label="Nomor Laporan" :value="$d['nomor_laporan'] ?? null" required maxlength="50"
            autocomplete="off" style="text-transform:uppercase" hint="Contoh: LPI/2026/10/001" />
        <x-aset-field name="nomor_tiket" label="Nomor Tiket Insiden" :value="$d['nomor_tiket'] ?? null" maxlength="50"
            autocomplete="off" style="text-transform:uppercase" hint="Opsional. Nomor dari helpdesk/SOC, contoh: INC-20261014-001" />
    </div>

    <x-aset-field name="judul" label="Judul Insiden" :value="$d['judul'] ?? null" required maxlength="255"
        hint="Jenis insiden + aset terdampak. Contoh: Perubahan Tampilan (Defacement) Situs Web dinasxyz.baliprov.go.id" />

    <div class="row">
        <x-aset-field name="jenis_laporan" label="Jenis Laporan" type="select" :options="IC::JENIS_LAPORAN"
            :value="$d['jenis_laporan'] ?? null" required data-jenis
            hint="Awal: segera setelah insiden diketahui (status belum Ditutup). Perkembangan: selama penanganan. Akhir: setelah insiden Ditutup." />
        <x-aset-field name="status" label="Status Insiden" type="select" :options="IC::STATUS"
            :value="$d['status'] ?? null" required data-status />
    </div>
</section>

{{-- ================= B ================= --}}
<section class="panel">
    <h2 class="panel-head">B. Aset Terdampak &amp; Pemilik Aset</h2>

    <div class="row">
        <x-aset-field name="nama_aset" label="Nama Aset Terdampak" :value="$d['nama_aset'] ?? null" required maxlength="255"
            hint="Contoh: Situs Web Dinas XYZ (dinasxyz.baliprov.go.id)" />
        <x-aset-field name="kode_aset" label="Kode Aset" :value="$d['kode_aset'] ?? null" maxlength="9" autocomplete="off"
            style="text-transform:uppercase; letter-spacing:.5px"
            hint="Opsional. Kode dari menu A (Identifikasi Aset TIK), contoh: PL-000012" />
    </div>

    <x-aset-field name="lokasi_aset" label="Lokasi & Spesifikasi Aset" type="textarea" rows="2" :value="$d['lokasi_aset'] ?? null"
        required maxlength="1000"
        hint="Lokasi fisik/virtual dan teknologi yang dipakai. Contoh: Pusat Data Pemprov Bali, VM web-dinasxyz (Ubuntu 22.04, Nginx, PHP 8.2, WordPress 6.x, MariaDB)" />

    <x-aset-field name="pemilik_aset" label="Nama Instansi Pemilik Aset" :value="$d['pemilik_aset'] ?? null" required maxlength="255"
        hint="Contoh: Dinas XYZ Provinsi Bali" />

    <div class="row">
        <x-aset-field name="pimpinan_nama" label="Nama Pimpinan Instansi Pemilik Aset" :value="$d['pimpinan_nama'] ?? null"
            required maxlength="255" hint="Penandatangan laporan. Contoh: I Made Contoh" />
        <x-aset-field name="pimpinan_jabatan" label="Jabatan Pimpinan" :value="$d['pimpinan_jabatan'] ?? null"
            required maxlength="255" hint="Contoh: Kepala Dinas" />
    </div>
</section>

{{-- ================= C ================= --}}
<section class="panel">
    <h2 class="panel-head">C. Waktu Insiden <small>WITA</small></h2>

    <div class="row">
        <x-aset-field name="waktu_kejadian" label="Waktu Kejadian (perkiraan)" type="datetime-local" :value="$d['waktu_kejadian'] ?? null"
            required data-waktu hint="Saat penyerang pertama kali masuk, dari log. Bila belum diketahui (laporan awal), isi sama dengan waktu terdeteksi." />
        <x-aset-field name="waktu_deteksi" label="Waktu Terdeteksi" type="datetime-local" :value="$d['waktu_deteksi'] ?? null"
            required data-waktu hint="Saat insiden pertama kali diketahui. Contoh: 14-10-2026 07.42" />
    </div>

    <div class="row">
        <x-aset-field name="waktu_lapor" label="Waktu Dilaporkan ke Tim" type="datetime-local" :value="$d['waktu_lapor'] ?? null"
            required data-waktu hint="Saat tim penanganan insiden menerima laporan. Contoh: 14-10-2026 07.55" />
        <x-aset-field name="waktu_pulih" label="Waktu Layanan Pulih" type="datetime-local" :value="$d['waktu_pulih'] ?? null"
            data-waktu hint="Wajib bila status Layanan Pulih atau Ditutup. Contoh: 14-10-2026 11.20" />
    </div>

    <div class="row" data-blok-tutup @if ($status !== 'ditutup') hidden @endif>
        <x-aset-field name="waktu_tutup" label="Waktu Insiden Ditutup" type="datetime-local" :value="$d['waktu_tutup'] ?? null"
            data-waktu hint="Saat laporan akhir disetujui dan insiden dinyatakan selesai. Contoh: 21-10-2026 15.00" />
        <div></div>
    </div>

    <div class="durasi" data-durasi hidden></div>
</section>

{{-- ================= D ================= --}}
<section class="panel">
    <h2 class="panel-head">D. Deteksi &amp; Pelaporan</h2>

    <div class="row">
        <x-aset-field name="sumber_deteksi" label="Sumber Deteksi" type="select" :options="IC::SUMBER_DETEKSI"
            :value="$d['sumber_deteksi'] ?? null" required data-sumber />
        <x-aset-field name="pelapor" label="Pelapor" :value="$d['pelapor'] ?? null" required maxlength="255"
            hint="Pihak yang melaporkan ke tim. Contoh: Operator Situs Dinas XYZ" />
    </div>

    <div data-blok-sumber @if ($sumber !== 'lainnya') hidden @endif>
        <x-aset-field name="sumber_lainnya" label="Sumber Deteksi Lainnya" :value="$d['sumber_lainnya'] ?? null" maxlength="255"
            hint="Contoh: Hasil pemindaian kerentanan berkala" />
    </div>

    <x-aset-field name="pihak_diberitahu" label="Pihak yang Diberi Tahu" type="textarea" rows="3" :value="$d['pihak_diberitahu'] ?? null"
        maxlength="1000"
        hint="Opsional, satu per baris. Contoh: 1. Kepala Dinas XYZ  2. Kepala Diskominfos  3. BSSN melalui kanal aduan siber" />
</section>

{{-- ================= E ================= --}}
<section class="panel">
    <h2 class="panel-head">E. Tim Penanganan Insiden</h2>

    <x-aset-field name="tim_nama" label="Nama Tim Penanganan Insiden" :value="$d['tim_nama'] ?? null" required maxlength="255"
        hint="Contoh: Tim Tanggap Insiden Siber (TTIS) Pemerintah Provinsi Bali" />

    <div class="row">
        <x-aset-field name="ketua_nama" label="Nama Ketua Tim" :value="$d['ketua_nama'] ?? null" required maxlength="255"
            hint="Penandatangan laporan. Contoh: I Putu Contoh" />
        <x-aset-field name="ketua_jabatan" label="Jabatan Ketua Tim" :value="$d['ketua_jabatan'] ?? null" required maxlength="255"
            hint="Contoh: Ketua TTIS" />
    </div>

    <x-aset-field name="ketua_instansi" label="Instansi Ketua Tim" :value="$d['ketua_instansi'] ?? null" required maxlength="255"
        hint="Contoh: Dinas Kominfos Provinsi Bali" />
</section>

{{-- ================= F ================= --}}
<section class="panel">
    <h2 class="panel-head">F. Ringkasan Insiden</h2>

    <x-aset-field name="ringkasan" label="Ringkasan Insiden" type="textarea" rows="5" :value="$d['ringkasan'] ?? null"
        required maxlength="3000" :hint="$hintRingkasan[$jenis] ?? $hintRingkasan['']" data-ringkasan />
</section>

<script>
(function () {
    const contoh = @json($contoh);
    const hintR  = @json($hintRingkasan);
    const form   = document.querySelector('[data-status]').form;
    const status = form.querySelector('[data-status]');
    const sumber = form.querySelector('[data-sumber]');
    const jenis  = form.querySelector('[data-jenis]');

    // Status Ditutup hanya untuk Laporan Akhir, dan Laporan Akhir selalu berstatus Ditutup
    function selaraskanStatus(ubah) {
        const akhir = jenis.value === 'akhir';
        status.querySelector('option[value="ditutup"]').disabled = jenis.value !== '' && !akhir;
        if (ubah && akhir) status.value = 'ditutup';
        if (ubah && !akhir && status.value === 'ditutup') status.value = '';
        const hint = form.elements.ringkasan.parentElement.querySelector('.hint');
        if (hint) hint.textContent = hintR[jenis.value] ?? hintR[''];
    }

    function tampil() {
        form.querySelector('[data-blok-tutup]').hidden  = status.value !== 'ditutup';
        form.querySelector('[data-blok-sumber]').hidden = sumber.value !== 'lainnya';
    }

    // Ukuran waktu penanganan, dihitung langsung dari isian waktu
    const durasi = (a, b) => {
        const x = Date.parse(a), y = Date.parse(b);
        if (isNaN(x) || isNaN(y) || y < x) return null;
        let m = Math.round((y - x) / 60000);
        const h = Math.floor(m / 1440); m -= h * 1440;
        const j = Math.floor(m / 60);   m -= j * 60;
        return [h && h + ' hari', j && j + ' jam', m && m + ' menit'].filter(Boolean).join(' ') || '0 menit';
    };

    function hitung() {
        const v = (n) => form.elements[n].value;
        const baris = [
            ['Waktu tinggal penyerang sebelum terdeteksi (dwell time)', durasi(v('waktu_kejadian'), v('waktu_deteksi'))],
            ['Waktu deteksi hingga dilaporkan ke tim',                    durasi(v('waktu_deteksi'), v('waktu_lapor'))],
            ['Waktu pemulihan (terdeteksi hingga layanan pulih)',         durasi(v('waktu_deteksi'), v('waktu_pulih'))],
        ].filter(r => r[1]);

        const kotak = form.querySelector('[data-durasi]');
        kotak.hidden = baris.length === 0;
        kotak.replaceChildren(...baris.map(([k, n]) => {
            const d = document.createElement('div');
            const s = document.createElement('span'); s.textContent = k;
            const b = document.createElement('b');    b.textContent = n;
            d.append(s, b);
            return d;
        }));
    }

    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        const ada = Object.keys(contoh).some(n => form.elements[n] && form.elements[n].value.trim() !== '');
        if (ada && !confirm('Isian Tab I yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        const isi = contoh[jenis.value] || contoh.akhir;
        for (const [n, nilai] of Object.entries(isi)) {
            if (form.elements[n]) form.elements[n].value = nilai;
        }
        selaraskanStatus(false); tampil(); hitung();
    });

    ['nomor_laporan', 'nomor_tiket', 'kode_aset'].forEach(n => {
        form.elements[n].addEventListener('input', (e) => { e.target.value = e.target.value.toUpperCase().replace(/\s+/g, ''); });
    });

    jenis.addEventListener('change', () => { selaraskanStatus(true); tampil(); });
    status.addEventListener('change', tampil);
    sumber.addEventListener('change', tampil);
    form.querySelectorAll('[data-waktu]').forEach(el => el.addEventListener('change', hitung));
    window.addEventListener('pageshow', () => { selaraskanStatus(false); tampil(); hitung(); });
    selaraskanStatus(false); tampil(); hitung();
})();
</script>
