<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class HutangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Daftar semua hutang ──────────────────────────────────────────────────
    public function read(Request $request)
    {
        $query = DB::table('hutang as h')
            ->leftJoin('users as u', 'h.id_user', '=', 'u.id')
            ->select(
                'h.*',
                'u.name as nama_kasir',
                DB::raw('(h.total - h.terbayar) as sisa')
            );

        // Filter status
        if ($request->filled('status')) {
            $query->where('h.status', $request->status);
        }

        // Filter bulan
        if ($request->filled('bln')) {
            $query->where('h.tanggal', 'like', $request->bln . '%');
        }

        // Non-admin hanya lihat milik sendiri
        if (Auth::user()->level != '1') {
            $query->where('h.id_user', Auth::id());
        }

        $hutang     = $query->orderBy('h.id', 'DESC')->get();
        $totalSisa  = $hutang->where('status', 'belum')->sum('sisa');
        $totalLunas = $hutang->where('status', 'lunas')->sum('total');

        return view('admin.hutang.index', compact('hutang', 'totalSisa', 'totalLunas'));
    }

    // ── Cetak / download laporan hutang (PDF) ────────────────────────────────
    public function cetak(Request $request)
    {
        $query = DB::table('hutang as h')
            ->leftJoin('users as u', 'h.id_user', '=', 'u.id')
            ->select(
                'h.*',
                'u.name as nama_kasir',
                DB::raw('(h.total - h.terbayar) as sisa')
            );

        if ($request->filled('status')) {
            $query->where('h.status', $request->status);
        }

        if ($request->filled('bln')) {
            $query->where('h.tanggal', 'like', trim($request->bln) . '%');
        }

        if (Auth::user()->level != '1') {
            $query->where('h.id_user', Auth::id());
        }

        $hutang           = $query->orderBy('h.id', 'DESC')->get();
        $totalSisa        = $hutang->where('status', 'belum')->sum('sisa');
        $totalLunas       = $hutang->where('status', 'lunas')->sum('total');
        $totalKeseluruhan = $hutang->sum('total');
        $label            = $this->buildLabel($request);

        $pdf = PDF::loadview('admin.hutang.cetak', compact('hutang', 'totalSisa', 'totalLunas', 'totalKeseluruhan', 'label'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan Hutang ' . $label . '.pdf');
    }

    /**
     * Membuat label periode/status untuk judul laporan berdasarkan
     * query string yang sedang aktif (bln bisa YYYY / YYYY-MM / YYYY-MM-DD).
     */
    private function buildLabel(Request $request): string
    {
        $parts = [];

        if ($request->filled('bln')) {
            $bln = trim($request->bln);
            if (preg_match('/^\d{4}$/', $bln)) {
                $parts[] = 'Tahun ' . $bln;
            } elseif (preg_match('/^\d{4}-\d{2}$/', $bln)) {
                $parts[] = 'Bulan ' . \Carbon\Carbon::parse($bln . '-01')->locale('id')->translatedFormat('F Y');
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $bln)) {
                $parts[] = 'Tanggal ' . \Carbon\Carbon::parse($bln)->locale('id')->translatedFormat('d F Y');
            }
        }

        if ($request->filled('status')) {
            $parts[] = ucfirst($request->status);
        }

        return $parts ? implode(' - ', $parts) : 'Semua Data';
    }

    // ── Form tambah hutang manual ────────────────────────────────────────────
    public function add()
    {
        return view('admin.hutang.tambah');
    }

    // ── Simpan hutang manual (bukan dari transaksi) ──────────────────────────
    public function create(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp'          => 'nullable|digits_between:1,12',
            'total'          => 'required',
            'tanggal'        => 'required|date',
        ]);

        $total = intval(preg_replace('/\D/', '', $request->total));

        DB::table('hutang')->insert([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'keterangan'     => $request->keterangan,
            'total'          => $total,
            'terbayar'       => 0,
            'status'         => 'belum',
            'id_transaksi'   => null,
            'id_user'        => Auth::id(),
            'tanggal'        => $request->tanggal,
            'jatuh_tempo'    => $request->jatuh_tempo ?: null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect('/admin/hutang')->with('success', 'Hutang berhasil ditambahkan!');
    }

    // ── Detail hutang + riwayat bayar ────────────────────────────────────────
    public function detail($id)
    {
        $hutang = DB::table('hutang as h')
            ->leftJoin('users as u', 'h.id_user', '=', 'u.id')
            ->select('h.*', 'u.name as nama_kasir', DB::raw('(h.total - h.terbayar) as sisa'))
            ->where('h.id', $id)
            ->first();

        if (!$hutang) {
            return redirect('/admin/hutang')->with('error', 'Data hutang tidak ditemukan!');
        }

        $riwayat = DB::table('hutang_bayar as hb')
            ->leftJoin('users as u', 'hb.id_user', '=', 'u.id')
            ->select('hb.*', 'u.name as nama_kasir')
            ->where('hb.id_hutang', $id)
            ->orderBy('hb.id', 'DESC')
            ->get();

        // Ambil detail transaksi jika hutang dari transaksi
        $transaksi = null;
        if ($hutang->id_transaksi) {
            $transaksi = DB::table('transaksi as t')
                ->leftJoin('metode as mp', 't.id_metode', '=', 'mp.id')
                ->select('t.*', 'mp.nama as nama_metode')
                ->where('t.id', $hutang->id_transaksi)
                ->first();
        }

        return view('admin.hutang.detail', compact('hutang', 'riwayat', 'transaksi'));
    }

    // ── Bayar sebagian / lunas ───────────────────────────────────────────────
    public function bayar(Request $request, $id)
    {
        $request->validate([
            'jumlah'    => 'required',
            'id_metode' => 'required|integer|exists:metode,id',
        ]);

        $hutang = DB::table('hutang')->where('id', $id)->first();
        if (!$hutang) {
            return redirect('/admin/hutang')->with('error', 'Hutang tidak ditemukan!');
        }

        $sisa   = $hutang->total - $hutang->terbayar;
        $jumlah = intval(preg_replace('/\D/', '', $request->jumlah));

        if ($jumlah <= 0) {
            return back()->with('error', 'Jumlah bayar harus lebih dari 0!');
        }

        // Tidak boleh bayar melebihi sisa
        $jumlah = min($jumlah, $sisa);

        DB::beginTransaction();
        try {
            // 1. Catat riwayat bayar hutang
            DB::table('hutang_bayar')->insert([
                'id_hutang'  => $id,
                'jumlah'     => $jumlah,
                'keterangan' => $request->keterangan,
                'id_user'    => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $terbayarBaru = $hutang->terbayar + $jumlah;
            $statusBaru   = ($terbayarBaru >= $hutang->total) ? 'lunas' : 'belum';

            // 2. Update status hutang
            DB::table('hutang')->where('id', $id)->update([
                'terbayar'   => $terbayarBaru,
                'status'     => $statusBaru,
                'updated_at' => now(),
            ]);

            // 3. Catat ke pemasukan
            //    Label keterangan: nama pelanggan + nomor hutang + status (cicilan/pelunasan)
            $labelBayar = $statusBaru === 'lunas' ? 'Pelunasan' : 'Cicilan';
            $kodeRef    = $hutang->id_transaksi
                ? 'TRX-' . $hutang->id_transaksi
                : 'HTG-' . $hutang->id;

            DB::table('pemasukan')->insert([
                'tanggal'      => date('Y-m-d'),
                'keterangan'   => $labelBayar . ' Hutang ' . $hutang->nama_pelanggan
                                  . ' (' . $kodeRef . ')'
                                  . ($request->keterangan ? ' - ' . $request->keterangan : ''),
                'total'        => $jumlah,
                'id_metode'    => $request->id_metode,
                'id_transaksi' => $hutang->id_transaksi ?? 0,
                'id_user'      => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }

        return redirect("/admin/hutang/detail/{$id}")
            ->with('success', 'Pembayaran berhasil dicatat dan masuk ke pemasukan!');
    }

    // ── Hapus hutang (hanya jika belum ada riwayat bayar) ───────────────────
    public function delete($id)
    {
        $cek = DB::table('hutang_bayar')->where('id_hutang', $id)->count();
        if ($cek > 0) {
            return redirect('/admin/hutang')
                ->with('error', 'Hutang tidak bisa dihapus karena sudah ada riwayat pembayaran!');
        }

        DB::table('hutang')->where('id', $id)->delete();
        return redirect('/admin/hutang')->with('success', 'Data hutang berhasil dihapus!');
    }
}