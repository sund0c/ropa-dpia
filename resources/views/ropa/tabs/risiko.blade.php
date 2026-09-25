@php
    $hasOld      = session()->hasOldInput();
    $dipilih     = (array) ($hasOld ? old('indikator', []) : ($d['indikator'] ?? []));
    $tab3Ada     = isset($ropa['pemetaan']);
    $adaSpesifik = ! empty($ropa['pemetaan']['jenis_spesifik']);
    $estimasi    = $ropa['pemetaan']['estimasi_subjek'] ?? null;
@endphp

<div class="alert warn">
    Jika salah satu indikator terpenuhi, aktivitas ini <strong>wajib dilengkapi dengan DPIA</strong>
    (Pasal 34 ayat (2) UU PDP).
</div>

<fieldset class="field">
    <legend>Indikator Risiko Tinggi</legend>
    <small class="hint">Boleh pilih lebih dari satu. Biarkan kosong jika tidak ada indikator yang terpenuhi.</small>

    <div class="checks" style="margin-top:.5rem">
        @foreach ($options['risiko'] as $key => $label)
            @if ($key === \App\Http\Controllers\RopaController::INDIKATOR_OTOMATIS)
                <label class="check">
                    <input type="checkbox" disabled @checked($adaSpesifik)>
                    <span>
                        {{ $label }}
                        <small class="hint">
                            (otomatis dari Tab III:
                            {{ ! $tab3Ada ? 'Tab III belum disimpan'
                               : ($adaSpesifik ? 'terdapat data pribadi spesifik' : 'tidak ada data pribadi spesifik') }})
                        </small>
                    </span>
                </label>
            @else
                <label class="check">
                    <input type="checkbox" name="indikator[]" value="{{ $key }}" @checked(in_array($key, $dipilih, true))>
                    <span>
                        {{ $label }}
                        @if ($key === 'skala_besar' && $estimasi !== null)
                            <small class="hint">(estimasi subjek data di Tab III: {{ number_format($estimasi, 0, ',', '.') }} orang)</small>
                        @endif
                    </span>
                </label>
            @endif
        @endforeach
    </div>

    @error('indikator.*') <small class="error">{{ $message }}</small> @enderror
</fieldset>

@if (isset($ropa['risiko']))
    @if (\App\Support\RopaStatus::wajibDpia($ropa))
        <div class="alert err"><strong>Hasil:</strong> terdapat indikator risiko tinggi. Aktivitas ini wajib dilengkapi DPIA.</div>
    @else
        <div class="alert ok"><strong>Hasil:</strong> tidak ada indikator risiko tinggi. DPIA tidak diwajibkan.</div>
    @endif
@endif
