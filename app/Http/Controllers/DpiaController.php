<?php

namespace App\Http\Controllers;

use App\Support\RopaStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DpiaController extends Controller
{
    public function form()
    {
        if ($redirect = $this->guard()) return $redirect;

        return view('dpia.form', [
            'ropa' => session('ropa'),
            'dpia' => session('dpia'),
        ]);
    }

    public function save(Request $request)
    {
        if ($redirect = $this->guard()) return $redirect;

        // SEMENTARA: satu field untuk uji. Diganti saat mendesain DPIA.
        $data = $request->validate([
            'ringkasan_risiko' => ['required', 'string', 'max:5000'],
        ]);

        session(['dpia' => $data]);

        return redirect()->route('dpia.form')->with('saved', 'DPIA tersimpan di sesi ini.');
    }

    private function guard(): ?RedirectResponse
    {
        $ropa = session('ropa', []);

        if (! RopaStatus::lengkap($ropa)) {
            return redirect()->route('ropa.form')
                ->withErrors(['ropa' => 'Lengkapi seluruh tab RoPA terlebih dahulu.']);
        }

        if (! RopaStatus::wajibDpia($ropa)) {
            return redirect()->route('ropa.form', ['tab' => 'risiko'])
                ->withErrors(['ropa' => 'Tidak ada indikator risiko tinggi, DPIA tidak diwajibkan untuk aktivitas ini.']);
        }

        return null;
    }
}
