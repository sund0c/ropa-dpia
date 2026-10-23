@php
    use App\Http\Controllers\PengujianController as PC;

    $alatDipilih = (array) old('alat', $d['alat'] ?? []);
    $prinsip     = fn (string $p) => session()->hasOldInput() ? (bool) old("prinsip.$p") : (bool) ($d['prinsip'][$p] ?? false);

    // Contoh: aplikasi fiktif, diuji di staging dengan izin tertulis
    $contoh = [
        'nomor_laporan'    => 'LPK/2026/10/001',
        'nama_aplikasi'    => 'Aplikasi Layanan Perizinan Dinas XYZ',
        'url'              => 'https://staging-perizinan.dinasxyz.baliprov.go.id',
        'lingkungan'       => 'staging',
        'kode_aset'        => 'PL-000021',
        'teknologi'        => 'Laravel 11, PHP 8.3, MariaDB, Nginx',
        'pemilik_aset'     => 'Dinas XYZ Provinsi Bali',
        'pimpinan_nama'    => 'I Made Contoh',
        'pimpinan_jabatan' => 'Kepala Dinas',
        'nomor_surat'      => '800/123/DXYZ/2026',
        'tanggal_surat'    => '2026-10-12',
        'pemberi_izin'     => 'Kepala Dinas XYZ Provinsi Bali',
        'periode_mulai'    => '2026-10-14',
        'periode_selesai'  => '2026-10-16',
        'tim_nama'         => 'Tim Pengujian Keamanan Bidang Persandian',
        'ketua_nama'       => 'I Putu Contoh',
        'ketua_jabatan'    => 'Ketua Tim Penguji',
        'ketua_instansi'   => 'Dinas Kominfos Provinsi Bali',
        'anggota'          => "1. I Kadek Contoh\n2. Ni Luh Contoh",
        'peran'            => "1. Anonim (tanpa login)\n2. Pemohon (akun uji: pemohon01, pemohon02)\n3. Verifikator (akun uji: verif01)\n4. Admin (akun uji: admin-uji)",
        'cakupan'          => "1. Halaman login, registrasi, dan reset password\n2. Modul pengajuan izin (/pengajuan/*) dan unggah berkas\n3. Modul verifikasi (/verifikasi/*)\n4. Panel admin (/admin/*)\n5. REST API /api/v1/*",
        'luar_cakupan'     => "1. Server basis data dan infrastruktur jaringan\n2. Integrasi pihak ketiga (gateway pembayaran)\n3. Uji beban / DoS",
        'alat'             => ['burp', 'devtools', 'curl', 'tls'],
        'alat_lainnya'     => '',
    ];
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .label-klas { display: inline-block; padding: .1rem .5rem; border: 1px solid #b91c1c; color: #b91c1c; font-weight: bold;
                  font-size: .75rem; letter-spacing: 1px; border-radius: 4px; }
    .cek-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .35rem 1rem; margin: .25rem 0 .75rem; }
    .prinsip { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1rem; }
    .prinsip .check { padding: .55rem .7rem; border: 1px solid #e5e7eb; border-radius: 6px; background: #fafafa; line-height: 1.45; }
    @media (max-width: 640px) { .cek-grid { grid-template-columns: 1fr; } }
</style>

<div class="contoh-bar">
    <span>Laporan dicetak dengan klasifikasi <span class="label-klas">TERBATAS</span>.
        Muat contoh pengujian aplikasi perizinan fiktif untuk melihat cara pengisiannya.</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

{{-- ================= A ================= --}}
<section class="panel">
    <h2 class="panel-head">A. Aplikasi yang Diuji</h2>

    <div class="row">
        <x-aset-field name="nomor_laporan" label="Nomor Laporan" :value="$d['nomor_laporan'] ?? null" required maxlength="50"
            autocomplete="off" style="text-transform:uppercase" hint="Contoh: LPK/2026/10/001" />
        <x-aset-field name="kode_aset" label="Kode Aset" :value="$d['kode_aset'] ?? null" maxlength="9" autocomplete="off"
            style="text-transform:uppercase; letter-spacing:.5px" hint="Opsional. Kode dari menu A, contoh: PL-000021" />
    </div>

    <x-aset-field name="nama_aplikasi" label="Nama Aplikasi" :value="$d['nama_aplikasi'] ?? null" required maxlength="255"
        hint="Contoh: Aplikasi Layanan Perizinan Dinas XYZ" />

    <div class="row">
        <x-aset-field name="url" label="URL Aplikasi" type="url" :value="$d['url'] ?? null" required maxlength="255"
            hint="Alamat yang diuji. Contoh: https://staging-perizinan.dinasxyz.baliprov.go.id" />
        <x-aset-field name="lingkungan" label="Lingkungan Pengujian" type="select" :options="PC::LINGKUNGAN"
            :value="$d['lingkungan'] ?? null" required data-lingkungan
            hint="Utamakan staging. Uji yang mengubah data tidak boleh dilakukan pada data produksi nyata." />
    </div>

    <div class="alert warn" data-peringatan-produksi @if (old('lingkungan', $d['lingkungan'] ?? '') !== 'produksi') hidden @endif>
        <strong>Pengujian di produksi:</strong> gunakan akun dan objek uji, jangan replay request POST/PUT/DELETE/PATCH ke
        data nyata, dan hindari uji yang dapat mengganggu layanan (lockout massal, input besar, SSRF).
    </div>

    <x-aset-field name="teknologi" label="Teknologi" :value="$d['teknologi'] ?? null" maxlength="255"
        hint="Opsional. Contoh: Laravel 11, PHP 8.3, MariaDB, Nginx" />

    <x-aset-field name="pemilik_aset" label="Nama Instansi Pemilik Aplikasi" :value="$d['pemilik_aset'] ?? null" required maxlength="255"
        hint="Contoh: Dinas XYZ Provinsi Bali" />

    <div class="row">
        <x-aset-field name="pimpinan_nama" label="Nama Pimpinan Instansi Pemilik Aplikasi" :value="$d['pimpinan_nama'] ?? null"
            required maxlength="255" hint="Penandatangan laporan. Contoh: I Made Contoh" />
        <x-aset-field name="pimpinan_jabatan" label="Jabatan Pimpinan" :value="$d['pimpinan_jabatan'] ?? null"
            required maxlength="255" hint="Contoh: Kepala Dinas" />
    </div>
</section>

{{-- ================= B ================= --}}
<section class="panel">
    <h2 class="panel-head">B. Otorisasi Pengujian <small>wajib</small></h2>
    <p class="hint" style="margin-top:0">
        Pengujian hanya boleh dilakukan berdasarkan izin tertulis dari pemilik aplikasi. Catat surat izinnya di sini.
    </p>

    <div class="row">
        <x-aset-field name="nomor_surat" label="Nomor Surat Otorisasi" :value="$d['nomor_surat'] ?? null" required maxlength="255"
            hint="Contoh: 800/123/DXYZ/2026" />
        <x-aset-field name="tanggal_surat" label="Tanggal Surat Otorisasi" type="date" :value="$d['tanggal_surat'] ?? null" required />
    </div>

    <x-aset-field name="pemberi_izin" label="Pemberi Izin" :value="$d['pemberi_izin'] ?? null" required maxlength="255"
        hint="Jabatan dan instansi penanda tangan surat izin. Contoh: Kepala Dinas XYZ Provinsi Bali" />

    <div class="row">
        <x-aset-field name="periode_mulai" label="Tanggal Mulai Pengujian" type="date" :value="$d['periode_mulai'] ?? null" required
            hint="Tidak boleh sebelum tanggal surat otorisasi." />
        <x-aset-field name="periode_selesai" label="Tanggal Selesai Pengujian" type="date" :value="$d['periode_selesai'] ?? null" required />
    </div>
</section>

{{-- ================= C ================= --}}
<section class="panel">
    <h2 class="panel-head">C. Tim Penguji</h2>

    <x-aset-field name="tim_nama" label="Nama Tim Penguji" :value="$d['tim_nama'] ?? null" required maxlength="255"
        hint="Contoh: Tim Pengujian Keamanan Bidang Persandian" />

    <div class="row">
        <x-aset-field name="ketua_nama" label="Nama Ketua Tim Penguji" :value="$d['ketua_nama'] ?? null" required maxlength="255"
            hint="Penandatangan laporan. Contoh: I Putu Contoh" />
        <x-aset-field name="ketua_jabatan" label="Jabatan Ketua Tim" :value="$d['ketua_jabatan'] ?? null" required maxlength="255"
            hint="Contoh: Ketua Tim Penguji" />
    </div>

    <x-aset-field name="ketua_instansi" label="Instansi Ketua Tim" :value="$d['ketua_instansi'] ?? null" required maxlength="255"
        hint="Contoh: Dinas Kominfos Provinsi Bali" />

    <x-aset-field name="anggota" label="Anggota Tim" type="textarea" rows="2" :value="$d['anggota'] ?? null" maxlength="1000"
        hint="Opsional, satu nama per baris." />
</section>

{{-- ================= D ================= --}}
<section class="panel">
    <h2 class="panel-head">D. Ruang Lingkup</h2>

    <x-aset-field name="peran" label="Peran / Akun yang Diuji" type="textarea" rows="3" :value="$d['peran'] ?? null"
        required maxlength="1000"
        hint="Satu per baris, termasuk kondisi anonim sebagai pembanding. Pakai akun uji, bukan akun pengguna nyata. Contoh: 1. Anonim  2. Pemohon (pemohon01, pemohon02)  3. Admin (admin-uji)" />

    <x-aset-field name="cakupan" label="Cakupan Pengujian" type="textarea" rows="4" :value="$d['cakupan'] ?? null"
        required maxlength="3000"
        hint="Modul, halaman, atau endpoint yang diuji. Contoh: 1. Login & reset password  2. Modul pengajuan (/pengajuan/*)  3. REST API /api/v1/*" />

    <x-aset-field name="luar_cakupan" label="Di Luar Cakupan" type="textarea" rows="2" :value="$d['luar_cakupan'] ?? null"
        maxlength="2000"
        hint="Opsional. Yang sengaja tidak diuji. Contoh: Infrastruktur jaringan, integrasi pihak ketiga, uji beban/DoS" />

    <div class="field" style="margin-bottom:.25rem">
        <label>Alat yang Dipakai <span class="req">*</span></label>
        <small class="hint">Semua dioperasikan manual, bukan pemindaian otomatis.</small>
    </div>
    <div class="cek-grid">
        @foreach (PC::ALAT as $k => $t)
            <label class="check">
                <input type="checkbox" name="alat[]" value="{{ $k }}" @checked(in_array($k, $alatDipilih, true))>
                <span>{{ $t }}</span>
            </label>
        @endforeach
    </div>
    @error('alat') <small class="error" style="display:block; margin:-.4rem 0 .75rem">{{ $message }}</small> @enderror

    <x-aset-field name="alat_lainnya" label="Alat Lainnya" :value="$d['alat_lainnya'] ?? null" maxlength="255"
        hint="Opsional. Contoh: Postman, sqlmap (hanya mode deteksi)" />
</section>

{{-- ================= E ================= --}}
<section class="panel">
    <h2 class="panel-head">E. Persiapan &amp; Prinsip Pengujian <small>wajib disetujui</small></h2>
    <p class="hint" style="margin-top:0">Prinsip berikut berlaku untuk semua butir uji di Tab A01–A10.</p>

    <div class="prinsip">
        @foreach (PC::PRINSIP as $k => $t)
            <div>
                <label class="check">
                    <input type="checkbox" name="prinsip[{{ $k }}]" value="1" @checked($prinsip($k))>
                    <span>{{ $t }}</span>
                </label>
                @error("prinsip.$k") <small class="error">{{ $message }}</small> @enderror
            </div>
        @endforeach
    </div>
</section>

<script>
(function () {
    const contoh = @json($contoh);
    const form   = document.querySelector('[data-lingkungan]').form;
    const ling   = form.querySelector('[data-lingkungan]');

    const tampil = () => { form.querySelector('[data-peringatan-produksi]').hidden = ling.value !== 'produksi'; };

    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        const ada = Object.keys(contoh).some(n => form.elements[n] && typeof form.elements[n].value === 'string'
                                                   && form.elements[n].value.trim() !== '');
        if (ada && !confirm('Isian Tab I yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        for (const [n, v] of Object.entries(contoh)) {
            if (n === 'alat') {
                form.querySelectorAll('input[name="alat[]"]').forEach(c => c.checked = v.includes(c.value));
            } else if (form.elements[n]) {
                form.elements[n].value = v;
            }
        }
        // Prinsip sengaja tidak dicentang otomatis: penguji harus membacanya sendiri
        tampil();
    });

    ['nomor_laporan', 'kode_aset'].forEach(n => {
        form.elements[n].addEventListener('input', (e) => { e.target.value = e.target.value.toUpperCase().replace(/\s+/g, ''); });
    });

    ling.addEventListener('change', tampil);
    window.addEventListener('pageshow', tampil);
    tampil();
})();
</script>
