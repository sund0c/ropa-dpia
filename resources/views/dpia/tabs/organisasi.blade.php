@php
    $o = $ropa['organisasi'];
    $kontakInstansi = implode(' / ', array_filter([$o['telepon'] ?? null, $o['email'] ?? null]));
    $kontakPpdp = implode(' / ', array_filter([$o['ppdp_email'] ?? null, $o['ppdp_hp'] ?? null]));
@endphp

<x-readonly label="Nama Pengendali Data Pribadi" :value="$o['nama_instansi']" />
<x-readonly label="Alamat Kantor Resmi" :value="$o['alamat']" multiline />
<x-readonly label="Nomor Telepon / Email Resmi" :value="$kontakInstansi" />
<div class="row">
    <x-readonly label="Pimpinan Pengendali Data Pribadi" :value="$o['nama_pengendali']" />
    <x-readonly label="Jabatan" :value="$o['jabatan_pengendali'] ?? '-'" />
</div>
<div class="row">
    <x-readonly label="Nama PPDP" :value="$o['nama_ppdp']" />
    <x-readonly label="Jabatan" :value="$o['jabatan_ppdp'] ?? '-'" />
</div>
<x-readonly label="Kontak PPDP (Email/Telepon)" :value="$kontakPpdp" />
<div class="row">
    <x-readonly label="Nama Koordinator PPDP" :value="$o['koordinator_nama'] ?? '-'" />
    <x-readonly label="Jabatan" :value="$o['koordinator_jabatan'] ?? '-'" />
</div>
<x-readonly label="Kontak Koordinator PPDP (Email/Telepon)"
    :value="implode(' / ', array_filter([$o['koordinator_email'] ?? null, $o['koordinator_hp'] ?? null])) ?: '-'" />
