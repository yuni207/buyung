<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function sesiHariIni()
    {
        $tanggalHariIni = now()->toDateString();

        return DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('waktu_buka', 'like', $tanggalHariIni . '%')
            ->first();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — daftar semua sesi kasir (admin: semua, kasir: milik sendiri)
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = DB::table('kasir_session as ks')
            ->leftJoin('users as u', 'ks.id_user', '=', 'u.id')
            ->select(
                'ks.*',
                'u.name as nama_kasir',
                DB::raw('COALESCE(ks.uang_akhir, 0) - (ks.modal_awal + COALESCE(ks.pemasukan_sesi, 0)) as selisih_hitung')
            );

        // Non-admin hanya melihat sesi miliknya
        if (Auth::user()->level != '1') {
            $query->where('ks.id_user', Auth::id());
        }

        // Filter bulan
        if ($request->filled('bln')) {
            $query->where('ks.waktu_buka', 'like', $request->bln . '%');
        }

        $sesi = $query->orderBy('ks.id', 'DESC')->get();

        return view('admin.kasir.index', compact('sesi'))->with('activePage', 'kasir');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM BUKA KASIR
    // ─────────────────────────────────────────────────────────────────────────
    public function formBuka()
    {
        $sesiAktif = DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('status', 'buka')
            ->first();

        if ($sesiAktif) {
            return redirect('/admin/kasir')
                ->with('error', 'Anda masih memiliki sesi kasir yang belum ditutup! Tutup sesi tersebut terlebih dahulu.');
        }

        if ($this->sesiHariIni()) {
            return redirect('/admin/kasir')
                ->with('error', 'Anda sudah membuka dan menutup sesi kasir hari ini.');
        }

        $metode = DB::table('metode')->orderBy('nama')->get();

        return view('admin.kasir.buka', compact('metode'))->with('activePage', 'kasir');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROSES BUKA KASIR
    // Alur:
    //   1. Catat modal awal ke tabel `pemasukan` (agar terlihat di laporan)
    //   2. Buat baris baru di kasir_session dengan status 'buka'
    // ─────────────────────────────────────────────────────────────────────────
    public function buka(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required',
            'id_metode'  => 'required|exists:metode,id',
        ]);

        $sesiAktif = DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('status', 'buka')
            ->first();

        if ($sesiAktif) {
            return redirect('/admin/kasir')
                ->with('error', 'Anda masih memiliki sesi kasir yang aktif!');
        }

        if ($this->sesiHariIni()) {
            return redirect('/admin/kasir')
                ->with('error', 'Anda sudah membuka dan menutup sesi kasir hari ini.');
        }

        $modalAwal  = intval(preg_replace('/\D/', '', $request->modal_awal));
        $waktuBuka  = now();
        $tanggal    = $waktuBuka->toDateString();

        DB::beginTransaction();
        try {
            // 1. Catat modal awal ke pemasukan hanya jika nominal lebih dari 0
            $idPemasukan = null;
            if ($modalAwal > 0) {
                $idPemasukan = DB::table('pemasukan')->insertGetId([
                    'tanggal'      => $tanggal,
                    'keterangan'   => 'Modal Awal Buka Kasir'
                                      . ' — ' . Auth::user()->name
                                      . ($request->keterangan_buka ? ' (' . $request->keterangan_buka . ')' : ''),
                    'total'        => $modalAwal,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => 0,
                    'id_user'      => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // 2. Buat sesi kasir baru
            DB::table('kasir_session')->insert([
                'id_user'           => Auth::id(),
                'modal_awal'        => $modalAwal,
                'pemasukan_sesi'    => 0,
                'uang_akhir'        => null,
                'selisih'           => null,
                'keterangan_buka'   => $request->keterangan_buka,
                'keterangan_tutup'  => null,
                'waktu_buka'        => $waktuBuka,
                'waktu_tutup'       => null,
                'status'            => 'buka',
                'id_pemasukan_buka' => $idPemasukan,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuka kasir: ' . $e->getMessage());
        }

        return redirect('/admin/kasir')
            ->with('success', 'Kasir berhasil dibuka! Modal awal Rp ' . number_format($modalAwal, 0, ',', '.') . ' telah dicatat ke pemasukan.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM TUTUP KASIR
    // Menampilkan ringkasan: modal awal + pemasukan hari ini + uang yang
    // seharusnya ada di laci, lalu kasir memasukkan uang fisik yang ada.
    // ─────────────────────────────────────────────────────────────────────────
    public function formTutup($id)
    {
        $sesi = DB::table('kasir_session as ks')
            ->leftJoin('users as u', 'ks.id_user', '=', 'u.id')
            ->select('ks.*', 'u.name as nama_kasir')
            ->where('ks.id', $id)
            ->first();

        if (!$sesi) {
            return redirect('/admin/kasir')->with('error', 'Sesi tidak ditemukan!');
        }

        // Hanya pemilik sesi atau admin yang boleh tutup
        if (Auth::user()->level != '1' && $sesi->id_user != Auth::id()) {
            return redirect('/admin/kasir')->with('error', 'Anda tidak memiliki akses untuk menutup sesi ini!');
        }

        if ($sesi->status === 'tutup') {
            return redirect('/admin/kasir')->with('error', 'Sesi ini sudah ditutup!');
        }

        // Hitung total pemasukan sejak buka kasir (dari waktu_buka sampai sekarang)
        // Khusus kasir: hanya pemasukan milik dia; admin: semua
        $qPemasukan = DB::table('pemasukan')
            ->where('tanggal', '>=', date('Y-m-d', strtotime($sesi->waktu_buka)))
            ->where('tanggal', '<=', date('Y-m-d'));

        // Kecualikan modal awal buka kasir itu sendiri agar tidak double-count
        if ($sesi->id_pemasukan_buka) {
            $qPemasukan->where('id', '!=', $sesi->id_pemasukan_buka);
        }

        // Filter per user jika bukan admin
        if (Auth::user()->level != '1') {
            $qPemasukan->where('id_user', Auth::id());
        }

        $pemasukanSesi = $qPemasukan->sum('total');

        // Hitung juga pengeluaran sesi untuk info lengkap
        $qPengeluaran = DB::table('pengeluaran')
            ->where('tanggal', '>=', date('Y-m-d', strtotime($sesi->waktu_buka)))
            ->where('tanggal', '<=', date('Y-m-d'));

        if (Auth::user()->level != '1') {
            $qPengeluaran->where('id_user', Auth::id());
        }

        $pengeluaranSesi = $qPengeluaran->sum('total');

        // Uang yang seharusnya ada = modal awal + pemasukan - pengeluaran
        $seharusnya = $sesi->modal_awal + $pemasukanSesi - $pengeluaranSesi;

        $metode = DB::table('metode')->orderBy('nama')->get();

        return view('admin.kasir.tutup', compact(
            'sesi',
            'pemasukanSesi',
            'pengeluaranSesi',
            'seharusnya',
            'metode'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROSES TUTUP KASIR
    // Alur:
    //   1. Snapshot pemasukan sesi ke kolom pemasukan_sesi
    //   2. Hitung selisih (uang_akhir - seharusnya)
    //   3. Update kasir_session → status 'tutup'
    // ─────────────────────────────────────────────────────────────────────────
    public function tutup(Request $request, $id)
    {
        $request->validate([
            'uang_akhir'  => 'required',
            'setor_owner' => 'required',
        ]);

        $sesi = DB::table('kasir_session')->where('id', $id)->first();

        if (!$sesi) {
            return redirect('/admin/kasir')->with('error', 'Sesi tidak ditemukan!');
        }

        if (Auth::user()->level != '1' && $sesi->id_user != Auth::id()) {
            return redirect('/admin/kasir')->with('error', 'Akses ditolak!');
        }

        if ($sesi->status === 'tutup') {
            return redirect('/admin/kasir')->with('error', 'Sesi ini sudah ditutup!');
        }

        $uangAkhir  = intval(preg_replace('/\D/', '', $request->uang_akhir));
        $setorOwner = intval(preg_replace('/\D/', '', $request->setor_owner));

        // Hitung ulang pemasukan sesi (snapshot final)
        $qPemasukan = DB::table('pemasukan')
            ->where('tanggal', '>=', date('Y-m-d', strtotime($sesi->waktu_buka)))
            ->where('tanggal', '<=', date('Y-m-d'));

        if ($sesi->id_pemasukan_buka) {
            $qPemasukan->where('id', '!=', $sesi->id_pemasukan_buka);
        }

        if (Auth::user()->level != '1') {
            $qPemasukan->where('id_user', Auth::id());
        }

        $pemasukanSesi = $qPemasukan->sum('total');

        $qPengeluaran = DB::table('pengeluaran')
            ->where('tanggal', '>=', date('Y-m-d', strtotime($sesi->waktu_buka)))
            ->where('tanggal', '<=', date('Y-m-d'));

        if (Auth::user()->level != '1') {
            $qPengeluaran->where('id_user', Auth::id());
        }

        $pengeluaranSesi = $qPengeluaran->sum('total');

        $seharusnya = $sesi->modal_awal + $pemasukanSesi - $pengeluaranSesi;
        $selisih    = $uangAkhir - $seharusnya;

        DB::table('kasir_session')->where('id', $id)->update([
            'pemasukan_sesi'   => $pemasukanSesi,
            'pengeluaran_sesi' => $pengeluaranSesi,
            'uang_akhir'       => $uangAkhir,
            'setor_owner'      => $setorOwner,
            'selisih'          => $selisih,
            'keterangan_tutup' => $request->keterangan_tutup,
            'waktu_tutup'      => now(),
            'status'           => 'tutup',
            'updated_at'       => now(),
        ]);

        $sisaKas = $uangAkhir - $setorOwner;

        return redirect('/admin/kasir')
            ->with('success', 'Kasir berhasil ditutup! Setor ke Owner Rp ' . number_format($setorOwner, 0, ',', '.') . ' — Sisa kas di laci Rp ' . number_format($sisaKas, 0, ',', '.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DETAIL sesi kasir
    // ─────────────────────────────────────────────────────────────────────────
    public function detail($id)
    {
        $sesi = DB::table('kasir_session as ks')
            ->leftJoin('users as u', 'ks.id_user', '=', 'u.id')
            ->select('ks.*', 'u.name as nama_kasir')
            ->where('ks.id', $id)
            ->first();

        if (!$sesi) {
            return redirect('/admin/kasir')->with('error', 'Sesi tidak ditemukan!');
        }

        if (Auth::user()->level != '1' && $sesi->id_user != Auth::id()) {
            return redirect('/admin/kasir')->with('error', 'Akses ditolak!');
        }

        // Pemasukan selama sesi
        $qPemasukan = DB::table('pemasukan as p')
            ->leftJoin('metode as m', 'p.id_metode', '=', 'm.id')
            ->select('p.*', 'm.nama as nama_metode')
            ->where('p.tanggal', '>=', date('Y-m-d', strtotime($sesi->waktu_buka)))
            ->where('p.tanggal', '<=', $sesi->waktu_tutup
                ? date('Y-m-d', strtotime($sesi->waktu_tutup))
                : date('Y-m-d'));

        if ($sesi->id_pemasukan_buka) {
            $qPemasukan->where('p.id', '!=', $sesi->id_pemasukan_buka);
        }

        if (Auth::user()->level != '1') {
            $qPemasukan->where('p.id_user', Auth::id());
        }

        $listPemasukan = $qPemasukan->orderBy('p.id', 'DESC')->get();

        // Pengeluaran selama sesi
        $qPengeluaran = DB::table('pengeluaran as pe')
            ->leftJoin('metode as m', 'pe.id_metode', '=', 'm.id')
            ->select('pe.*', 'm.nama as nama_metode')
            ->where('pe.tanggal', '>=', date('Y-m-d', strtotime($sesi->waktu_buka)))
            ->where('pe.tanggal', '<=', $sesi->waktu_tutup
                ? date('Y-m-d', strtotime($sesi->waktu_tutup))
                : date('Y-m-d'));

        if (Auth::user()->level != '1') {
            $qPengeluaran->where('pe.id_user', Auth::id());
        }

        $listPengeluaran = $qPengeluaran->orderBy('pe.id', 'DESC')->get();

        $seharusnya = $sesi->modal_awal + $listPemasukan->sum('total') - $listPengeluaran->sum('total');

        return view('admin.kasir.detail', compact(
            'sesi',
            'listPemasukan',
            'listPengeluaran',
            'seharusnya'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CEK apakah user saat ini punya sesi aktif (AJAX — opsional)
    // ─────────────────────────────────────────────────────────────────────────
    public function cekSesiAktif()
    {
        $sesi = DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('status', 'buka')
            ->first();

        return response()->json([
            'aktif' => (bool) $sesi,
            'sesi'  => $sesi,
        ]);
    }
}