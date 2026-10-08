@php
    $MR = \App\Support\MetodologiRisiko::class;
    $pen = $dpia['penilaian'] ?? [];
    $m = $MR::nilai($pen['metodologi'] ?? []);
    $risiko = $MR::daftarRisiko($pen);
    $kat = $MR::KATEGORI;
    $teks = fn(int $lv) => in_array($lv, $MR::TEKS_PUTIH, true) ? '#fff' : '#111';

    $bulanNama = collect(range(1, 12))
        ->mapWithKeys(
            fn($b) => [$b => \Illuminate\Support\Carbon::create(2000, $b, 1)->locale('id')->translatedFormat('F')],
        )
        ->all();
    $tahunPilihan = range((int) now()->year - 1, (int) now()->year + 5);

    $hasOld = session()->hasOldInput();
    $kontrolAll = $hasOld ? (array) old('kontrol', []) : $d['kontrol'] ?? [];
    $residualAll = $hasOld ? (array) old('residual', []) : $d['residual'] ?? [];
@endphp

<div id="pengendalian" data-warna="{{ json_encode(collect($kat)->map(fn($k) => $k['warna'])->all()) }}"
    data-putih="{{ json_encode($MR::TEKS_PUTIH) }}" data-batas="{{ json_encode($m['kategori_batas']) }}">

    @if (empty($risiko))
        <div class="alert warn">
            Belum ada risiko inheren. Isi dan simpan dulu
            <a href="{{ route('dpia.form', ['tab' => 'penilaian']) }}">Tab VI bagian 5</a>.
        </div>
    @endif
    @error('residual')
        <div class="alert err">{{ $message }}</div>
    @enderror

    <p class="hint" style="margin-top:0">
        Setiap risiko dari Tab VI bagian 5 ditampilkan di bawah. Untuk masing-masing risiko, isi langkah pengendalian
        yang sudah ada, lalu nilai risiko residualnya dan tentukan keputusan penanganannya. Langkah penanganan (bagian
        3)
        muncul bila keputusannya <strong>Mitigasi</strong>.
    </p>

    @foreach ($risiko as $rid => $r)
        @php
            $rid = (string) $rid;
            $kontrolRisk = (array) ($kontrolAll[$rid] ?? []);
            $residualRisk = (array) ($residualAll[$rid] ?? []);
            if (empty($residualRisk)) {
                $residualRisk = [[]];
            }
        @endphp

        <section class="risk-block" data-risk data-skor="{{ $r['skor'] }}">
            <div class="risk-head">
                <span class="risk-kode">{{ $r['kode'] }}</span>
                <div class="risk-teks">
                    <small class="hint">{{ $r['siklus'] }}</small>
                    <div>{{ $r['risiko'] }}</div>
                </div>
                <div class="risk-skor">
                    <small>Skor inheren</small>
                    <span class="skor-badge"
                        style="background:{{ $kat[$r['level']]['warna'] }}; color:{{ $teks($r['level']) }}">{{ $r['skor'] }}</span>
                </div>
            </div>

            {{-- 1. Langkah pengendalian --}}
            <h4 class="sub4">1. Langkah Pengendalian</h4>
            <div class="field repeater" data-repeater data-min="0">
                <div class="rep-rows" data-rows>
                    @foreach ($kontrolRisk as $i => $row)
                        @include('dpia.tabs._kontrol-row', ['rid' => $rid, 'i' => $i, 'row' => $row])
                    @endforeach
                </div>
                <p class="hint" data-empty @if (count($kontrolRisk)) hidden @endif>Belum ada langkah
                    pengendalian untuk risiko ini.</p>
                <template data-template>
                    @include('dpia.tabs._kontrol-row', ['rid' => $rid, 'i' => '__i__', 'row' => []])
                </template>
                <button type="button" class="secondary small" data-add>+ Tambah langkah pengendalian</button>
                @error("kontrol.$rid")
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            {{-- 2. Risiko residual & keputusan (berisi 3. penanganan bila Mitigasi) --}}
            <h4 class="sub4">2. Risiko Residual dan Keputusan Penanganan</h4>
            <div class="field repeater" data-repeater>
                <div class="rep-rows" data-rows>
                    @foreach ($residualRisk as $i => $row)
                        @include('dpia.tabs._residual-row', ['rid' => $rid, 'i' => $i, 'row' => $row])
                    @endforeach
                </div>
                <template data-template>
                    @include('dpia.tabs._residual-row', ['rid' => $rid, 'i' => '__i__', 'row' => []])
                </template>
                <button type="button" class="secondary small" data-add>+ Tambah risiko residual</button>
                @error("residual.$rid")
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>
        </section>
    @endforeach
</div>

<script>
    (function() {
        const root = document.getElementById('pengendalian');
        if (!root) return;

        const warna = JSON.parse(root.dataset.warna);
        const putih = JSON.parse(root.dataset.putih);
        const batas = JSON.parse(root.dataset.batas);

        const level = (skor) => {
            for (let i = 1; i <= 4; i++)
                if (skor <= parseInt(batas[i], 10)) return i;
            return 5;
        };

        const badge = (el, skor) => {
            if (!skor) {
                el.textContent = '–';
                el.style.background = '';
                el.style.color = '';
                return;
            }
            const lv = level(skor);
            el.textContent = skor;
            el.style.background = warna[lv];
            el.style.color = putih.includes(lv) ? '#fff' : '#111';
        };

        function init(card) {
            const inheren = parseInt(card.closest('[data-risk]').dataset.skor, 10);
            const k = parseInt(card.querySelector('[data-res-k]').value, 10);
            const d = parseInt(card.querySelector('[data-res-d]').value, 10);
            const residual = (!isNaN(k) && !isNaN(d)) ? k * d : null;

            badge(card.querySelector('[data-res-skor]'), residual);
            card.querySelector('[data-res-warn]').hidden = !(residual && residual > inheren);
            card.querySelector('[data-penanganan]').hidden = card.querySelector('[data-res-keputusan]').value !==
                'mitigasi';
        }

        root.addEventListener('change', (e) => {
            const card = e.target.closest('[data-res]');
            if (card) init(card);
        });

        root.addEventListener('repeater:add', (e) => {
            if (e.target.matches('[data-res]')) init(e.target);
        });

        root.querySelectorAll('[data-res]').forEach(init);
    })();
</script>
<script>
    (function() {
        // Tanda * pada Bukti Dukung mengikuti status langkah pengendalian
        document.addEventListener('change', (e) => {
            if (!e.target.matches('[data-kontrol-status]')) return;
            const tanda = e.target.closest('[data-row]').querySelector('[data-bukti-req]');
            if (tanda) tanda.hidden = e.target.value !== 'aktif';
        });
    })();
</script>
