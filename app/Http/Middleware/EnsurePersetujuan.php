<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersetujuan
{
    /** Semua halaman aplikasi hanya bisa diakses setelah pengguna menyetujui pernyataan penggunaan. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('persetujuan')) {
            return redirect()->route('persetujuan');
        }

        return $next($request);
    }
}
