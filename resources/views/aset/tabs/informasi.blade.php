@php
    $o = $options;
    $klas = (string) old('klasifikasi', $d['klasifikasi'] ?? '');
    $aktif = $kelompok[$klas] ?? null; // pl | pk | data | sdm | null
    $enkripsi = (string) old('enkripsi', $d['enkripsi'] ?? '');
@endphp

{{-- ============================ A. INFORMASI DASAR ============================ --}}
<section class="panel">
    <h2 class="panel-head">A. Informasi Dasar</h2>

    <div class="row">
        <x-aset-field name="nama_aset" label="Nama Aset" :value="$d['nama_aset'] ?? null" required maxlength="255" />
        <x-aset-field name="klasifikasi" label="Klasifikasi Aset" type="select" :options="$o['klasifikasi']" :value="$d['klasifikasi'] ?? null"
            required data-klasifikasi hint="Field pada bagian B menyesuaikan klasifikasi yang dipilih." />
    </div>

    <div class="row">
        <x-aset-field name="nomor_urut" label="Nomor Urut Aset" :value="$d['nomor_urut'] ?? null" required inputmode="numeric"
            maxlength="6" placeholder="Contoh: 1" data-nomor-urut
            hint="Nomor urut aset pada inventaris instansi (1–999999)." />
        <div class="field">
            <label for="kode_aset">Kode Aset</label>
            <input id="kode_aset" type="text" readonly tabindex="-1" data-kode-aset
                style="background:#f3f4f6; font-weight:bold; letter-spacing:.5px"
                value="{{ \App\Http\Controllers\AsetController::kodeAset(['klasifikasi' => old('klasifikasi', $d['klasifikasi'] ?? ''), 'nomor_urut' => old('nomor_urut', $d['nomor_urut'] ?? '')]) }}"
                placeholder="Tersusun otomatis">
            <small class="hint">Kode klasifikasi + nomor urut 6 digit, contoh PL-000001.</small>
        </div>
    </div>

    <x-aset-field name="pemilik_aset" label="Nama Instansi Pemilik Aset" :value="$d['pemilik_aset'] ?? null" required maxlength="255"
        hint="Contoh: Dinas Kominfos Provinsi Bali" />

    <div class="row">
        <x-aset-field name="pimpinan_nama" label="Nama Pimpinan Instansi Pemilik Aset" :value="$d['pimpinan_nama'] ?? null" required
            maxlength="255" hint="Contoh: Gede Pramana" />
        <x-aset-field name="pimpinan_jabatan" label="Jabatan Pemilik Aset" :value="$d['pimpinan_jabatan'] ?? null" required maxlength="255"
            hint="Contoh: Kepala Dinas" />
    </div>

    <div class="row">
        <x-aset-field name="pj_nama" label="Penanggung Jawab Aset" :value="$d['pj_nama'] ?? null" required maxlength="255"
            hint="Contoh: I Putu Sundika" />
        <x-aset-field name="pj_jabatan" label="Jabatan Penanggung Jawab" :value="$d['pj_jabatan'] ?? null" required maxlength="255"
            hint="Contoh: Kepala Bidang Persandian" />
    </div>

    <x-aset-field name="keterangan" label="Keterangan" type="textarea" :value="$d['keterangan'] ?? null" required maxlength="2000" />
</section>

