@php
    use App\Support\MetodologiRisiko as MR;
    use App\Support\PetaRisiko as PR;

    $m = PR::nilai($d);
    if (session()->hasOldInput()) {
        $m = array_replace_recursive($m, array_filter([
            'kategori_batas' => (array) old('kategori_batas', []),
            'kemungkinan'    => (array) old('kemungkinan', []),
            'dampak'         => (array) old('dampak', []),
        ]));
    }

    $kat   = MR::KATEGORI;
    $batas = $m['kategori_batas'];
    $warna = collect($kat)->map(fn ($k) => $k['warna'])->all();
    $teks  = fn (int $lv) => in_array($lv, MR::TEKS_PUTIH, true) ? '#fff' : '#111';
@endphp

<style>
    .peta h3.sub { font-size: 1rem; margin: 1.75rem 0 .6rem; padding-bottom: .3rem; border-bottom: 1px solid #e5e7eb; }
    .peta h3.sub:first-of-type { margin-top: .5rem; }
    .peta .bar-default { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: .5rem; }
    .peta .tabel-wrap { overflow-x: auto; }
    .peta table { border-collapse: collapse; }
    .peta .tbl { width: 100%; }
    .peta .tbl th, .peta .tbl td { border: 1px solid #d1d5db; padding: .45rem .5rem; vertical-align: top; text-align: left; }
    .peta .tbl th { background: #f3f4f6; font-size: .88rem; }
    .peta input[type=text], .peta textarea { width: 100%; box-sizing: border-box; padding: .4rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; background: #fff; }
    .peta input.angka { width: 4.5rem; padding: .35rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
    .peta .lv { font-weight: bold; text-align: center; }

    /* Matriks */
    .peta .matriks th, .peta .matriks td { border: 1px solid #374151; padding: .4rem .55rem; text-align: center; font-size: .85rem; }
    .peta .matriks th { background: #fff; font-weight: normal; }
    .peta .matriks td { width: 4.6rem; height: 2.5rem; font-weight: bold; }
    .peta .matriks th.vertikal span { writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; }

    /* Dampak: baris = dimensi, kolom = level */
    .peta .dampak { min-width: 860px; table-layout: fixed; }
    .peta .dampak th.dim { width: 8.5rem; }
    .peta .dampak th.lvh { text-align: center; font-size: .8rem; line-height: 1.25; }
    .peta .dampak th.lvh b { display: block; font-size: 1rem; }
    .peta .dampak tr.grup td { background: #eef2ff; color: #1e3a8a; font-weight: bold; font-size: .82rem; letter-spacing: .3px; text-transform: uppercase; }
    .peta .dampak td.dim { font-weight: bold; font-size: .88rem; background: #fafafa; }
    .peta .dampak textarea, .peta .dampak input[type=text] { font-size: .8rem; line-height: 1.35; }
    .peta .dampak textarea { resize: vertical; min-height: 4.2rem; }
    .peta .error { display: block; }
    .peta button.small { padding: .35rem .8rem; font-size: .85rem; white-space: nowrap; }
</style>

<div class="peta" id="peta" data-warna="{{ json_encode($warna) }}" data-putih="{{ json_encode(MR::TEKS_PUTIH) }}">

    <div class="bar-default">
        <p class="hint" style="margin:0">
            Nilai di bawah ini adalah kriteria default. Ubah bila instansi Anda memakai kriteria yang berbeda,
            lalu klik <strong>Simpan tab ini</strong>.
        </p>
        <button type="submit" form="form-default-peta" class="secondary small"
                onclick="return confirm('Kembalikan seluruh Peta Risiko ke nilai default? Perubahan yang sudah disimpan akan hilang.')">
            ↺ Kembalikan ke default
        </button>
    </div>

    {{-- 1. MATRIKS --}}
    <h3 class="sub">1. Matriks Risiko</h3>
    <div class="tabel-wrap">
        <table class="matriks">
            <tr>
                <th colspan="3" rowspan="3">Matriks Risiko 5×5</th>
                <th colspan="5">Level Dampak</th>
            </tr>
            <tr>
                @foreach (range(1, 5) as $dp) <th>{{ $dp }}</th> @endforeach
            </tr>
            <tr>
                @foreach (range(1, 5) as $dp) <th>{{ $m['dampak'][$dp]['nama'] }}</th> @endforeach
            </tr>
            @foreach (range(1, 5) as $k)
                <tr>
                    @if ($k === 1)
                        <th rowspan="5" class="vertikal"><span>Level Kemungkinan</span></th>
                    @endif
                    <th>{{ $k }}</th>
                    <th>{{ $m['kemungkinan'][$k]['nama'] }}</th>
                    @foreach (range(1, 5) as $dp)
                        @php $skor = $k * $dp; $lv = PR::level($skor, $m); @endphp
                        <td data-skor="{{ $skor }}" style="background:{{ $kat[$lv]['warna'] }}; color:{{ $teks($lv) }}">{{ $skor }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>
    <small class="hint">Angka matriks = Kemungkinan × Dampak. Warna mengikuti rentang pada tabel 2.</small>

    {{-- 2. KATEGORI --}}
    <h3 class="sub">2. Kategori Tingkat Risiko</h3>
    <div class="tabel-wrap">
        <table class="tbl" style="max-width:640px">
            <thead>
                <tr><th colspan="2">Level Risiko</th><th>Rentang Besaran Risiko</th><th style="width:7rem">Warna</th></tr>
            </thead>
            <tbody>
                @foreach ($kat as $i => $k)
                    <tr>
                        <td style="width:2rem">{{ $i }}</td>
                        <td>{{ $k['nama'] }}</td>
                        <td>
                            <span data-bawah="{{ $i }}">{{ $i === 1 ? 1 : (int) $batas[$i - 1] + 1 }}</span> –
                            @if ($i < 5)
                                <input type="number" class="angka" min="1" max="24"
                                       name="kategori_batas[{{ $i }}]" value="{{ $batas[$i] }}">
                                @error("kategori_batas.$i") <small class="error">{{ $message }}</small> @enderror
                            @else
                                25
                            @endif
                        </td>
                        <td style="background:{{ $k['warna'] }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 3. KEMUNGKINAN --}}
    <h3 class="sub">3. Skala Kemungkinan Terjadi (<em>Likelihood</em>)</h3>
    <div class="tabel-wrap">
        <table class="tbl">
            <thead>
                <tr><th style="width:4rem">Skor</th><th style="width:30%">Level Kemungkinan</th><th>Periode Kejadian</th></tr>
            </thead>
            <tbody>
                @foreach (range(1, 5) as $k)
                    <tr>
                        <td class="lv" style="background:{{ $kat[$k]['warna'] }}; color:{{ $teks($k) }}">{{ $k }}</td>
                        <td style="background:{{ $kat[$k]['warna'] }}; color:{{ $teks($k) }}; font-weight:bold">{{ $m['kemungkinan'][$k]['nama'] }}</td>
                        <td>
                            <input type="text" name="kemungkinan[{{ $k }}][periode]" value="{{ $m['kemungkinan'][$k]['periode'] }}" maxlength="255">
                            @error("kemungkinan.$k.periode") <small class="error">{{ $message }}</small> @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 4. DAMPAK --}}
    <h3 class="sub">4. Skala Dampak Kejadian (<em>Impact</em>)</h3>
    <p class="hint" style="margin-top:0">
        Setiap baris adalah satu dimensi dampak; kolom menunjukkan kriteria untuk tiap level.
        Saat menilai risiko, gunakan level tertinggi dari dimensi yang terdampak.
    </p>
    <div class="tabel-wrap">
        <table class="tbl dampak">
            <thead>
                <tr>
                    <th class="dim">Dimensi Dampak</th>
                    @foreach (range(1, 5) as $dp)
                        <th class="lvh" style="background:{{ $kat[$dp]['warna'] }}; color:{{ $teks($dp) }}">
                            <b>{{ $dp }}</b>{{ $m['dampak'][$dp]['nama'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach (['finansial' => 'Dampak Finansial', 'non' => 'Dampak Non-Finansial'] as $grup => $judulGrup)
                    <tr class="grup"><td colspan="6">{{ $judulGrup }}</td></tr>
                    @foreach (PR::DIMENSI as $kunci => $dim)
                        @continue($dim['kelompok'] !== $grup)
                        <tr>
                            <td class="dim">{{ $dim['label'] }}</td>
                            @foreach (range(1, 5) as $dp)
                                <td>
                                    <textarea name="dampak[{{ $dp }}][{{ $kunci }}]" rows="3" maxlength="1000">{{ $m['dampak'][$dp][$kunci] ?? '' }}</textarea>
                                    @error("dampak.$dp.$kunci") <small class="error">{{ $message }}</small> @enderror
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <small class="hint">Tabel dapat digeser ke samping pada layar sempit.</small>
</div>

<script>
(function () {
    const peta = document.getElementById('peta');
    if (!peta) return;

    const warna = JSON.parse(peta.dataset.warna);
    const putih = JSON.parse(peta.dataset.putih);

    const batas = () => [1, 2, 3, 4].map(i =>
        parseInt(peta.querySelector(`[name="kategori_batas[${i}]"]`)?.value, 10));

    const level = (skor, b) => {
        for (let i = 0; i < 4; i++) if (!isNaN(b[i]) && skor <= b[i]) return i + 1;
        return 5;
    };

    function render() {
        const b = batas();
        peta.querySelectorAll('[data-skor]').forEach(td => {
            const lv = level(parseInt(td.dataset.skor, 10), b);
            td.style.background = warna[lv];
            td.style.color = putih.includes(lv) ? '#fff' : '#111';
        });
        peta.querySelectorAll('[data-bawah]').forEach(el => {
            const i = parseInt(el.dataset.bawah, 10);
            el.textContent = i === 1 ? 1 : (isNaN(b[i - 2]) ? '?' : b[i - 2] + 1);
        });
    }

    // Batas kategori diubah → warna matriks & batas bawah ikut berubah
    peta.addEventListener('input', (e) => {
        if ((e.target.name || '').startsWith('kategori_batas')) render();
    });
})();
</script>
