@extends('layouts.app')

@section('title', 'DPIA')

@section('content')
    <h1>Data Protection Impact Assessment (DPIA)</h1>

    <div class="meta">
        <strong>Kode Dokumen:</strong> {{ $kode }}<br>
        <strong>Referensi RoPA:</strong> {{ $ropa['nomor'] }}<br>
        <strong>Terakhir diperbarui:</strong>
        {{ isset($dpia['diperbarui'])
            ? \Carbon\Carbon::parse($dpia['diperbarui'])->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') . ' WITA'
            : '-' }}
    </div>

    <nav class="tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dpia.form', ['tab' => $key]) }}" class="{{ $key === $tab ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($errors->any())
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



    <p style="margin-top:1.5rem"><a href="{{ route('ropa.form') }}">← Kembali ke RoPA</a></p>

    <form method="POST" action="{{ route('session.reset') }}" style="margin-top:1rem"
          onsubmit="return confirm('Semua data RoPA dan DPIA di sesi ini akan dihapus. Lanjutkan?')">
        @csrf
        <button type="submit" class="secondary">Mulai sesi baru</button>
    </form>
@endsection
