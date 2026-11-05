@php $status = old('status_siklus', $d['status_siklus'] ?? null); @endphp

<x-field name="masa_retensi" label="Masa Retensi Data"
         :value="$d['masa_retensi'] ?? null" required
         placeholder="Contoh: 5 tahun sejak berakhirnya layanan, sesuai Jadwal Retensi Arsip (JRA)"
         hint="Sebutkan jangka waktu dan titik awal penghitungannya, beserta acuannya bila ada." />

<x-field name="metode_pemusnahan" label="Metode Pemusnahan Data" type="textarea"
         :value="$d['metode_pemusnahan'] ?? null" required
         placeholder="Contoh: Penghapusan permanen dari basis data dan cadangan, penghancuran dokumen fisik dengan mesin penghancur kertas, disertai berita acara pemusnahan" />

@php $pg = \App\Http\Controllers\RopaController::pengamanan($d ?? []); @endphp

<x-repeater name="pengamanan_organisasi" label="Pengamanan Organisasional"
            :items="$pg['organisasi']"
            hint="Aturan dan tata kelola: kebijakan, SOP, penetapan peran dan tanggung jawab, perjanjian kerahasiaan (NDA), perjanjian dengan prosesor/pihak ketiga. Satu langkah satu isian; boleh dikosongkan."
            placeholder="Contoh: SOP Layanan Permohonan Informasi / Perjanjian kerahasiaan (NDA) bagi petugas"
            add-label="+ Tambah pengamanan organisasional" />

<x-repeater name="pengamanan_operasional" label="Pengamanan Operasional"
            :items="$pg['operasional']"
            hint="Pelaksanaan sehari-hari: pelatihan/sosialisasi petugas, backup berkala, pengamanan fisik dokumen dan ruangan, peninjauan log, pemusnahan sesuai prosedur. Satu langkah satu isian; boleh dikosongkan."
            placeholder="Contoh: Bimtek petugas layanan / Backup harian, mingguan, bulanan / Filing cabinet terkunci"
            add-label="+ Tambah pengamanan operasional" />

<x-repeater name="pengamanan_teknis" label="Pengamanan Teknis"
            :items="$pg['teknis']"
            hint="Pengamanan pada sistem dan perangkat: enkripsi, kontrol akses, autentikasi, log audit, firewall, pembaruan keamanan. Satu langkah satu isian; boleh dikosongkan."
            placeholder="Contoh: Enkripsi data saat disimpan (AES-256) / Kontrol akses berbasis peran (RBAC) / Log audit trail"
            add-label="+ Tambah pengamanan teknis" />

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
