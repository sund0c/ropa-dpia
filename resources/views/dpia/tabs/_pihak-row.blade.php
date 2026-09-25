<div class="rep-row card" data-row>
    <div class="card-head">
        <strong>Pihak <span class="rep-count"></span></strong>
        <button type="button" class="icon" data-remove>✕ Hapus pihak</button>
    </div>

    <label class="field">
        <span>Pihak <span class="req">*</span></span>
        <input type="text" name="pihak[{{ $i }}][nama]" value="{{ $row['nama'] ?? '' }}"
               placeholder="Contoh: Tenaga medis dan tenaga administrasi Puskesmas/RSUD">
        @error("pihak.$i.nama") <small class="error">{{ $message }}</small> @enderror
    </label>

    <div class="row">
        <label class="field">
            <span>Peran <span class="req">*</span></span>
            <select name="pihak[{{ $i }}][peran]">
                <option value="">— Pilih peran —</option>
                @foreach ($options['peran'] as $key => $label)
                    <option value="{{ $key }}" @selected(($row['peran'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error("pihak.$i.peran") <small class="error">{{ $message }}</small> @enderror
        </label>

        <div class="field">
            <span>Dalam/Luar Negeri <span class="req">*</span></span>
            <div class="radios">
                @foreach ($options['lokasi'] as $key => $label)
                    <label class="check">
                        <input type="radio" name="pihak[{{ $i }}][lokasi]" value="{{ $key }}"
                               @checked(($row['lokasi'] ?? '') === $key)> {{ $label }}
                    </label>
                @endforeach
            </div>
            @error("pihak.$i.lokasi") <small class="error">{{ $message }}</small> @enderror
            @if (($row['lokasi'] ?? '') === 'luar_negeri')
                <small class="hint">Transfer ke luar wilayah hukum Indonesia wajib memenuhi ketentuan Pasal 56 UU PDP.</small>
            @endif
        </div>
    </div>

    <div class="field">
        <span>Data yang Diakses <span class="req">*</span></span>
        <div class="checks">
            @foreach ($jenisRopa as $key => $label)
                <label class="check">
                    <input type="checkbox" name="pihak[{{ $i }}][data][]" value="{{ $key }}"
                           @checked(in_array($key, (array) ($row['data'] ?? []), true))> {{ $label }}
                </label>
            @endforeach
        </div>
        @error("pihak.$i.data")   <small class="error">{{ $message }}</small> @enderror
        @error("pihak.$i.data.*") <small class="error">{{ $message }}</small> @enderror
    </div>

    <label class="field">
        <span>Dasar Keterlibatan <span class="req">*</span></span>
        <textarea name="pihak[{{ $i }}][dasar]" rows="5">{{ $row['dasar'] ?? '' }}</textarea>
        @error("pihak.$i.dasar") <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Keterangan</span>
        <textarea name="pihak[{{ $i }}][keterangan]" rows="5">{{ $row['keterangan'] ?? '' }}</textarea>
        @error("pihak.$i.keterangan") <small class="error">{{ $message }}</small> @enderror
    </label>
</div>
