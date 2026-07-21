<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CekKasirBuka
{
    public function handle($request, Closure $next)
    {
        if (!$request->is('admin/*')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect('login');
        }

        // Halaman kasir sendiri boleh diakses tanpa cek sesi
        if ($request->is('admin/kasir*')) {
            return $next($request);
        }

        // Operator (level 3) tidak punya hak buka kasir,
        // langsung boleh akses semua fitur tanpa cek sesi
        if (Auth::user()->level == '3') {
            return $next($request);
        }

        // Owner (1) dan Kasir (2): wajib punya sesi aktif milik sendiri
        $sesiAktif = DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('status', 'buka')
            ->exists();

        if (!$sesiAktif) {
            return redirect('/admin/home')
                ->with('error', 'Anda harus membuka sesi kasir terlebih dahulu sebelum mengakses fitur lain.');
        }

        return $next($request);
    }
}