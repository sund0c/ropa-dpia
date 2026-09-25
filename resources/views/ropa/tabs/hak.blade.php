@php
    $hasOld  = session()->hasOldInput();
    $dipilih = (array) ($hasOld ? old('hak', []) : ($d['hak'] ?? []));

    // Pengingat konsistensi: Consent di Tab II → idealnya hak tarik persetujuan difasilitasi
    $pakaiConsent = in_array('consent', $ropa['aktivitas']['dasar'] ?? [], true);
@endphp

<fieldset class="field">
    <legend>Jenis Hak yang Difasilitasi <span class="req">*</span></legend>
    <small class="hint">Boleh pilih lebih dari satu.</small>

    <button type="button" class="secondary small" data-check-all="hak[]">Centang semua</button>

    <div class="checks" style="margin-top:.5rem">
        @foreach ($options['hak'] as $key => $label)
            <label class="check">
                <input type="checkbox" name="hak[]" value="{{ $key }}" @checked(in_array($key, $dipilih, true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    @error('hak')   <small class="error">{{ $message }}</small> @enderror
    @error('hak.*') <small class="error">{{ $message }}</small> @enderror

    @if ($pakaiConsent && ! in_array('tarik_persetujuan', $dipilih, true))
        <div class="alert warn" style="margin-top:.75rem">
            Tab II mencantumkan <strong>persetujuan (consent)</strong> sebagai dasar pemrosesan. Subjek data yang
            memberi persetujuan berhak menariknya kembali (Pasal 9 UU PDP), sehingga hak ini sebaiknya ikut difasilitasi.
        </div>
    @endif
</fieldset>

<x-field name="mekanisme_hak" label="Mekanisme / Kanal Permohonan Hak Subjek Data" type="textarea"
         :value="$d['mekanisme_hak'] ?? null" required
         hint="Sebutkan kanal (email, formulir, loket), unit/penanggung jawab yang menangani, dan waktu tanggapan. UU PDP menetapkan batas waktu 3 × 24 jam untuk sejumlah permintaan." />
