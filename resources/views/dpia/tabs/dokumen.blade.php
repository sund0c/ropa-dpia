@php
    $hasOld = session()->hasOldInput();
    $status = old('status_dokumen', $d['status_dokumen'] ?? 'draf');
    $riwayat = $hasOld
        ? (array) old('riwayat', [])
        : $d['riwayat'] ?? [
                ['versi' => '1.0', 'tanggal' => now()->format('Y-m-d'), 'deskripsi' => 'Penyusunan awal', 'oleh' => ''],
            ];
    if (empty($riwayat)) {
        $riwayat = [['versi' => '', 'tanggal' => '', 'deskripsi' => '', 'oleh' => '']];
    }
@endphp

<x-readonly label="Kode Dokumen" :value="$kode" hint="Dibuat otomatis mengikuti Nomor RoPA sesi ini." />

<x-readonly label="Nama Aktivitas Proses" :value="$ropa['aktivitas']['nama_aktivitas']" hint="Diambil dari RoPA Tab II." />

<x-field name="unit_kerja" label="Unit Kerja/Divisi/Departement" :value="$d['unit_kerja'] ?? null" required
    placeholder="Unit yang bertanggungjawab terhadap layanan/aktifitas Contoh: Bidang Pelayanan Kesehatan" />

<x-readonly label="Kode Referensi RoPA" :value="$ropa['nomor']" />

<div class="row">
    <x-field name="tanggal_penyusunan" label="Tanggal Penyusunan" type="date" :value="$d['tanggal_penyusunan'] ?? now()->format('Y-m-d')" required />
    <x-field name="tanggal_review" label="Tanggal Review Berikutnya" type="date" :value="$d['tanggal_review'] ?? null" required />
</div>

<fieldset class="field">
    <legend>Status Dokumen <span class="req">*</span></legend>
    <div class="radios">
        @foreach ($options['status'] as $key => $label)
            <label class="check">
                <input type="radio" name="status_dokumen" value="{{ $key }}" @checked($status === $key)>
                {{ $label }}
            </label>
        @endforeach
    </div>
    @error('status_dokumen')
        <small class="error">{{ $message }}</small>
    @enderror
</fieldset>

<div class="field repeater" data-repeater>
    <label>Riwayat Perubahan Dokumen <span class="req">*</span></label>
    <small class="hint">Semua kolom wajib diisi. Tanggal riwayat tidak boleh sebelum Tanggal Penyusunan.</small>
    <div class="riwayat-line riwayat-head">
        <span></span><span>Versi</span><span>Tanggal</span><span>Deskripsi Perubahan</span><span>Disusun/Direvisi
            oleh</span><span></span>
    </div>
    <div class="rep-rows" data-rows>
        @foreach ($riwayat as $i => $row)
            @include('dpia.tabs._riwayat-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>
    <template data-template>
        @include('dpia.tabs._riwayat-row', ['i' => '__i__', 'row' => []])
    </template>

    <button type="button" class="secondary small" data-add>+ Tambah riwayat</button>
    @error('riwayat')
        <small class="error">{{ $message }}</small>
    @enderror
</div>
