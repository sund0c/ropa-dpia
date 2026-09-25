@php
    $MR     = \App\Support\MetodologiRisiko::class;
    $pen    = $dpia['penilaian'] ?? [];
    $m      = $MR::nilai($pen['metodologi'] ?? []);
    $risiko = $MR::daftarRisiko($pen);

    // Risiko yang residualnya masih Tinggi/Sangat Tinggi (level >= 4) menurut Tab VII
    $masihTinggi = [];
    foreach ((array) ($dpia['pengendalian']['residual'] ?? []) as $rid => $daftar) {
        $rid = (string) $rid;
        if (! isset($risiko[$rid])) continue;
        foreach ((array) $daftar as $res) {
            $skor = (int) ($res['kemungkinan'] ?? 0) * (int) ($res['dampak'] ?? 0);
            if ($skor && $MR::level($skor, $m) >= 4) {
                $masihTinggi[] = $risiko[$rid]['kode'];
                break;
            }
        }
    }

    $konsultasi = old('lembaga_konsultasi', $d['lembaga_konsultasi'] ?? null);
@endphp

{{-- A. PPDP --}}
<h3 class="sub">A. Rekomendasi dengan PPDP</h3>

<x-readonly label="Nama PPDP" :value="$ropa['organisasi']['nama_ppdp']" hint="Diambil dari RoPA Tab I." />

<x-field name="ppdp_tanggal" label="Tanggal Rekomendasi PPDP" type="date"
         :value="$d['ppdp_tanggal'] ?? null" required />

<x-field name="ppdp_saran" label="Saran/Rekomendasi PPDP" type="textarea" rows="10"
         :value="$d['ppdp_saran'] ?? null" required />

<x-field name="ppdp_tindak_lanjut" label="Tindak Lanjut atas Saran PPDP" type="textarea" rows="10"
         :value="$d['ppdp_tindak_lanjut'] ?? null" required />

{{-- B. Lembaga --}}
<h3 class="sub">B. Rekomendasi dengan Lembaga</h3>

<div class="alert warn">
    Konsultasi ke Lembaga diperlukan jika terdapat kondisi:
    <ul style="margin:.4rem 0 0">
        <li>Pemrosesan berpotensi menimbulkan kerugian materiil dan/atau imateriil signifikan pada Subjek Data Pribadi yang belum sepenuhnya dapat dimitigasi; dan/atau</li>
        <li>Tidak tersedia langkah mitigasi yang memadai terhadap risiko yang teridentifikasi.</li>
    </ul>
</div>

@if ($masihTinggi)
    <div class="alert err">
        Berdasarkan Tab VII, risiko residual <strong>{{ implode(', ', $masihTinggi) }}</strong> masih berada pada level
        Tinggi/Sangat Tinggi. Konsultasi dengan Lembaga sangat disarankan.
    </div>
@endif

<fieldset class="field">
    <legend>Apakah konsultasi dengan Lembaga dilakukan? <span class="req">*</span></legend>
    <div class="radios">
        <label class="check">
            <input type="radio" name="lembaga_konsultasi" value="ya" data-lembaga-toggle @checked($konsultasi === 'ya')>
            Ya, konsultasi dilakukan
        </label>
        <label class="check">
            <input type="radio" name="lembaga_konsultasi" value="tidak" data-lembaga-toggle @checked($konsultasi === 'tidak')>
            Tidak diperlukan
        </label>
    </div>
    @error('lembaga_konsultasi') <small class="error">{{ $message }}</small> @enderror
</fieldset>

<div data-lembaga @if ($konsultasi !== 'ya') hidden @endif>
    <x-field name="lembaga_tanggal" label="Tanggal Rekomendasi Lembaga" type="date"
             :value="$d['lembaga_tanggal'] ?? null" required />

    <x-field name="lembaga_saran" label="Saran/Rekomendasi Lembaga" type="textarea" rows="10"
             :value="$d['lembaga_saran'] ?? null" required />

    <x-field name="lembaga_tindak_lanjut" label="Tindak Lanjut atas Saran Lembaga" type="textarea" rows="10"
             :value="$d['lembaga_tindak_lanjut'] ?? null" required />
</div>

<script>
(function () {
    const box = document.querySelector('[data-lembaga]');
    if (!box) return;
    document.addEventListener('change', (e) => {
        if (e.target.matches('[data-lembaga-toggle]')) box.hidden = e.target.value !== 'ya';
    });
})();
</script>
