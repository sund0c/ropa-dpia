@php
    $nk       = (int) ($row['kemungkinan'] ?? 0);
    $nd       = (int) ($row['dampak'] ?? 0);
    $mitigasi = ($row['keputusan'] ?? '') === 'mitigasi';
    $pRows    = (array) ($row['penanganan'] ?? []);
    if (empty($pRows)) $pRows = [[]];
@endphp
<div class="rep-row card" data-row data-res>
    <div class="card-head">
        <strong>Risiko Residual <span class="rep-count"></span></strong>
        <button type="button" class="icon" data-remove>✕ Hapus</button>
    </div>

    <label class="field">
        <span>Risiko Residual <span class="req">*</span></span>
        <textarea name="residual[{{ $rid }}][{{ $i }}][risiko]" rows="3"
                  placeholder="Contoh: Tidak ada kebijakan retensi">{{ $row['risiko'] ?? '' }}</textarea>
        @error("residual.$rid.$i.risiko") <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Langkah Mitigasi <span class="req">*</span></span>
        <textarea name="residual[{{ $rid }}][{{ $i }}][langkah]" rows="3"
                  placeholder="Contoh: Draf Kebijakan PDP">{{ $row['langkah'] ?? '' }}</textarea>
        @error("residual.$rid.$i.langkah") <small class="error">{{ $message }}</small> @enderror
    </label>

    <div class="row3">
        <label class="field">
            <span>Kemungkinan (Residual) <span class="req">*</span></span>
            <select name="residual[{{ $rid }}][{{ $i }}][kemungkinan]" data-res-k>
                <option value="">— Pilih —</option>
                @foreach (range(1, 5) as $v)
                    <option value="{{ $v }}" @selected($nk === $v)>{{ $v }} – {{ $m['kemungkinan'][$v]['nama'] }}</option>
                @endforeach
            </select>
            @error("residual.$rid.$i.kemungkinan") <small class="error">{{ $message }}</small> @enderror
        </label>
        <label class="field">
            <span>Dampak (Residual) <span class="req">*</span></span>
            <select name="residual[{{ $rid }}][{{ $i }}][dampak]" data-res-d>
                <option value="">— Pilih —</option>
                @foreach (range(1, 5) as $v)
                    <option value="{{ $v }}" @selected($nd === $v)>{{ $v }} – {{ $m['dampak'][$v]['nama'] }}</option>
                @endforeach
            </select>
            @error("residual.$rid.$i.dampak") <small class="error">{{ $message }}</small> @enderror
        </label>
        <label class="field">
            <span>Keputusan <span class="req">*</span></span>
            <select name="residual[{{ $rid }}][{{ $i }}][keputusan]" data-res-keputusan>
                <option value="">— Pilih —</option>
                @foreach ($options['keputusan'] as $key => $label)
                    <option value="{{ $key }}" @selected(($row['keputusan'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error("residual.$rid.$i.keputusan") <small class="error">{{ $message }}</small> @enderror
        </label>
    </div>

    <div class="skor-baris">Skor Risiko Residual: <span class="skor-badge" data-res-skor>–</span></div>
    <small class="hint" data-res-warn hidden style="color:#b45309">
        Skor residual lebih tinggi dari skor inheren. Periksa kembali penilaian atau langkah mitigasinya.
    </small>

    {{-- 3. Langkah penanganan: hanya bila keputusan Mitigasi --}}
    <div class="penanganan" data-penanganan @unless ($mitigasi) hidden @endunless>
        <h5 class="sub5">3. Langkah Penanganan Risiko</h5>
        <div class="field repeater" data-repeater>
            <div class="rep-rows" data-rows>
                @foreach ($pRows as $j => $prow)
                    @include('dpia.tabs._penanganan-row', ['rid' => $rid, 'i' => $i, 'j' => $j, 'prow' => $prow])
                @endforeach
            </div>
            <template data-template data-placeholder="__j__">
                @include('dpia.tabs._penanganan-row', ['rid' => $rid, 'i' => $i, 'j' => '__j__', 'prow' => []])
            </template>
            <button type="button" class="secondary small" data-add>+ Tambah langkah penanganan</button>
            @error("residual.$rid.$i.penanganan") <small class="error">{{ $message }}</small> @enderror
        </div>
    </div>
</div>
