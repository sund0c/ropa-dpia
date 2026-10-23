@extends('layouts.insiden')

@section('title', 'Laporan Penanganan Insiden')

@section('content')
    @php
        $lengkap = \App\Http\Controllers\InsidenController::lengkap($insiden);
        $belum   = \App\Http\Controllers\InsidenController::tabBelumDisimpan($insiden);
    @endphp

    <style>
        button.btn-hijau { background: #047857; }
        button.btn-hijau:hover { background: #065f46; }
        .ttd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-top: .5rem; }
        .ttd-kartu { border: 1px solid #e5e7eb; border-radius: 8px; padding: .75rem; display: flex; flex-direction: column; gap: .3rem; }
        .ttd-kartu .peran { font-size: .78rem; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
        .ttd-kartu .jab { font-size: .88rem; color: #374151; }
        .ttd-kartu .check { margin-top: .35rem; }
        .ttd-kartu .tte-unggah { display: flex; flex-direction: column; gap: .25rem; margin-top: .25rem; }
        .ttd-kartu .tte-unggah input[type=file] { font-size: .85rem; max-width: 100%; }
        @media (max-width: 640px) { .ttd-grid { grid-template-columns: 1fr; } }
    </style>

    <h1>Laporan Penanganan Insiden</h1>

    <div class="meta">
        <strong>Nomor Laporan:</strong> {{ $insiden['nomor'] ?? 'diisi pada Tab I' }}<br>
        <strong>Terakhir diperbarui:</strong>
        {{ isset($insiden['diperbarui'])
            ? \Carbon\Carbon::parse($insiden['diperbarui'])->timezone(config('app.timezone'))->locale('id')->translatedFormat('d F Y, H:i') . ' WITA'
            : '-' }}
    </div>

    <nav class="tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('insiden.form', ['tab' => $key]) }}" class="{{ $key === $tab ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </nav>

    @if (($insiden['informasi']['jenis_laporan'] ?? null) === 'awal')
        <div class="alert warn">
            <strong>Laporan Awal</strong> cukup berisi Tab I–III. Tab Analisis dan seterusnya akan muncul bila
            <strong>Jenis Laporan</strong> di Tab I diubah menjadi Laporan Perkembangan atau Laporan Akhir.
        </div>
    @endif

    @if ($errors->has('insiden'))
        <div class="alert err">{{ $errors->first('insiden') }}</div>
    @elseif ($errors->any())
        <div class="alert err">Periksa kembali isian yang ditandai merah.</div>
    @endif

    <form method="POST" action="{{ route('insiden.save', ['tab' => $tab]) }}" novalidate>
        @csrf
        @include('insiden.tabs.' . $tab, ['d' => $insiden[$tab] ?? []])

        <div class="actions">
            <button type="submit">Simpan tab ini</button>

            @if ($lengkap)
                <button type="button" class="btn-hijau" data-open-dialog="dialog-pdf">Export PDF</button>
            @elseif (! empty($insiden['nomor']))
                <small class="hint" style="text-align:right">
                    Export PDF tersedia setelah semua tab disimpan.<br>Belum disimpan: {{ implode(', ', $belum) }}
                </small>
            @endif
        </div>
    </form>


    {{-- Popup pengesahan (di luar form utama: form tidak boleh bersarang) --}}
    @if ($lengkap)
        @php $inf = $insiden['informasi']; @endphp
        <dialog id="dialog-pdf" class="dialog dialog-lebar">
            <form method="POST" action="{{ route('insiden.pdf') }}" enctype="multipart/form-data" data-close-on-submit>
                @csrf
                <h3 style="margin-top:0">Pengesahan Laporan</h3>
                <p class="hint">Dokumen dicetak dengan klasifikasi <strong>TERBATAS</strong>. Lokasi, tanggal, pilihan TTE, dan label TTE hanya dipakai untuk cetakan ini dan tidak disimpan.</p>

                <div class="row">
                    <div class="field">
                        <label for="pdf-lokasi">Lokasi <span class="req">*</span></label>
                        <input id="pdf-lokasi" name="lokasi" required maxlength="100" placeholder="Contoh: Denpasar">
                    </div>
                    <div class="field">
                        <label for="pdf-tanggal">Tanggal Laporan <span class="req">*</span></label>
                        <input id="pdf-tanggal" name="tanggal" type="date" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="field" style="margin-bottom:0">
                    <strong>Penandatangan</strong>
                    <small class="hint">
                        Diambil dari Tab I. Centang <em>TTE</em> bila penandatangan menggunakan Tanda Tangan Elektronik,
                        lalu unggah gambar label TTE-nya (PNG/JPG, maks. {{ \App\Support\LabelTte::MAKS_KB }} KB).
                        Kosongkan untuk tanda tangan basah.
                    </small>
                </div>

                <div class="ttd-grid">
                    <div class="ttd-kartu" data-tte-item>
                        <span class="peran">Ketua Tim Penanganan Insiden</span>
                        <strong>{{ $inf['ketua_nama'] ?? '-' }}</strong>
                        <span class="jab">{{ $inf['ketua_jabatan'] ?? '' }} {{ $inf['ketua_instansi'] ?? '' }}</span>
                        <label class="check">
                            <input type="checkbox" name="tte[]" value="ketua" data-tte-cek>
                            <span>Menggunakan TTE</span>
                        </label>
                        <div class="tte-unggah" hidden>
                            <input type="file" name="tte_label[ketua]" accept="image/png,image/jpeg" disabled>
                            <small class="hint">Label TTE (PNG/JPG)</small>
                        </div>
                    </div>

                    <div class="ttd-kartu" data-tte-item>
                        <span class="peran">Pimpinan Instansi Pemilik Aset</span>
                        <strong>{{ $inf['pimpinan_nama'] ?? '-' }}</strong>
                        <span class="jab">{{ $inf['pimpinan_jabatan'] ?? '' }} {{ $inf['pemilik_aset'] ?? '' }}</span>
                        <label class="check">
                            <input type="checkbox" name="tte[]" value="pimpinan" data-tte-cek>
                            <span>Menggunakan TTE</span>
                        </label>
                        <div class="tte-unggah" hidden>
                            <input type="file" name="tte_label[pimpinan]" accept="image/png,image/jpeg" disabled>
                            <small class="hint">Label TTE (PNG/JPG)</small>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="secondary" data-close-dialog>Batal</button>
                    <button type="submit" class="btn-hijau">Cetak PDF</button>
                </div>
            </form>
            @include('partials.tte-js')
        </dialog>

        <script>
        // Tutup popup setelah form cetak dikirim (PDF langsung terunduh)
        document.addEventListener('submit', (e) => {
            if (e.target.matches('[data-close-on-submit]')) {
                setTimeout(() => e.target.closest('dialog')?.close(), 0);
            }
        });
        </script>
    @endif

    <div style="margin-top:2rem">
        @include('insiden._data-json')
    </div>
    <div style="margin-top:1rem">
        @include('insiden._sesi-baru')
    </div>
@endsection
