@extends('layouts.app')

@section('title', 'RoPA')

@section('content')
    <h1>Record of Processing Activities (RoPA)</h1>

    <div class="meta">
        <strong>Nomor RoPA:</strong> {{ $ropa['nomor'] ?? 'dibuat otomatis saat pertama kali menyimpan' }}<br>
        <strong>Terakhir diperbarui:</strong>
        {{ isset($ropa['diperbarui'])
            ? \Carbon\Carbon::parse($ropa['diperbarui'])->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') . ' WITA'
            : '-' }}
    </div>

    <nav class="tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('ropa.form', ['tab' => $key]) }}" class="{{ $key === $tab ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($errors->has('ropa'))
        <div class="alert err">{{ $errors->first('ropa') }}</div>
    @elseif ($errors->any())
        <div class="alert err">Periksa kembali isian yang ditandai merah.</div>
    @endif

    <form method="POST" action="{{ route('ropa.save', ['tab' => $tab]) }}" novalidate>
        @csrf
        @include('ropa.tabs.' . $tab, ['d' => $ropa[$tab] ?? []])

        <div class="actions">
            <button type="submit">Simpan tab ini</button>
            @if (\App\Support\RopaStatus::lengkap($ropa))
                @if (\App\Support\RopaStatus::wajibDpia($ropa))
                    <a class="btn" href="{{ route('dpia.form') }}">Lanjut ke DPIA →</a>
                @else
<button type="button" class="btn-hijau" data-open-dialog="dialog-pdf">Export PDF</button>
                @endif
            @elseif (! empty($ropa['nomor']))
                <small class="hint">
                    Belum disimpan: {{ implode(', ', \App\Support\RopaStatus::tabBelumDisimpan($ropa)) }}
                </small>
            @endif
        </div>
    </form>

    @if (\App\Support\RopaStatus::lengkap($ropa))
        <dialog id="dialog-pdf" class="dialog">
            <form method="GET" action="{{ route('ropa.pdf') }}" target="_blank" data-close-on-submit>
                <h3 style="margin-top:0">Pengesahan Dokumen</h3>
                <p class="hint">Lokasi dan tanggal ini hanya dipakai untuk cetakan ini dan tidak disimpan.</p>

                <div class="field">
                    <label for="pdf-lokasi">Lokasi pengesahan <span class="req">*</span></label>
                    <input id="pdf-lokasi" name="lokasi" required maxlength="100" placeholder="Contoh: Denpasar">
                </div>

                <div class="field">
                    <label for="pdf-tanggal">Tanggal pengesahan <span class="req">*</span></label>
                    <input id="pdf-tanggal" name="tanggal" type="date" required value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="actions">
                    <button type="button" class="secondary" data-close-dialog>Batal</button>
                    <button type="submit">Cetak PDF</button>
                </div>
            </form>
        </dialog>
    @endif

    <div style="margin-top:2rem">
        @include('partials.data-json')
    </div>
    <div style="margin-top:1rem">
        @include('partials.sesi-baru')
    </div>

@endsection
