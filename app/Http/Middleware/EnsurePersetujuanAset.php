<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersetujuanAset
{
    /** Modul aset hanya bisa diakses setelah pengguna menyetujui pernyataan penggunaan modul aset. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('persetujuan_aset')) {
            return redirect()->route('aset.persetujuan');
        }

        return $next($request);
    }
}
