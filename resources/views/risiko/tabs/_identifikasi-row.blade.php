{{-- Satu set identifikasi risiko. $i = indeks baris (atau __i__ untuk template), $row = isian. --}}
@php
    $err = fn (string $f) => $errors->first("risiko.$i.$f");
    $lv  = (string) ($row['dampak'] ?? '');
    $ar  = (string) ($row['area'] ?? '');
@endphp
<div class="idr" data-idr>
    <div class="idr-head">
        <span class="idr-kode" data-idr-kode>R{{ is_int($i) ? str_pad($i + 1, 2, '0', STR_PAD_LEFT) : '' }}</span>
        <button type="button" class="idr-hapus" data-idr-hapus title="Hapus risiko ini">✕ Hapus</button>
    </div>

    <input type="hidden" name="risiko[{{ $i }}][id]" value="{{ $row['id'] ?? '__id__' }}">

    <div class="field">
        <label>Ancaman <span class="req">*</span></label>
        <input type="text" name="risiko[{{ $i }}][ancaman]" value="{{ $row['ancaman'] ?? '' }}" maxlength="500"
               list="katalog-ancaman" autocomplete="off" data-idr-ancaman
               placeholder="Ketik atau pilih dari saran">
        @if ($err('ancaman')) <small class="error">{{ $err('ancaman') }}</small> @endif
    </div>

    <div class="field">
        <label>Kerentanan <span class="req">*</span></label>
        <input type="text" name="risiko[{{ $i }}][kerentanan]" value="{{ $row['kerentanan'] ?? '' }}" maxlength="1000"
               list="kerentanan-{{ $i }}" autocomplete="off" data-idr-kerentanan
               placeholder="Ketik atau pilih dari saran (menyesuaikan ancaman)">
        <datalist id="kerentanan-{{ $i }}" data-idr-dl></datalist>
        @if ($err('kerentanan')) <small class="error">{{ $err('kerentanan') }}</small> @endif
    </div>

    <div class="idr-grid">
        <div class="field">
            <label>Kategori Risiko <span class="req">*</span></label>
            <select name="risiko[{{ $i }}][kategori]">
                <option value="">— Pilih —</option>
                @foreach (\App\Support\KatalogRisiko::KATEGORI as $k => $t)
                    <option value="{{ $k }}" @selected(($row['kategori'] ?? '') === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('kategori')) <small class="error">{{ $err('kategori') }}</small> @endif
        </div>

        <div class="field">
            <label>Area Dampak <span class="req">*</span></label>
            <select name="risiko[{{ $i }}][area]" data-idr-area>
                <option value="">— Pilih —</option>
                @foreach ($areaDampak as $k => $t)
                    <option value="{{ $k }}" @selected($ar === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('area')) <small class="error">{{ $err('area') }}</small> @endif
        </div>

        <div class="field">
            <label>Dampak <span class="req">*</span></label>
            <select name="risiko[{{ $i }}][dampak]" data-idr-dampak>
                <option value="">— Pilih —</option>
                @foreach ($levelDampak as $k => $t)
                    <option value="{{ $k }}" @selected($lv === (string) $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('dampak')) <small class="error">{{ $err('dampak') }}</small> @endif
        </div>
    </div>

    <div class="idr-kriteria" data-idr-kriteria hidden></div>
</div>
