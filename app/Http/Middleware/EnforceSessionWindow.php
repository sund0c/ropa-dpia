<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionWindow
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $started = $session->get('started_at');

        if (! $started) {
            $session->put('started_at', now()->timestamp);
        } elseif (now()->timestamp - $started > 86400) {
            $session->invalidate();           // hapus semua data + ganti session ID
            $session->regenerateToken();
            $session->put('started_at', now()->timestamp);
            $session->flash('expired', true); // untuk menampilkan pesan ke user
        }

        return $next($request);
    }
}
