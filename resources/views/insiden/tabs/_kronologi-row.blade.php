{{-- Satu baris kronologi. $i = indeks (atau __i__ untuk template), $row = isian. --}}
@php
    $err = fn (string $f) => is_int($i) ? $errors->first("kronologi.$i.$f") : null;
    $pil = (string) ($row['fase'] ?? '');
@endphp
<div class="kr" data-kr>
    <div class="kr-grid">
        <div class="field">
            <label>Tanggal &amp; Waktu <span class="req">*</span></label>
            <input type="datetime-local" name="kronologi[{{ $i }}][waktu]" value="{{ $row['waktu'] ?? '' }}">
            @if ($err('waktu')) <small class="error">{{ $err('waktu') }}</small> @endif
        </div>
        <div class="field">
            <label>Fase <span class="req">*</span></label>
            <select name="kronologi[{{ $i }}][fase]">
                <option value="">— Pilih —</option>
                @foreach (\App\Http\Controllers\InsidenController::FASE as $k => $t)
                    <option value="{{ $k }}" @selected($pil === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('fase')) <small class="error">{{ $err('fase') }}</small> @endif
        </div>
        <div class="field">
            <label>Sumber Bukti</label>
            <input type="text" name="kronologi[{{ $i }}][bukti]" value="{{ $row['bukti'] ?? '' }}" maxlength="255"
                   placeholder="Contoh: access.log Nginx">
            @if ($err('bukti')) <small class="error">{{ $err('bukti') }}</small> @endif
        </div>
        <button type="button" class="kr-hapus" data-kr-hapus title="Hapus baris" aria-label="Hapus baris">✕</button>
    </div>
    <div class="field">
        <label>Kejadian <span class="req">*</span></label>
        <textarea name="kronologi[{{ $i }}][kejadian]" rows="2" maxlength="1000"
                  placeholder="Apa yang terjadi atau dilakukan pada waktu tersebut">{{ $row['kejadian'] ?? '' }}</textarea>
        @if ($err('kejadian')) <small class="error">{{ $err('kejadian') }}</small> @endif
    </div>
</div>
