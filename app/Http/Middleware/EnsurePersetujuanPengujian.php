<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersetujuanPengujian
{
    /** Modul Pengecekan Keamanan Aplikasi Web hanya bisa diakses setelah pengguna menyetujui pernyataan penggunaannya. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('persetujuan_pengujian')) {
            return redirect()->route('pengujian.persetujuan');
        }

        return $next($request);
    }
}
