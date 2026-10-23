<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersetujuanInsiden
{
    /** Modul Laporan Penanganan Insiden hanya bisa diakses setelah pengguna menyetujui pernyataan penggunaannya. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('persetujuan_insiden')) {
            return redirect()->route('insiden.persetujuan');
        }

        return $next($request);
    }
}
