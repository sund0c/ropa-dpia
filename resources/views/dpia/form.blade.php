<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>DPIA</title></head>
<body>
    <h1>DPIA untuk RoPA: {{ $ropa['nomor'] }}</h1>
    @if (session('saved'))  <p style="color:green">{{ session('saved') }}</p> @endif
    @if ($errors->any())    <p style="color:red">{{ $errors->first() }}</p> @endif

    <form method="POST" action="{{ route('dpia.save') }}">
        @csrf
        <label>Ringkasan risiko
            <textarea name="ringkasan_risiko">{{ old('ringkasan_risiko', $dpia['ringkasan_risiko'] ?? '') }}</textarea>
        </label>
        <button type="submit">Simpan DPIA</button>
    </form>

    <p><a href="{{ route('ropa.form') }}">← Kembali ke RoPA</a></p>
</body>
</html>