{{-- ============================ B. KELENGKAPAN DATA ============================ --}}
<section class="panel">
    <h2 class="panel-head">
        B. Kelengkapan Data
        <small data-klas-label>{{ $o['klasifikasi'][$klas] ?? '' }}</small>
    </h2>

    <p class="hint" data-kelompok-kosong @if ($aktif) hidden @endif>
        Pilih <strong>Klasifikasi</strong> pada bagian A terlebih dahulu.
    </p>

    {{-- ---------- Perangkat Lunak ---------- --}}
    <fieldset class="kelompok" data-kelompok="pl" @if ($aktif !== 'pl') hidden disabled @endif>
        <div class="row">
            <x-aset-field name="url" label="URL" type="url" :value="$d['url'] ?? null" required
                placeholder="https://contoh.baliprov.go.id" />
            <x-aset-field name="versi" label="Versi" :value="$d['versi'] ?? null" required maxlength="50"
                placeholder="Contoh: 2.1.0" />
        </div>
        <div class="row">
            <x-aset-field name="lisensi" label="Lisensi" type="select" :options="$o['lisensi']" :value="$d['lisensi'] ?? null"
                required />
            <x-aset-field name="tgl_lisensi_berakhir" label="Tanggal Lisensi Berakhir" type="date" :value="$d['tgl_lisensi_berakhir'] ?? null"
                hint="Opsional" />
        </div>
        <div class="row">
            <x-aset-field name="vendor" label="Vendor/Pengembang" type="select" :options="$o['vendor']" :value="$d['vendor'] ?? null"
                required />
            <x-aset-field name="platform" label="Platform" type="select" :options="$o['platform']" :value="$d['platform'] ?? null"
                required />
        </div>
        <div class="row">
            <x-aset-field name="lead_developer" label="Lead Developer" :value="$d['lead_developer'] ?? null" required maxlength="255" />
            <x-aset-field name="lokasi_hosting" label="Lokasi Hosting" type="select" :options="$o['hosting']"
                :value="$d['lokasi_hosting'] ?? null" required />
        </div>
    </fieldset>

    {{-- ---------- Perangkat Keras & Sarana Penunjang ---------- --}}
    <fieldset class="kelompok" data-kelompok="pk" @if ($aktif !== 'pk') hidden disabled @endif>
        <div class="row">
            <x-aset-field name="merk" label="Merk" :value="$d['merk'] ?? null" required maxlength="100" />
            <x-aset-field name="model" label="Model" :value="$d['model'] ?? null" required maxlength="100" />
        </div>
        <div class="row">
            <x-aset-field name="serial_number" label="Serial Number" :value="$d['serial_number'] ?? null" required maxlength="100" />
            <x-aset-field name="kapasitas" label="Kapasitas" :value="$d['kapasitas'] ?? null" required maxlength="100"
                placeholder="Contoh: 2 TB, 5 kVA, 24 port" />
        </div>
        <div class="row">
            <x-aset-field name="tahun_perolehan" label="Tahun Perolehan" type="number" :value="$d['tahun_perolehan'] ?? null" required
                min="1970" :max="now()->year" step="1" :placeholder="now()->year" />
            <x-aset-field name="kondisi" label="Kondisi" type="select" :options="$o['kondisi']" :value="$d['kondisi'] ?? null"
                required />
        </div>
        <x-aset-field name="lokasi_fisik" label="Lokasi Fisik" :value="$d['lokasi_fisik'] ?? null" required maxlength="255"
            placeholder="Contoh: Ruang Server Lt. 2, Rak A3" />
        <x-aset-field name="spesifikasi" label="Spesifikasi" type="textarea" :value="$d['spesifikasi'] ?? null" required
            maxlength="2000" />
        <x-aset-field name="ip_address" label="IP Address" :value="$d['ip_address'] ?? null" hint="Opsional. IPv4 atau IPv6."
            placeholder="Contoh: 10.10.1.20" />
    </fieldset>

    {{-- ---------- Data dan Informasi ---------- --}}
    <fieldset class="kelompok" data-kelompok="data" @if ($aktif !== 'data') hidden disabled @endif>
        <div class="row">
            <x-aset-field name="bentuk_informasi" label="Bentuk Informasi" type="select" :options="$o['bentuk']"
                :value="$d['bentuk_informasi'] ?? null" required />
            <x-aset-field name="format" label="Format" :value="$d['format'] ?? null" required maxlength="100"
                placeholder="Contoh: PDF, XLSX, basis data MySQL, arsip kertas" />
        </div>
        <div class="row">
            <x-aset-field name="klasifikasi_data" label="Klasifikasi Data" type="select" :options="$o['klasifikasi_data']"
                :value="$d['klasifikasi_data'] ?? null" required />
            <x-aset-field name="retensi" label="Retensi" :value="$d['retensi'] ?? null" required maxlength="100"
                placeholder="Contoh: 5 tahun" />
        </div>
        <div class="row">
            <x-aset-field name="enkripsi" label="Enkripsi" type="select" :options="$o['enkripsi']" :value="$d['enkripsi'] ?? null"
                required data-enkripsi />
            <fieldset class="kelompok" data-metode-enkripsi @if ($aktif !== 'data' || $enkripsi !== 'ada') hidden disabled @endif>
                <x-aset-field name="metode_enkripsi" label="Metode Enkripsi" :value="$d['metode_enkripsi'] ?? null" required
                    maxlength="255" placeholder="Contoh: AES-256 at rest, TLS 1.3 in transit" />
            </fieldset>
        </div>
    </fieldset>

    {{-- ---------- SDM ---------- --}}
    <fieldset class="kelompok" data-kelompok="sdm" @if ($aktif !== 'sdm') hidden disabled @endif>
        <div class="row">
            <x-aset-field name="jabatan" label="Jabatan" :value="$d['jabatan'] ?? null" required maxlength="255" />
            <x-aset-field name="unit_kerja" label="Unit Kerja" :value="$d['unit_kerja'] ?? null" required maxlength="255" />
        </div>
        <div class="row">
            <x-aset-field name="no_hp" label="No. HP" type="tel" :value="$d['no_hp'] ?? null" required
                hint="Contoh: 081234567890 atau +6281234567890" />
            <x-aset-field name="email" label="Email" type="email" :value="$d['email'] ?? null" required
                maxlength="255" />
        </div>
        <div class="row">
            <x-aset-field name="tipe" label="Tipe" type="select" :options="$o['tipe_sdm']" :value="$d['tipe'] ?? null"
                required />
            <x-aset-field name="tgl_kontrak_berakhir" label="Tanggal Kontrak Berakhir" type="date"
                :value="$d['tgl_kontrak_berakhir'] ?? null" hint="Opsional" />
        </div>
        <x-aset-field name="akses_sistem" label="Akses ke Sistem" type="textarea" :value="$d['akses_sistem'] ?? null" required
            maxlength="2000"
            placeholder="Sistem yang dapat diakses beserta tingkat aksesnya. Contoh: PERISAI (admin), SIMPEG (read-only)" />
    </fieldset>
