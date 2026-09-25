@php
    $MR = \App\Support\MetodologiRisiko::class;
    $h  = \App\Support\RingkasanDpia::hitung($dpia);
    $u  = \App\Support\RingkasanDpia::usulan($h);

    $hasOld    = session()->hasOldInput();
    $ringkasan = $hasOld ? (array) old('ringkasan', []) : ($d['ringkasan'] ?? []);
    $efek      = old('efektivitas', $d['efektivitas'] ?? null);
    $kep       = old('keputusan_akhir', $d['keputusan_akhir'] ?? null);

    $aspek = [
        'kebutuhan' => 'Kebutuhan dan Proporsionalitas',
        'inheren'   => 'Risiko Inheren (Sebelum Mitigasi) Tertinggi',
        'residual'  => 'Risiko Residual (Setelah Mitigasi) Tertinggi',
    ];

    $residualTinggi = $h['residual'] && $h['residual']['level'] >= 4;
    $namaLevel      = fn ($r) => $r ? $MR::KATEGORI[$r['level']]['nama'] : null;
@endphp

{{-- 1 --}}
<h3 class="sub">1. Ringkasan Penilaian</h3>
<p class="hint" style="margin-top:0">
    Kolom kesimpulan terisi awal dari hasil Tab V–VII bila belum pernah disimpan. Sesuaikan narasinya bila perlu.
</p>

<div class="tabel-wrap">
    <table class="tabel-form">
        <thead>
            <tr><th style="width:32%">Aspek</th><th>Kesimpulan <span class="req">*</span></th></tr>
        </thead>
        <tbody>
            @foreach ($aspek as $key => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>
                        <textarea name="ringkasan[{{ $key }}]" rows="5">{{ $ringkasan[$key] ?? $u[$key] }}</textarea>
                        @error("ringkasan.$key") <small class="error">{{ $message }}</small> @enderror
                    </td>
                </tr>
            @endforeach
            <tr>
                <td>Efektivitas Langkah Mitigasi</td>
                <td>
                    <div class="radios">
                        @foreach ($options['efektivitas'] as $key => $label)
                            <label class="check">
                                <input type="radio" name="efektivitas" value="{{ $key }}" @checked($efek === $key)> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @if ($h['inheren'] && $h['residual'])
                        <small class="hint">
                            Skor tertinggi sebelum mitigasi {{ $h['inheren']['skor'] }} ({{ $namaLevel($h['inheren']) }}),
                            setelah mitigasi {{ $h['residual']['skor'] }} ({{ $namaLevel($h['residual']) }}).
                        </small>
                    @endif
                    @error('efektivitas') <small class="error">{{ $message }}</small> @enderror
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- 2 --}}
<h3 class="sub">2. Keputusan Akhir Pemrosesan <span class="req">*</span></h3>
<div class="checks">
    @foreach ($options['keputusan_akhir'] as $key => $label)
        <label class="check">
            <input type="radio" name="keputusan_akhir" value="{{ $key }}" @checked($kep === $key)>
            <span>
                {{ $label }}
                @if ($key === 'lanjut' && $residualTinggi)
                    <br><small style="color:#b45309">
                        Perhatian: risiko residual tertinggi ({{ $h['residual']['kode'] }}) masih level {{ $namaLevel($h['residual']) }}.
                    </small>
                @endif
                @if ($key === 'tunda' && $h['lembaga'] !== 'ya')
                    <br><small style="color:#b45309">
                        Perhatian: di Tab VIII konsultasi dengan Lembaga {{ $h['lembaga'] === 'tidak' ? 'dinyatakan tidak dilakukan' : 'belum diisi' }}.
                    </small>
                @endif
            </span>
        </label>
    @endforeach
</div>
@error('keputusan_akhir') <small class="error">{{ $message }}</small> @enderror

<x-field name="catatan" label="Catatan Tambahan" type="textarea" rows="5"
         :value="$d['catatan'] ?? null" style="margin-top:1rem" />
