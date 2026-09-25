@php
    $a = $ropa['aktivitas'];
    $p = $ropa['pemetaan'];

    $hasOld = session()->hasOldInput();
    $pihak  = $hasOld ? (array) old('pihak', []) : ($d['pihak'] ?? []);
    if (empty($pihak)) $pihak = [[]];

    // Pilihan data untuk setiap pihak = jenis data yang dicentang di RoPA
    $jenisRopa = array_intersect_key(
        $options['jenis_umum'] + $options['jenis_spesifik'],
        array_flip(array_merge((array) ($p['jenis_umum'] ?? []), (array) ($p['jenis_spesifik'] ?? [])))
    );
@endphp

{{-- 1 --}}
<h3 class="sub">1. Deskripsi Singkat Aktivitas Pemrosesan Data Pribadi</h3>
<div class="ro-box">
    <strong>{{ $a['nama_aktivitas'] }}</strong>, meliputi:
    <ol>
        @foreach ($a['tahapan'] as $t)
            <li>{{ $t }}</li>
        @endforeach
    </ol>
</div>
<small class="hint">Diambil dari RoPA Tab II.</small>

{{-- 2 --}}
<h3 class="sub">2. Tujuan Pemrosesan Data Pribadi</h3>
<div class="ro-box">{!! nl2br(e($a['tujuan'])) !!}</div>
<small class="hint">Diambil dari RoPA Tab II.</small>

{{-- 3 --}}
<h3 class="sub">3. Latar Belakang Pemrosesan Data Pribadi</h3>
<x-field name="latar_belakang" label="Latar Belakang Pemrosesan" type="textarea" rows="10"
         :value="$d['latar_belakang'] ?? null" required />

{{-- 4 --}}
<h3 class="sub">4. Data Pribadi yang Diproses</h3>
<x-readonly label="Kategori Subjek Data Pribadi" :value="implode(', ', $p['kategori_subjek'])" />
<x-readonly label="Estimasi Jumlah Subjek Data Pribadi"
            :value="number_format($p['estimasi_subjek'], 0, ',', '.') . ' orang'" />

<div class="field">
    <div class="checks ro">
        <label class="check"><input type="checkbox" disabled @checked($p['data_anak'])> Pemrosesan melibatkan Data Pribadi Anak</label>
        <label class="check"><input type="checkbox" disabled @checked($p['kelompok_rentan'])> Pemrosesan melibatkan kelompok rentan (disabilitas, lansia, minoritas)</label>
    </div>
</div>

<fieldset class="field">
    <legend>a. Data Pribadi yang bersifat spesifik</legend>
    <div class="checks ro">
        @foreach ($options['jenis_spesifik'] as $key => $label)
            <label class="check">
                <input type="checkbox" disabled @checked(in_array($key, (array) ($p['jenis_spesifik'] ?? []), true))> {{ $label }}
            </label>
        @endforeach
    </div>
</fieldset>

<fieldset class="field">
    <legend>b. Data Pribadi yang bersifat umum</legend>
    <div class="checks ro">
        @foreach ($options['jenis_umum'] as $key => $label)
            <label class="check">
                <input type="checkbox" disabled @checked(in_array($key, (array) ($p['jenis_umum'] ?? []), true))> {{ $label }}
            </label>
        @endforeach
    </div>
    <small class="hint">Seluruh isian bagian 4 diambil dari RoPA Tab III dan hanya bisa diubah di sana.</small>
</fieldset>

{{-- 5 --}}
<h3 class="sub">5. Pihak yang Terlibat dalam Pemrosesan <span class="req">*</span></h3>
<div class="field repeater" data-repeater>
    <div class="rep-rows" data-rows>
        @foreach ($pihak as $i => $row)
            @include('dpia.tabs._pihak-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>
    <template data-template>
        @include('dpia.tabs._pihak-row', ['i' => '__i__', 'row' => []])
    </template>

    <button type="button" class="secondary small" data-add>+ Tambah pihak</button>
    @error('pihak') <small class="error">{{ $message }}</small> @enderror
</div>