</section>

<script>
    (function() {
        const kelompok = @json($kelompok);
        const label = @json($o['klasifikasi']);
        const klas = document.querySelector('[data-klasifikasi]');
        const enk = document.querySelector('[data-enkripsi]');

        // Tampilkan hanya kelompok sesuai klasifikasi; kelompok lain di-disable agar tidak ikut terkirim
        function syncKelompok() {
            const aktif = kelompok[klas.value] ?? null;
            document.querySelectorAll('[data-kelompok]').forEach(fs => {
                const on = fs.dataset.kelompok === aktif;
                fs.hidden = !on;
                fs.disabled = !on;
            });
            document.querySelector('[data-kelompok-kosong]').hidden = aktif !== null;
            document.querySelector('[data-klas-label]').textContent = label[klas.value] ?? '';
            syncEnkripsi();
        }

        function syncEnkripsi() {
            const fs = document.querySelector('[data-metode-enkripsi]');
            const on = enk.value === 'ada' && klas.value === 'data';
            fs.hidden = !on;
            fs.disabled = !on;
        }

        klas.addEventListener('change', syncKelompok);
        enk.addEventListener('change', syncEnkripsi);
    })();

    // Pratinjau Kode Aset
    const kodeKlas = @json($kodeKlasifikasi);
    const urut = document.querySelector('[data-nomor-urut]');
    const kodeOut = document.querySelector('[data-kode-aset]');

    function syncKode() {
        const awalan = kodeKlas[klas.value];
        const n = parseInt((urut.value || '').replace(/\D/g, ''), 10);
        kodeOut.value = (awalan && n >= 1 && n <= 999999) ? awalan + '-' + String(n).padStart(6, '0') : '';
    }
    klas.addEventListener('change', syncKode);
    urut.addEventListener('input', syncKode);
</script>
