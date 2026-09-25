@php
    $aktif    = \App\Support\RopaStatus::indikatorAktif($ropa);
    $hasOld   = session()->hasOldInput();
    $ket      = $hasOld ? (array) old('keterangan', []) : ($d['keterangan'] ?? []);
    $estimasi = $ropa['pemetaan']['estimasi_subjek'] ?? null;

    // Isian awal untuk indikator data spesifik: daftar jenis data spesifik dari RoPA Tab III
    $spesifik = collect($ropa['pemetaan']['jenis_spesifik'] ?? [])
        ->map(fn ($k) => \App\Http\Controllers\RopaController::JENIS_DATA_SPESIFIK[$k] ?? $k)
        ->implode(', ');
    $isianAwal = ['data_spesifik' => $spesifik];
@endphp

<p class="hint" style="margin-top:0">
    Status Ya/Tidak diambil dari <a href="{{ route('ropa.form', ['tab' => 'risiko']) }}">RoPA Tab VI</a>
    dan hanya bisa diubah di sana. Keterangan wajib diisi untuk setiap potensi risiko yang bernilai <strong>Ya</strong>.
</p>

<div class="tabel-wrap">
    <table class="tabel-form">
        <thead>
            <tr>
                <th>Potensi Risiko Tinggi</th>
                <th style="width:5.5rem; text-align:center">Ya/Tidak</th>
                <th style="width:45%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($options['risiko'] as $key => $label)
                @php $ya = in_array($key, $aktif, true); @endphp
                <tr class="{{ $ya ? '' : 'tidak' }}">
                    <td>{{ $label }}</td>
                    <td style="text-align:center">
                        <span class="badge {{ $ya ? 'ya' : 'no' }}">{{ $ya ? 'Ya' : 'Tidak' }}</span>
                    </td>
                    <td>
                        @if ($ya)
                            <textarea name="keterangan[{{ $key }}]" rows="3"
                                @if ($key === 'skala_besar' && $estimasi !== null)
                                    placeholder="Estimasi subjek data di RoPA: {{ number_format($estimasi, 0, ',', '.') }} orang"
                                @endif
                            >{{ $ket[$key] ?? ($isianAwal[$key] ?? '') }}</textarea>
                            @error("keterangan.$key") <small class="error">{{ $message }}</small> @enderror
                        @else
                            <textarea rows="3" class="ro" readonly disabled>-</textarea>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
