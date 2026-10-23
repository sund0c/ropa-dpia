{{-- Satu baris rencana tindak lanjut. $i = indeks (atau __i__), $row = isian. --}}
@php
    $IC  = \App\Http\Controllers\InsidenController::class;
    $err = fn (string $f) => is_int($i) ? $errors->first("tindak_lanjut.$i.$f") : null;
@endphp
<div class="tl" data-tl>
    <div class="tl-head">
        <span class="tl-no" data-tl-no></span>
        <button type="button" class="tl-hapus" data-tl-hapus title="Hapus baris" aria-label="Hapus baris">✕</button>
    </div>
    <div class="field">
        <label>Tindakan <span class="req">*</span></label>
        <input type="text" name="tindak_lanjut[{{ $i }}][tindakan]" value="{{ $row['tindakan'] ?? '' }}" maxlength="1000"
               placeholder="Mis. Wajibkan 2FA untuk seluruh akun admin CMS">
        @if ($err('tindakan')) <small class="error">{{ $err('tindakan') }}</small> @endif
    </div>
    <div class="tl-grid">
        <div class="field">
            <label>Kontrol ISO 27001</label>
            <input type="text" name="tindak_lanjut[{{ $i }}][kontrol]" value="{{ $row['kontrol'] ?? '' }}" maxlength="255"
                   list="daftar-kontrol" placeholder="Mis. A.8.5">
            @if ($err('kontrol')) <small class="error">{{ $err('kontrol') }}</small> @endif
        </div>
        <div class="field">
            <label>Penanggung Jawab <span class="req">*</span></label>
            <input type="text" name="tindak_lanjut[{{ $i }}][pj]" value="{{ $row['pj'] ?? '' }}" maxlength="255"
                   placeholder="Mis. Bidang Persandian">
            @if ($err('pj')) <small class="error">{{ $err('pj') }}</small> @endif
        </div>
        <div class="field">
            <label>Target <span class="req">*</span></label>
            <input type="date" name="tindak_lanjut[{{ $i }}][target]" value="{{ $row['target'] ?? '' }}">
            @if ($err('target')) <small class="error">{{ $err('target') }}</small> @endif
        </div>
        <div class="field">
            <label>Status <span class="req">*</span></label>
            <select name="tindak_lanjut[{{ $i }}][status]">
                <option value="">— Pilih —</option>
                @foreach ($IC::STATUS_TL as $k => $t)
                    <option value="{{ $k }}" @selected(($row['status'] ?? '') === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('status')) <small class="error">{{ $err('status') }}</small> @endif
        </div>
    </div>
</div>
