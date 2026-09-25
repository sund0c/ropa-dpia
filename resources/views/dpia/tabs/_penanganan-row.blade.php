<div class="rep-row card" data-row>
    <div class="card-head">
        <strong>Langkah <span class="rep-count"></span></strong>
        <button type="button" class="icon" data-remove>✕ Hapus</button>
    </div>

    <label class="field">
        <span>Langkah Mitigasi yang Direncanakan <span class="req">*</span></span>
        <textarea name="residual[{{ $rid }}][{{ $i }}][penanganan][{{ $j }}][langkah]" rows="3"
                  placeholder="Contoh: Menyusun kebijakan retensi data pribadi sesuai aturan yang berlaku">{{ $prow['langkah'] ?? '' }}</textarea>
        @error("residual.$rid.$i.penanganan.$j.langkah") <small class="error">{{ $message }}</small> @enderror
    </label>

    <div class="row3">
        <label class="field">
            <span>Penanggung Jawab <span class="req">*</span></span>
            <input type="text" name="residual[{{ $rid }}][{{ $i }}][penanganan][{{ $j }}][pj]" value="{{ $prow['pj'] ?? '' }}"
                   placeholder="Contoh: Kepala Bidang TI">
            @error("residual.$rid.$i.penanganan.$j.pj") <small class="error">{{ $message }}</small> @enderror
        </label>
        <div class="field">
            <span>Target Waktu Penyelesaian <span class="req">*</span></span>
            <div class="bulan-tahun">
                <select name="residual[{{ $rid }}][{{ $i }}][penanganan][{{ $j }}][bulan]" aria-label="Bulan">
                    <option value="">Bulan</option>
                    @foreach ($bulanNama as $b => $nm)
                        <option value="{{ $b }}" @selected((int) ($prow['bulan'] ?? 0) === $b)>{{ $nm }}</option>
                    @endforeach
                </select>
                <select name="residual[{{ $rid }}][{{ $i }}][penanganan][{{ $j }}][tahun]" aria-label="Tahun">
                    <option value="">Tahun</option>
                    @foreach ($tahunPilihan as $t)
                        <option value="{{ $t }}" @selected((int) ($prow['tahun'] ?? 0) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            @error("residual.$rid.$i.penanganan.$j.bulan") <small class="error">{{ $message }}</small> @enderror
            @error("residual.$rid.$i.penanganan.$j.tahun") <small class="error">{{ $message }}</small> @enderror
        </div>
        <label class="field">
            <span>Status <span class="req">*</span></span>
            <select name="residual[{{ $rid }}][{{ $i }}][penanganan][{{ $j }}][status]">
                <option value="">— Pilih —</option>
                @foreach ($options['status_penanganan'] as $key => $label)
                    <option value="{{ $key }}" @selected(($prow['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error("residual.$rid.$i.penanganan.$j.status") <small class="error">{{ $message }}</small> @enderror
        </label>
    </div>
</div>
