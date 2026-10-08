@php
    // Setelah validasi gagal, pakai input terakhir; kalau tidak, pakai data tersimpan.
    $hasOld = session()->hasOldInput();
    $pick = fn(string $key, $default = []) => $hasOld ? old($key, $default) : $d[$key] ?? $default;

    $jenisUmum = (array) $pick('jenis_umum');
    $jenisSpesifik = (array) $pick('jenis_spesifik');
    $pengumpulan = $pick('pengumpulan') ?: [['sumber' => '', 'lokasi' => '']];
    $transfer = (array) $pick('transfer');
    $semuaJenis = $options['jenis_umum'] + $options['jenis_spesifik'];
    $dataAnak = (bool) $pick('data_anak', false);
@endphp

<x-repeater name="kategori_subjek" label="Kategori Subjek Data" :items="$d['kategori_subjek'] ?? []" required hint="Satu kategori satu isian."
    placeholder="Contoh: Pemohon beasiswa (masyarakat umum)" add-label="+ Tambah kategori" />

<div class="field">
    <label class="check">
        <input type="hidden" name="kelompok_rentan" value="0">
        <input type="checkbox" name="kelompok_rentan" value="1" @checked($pick('kelompok_rentan', false))>
        Pemrosesan melibatkan kelompok rentan (disabilitas, lansia, minoritas)
    </label>
    <label class="check">
        <input type="hidden" name="data_anak" value="0">
        <input type="checkbox" name="data_anak" value="1" data-anak-toggle @checked($dataAnak)> Pemrosesan
        melibatkan Data Pribadi Anak
    </label>
</div>

<x-field name="estimasi_subjek" label="Estimasi Jumlah Subjek Data" type="number" min="0" step="1"
    :value="$d['estimasi_subjek'] ?? null" required hint="Perkiraan jumlah subjek data yang datanya sudah diproses sampai saat ini." />

<fieldset class="field">
    <legend>Jenis Data Pribadi Umum <small class="hint">(Pasal 4 ayat (3) UU PDP)</small></legend>
    <div class="checks">
        @foreach ($options['jenis_umum'] as $key => $label)
            <label class="check">
                <input type="checkbox" name="jenis_umum[]" value="{{ $key }}" data-jenis
                    @checked(in_array($key, $jenisUmum, true))>
                {{ $label }}
            </label>
        @endforeach
    </div>
</fieldset>

<fieldset class="field">
    <legend>Jenis Data Pribadi Spesifik <small class="hint">(Pasal 4 ayat (2) UU PDP)</small></legend>
    <div class="checks">
        @foreach ($options['jenis_spesifik'] as $key => $label)
            @if ($key === 'anak')
                {{-- Dikunci: mengikuti "Pemrosesan melibatkan Data Pribadi Anak"; nilainya ditetapkan server --}}
                <label class="check">
                    <input type="checkbox" value="anak" data-jenis data-anak-otomatis disabled
                        @checked($dataAnak)>
                    <span>{{ $label }}
                        <small class="hint">(otomatis mengikuti pilihan "Pemrosesan melibatkan Data Pribadi
                            Anak")</small>
                    </span>
                </label>
            @else
                <label class="check">
                    <input type="checkbox" name="jenis_spesifik[]" value="{{ $key }}" data-jenis
                        @checked(in_array($key, $jenisSpesifik, true))>
                    {{ $label }}
                </label>
            @endif
        @endforeach
    </div>
    @if ($msg = $errors->first('jenis_umum') ?: $errors->first('jenis_spesifik'))
        <small class="error">{{ $msg }}</small>
    @endif
</fieldset>

{{-- Sumber pengumpulan + lokasi penyimpanan (berpasangan) --}}
<div class="field repeater" data-repeater>
    <label>Sumber Pengumpulan & Lokasi Penyimpanan Data <span class="req">*</span></label>
    <small class="hint">Satu baris untuk satu sumber, beserta lokasi penyimpanan data dari sumber tersebut.</small>

    <div class="pair-head"><span></span><span>Sumber pengumpulan</span><span>Lokasi penyimpanan</span><span></span>
    </div>
    <div class="rep-rows" data-rows>
        @foreach ($pengumpulan as $i => $row)
            @include('ropa.tabs._pengumpulan-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>
    <template data-template>
        @include('ropa.tabs._pengumpulan-row', ['i' => '__i__', 'row' => []])
    </template>

    <button type="button" class="secondary small" data-add>+ Tambah sumber</button>
    @error('pengumpulan')
        <small class="error">{{ $message }}</small>
    @enderror
</div>

{{-- Transfer data (opsional, bisa banyak penerima) --}}
<div class="field repeater" data-repeater data-min="0">
    <label>Transfer Data Pribadi</label>
    <small class="hint">Isi jika data pribadi dikirim atau dibagikan ke pihak lain. Biarkan kosong jika tidak
        ada.</small>

    <div class="rep-rows" data-rows>
        @foreach ($transfer as $i => $row)
            @include('ropa.tabs._transfer-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>
    <p class="hint" data-empty @if (count($transfer)) hidden @endif>Belum ada penerima.</p>
    <template data-template>
        @include('ropa.tabs._transfer-row', ['i' => '__i__', 'row' => []])
    </template>

    <button type="button" class="secondary small" data-add>+ Tambah penerima</button>
    @error('transfer')
        <small class="error">{{ $message }}</small>
    @enderror
</div>
<script>
    (function() {
        const toggle = document.querySelector('[data-anak-toggle]');
        const anak = document.querySelector('[data-anak-otomatis]');
        if (!toggle || !anak) return;

        toggle.addEventListener('change', () => {
            anak.checked = toggle.checked;
            // Perbarui pilihan "data yang dikirim" pada kartu penerima transfer
            if (typeof syncTransferData === 'function') syncTransferData();
        });
    })();
</script>
