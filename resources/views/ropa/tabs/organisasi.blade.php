<x-field name="nama_instansi" label="Nama Instansi / Organisasi / Perusahaan" :value="$d['nama_instansi'] ?? null" required />

<x-field name="alamat" label="Alamat Kantor Resmi" type="textarea" :value="$d['alamat'] ?? null" required />

<p style="margin:0 0 .25rem">Nomor Telepon / Email Resmi <span class="req">*</span>
    <small class="hint">(minimal salah satu)</small>
</p>
<div class="row">
    <x-field name="telepon" label="Nomor telepon" type="tel" :value="$d['telepon'] ?? null" />
    <x-field name="email" label="Email" type="email" :value="$d['email'] ?? null" />
</div>

<div class="row">
    <x-field name="nama_pengendali" label="Nama Pengendali Data Pribadi" :value="$d['nama_pengendali'] ?? null" required />
    <x-field name="jabatan_pengendali" label="Jabatan" :value="$d['jabatan_pengendali'] ?? null" required placeholder="Contoh: Kepala Dinas" />
</div>
<small class="hint" style="display:block; margin:-.6rem 0 1rem">
    Pengendali: pihak yang menentukan tujuan dan kendali pemrosesan, umumnya instansi itu sendiri atau pimpinannya.
</small>

<div class="row">
    <x-field name="nama_ppdp" label="Nama Pejabat Pelindung Data Pribadi (PPDP)" :value="$d['nama_ppdp'] ?? null" required />
    <x-field name="jabatan_ppdp" label="Jabatan" :value="$d['jabatan_ppdp'] ?? null" required
        placeholder="Contoh: Kepala Bidang Persandian" />
</div>

<p style="margin:0 0 .25rem">Kontak PPDP <span class="req">*</span>
    <small class="hint">(minimal salah satu)</small>
</p>
<div class="row">
    <x-field name="ppdp_email" label="Email" type="email" :value="$d['ppdp_email'] ?? null" />
    <x-field name="ppdp_hp" label="No. HP" type="tel" :value="$d['ppdp_hp'] ?? null"
        hint="Contoh: 081234567890 atau +6281234567890" />
</div>
