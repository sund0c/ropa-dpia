@php $status = old('status_siklus', $d['status_siklus'] ?? null); @endphp

<x-field name="masa_retensi" label="Masa Retensi Data"
         :value="$d['masa_retensi'] ?? null" required
         placeholder="Contoh: 5 tahun sejak berakhirnya layanan, sesuai Jadwal Retensi Arsip (JRA)"
         hint="Sebutkan jangka waktu dan titik awal penghitungannya, beserta acuannya bila ada." />

<x-field name="metode_pemusnahan" label="Metode Pemusnahan Data" type="textarea"
         :value="$d['metode_pemusnahan'] ?? null" required
         placeholder="Contoh: Penghapusan permanen dari basis data dan cadangan, penghancuran dokumen fisik dengan mesin penghancur kertas, disertai berita acara pemusnahan" />

<x-repeater name="pengamanan" label="Pengamanan Teknis & Organisasional"
            :items="$d['pengamanan'] ?? []" required
            hint="Satu langkah pengamanan satu isian. Cantumkan langkah teknis maupun organisasional."
            placeholder="Contoh: Enkripsi data saat disimpan (AES-256) / Kontrol akses berbasis peran / Perjanjian kerahasiaan (NDA) bagi petugas"
            add-label="+ Tambah pengamanan" />

<fieldset class="field">
    <legend>Status Siklus Hidup <span class="req">*</span></legend>
    <div class="radios">
        @foreach ($options['status_siklus'] as $key => $label)
            <label class="check">
                <input type="radio" name="status_siklus" value="{{ $key }}" @checked($status === $key)>
                {{ $label }}
            </label>
        @endforeach
    </div>
    @error('status_siklus') <small class="error">{{ $message }}</small> @enderror
</fieldset>
