{{-- Nilai Risiko Saat Ini untuk satu risiko Tab III. $i = indeks, $r = risiko Tab III, $row = isian. --}}
@php
    $err = fn (string $f) => $errors->first("set.$i.$f");
    $pil = fn (string $f) => (string) ($row[$f] ?? '');
@endphp
<div class="nr" data-nr>
    <div class="nr-head">
        <span class="nr-kode">{{ $r['kode'] }}</span>
    </div>

    <input type="hidden" name="set[{{ $i }}][risiko_id]" value="{{ $r['id'] }}">

    <div class="nr-ringkas">
        <div><span>Ancaman</span><b>{{ $r['ancaman'] }}</b></div>
        <div><span>Kerentanan</span><b>{{ $r['kerentanan'] }}</b></div>
    </div>
    @if ($err('risiko_id')) <small class="error">{{ $err('risiko_id') }}</small> @endif

    <div class="field">
        <label>Kontrol Saat Ini <span class="req">*</span></label>
        <textarea name="set[{{ $i }}][kontrol]" rows="4" maxlength="2000"
                  placeholder="Kontrol/pengamanan yang sudah berjalan, satu per baris. Contoh:&#10;1. Firewall aktif&#10;2. Backup harian ke server terpisah&#10;Tulis &quot;Belum ada&quot; bila belum ada kontrol.">{{ $row['kontrol'] ?? '' }}</textarea>
        @if ($err('kontrol')) <small class="error">{{ $err('kontrol') }}</small> @endif
    </div>

    <div class="nr-grid">
        <div class="field">
            <label>Nilai Dampak <span class="req">*</span></label>
            <select name="set[{{ $i }}][dampak]" data-nr-d>
                <option value="">— Pilih —</option>
                @foreach ($levelDampak as $k => $t)
                    <option value="{{ $k }}" @selected($pil('dampak') === (string) $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('dampak')) <small class="error">{{ $err('dampak') }}</small> @endif
        </div>

        <div class="field">
            <label>Nilai Kemungkinan <span class="req">*</span></label>
            <select name="set[{{ $i }}][kemungkinan]" data-nr-k>
                <option value="">— Pilih —</option>
                @foreach ($levelKemungkinan as $k => $t)
                    <option value="{{ $k }}" @selected($pil('kemungkinan') === (string) $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('kemungkinan')) <small class="error">{{ $err('kemungkinan') }}</small> @endif
        </div>

        <div class="field">
            <label>IR</label>
            <span class="nr-badge" data-nr-ir>–</span>
        </div>

        <div class="field">
            <label>Level Risiko</label>
            <span class="nr-badge" data-nr-level>–</span>
        </div>
    </div>

    <div class="nr-grid2">
        <div class="field">
            <label>Keputusan Penanganan Risiko <span class="req">*</span></label>
            <select name="set[{{ $i }}][keputusan]">
                <option value="">— Pilih —</option>
                @foreach (\App\Http\Controllers\RisikoController::KEPUTUSAN as $k => $t)
                    <option value="{{ $k }}" @selected($pil('keputusan') === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('keputusan')) <small class="error">{{ $err('keputusan') }}</small> @endif
        </div>

        <div class="field">
            <label>Prioritas Risiko <span class="req">*</span></label>
            <input type="number" name="set[{{ $i }}][prioritas]" value="{{ $row['prioritas'] ?? '' }}"
                   min="1" max="999" step="1" placeholder="Contoh: 1">
            @if ($err('prioritas')) <small class="error">{{ $err('prioritas') }}</small> @endif
        </div>
    </div>
</div>
