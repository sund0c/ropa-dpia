@php
    $aktif    = \App\Support\RopaStatus::indikatorAktif($ropa);
    $hasOld   = session()->hasOldInput();
    $ket      = $hasOld ? (array) old('keterangan', []) : ($d['keterangan'] ?? []);
    $estimasi = $ropa['pemetaan']['estimasi_subjek'] ?? null;

    // Isian awal untuk indikator data spesifik: daftar jenis data spesifik dari RoPA Tab III
    $spesifik = collect($ropa['pemetaan']['jenis_spesifik'] ?? [])
        ->map(fn ($k) => \App\Http\Controllers\RopaController::JENIS_DATA_SPESIFIK[$k] ?? $k)
        ->implode(', ');
    $isianAwal = array_filter([
        'data_spesifik' => $spesifik,
        'skala_besar'   => $estimasi !== null
            ? 'Estimasi jumlah subjek data ' . number_format($estimasi, 0, ',', '.') . ' orang.'
            : null,
    ], 'filled');
@endphp

<div class="bar-default">
    <p class="hint" style="margin:0; flex:1">
        Status Ya/Tidak diambil dari <a href="{{ route('ropa.form', ['tab' => 'risiko']) }}">RoPA Tab VI</a>
        dan hanya bisa diubah di sana. Keterangan wajib diisi untuk setiap potensi risiko yang bernilai <strong>Ya</strong>.
    </p>
    @if ($isianAwal)
        <button type="button" class="secondary small" data-keterangan-default style="margin-top:0">↺ Kembalikan ke default</button>
    @endif
</div>
@if ($isianAwal)
    <p class="hint" style="margin-top:0">
        <strong>Kembalikan ke default</strong> mengisi ulang keterangan yang punya isian otomatis dari RoPA
        (data spesifik dan estimasi jumlah subjek). Keterangan lain tidak diubah. Klik <strong>Simpan</strong> setelahnya.
    </p>
@endif

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
                            <textarea name="keterangan[{{ $key }}]" rows="3" data-keterangan="{{ $key }}"
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

<script>
(function () {
    const awal = @json((object) $isianAwal);
    document.querySelector('[data-keterangan-default]')?.addEventListener('click', () => {
        if (!confirm('Isi ulang keterangan dari data RoPA? Keterangan data spesifik dan skala besar yang sudah Anda ubah akan diganti.')) return;
        document.querySelectorAll('[data-keterangan]').forEach(t => {
            if (awal[t.dataset.keterangan] !== undefined) t.value = awal[t.dataset.keterangan];
        });
    });
})();
</script>
