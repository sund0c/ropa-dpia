@php
    $MR     = \App\Support\MetodologiRisiko::class;
    $hasOld = session()->hasOldInput();

    $m = $MR::nilai($d['metodologi'] ?? []);
    if ($hasOld) {
        $m = array_replace_recursive($m, (array) old('metodologi', []));
    }

    $kat    = $MR::KATEGORI;
    $batas  = $m['kategori_batas'];
    $warna  = collect($kat)->map(fn ($k) => $k['warna'])->all();
    $teks   = fn (int $lv) => in_array($lv, $MR::TEKS_PUTIH, true) ? '#fff' : '#111';
    $nama   = fn (string $jenis, int $i, string $kolom) => "metodologi[$jenis][$i][$kolom]";

    // Risiko inheren: input terakhir bila validasi gagal, lalu data tersimpan, lalu contoh default
    $inheren = $hasOld ? (array) old('inheren', []) : ($d['inheren'] ?? $MR::DEFAULT_INHEREN);
@endphp

<div id="metodologi" data-warna="{{ json_encode($warna) }}" data-putih="{{ json_encode($MR::TEKS_PUTIH) }}">

    <div class="bar-default">
        <p class="hint" style="margin:0">
            Nilai di bawah ini adalah metodologi default. Ubah bila instansi Anda memakai kriteria yang berbeda,
            lalu klik <strong>Simpan tab ini</strong>.
        </p>
        <button type="submit" form="form-default-metodologi" class="secondary small"
                onclick="return confirm('Kembalikan seluruh metodologi ke nilai default? Perubahan yang sudah disimpan akan hilang.')">
            ↺ Kembalikan ke default
        </button>
    </div>

    {{-- 1. MATRIKS --}}
    <h3 class="sub">1. Metodologi Penilaian Risiko dengan Matriks Risiko</h3>
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
                @foreach (range(1, 5) as $dp)
                    <th>{{ $m['dampak'][$dp]['nama'] }}</th>
                @endforeach
            </tr>
            @foreach (range(1, 5) as $k)
                <tr>
                    @if ($k === 1)
                        <th rowspan="5" class="vertikal"><span>Level Kemungkinan</span></th>
                    @endif
                    <th>{{ $k }}</th>
                    <th>{{ $m['kemungkinan'][$k]['nama'] }}</th>
                    @foreach (range(1, 5) as $dp)
                        @php $skor = $k * $dp; $lv = $MR::level($skor, $m); @endphp
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
        <table class="tabel-form" style="max-width:640px">
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
                                       name="metodologi[kategori_batas][{{ $i }}]" value="{{ $batas[$i] }}">
                                @error("metodologi.kategori_batas.$i") <small class="error">{{ $message }}</small> @enderror
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
        <table class="tabel-form">
            <thead>
                <tr><th style="width:4rem">Skor</th><th style="width:32%">Level Kemungkinan</th><th>Periode Kejadian</th></tr>
            </thead>
            <tbody>
                @foreach (range(1, 5) as $k)
                    <tr>
                        <td style="background:{{ $kat[$k]['warna'] }}; color:{{ $teks($k) }}; text-align:center; font-weight:bold">{{ $k }}</td>
                        <td style="background:{{ $kat[$k]['warna'] }}; color:{{ $teks($k) }}; font-weight:bold">
                            {{ $m['kemungkinan'][$k]['nama'] }}
                        </td>
                        <td>
                            <input type="text" name="{{ $nama('kemungkinan', $k, 'periode') }}" value="{{ $m['kemungkinan'][$k]['periode'] }}">
                            @error("metodologi.kemungkinan.$k.periode") <small class="error">{{ $message }}</small> @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 4. DAMPAK --}}
    <h3 class="sub">4. Skala Dampak Kejadian (<em>Impact</em>)</h3>
    <div class="tabel-wrap">
        <table class="tabel-form dampak">
            <thead>
                <tr>
                    <th rowspan="2" style="width:9rem">Level Dampak</th>
                    <th rowspan="2" style="width:3rem">Skor</th>
                    <th>Dampak Finansial</th>
                    <th colspan="3">Dampak Non-Finansial</th>
                </tr>
                <tr>
                    <th style="width:8rem">Rupiah</th><th>Reputasi</th><th>Kepatuhan Regulasi</th><th>Hukum</th>
                </tr>
            </thead>
            <tbody>
                @foreach (range(1, 5) as $dp)
                    <tr>
                        <td style="background:{{ $kat[$dp]['warna'] }}; color:{{ $teks($dp) }}; font-weight:bold">
                            {{ $m['dampak'][$dp]['nama'] }}
                        </td>
                        <td style="text-align:center; font-weight:bold">{{ $dp }}</td>
                        <td>
                            <input type="text" name="{{ $nama('dampak', $dp, 'finansial') }}" value="{{ $m['dampak'][$dp]['finansial'] }}">
                            @error("metodologi.dampak.$dp.finansial") <small class="error">{{ $message }}</small> @enderror
                        </td>
                        @foreach (['reputasi', 'kepatuhan', 'hukum'] as $kolom)
                            <td>
                                <textarea name="{{ $nama('dampak', $dp, $kolom) }}" rows="6">{{ $m['dampak'][$dp][$kolom] }}</textarea>
                                @error("metodologi.dampak.$dp.$kolom") <small class="error">{{ $message }}</small> @enderror
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 5. RISIKO INHEREN (tombol default tersendiri, tidak ikut reset metodologi) --}}
<div id="inheren">
    <div class="bar-default" style="margin-top:1.75rem">
        <h3 class="sub" style="margin:0; border:0; flex:1">5. Identifikasi dan Analisis Risiko Inheren</h3>
        <button type="submit" form="form-default-inheren" class="secondary small"
                onclick="return confirm('Kembalikan daftar risiko inheren ke contoh default? Risiko yang sudah disimpan akan hilang.')">
            ↺ Kembalikan risiko ke default
        </button>
    </div>
    <p class="hint">
        Siklus pemrosesan mengikuti Pasal 16 ayat (1) UU PDP dan tidak dapat diubah. Setiap siklus boleh memiliki
        lebih dari satu risiko, atau dikosongkan jika tidak relevan. Skor = Kemungkinan × Dampak.
    </p>

    <div class="tabel-wrap">
        <table class="tabel-form inheren">
            <thead>
                <tr>
                    <th style="width:3rem">No.</th>
                    <th>Risiko</th>
                    <th style="width:12rem">Kemungkinan</th>
                    <th style="width:12rem">Dampak</th>
                    <th style="width:6.5rem">Skor Risiko Inheren</th>
                    <th style="width:2.5rem"></th>
                </tr>
            </thead>
            @foreach ($MR::SIKLUS as $s => $labelSiklus)
                <tbody data-siklus="{{ $s }}">
                    <tr class="siklus-head">
                        <td style="text-align:center"><strong>{{ $loop->iteration }}</strong></td>
                        <td colspan="5"><strong>{{ $labelSiklus }}</strong></td>
                    </tr>
                    @foreach ((array) ($inheren[$s] ?? []) as $i => $row)
                        @include('dpia.tabs._inheren-row', ['s' => $s, 'i' => $i, 'row' => $row])
                    @endforeach
                    <tr class="kosong" @if (! empty($inheren[$s])) hidden @endif>
                        <td></td>
                        <td colspan="5" class="hint">Tidak ada risiko teridentifikasi pada siklus ini.</td>
                    </tr>
                    <tr class="tambah">
                        <td></td>
                        <td colspan="5"><button type="button" class="secondary small" data-inh-add>+ Tambah risiko</button></td>
                    </tr>
                    <template>
                        @include('dpia.tabs._inheren-row', ['s' => $s, 'i' => '__i__', 'row' => []])
                    </template>
                </tbody>
            @endforeach
        </table>
    </div>
    @error('inheren') <small class="error">{{ $message }}</small> @enderror
