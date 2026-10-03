@extends('layouts.app')

@section('title', 'DPIA')

@section('content')
    <h1>Data Protection Impact Assessment (DPIA)</h1>

    <div class="meta">
        <strong>Kode Dokumen:</strong> {{ $kode }}<br>
        <strong>Referensi RoPA:</strong> {{ $ropa['nomor'] }}<br>
        <strong>Terakhir diperbarui:</strong>
        {{ isset($dpia['diperbarui'])
            ? \Carbon\Carbon::parse($dpia['diperbarui'])->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') .
                ' WITA'
            : '-' }}
    </div>

    <nav class="tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dpia.form', ['tab' => $key]) }}" class="{{ $key === $tab ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($errors->has('dpia'))
        <div class="alert err">{{ $errors->first('dpia') }}</div>
    @elseif ($errors->any())
        <div class="alert err">Periksa kembali isian yang ditandai merah.</div>
    @endif

    @if ($otomatis)
        <div class="alert ok">
            Seluruh isian bagian ini diambil otomatis dari RoPA. Untuk mengubahnya, edit di
            <a href="{{ route('ropa.form', ['tab' => 'organisasi']) }}">RoPA Tab I</a>.
        </div>
        @include('dpia.tabs.' . $tab)
    @else
        <form method="POST" action="{{ route('dpia.save', ['tab' => $tab]) }}" novalidate>
            @csrf
            @include('dpia.tabs.' . $tab, ['d' => $dpia[$tab] ?? []])

            <div class="actions">
                <button type="submit">Simpan tab ini</button>
            </div>
        </form>
    @endif

    @if ($tab === 'penilaian')
        <form id="form-default-metodologi" method="POST" action="{{ route('dpia.metodologi.default') }}">
            @csrf
        </form>
        <form id="form-default-inheren" method="POST" action="{{ route('dpia.inheren.default') }}">
            @csrf
        </form>
    @endif

    @php $masalahDpia = \App\Support\DpiaStatus::masalah($ropa, $dpia); @endphp

    <div class="status-dpia">
        @if (empty($masalahDpia))
            <button type="button" class="btn-hijau" data-open-dialog="dialog-pdf-dpia">Export PDF DPIA</button>
        @else
            <strong>DPIA belum dapat diekspor:</strong>
            <ul>
                @foreach ($masalahDpia as $mm)
                    <li>{{ $mm }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    @if (empty($masalahDpia))
        <dialog id="dialog-pdf-dpia" class="dialog">
            <form method="GET" action="{{ route('dpia.pdf') }}" target="_blank" data-close-on-submit>
                <h3 style="margin-top:0">Pengesahan Dokumen DPIA</h3>
                <p class="hint">Data ini hanya dipakai untuk cetakan ini dan tidak disimpan.</p>

                <div class="field">
                    <label for="dpia-lokasi">Lokasi <span class="req">*</span></label>
                    <input id="dpia-lokasi" name="lokasi" required maxlength="100" placeholder="Contoh: Denpasar">
                </div>
                <div class="field">
                    <label for="dpia-tanggal">Tanggal <span class="req">*</span></label>
                    <input id="dpia-tanggal" name="tanggal" type="date" required value="{{ now()->format('Y-m-d') }}">
                </div>
                @include('partials.pilihan-tte')

                <div class="actions">
                    <button type="button" class="secondary" data-close-dialog>Batal</button>
                    <button type="submit">Cetak PDF</button>
                </div>
            </form>
        </dialog>
    @endif


    <p style="margin-top:1.5rem"><a href="{{ route('ropa.form') }}">← Kembali ke RoPA</a></p>

    <div style="margin-top:1rem">
        @include('partials.data-json')
    </div>
    <div style="margin-top:1rem">
        @include('partials.sesi-baru')
    </div>

@endsection
