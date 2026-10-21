@php
    $klas = (string) old('klasifikasi', $d['klasifikasi'] ?? '');
@endphp

<div class="row">
    <x-aset-field name="nama_aset" label="Nama Aset" :value="$d['nama_aset'] ?? null" required maxlength="255" />
    <x-aset-field name="klasifikasi" label="Klasifikasi Aset" type="select" :options="$klasifikasi"
        :value="$d['klasifikasi'] ?? null" required data-klasifikasi />
</div>

<div class="row">
    <x-aset-field name="kode_aset" label="Kode Aset" :value="$d['kode_aset'] ?? null" required
        maxlength="9" autocomplete="off" style="text-transform:uppercase; font-weight:bold; letter-spacing:.5px"
        data-kode-aset
        hint="Kode dari dokumen Identifikasi dan Penilaian Aset TIK (menu A), contoh PL-000001." />
    <div></div>
</div>

<x-aset-field name="pemilik_aset" label="Nama Instansi Pemilik Aset" :value="$d['pemilik_aset'] ?? null" required maxlength="255"
    hint="Contoh: Dinas Kominfos Provinsi Bali" />

<div class="row">
    <x-aset-field name="pimpinan_nama" label="Nama Pimpinan Instansi Pemilik Aset" :value="$d['pimpinan_nama'] ?? null"
        required maxlength="255" hint="Contoh: Gede Pramana" />
    <x-aset-field name="pimpinan_jabatan" label="Jabatan Pemilik Aset" :value="$d['pimpinan_jabatan'] ?? null"
        required maxlength="255" hint="Contoh: Kepala Dinas" />
</div>

<div class="row">
    <x-aset-field name="pj_nama" label="Penanggung Jawab Aset" :value="$d['pj_nama'] ?? null"
        required maxlength="255" hint="Contoh: I Putu Sundika" />
    <x-aset-field name="pj_jabatan" label="Jabatan Penanggung Jawab" :value="$d['pj_jabatan'] ?? null"
        required maxlength="255" hint="Contoh: Kepala Bidang Persandian" />
</div>

<script>
(function () {
    const kodeKlas = @json($kodeKlasifikasi);
    const klas     = document.querySelector('[data-klasifikasi]');
    const kode     = document.querySelector('[data-kode-aset]');

    // Contoh kode menyesuaikan klasifikasi; awalan diisikan otomatis bila kolom kode masih kosong
    function syncContoh() {
        const awalan = kodeKlas[klas.value] || 'PL';
        kode.placeholder = awalan + '-000001';
        if (kode.value.trim() === '' && kodeKlas[klas.value]) kode.value = awalan + '-';
    }

    klas.addEventListener('change', syncContoh);
    kode.addEventListener('input', () => { kode.value = kode.value.toUpperCase().replace(/\s+/g, ''); });
    syncContoh();
})();
</script>
