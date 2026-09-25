<div class="rep-row card" data-row>
    <div class="card-head">
        <strong>Penerima <span class="rep-count"></span></strong>
        <button type="button" class="icon" data-remove>✕ Hapus penerima</button>
    </div>

    <div class="row">
        <label class="field">
            <span>Organisasi penerima <span class="req">*</span></span>
            <input type="text" name="transfer[{{ $i }}][organisasi]" value="{{ $row['organisasi'] ?? '' }}">
            @error("transfer.$i.organisasi") <small class="error">{{ $message }}</small> @enderror
        </label>
        <label class="field">
            <span>Peran <span class="req">*</span></span>
            <select name="transfer[{{ $i }}][peran]">
                <option value="">— Pilih peran —</option>
                @foreach ($options['peran'] as $key => $label)
                    <option value="{{ $key }}" @selected(($row['peran'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error("transfer.$i.peran") <small class="error">{{ $message }}</small> @enderror
        </label>
    </div>

    <label class="field">
        <span>Kontak / PIC penerima <span class="req">*</span></span>
        <input type="text" name="transfer[{{ $i }}][kontak]" value="{{ $row['kontak'] ?? '' }}"
               placeholder="Nama PIC, email, atau nomor telepon">
        @error("transfer.$i.kontak") <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Tujuan pengiriman <span class="req">*</span></span>
        <textarea name="transfer[{{ $i }}][tujuan]" rows="3">{{ $row['tujuan'] ?? '' }}</textarea>
        @error("transfer.$i.tujuan") <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Mekanisme pengiriman <span class="req">*</span></span>
        <textarea name="transfer[{{ $i }}][mekanisme]" rows="3"
                  placeholder="Contoh: API melalui HTTPS (TLS 1.2+), SFTP, surat dinas dengan lampiran terenkripsi">{{ $row['mekanisme'] ?? '' }}</textarea>
        @error("transfer.$i.mekanisme") <small class="error">{{ $message }}</small> @enderror
    </label>

    <div class="field">
        <span>Data pribadi yang dikirim <span class="req">*</span></span>
        <small class="hint" data-transfer-empty>Centang jenis data pribadi (umum/spesifik) di atas terlebih dahulu.</small>
        <div class="checks">
            @foreach ($semuaJenis as $key => $label)
                <label class="check" data-transfer-option>
                    <input type="checkbox" name="transfer[{{ $i }}][data][]" value="{{ $key }}"
                           @checked(in_array($key, (array) ($row['data'] ?? []), true))>
                    {{ $label }}
                </label>
            @endforeach
        </div>
        @error("transfer.$i.data")   <small class="error">{{ $message }}</small> @enderror
        @error("transfer.$i.data.*") <small class="error">{{ $message }}</small> @enderror
    </div>
</div>
