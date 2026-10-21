<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersetujuanRisiko
{
    /** Modul Manajemen Risiko hanya bisa diakses setelah pengguna menyetujui pernyataan penggunaannya. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('persetujuan_risiko')) {
            return redirect()->route('risiko.persetujuan');
        }

        return $next($request);
    }
}
