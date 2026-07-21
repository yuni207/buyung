<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class LoginController extends Controller
{
    public function index()
    {
        // Auto-close sesi kasir lama sebelum login, agar sesi kemarin tetap tertutup
        // meskipun user belum sempat login.
        $this->autoTutupSesiKemarinSemua();

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Auto-close sesi kasir yang masih terbuka dari hari sebelumnya
            $this->autoTutupSesiKemarin();

            return redirect('/admin/home');
        } else {
            return redirect()->route('login')->with('error', 'Username atau Password Salah !');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda Berhasil Logout!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Auto-close sesi kasir yang masih berstatus 'buka' dari hari sebelumnya.
    //
    // Cara kerja:
    //   - Cari semua sesi milik user ini yang status = 'buka' dan
    //     waktu_buka bukan hari ini (artinya dari hari sebelumnya / lebih lama).
    //   - Tutup sesi tersebut dengan waktu_tutup = 23:59:59 hari waktu_buka
    //     (bukan jam tutup hari ini), agar data pemasukan tidak merembes
    //     ke hari berikutnya.
    //   - Tandai keterangan_tutup = '[Auto-close] ...' agar mudah diidentifikasi
    //     di laporan.
    // ─────────────────────────────────────────────────────────────────────────
    private function autoTutupSesiKemarin()
    {
        $hariIni = now()->toDateString();

        // Ambil semua sesi aktif milik user ini yang bukan dibuka hari ini
        $sesiLama = DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('status', 'buka')
            ->whereDate('waktu_buka', '<', $hariIni)
            ->get();

        foreach ($sesiLama as $sesi) {
            // Batas tutup = 23:59:59 pada hari sesi dibuka (bukan hari ini)
            $tanggalBuka = date('Y-m-d', strtotime($sesi->waktu_buka));
            $waktuTutupOtomatis = $tanggalBuka . ' 23:59:59';

            // Hitung pemasukan selama sesi (dari waktu_buka s/d 23:59:59 hari buka)
            $pemasukanSesi = DB::table('pemasukan')
                ->where('created_at', '>=', $sesi->waktu_buka)
                ->where('created_at', '<=', $waktuTutupOtomatis)
                ->where('id', '!=', $sesi->id_pemasukan_buka ?? 0)
                ->sum('total');

            // Hitung pengeluaran selama sesi
            $pengeluaranSesi = DB::table('pengeluaran')
                ->where('created_at', '>=', $sesi->waktu_buka)
                ->where('created_at', '<=', $waktuTutupOtomatis)
                ->sum('total');

            $seharusnya = $sesi->modal_awal + $pemasukanSesi - $pengeluaranSesi;

            DB::table('kasir_session')->where('id', $sesi->id)->update([
                'pemasukan_sesi'   => $pemasukanSesi,
                'pengeluaran_sesi' => $pengeluaranSesi,
                // uang_akhir diisi sama dengan seharusnya (selisih = 0)
                // karena tidak ada kasir yang menghitung fisik uang
                'uang_akhir'       => $seharusnya,
                'setor_owner'      => 0,
                'selisih'          => 0,
                'keterangan_tutup' => '[Auto-close] Ditutup otomatis saat login '
                                      . now()->format('d/m/Y H:i')
                                      . ' karena sesi tidak ditutup pada hari buka.',
                'waktu_tutup'      => $waktuTutupOtomatis,
                'status'           => 'tutup',
                'updated_at'       => now(),
            ]);
        }
    }

    private function autoTutupSesiKemarinSemua()
    {
        $hariIni = now()->toDateString();

        $sesiLama = DB::table('kasir_session')
            ->where('status', 'buka')
            ->whereDate('waktu_buka', '<', $hariIni)
            ->get();

        foreach ($sesiLama as $sesi) {
            $tanggalBuka = date('Y-m-d', strtotime($sesi->waktu_buka));
            $waktuTutupOtomatis = $tanggalBuka . ' 23:59:59';

            $pemasukanSesi = DB::table('pemasukan')
                ->where('created_at', '>=', $sesi->waktu_buka)
                ->where('created_at', '<=', $waktuTutupOtomatis)
                ->where('id', '!=', $sesi->id_pemasukan_buka ?? 0)
                ->sum('total');

            $pengeluaranSesi = DB::table('pengeluaran')
                ->where('created_at', '>=', $sesi->waktu_buka)
                ->where('created_at', '<=', $waktuTutupOtomatis)
                ->sum('total');

            $seharusnya = $sesi->modal_awal + $pemasukanSesi - $pengeluaranSesi;

            DB::table('kasir_session')->where('id', $sesi->id)->update([
                'pemasukan_sesi'   => $pemasukanSesi,
                'pengeluaran_sesi' => $pengeluaranSesi,
                'uang_akhir'       => $seharusnya,
                'setor_owner'      => 0,
                'selisih'          => 0,
                'keterangan_tutup' => '[Auto-close] Ditutup otomatis sebelum login '
                                      . now()->format('d/m/Y H:i')
                                      . ' karena sesi tidak ditutup pada hari buka.',
                'waktu_tutup'      => $waktuTutupOtomatis,
                'status'           => 'tutup',
                'updated_at'       => now(),
            ]);
        }
    }
}