</div>

<script>
(function () {
    const meta    = document.getElementById('metodologi');
    const inheren = document.getElementById('inheren');
    if (!meta) return;

    const warna = JSON.parse(meta.dataset.warna);
    const putih = JSON.parse(meta.dataset.putih);
    let seq = Date.now();

    const batas = () => [1, 2, 3, 4].map(i =>
        parseInt(document.querySelector(`[name="metodologi[kategori_batas][${i}]"]`)?.value, 10));

    const level = (skor, b) => {
        for (let i = 0; i < 4; i++) if (!isNaN(b[i]) && skor <= b[i]) return i + 1;
        return 5;
    };

    const warnai = (el, lv) => {
        el.style.background = warna[lv];
        el.style.color = putih.includes(lv) ? '#fff' : '#111';
    };

    // --- Bagian 1–2: matriks & batas bawah kategori ---
    function renderMatriks() {
        const b = batas();
        meta.querySelectorAll('[data-skor]').forEach(td => warnai(td, level(parseInt(td.dataset.skor, 10), b)));
        meta.querySelectorAll('[data-bawah]').forEach(el => {
            const i = parseInt(el.dataset.bawah, 10);
            el.textContent = i === 1 ? 1 : (isNaN(b[i - 2]) ? '?' : b[i - 2] + 1);
        });
    }

    // --- Bagian 5: skor risiko inheren ---
    function hitung(tr) {
        const k = parseInt(tr.querySelector('[data-inh-k]').value, 10);
        const d = parseInt(tr.querySelector('[data-inh-d]').value, 10);
        const cell = tr.querySelector('[data-inh-skor]');
        if (isNaN(k) || isNaN(d)) {
            cell.textContent = '–';
            cell.style.background = '';
            cell.style.color = '';
            return;
        }
        const skor = k * d;
        cell.textContent = skor;
        warnai(cell, level(skor, batas()));
    }

    const hitungSemua = () => inheren?.querySelectorAll('[data-inh-row]').forEach(hitung);

    const kosong = (tbody) => {
        tbody.querySelector('tr.kosong').hidden = tbody.querySelectorAll('[data-inh-row]').length > 0;
    };

    // Batas kategori diubah → matriks & skor inheren ikut berubah warna
    meta.addEventListener('input', (e) => {
        if ((e.target.name || '').includes('[kategori_batas]')) {
            renderMatriks();
            hitungSemua();
        }
    });

    if (!inheren) return;

    inheren.addEventListener('click', (e) => {
        const add = e.target.closest('[data-inh-add]');
        if (add) {
            const tbody = add.closest('tbody');
            const html  = tbody.querySelector('template').innerHTML.replaceAll('__i__', String(seq++));
            const ref   = tbody.querySelector('tr.kosong');
            ref.insertAdjacentHTML('beforebegin', html);
            kosong(tbody);
            ref.previousElementSibling.querySelector('textarea').focus();
            return;
        }

        const rm = e.target.closest('[data-inh-remove]');
        if (rm) {
            const tbody = rm.closest('tbody');
            rm.closest('tr').remove();
            kosong(tbody);
        }
    });

    inheren.addEventListener('change', (e) => {
        if (e.target.matches('[data-inh-k], [data-inh-d]')) hitung(e.target.closest('tr'));
    });
})();
</script>
