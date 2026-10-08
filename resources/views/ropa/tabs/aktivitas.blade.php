<x-field name="nama_aktivitas" label="Nama Aktivitas Pemrosesan" :value="$d['nama_aktivitas'] ?? null" required />

<div class="row">
    <x-field name="penanggung_jawab" label="Nama Penanggung Jawab Layanan/Aktivitas" :value="$d['penanggung_jawab'] ?? null" required />
    <x-field name="jabatan_pj" label="Jabatan" :value="$d['jabatan_pj'] ?? null" required placeholder="Contoh: Kepala Bidang Pelayanan" />
</div>
<small class="hint" style="display:block; margin:-.6rem 0 1rem">
    Nama ini akan tercantum pada kolom tanda tangan RoPA dan DPIA.
</small>

<x-repeater name="tahapan" label="Deskripsi Aktivitas Pemrosesan" :items="$d['tahapan'] ?? []" required
    hint="Masukkan seluruh tahapan aktivitas yang dilakukan. Satu tahapan satu isian."
    placeholder="Contoh: Pemohon mengisi formulir pendaftaran daring" add-label="+ Tambah tahapan" />

<x-field name="tujuan" label="Tujuan Pemrosesan" type="textarea" :value="$d['tujuan'] ?? null" required />

@php $dipilih = old('dasar', $d['dasar'] ?? []); @endphp
<div class="field">
    <label>Dasar Pemrosesan <span class="req">*</span>
        <small class="hint">(Pasal 20 ayat (2) UU PDP)</small>
    </label>
    <small class="hint">Boleh pilih lebih dari satu.</small>
    <div class="checks">
        @foreach ($options['dasar'] as $key => $label)
            <label class="check">
                <input type="checkbox" name="dasar[]" value="{{ $key }}" @checked(in_array($key, $dipilih, true))>
                <span>{{ $label }}
                    <small class="hint">({{ \App\Http\Controllers\RopaController::PASAL_DASAR[$key] ?? '' }})</small>
                </span>
            </label>
        @endforeach
    </div>
    @error('dasar')
        <small class="error">{{ $message }}</small>
    @enderror
    @error('dasar.*')
        <small class="error">{{ $message }}</small>
    @enderror
</div>

<x-repeater name="referensi_hukum" label="Referensi Dasar Hukum" :items="$d['referensi_hukum'] ?? []" required
    placeholder="Contoh: UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi, Pasal 20"
    add-label="+ Tambah referensi" />
