@php
    $MR = \App\Support\MetodologiRisiko::class;
    $h  = \App\Support\RingkasanDpia::hitung($dpia);
    $u  = \App\Support\RingkasanDpia::usulan($h);

    $hasOld    = session()->hasOldInput();
    $ringkasan = $hasOld ? (array) old('ringkasan', []) : ($d['ringkasan'] ?? []);
    $efek      = old('efektivitas', $d['efektivitas'] ?? null);
    $kep       = old('keputusan_akhir', $d['keputusan_akhir'] ?? null);

    $aspek = [
        'kebutuhan' => 'Kebutuhan dan Proporsionalitas',
        'inheren'   => 'Risiko Inheren (Sebelum Mitigasi) Tertinggi',
        'residual'  => 'Risiko Residual (Setelah Mitigasi) Tertinggi',
    ];

    $residualTinggi = $h['residual'] && $h['residual']['level'] >= 4;
    $namaLevel      = fn ($r) => $r ? $MR::KATEGORI[$r['level']]['nama'] : null;
@endphp

{{-- 1 --}}
<div class="bar-default" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap">
    <h3 class="sub" style="margin:0; border:0; flex:1">1. Ringkasan Penilaian</h3>
    <button type="button" class="secondary small" data-ringkasan-default style="margin-top:0">↺ Kembalikan ke default</button>
</div>
<p class="hint">
    Kolom kesimpulan terisi awal dari hasil Tab V–VII bila belum pernah disimpan. Sesuaikan narasinya bila perlu.
    Tombol <strong>Kembalikan ke default</strong> mengisi ulang ketiga kolom dari data Tab V–VII yang sudah disimpan;
    klik <strong>Simpan</strong> setelahnya untuk menyimpan.
</p>

<div class="tabel-wrap">
    <table class="tabel-form">
        <thead>
            <tr><th style="width:32%">Aspek</th><th>Kesimpulan <span class="req">*</span></th></tr>
        </thead>
        <tbody>
            @foreach ($aspek as $key => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>
                        <textarea name="ringkasan[{{ $key }}]" rows="5" data-ringkasan="{{ $key }}">{{ $ringkasan[$key] ?? $u[$key] }}</textarea>
                        @error("ringkasan.$key") <small class="error">{{ $message }}</small> @enderror
                    </td>
                </tr>
            @endforeach
            <tr>
                <td>Efektivitas Langkah Mitigasi</td>
                <td>
                    <div class="radios">
                        @foreach ($options['efektivitas'] as $key => $label)
                            <label class="check">
                                <input type="radio" name="efektivitas" value="{{ $key }}" @checked($efek === $key)> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @if ($h['inheren'] && $h['residual'])
                        <small class="hint">
                            Skor tertinggi sebelum mitigasi {{ $h['inheren']['skor'] }} ({{ $namaLevel($h['inheren']) }}),
                            setelah mitigasi {{ $h['residual']['skor'] }} ({{ $namaLevel($h['residual']) }}).
                        </small>
                    @endif
                    @error('efektivitas') <small class="error">{{ $message }}</small> @enderror
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- 2 --}}
<h3 class="sub">2. Keputusan Akhir Pemrosesan <span class="req">*</span></h3>
<div class="checks">
    @foreach ($options['keputusan_akhir'] as $key => $label)
        <label class="check">
            <input type="radio" name="keputusan_akhir" value="{{ $key }}" @checked($kep === $key)>
            <span>
                {{ $label }}
                @if ($key === 'lanjut' && $residualTinggi)
                    <br><small style="color:#b45309">
                        Perhatian: risiko residual tertinggi ({{ $h['residual']['kode'] }}) masih level {{ $namaLevel($h['residual']) }}.
                    </small>
                @endif
                @if ($key === 'tunda' && $h['lembaga'] !== 'ya')
                    <br><small style="color:#b45309">
                        Perhatian: di Tab VIII konsultasi dengan Lembaga {{ $h['lembaga'] === 'tidak' ? 'dinyatakan tidak dilakukan' : 'belum diisi' }}.
                    </small>
                @endif
            </span>
        </label>
    @endforeach
</div>
@error('keputusan_akhir') <small class="error">{{ $message }}</small> @enderror

<x-field name="catatan" label="Catatan Tambahan" type="textarea" rows="5"
         :value="$d['catatan'] ?? null" style="margin-top:1rem" />

<style>
    .ringkasan-publik-info { margin: 0 0 1rem; padding: .75rem 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; font-size: .9rem; line-height: 1.5; }
    .ringkasan-publik-info .peringatan { margin-top: .5rem !important; padding: .55rem .75rem; background: #fef3c7; border-left: 4px solid #d97706; border-radius: 0 6px 6px 0; color: #78350f; }
</style>

{{-- 3 --}}
<h3 class="sub">3. Ringkasan Publik</h3>
<div class="ringkasan-publik-info">
    <p style="margin:0 0 .4rem">
        <strong>Ringkasan Publik</strong> adalah versi singkat DPIA yang boleh dibaca masyarakat, sebagai wujud
        transparansi pemrosesan data pribadi. Isinya menjelaskan dengan bahasa sederhana:
    </p>
    <ul style="margin:0 0 .4rem; padding-left:1.2rem">
        <li>layanan apa ini dan untuk apa data pribadi diproses;</li>
        <li>data pribadi apa saja yang dikumpulkan dan dari siapa;</li>
        <li>dasar pemrosesan dan berapa lama data disimpan;</li>
        <li>dengan siapa data dibagikan (bila ada), secara umum;</li>
        <li>hak subjek data dan cara menghubungi instansi untuk menggunakannya;</li>
        <li>pernyataan umum bahwa instansi telah menilai dampak dan menerapkan pengamanan yang memadai.</li>
    </ul>
    <p class="peringatan" style="margin:0">
        <strong>⚠ Perhatian:</strong> jangan mencantumkan informasi yang berpotensi menjadi ancaman bagi data pribadi
        yang diproses, misalnya rincian kendali keamanan dan teknologi yang dipakai, alamat server/IP, celah atau
        risiko yang teridentifikasi, skor risiko, rencana tindak lanjut, nama atau kontak pribadi petugas, maupun
        data pribadi itu sendiri. Cukup nyatakan secara umum, misalnya <em>"data dilindungi dengan pengamanan teknis
        dan organisasional yang sesuai"</em>.
    </p>
</div>
<x-field name="ringkasan_publik" label="Ringkasan Publik" type="textarea" rows="10"
         :value="$d['ringkasan_publik'] ?? null" maxlength="5000"
         hint="Teks ini dicetak apa adanya pada PDF." />

<script>
(function () {
    const usulan = @json($u);
    document.querySelector('[data-ringkasan-default]')?.addEventListener('click', () => {
        if (!confirm('Isi ulang Ringkasan Penilaian dari data Tab V–VII? Perubahan yang Anda ketik di ketiga kolom akan diganti.')) return;
        document.querySelectorAll('[data-ringkasan]').forEach(t => { t.value = usulan[t.dataset.ringkasan] ?? ''; });
    });
})();
</script>
