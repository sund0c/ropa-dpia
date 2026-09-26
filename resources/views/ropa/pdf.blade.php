<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>RoPA {{ $ropa['nomor'] }}</title>
<style>
    @page { margin: 1.8cm 1.6cm 2.2cm 1.6cm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #000; line-height: 1.35; }
    header { position: fixed; top: -1.15cm; left: 0; right: 0; text-align: right; }
    header span { font-weight: bold; font-size: 9pt; letter-spacing: 1.5pt; border: 0.75pt solid #000; padding: 2pt 8pt; }
    @include('ropa._css')
</style>
</head>
<body>

<header><span>TERBATAS</span></header>

<div class="dok-ropa">
    @include('ropa._isi')
</div>

</body>
</html>
