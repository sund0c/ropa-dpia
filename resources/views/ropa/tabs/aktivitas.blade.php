<x-field name="nama_aktivitas" label="Nama Aktivitas Pemrosesan"
         :value="$d['nama_aktivitas'] ?? null" required />

<x-repeater name="tahapan" label="Deskripsi Aktivitas Pemrosesan"
            :items="$d['tahapan'] ?? []" required
            hint="Masukkan seluruh tahapan aktivitas yang dilakukan. Satu tahapan satu isian."
            placeholder="Contoh: Pemohon mengisi formulir pendaftaran daring"
            add-label="+ Tambah tahapan" />

<x-field name="tujuan" label="Tujuan Pemrosesan" type="textarea"
         :value="$d['tujuan'] ?? null" required />

@php $dipilih = old('dasar', $d['dasar'] ?? []); @endphp
<div class="field">
    <label>Dasar Pemrosesan <span class="req">*</span></label>
    <small class="hint">Boleh pilih lebih dari satu.</small>
    <div class="checks">
        @foreach ($options['dasar'] as $key => $label)
            <label class="check">
                <input type="checkbox" name="dasar[]" value="{{ $key }}" @checked(in_array($key, $dipilih, true))>
                {{ $label }}
            </label>
        @endforeach
    </div>
    @error('dasar')   <small class="error">{{ $message }}</small> @enderror
    @error('dasar.*') <small class="error">{{ $message }}</small> @enderror
</div>

<x-repeater name="referensi_hukum" label="Referensi Dasar Hukum"
            :items="$d['referensi_hukum'] ?? []" required
            placeholder="Contoh: UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi, Pasal 20"
            add-label="+ Tambah referensi" />
