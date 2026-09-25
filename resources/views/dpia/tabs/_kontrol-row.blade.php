<div class="rep-row card" data-row>
    <div class="card-head">
        <strong>Langkah <span class="rep-count"></span></strong>
        <button type="button" class="icon" data-remove>✕ Hapus</button>
    </div>

    <label class="field">
        <span>Langkah Mitigasi <span class="req">*</span></span>
        <textarea name="kontrol[{{ $rid }}][{{ $i }}][langkah]" rows="3"
                  placeholder="Contoh: Role-Based Access Control pada aplikasi">{{ $row['langkah'] ?? '' }}</textarea>
        @error("kontrol.$rid.$i.langkah") <small class="error">{{ $message }}</small> @enderror
    </label>

    <div class="row3">
        <label class="field">
            <span>Status <span class="req">*</span></span>
            <select name="kontrol[{{ $rid }}][{{ $i }}][status]">
                <option value="">— Pilih —</option>
                @foreach ($options['status_kontrol'] as $key => $label)
                    <option value="{{ $key }}" @selected(($row['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error("kontrol.$rid.$i.status") <small class="error">{{ $message }}</small> @enderror
        </label>
        <label class="field">
            <span>Bukti Dukung</span>
            <input type="text" name="kontrol[{{ $rid }}][{{ $i }}][bukti]" value="{{ $row['bukti'] ?? '' }}"
                   placeholder="Contoh: Modul hak akses aplikasi">
            @error("kontrol.$rid.$i.bukti") <small class="error">{{ $message }}</small> @enderror
        </label>
        <label class="field">
            <span>Keterangan <span class="req">*</span></span>
            <select name="kontrol[{{ $rid }}][{{ $i }}][jenis]">
                <option value="">— Pilih —</option>
                @foreach ($options['jenis_kontrol'] as $key => $label)
                    <option value="{{ $key }}" @selected(($row['jenis'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error("kontrol.$rid.$i.jenis") <small class="error">{{ $message }}</small> @enderror
        </label>
    </div>
    <small class="hint">Bukti dukung wajib diisi bila status Aktif.</small>
</div>
