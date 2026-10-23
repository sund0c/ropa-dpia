{{-- Satu baris daftar bukti (chain of custody). $i = indeks (atau __i__), $row = isian. --}}
@php
    $err = fn (string $f) => is_int($i) ? $errors->first("bukti.$i.$f") : null;
@endphp
<div class="bk" data-bk>
    <div class="bk-grid">
        <div class="field">
            <label>Nama Bukti <span class="req">*</span></label>
            <input type="text" name="bukti[{{ $i }}][nama]" value="{{ $row['nama'] ?? '' }}" maxlength="255"
                   placeholder="Mis. Snapshot VM web-dinasxyz">
            @if ($err('nama')) <small class="error">{{ $err('nama') }}</small> @endif
        </div>
        <div class="field">
            <label>Waktu Diambil <span class="req">*</span></label>
            <input type="datetime-local" name="bukti[{{ $i }}][waktu]" value="{{ $row['waktu'] ?? '' }}">
            @if ($err('waktu')) <small class="error">{{ $err('waktu') }}</small> @endif
        </div>
        <button type="button" class="bk-hapus" data-bk-hapus title="Hapus baris" aria-label="Hapus baris">✕</button>
    </div>
    <div class="row">
        <div class="field">
            <label>Pengambil <span class="req">*</span></label>
            <input type="text" name="bukti[{{ $i }}][pengambil]" value="{{ $row['pengambil'] ?? '' }}" maxlength="255"
                   placeholder="Nama / peran petugas">
            @if ($err('pengambil')) <small class="error">{{ $err('pengambil') }}</small> @endif
        </div>
        <div class="field">
            <label>Lokasi Penyimpanan <span class="req">*</span></label>
            <input type="text" name="bukti[{{ $i }}][lokasi]" value="{{ $row['lokasi'] ?? '' }}" maxlength="255"
                   placeholder="Mis. Storage forensik Pusat Data (terenkripsi)">
            @if ($err('lokasi')) <small class="error">{{ $err('lokasi') }}</small> @endif
        </div>
    </div>
    <div class="field">
        <label>Hash SHA-256</label>
        <input type="text" name="bukti[{{ $i }}][hash]" value="{{ $row['hash'] ?? '' }}" maxlength="80"
               placeholder="64 karakter heksadesimal, hasil sha256sum / shasum -a 256" spellcheck="false"
               style="font-family:ui-monospace,monospace; font-size:.85rem">
        @if ($err('hash')) <small class="error">{{ $err('hash') }}</small> @endif
    </div>
</div>
