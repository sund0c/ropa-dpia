{{--
    Satu kartu penerima Data Pribadi.
    $f      = 'transfer' (ke luar wilayah hukum Indonesia, ada Negara Tujuan) atau 'pengungkapan' (pihak lain).
    $i      = indeks baris (atau __i__ untuk template), $row = isian.
--}}
@php $denganNegara = $f === 'transfer'; @endphp
<div class="rep-row card" data-row>
    <div class="card-head">
        <strong>Penerima <span class="rep-count"></span></strong>
        <button type="button" class="icon" data-remove>✕ Hapus penerima</button>
    </div>

    @if ($denganNegara)
        <div class="row">
            <label class="field">
                <span>Organisasi penerima <span class="req">*</span></span>
                <input type="text" name="{{ $f }}[{{ $i }}][organisasi]" value="{{ $row['organisasi'] ?? '' }}">
                @error("$f.$i.organisasi") <small class="error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>Negara Tujuan <span class="req">*</span></span>
                <select name="{{ $f }}[{{ $i }}][negara]">
                    <option value="">— Pilih negara —</option>
                    @foreach (\App\Support\Negara::DAFTAR as $kode => $nama)
                        <option value="{{ $kode }}" @selected(($row['negara'] ?? '') === $kode)>{{ $nama }}</option>
                    @endforeach
                </select>
                @error("$f.$i.negara") <small class="error">{{ $message }}</small> @enderror
            </label>
        </div>
    @else
        <label class="field">
            <span>Organisasi penerima <span class="req">*</span></span>
            <input type="text" name="{{ $f }}[{{ $i }}][organisasi]" value="{{ $row['organisasi'] ?? '' }}">
            @error("$f.$i.organisasi") <small class="error">{{ $message }}</small> @enderror
        </label>
    @endif

    <label class="field">
        <span>Kontak / PIC penerima <span class="req">*</span></span>
        <input type="text" name="{{ $f }}[{{ $i }}][kontak]" value="{{ $row['kontak'] ?? '' }}"
               placeholder="Nama PIC, email, atau nomor telepon">
        @error("$f.$i.kontak") <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Tujuan pengiriman <span class="req">*</span></span>
        <textarea name="{{ $f }}[{{ $i }}][tujuan]" rows="3">{{ $row['tujuan'] ?? '' }}</textarea>
        @error("$f.$i.tujuan") <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Mekanisme pengiriman <span class="req">*</span></span>
        <textarea name="{{ $f }}[{{ $i }}][mekanisme]" rows="3"
                  placeholder="Contoh: API melalui HTTPS (TLS 1.2+), SFTP, surat dinas dengan lampiran terenkripsi">{{ $row['mekanisme'] ?? '' }}</textarea>
        @error("$f.$i.mekanisme") <small class="error">{{ $message }}</small> @enderror
    </label>

    <div class="field">
        <span>Data pribadi yang dikirim <span class="req">*</span></span>
        <small class="hint" data-transfer-empty>Centang jenis data pribadi (umum/spesifik) di atas terlebih dahulu.</small>
        <div class="checks">
            @foreach ($semuaJenis as $key => $label)
                <label class="check" data-transfer-option>
                    <input type="checkbox" name="{{ $f }}[{{ $i }}][data][]" value="{{ $key }}"
                           @checked(in_array($key, (array) ($row['data'] ?? []), true))>
                    {{ $label }}
                </label>
            @endforeach
        </div>
        @error("$f.$i.data")   <small class="error">{{ $message }}</small> @enderror
        @error("$f.$i.data.*") <small class="error">{{ $message }}</small> @enderror
    </div>
</div>
