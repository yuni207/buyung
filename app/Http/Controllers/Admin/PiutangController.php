<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class PiutangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Daftar semua piutang ─────────────────────────────────────────────────
    public function read(Request $request)
    {
        $query = DB::table('piutang as p')
            ->leftJoin('users as u', 'p.id_user', '=', 'u.id')
            ->select(
                'p.*',
                'u.name as nama_kasir',
                DB::raw('(p.total - p.terbayar) as sisa')
            );

        // Filter status
        if ($request->filled('status')) {
            $query->where('p.status', $request->status);
        }

        // Filter bulan
        if ($request->filled('bln')) {
            $query->where('p.tanggal', 'like', $request->bln . '%');
        }

        // Non-admin hanya lihat milik sendiri
        if (Auth::user()->level != '1') {
            $query->where('p.id_user', Auth::id());
        }

        $piutang     = $query->orderBy('p.id', 'DESC')->get();
        $totalSisa   = $piutang->where('status', 'belum')->sum('sisa');
        $totalLunas  = $piutang->where('status', 'lunas')->sum('total');

        return view('admin.piutang.index', compact('piutang', 'totalSisa', 'totalLunas'));
    }

    // ── Cetak / download laporan piutang (PDF) ───────────────────────────────
    public function cetak(Request $request)
    {
        $query = DB::table('piutang as p')
            ->leftJoin('users as u', 'p.id_user', '=', 'u.id')
            ->select(
                'p.*',
                'u.name as nama_kasir',
                DB::raw('(p.total - p.terbayar) as sisa')
            );

        if ($request->filled('status')) {
            $query->where('p.status', $request->status);
        }

        if ($request->filled('bln')) {
            $query->where('p.tanggal', 'like', trim($request->bln) . '%');
        }

        if (Auth::user()->level != '1') {
            $query->where('p.id_user', Auth::id());
        }

        $piutang          = $query->orderBy('p.id', 'DESC')->get();
        $totalSisa        = $piutang->where('status', 'belum')->sum('sisa');
        $totalLunas       = $piutang->where('status', 'lunas')->sum('total');
        $totalKeseluruhan = $piutang->sum('total');
        $label            = $this->buildLabel($request);

        $pdf = PDF::loadview('admin.piutang.cetak', compact('piutang', 'totalSisa', 'totalLunas', 'totalKeseluruhan', 'label'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan Piutang ' . $label . '.pdf');
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

    // ── Form tambah piutang ──────────────────────────────────────────────────
    public function add()
    {
        $metode = DB::table('metode')->orderBy('nama')->get();
        return view('admin.piutang.tambah', compact('metode'));
    }

    // ── Simpan piutang baru ──────────────────────────────────────────────────
    // Otomatis mencatat ke tabel pengeluaran
    public function create(Request $request)
    {
        $request->validate([
            'nama_peminjam' => 'required|string|max:100',
            'no_hp'         => 'nullable|digits_between:1,12',
            'total'         => 'required',
            'tanggal'       => 'required|date',
            'id_metode'     => 'required|integer|exists:metode,id',
        ]);

        $total = intval(preg_replace('/\D/', '', $request->total));

        DB::beginTransaction();
        try {
            // 1. Catat ke pengeluaran
            $idPengeluaran = DB::table('pengeluaran')->insertGetId([
                'tanggal'    => $request->tanggal,
                'keterangan' => 'Piutang kepada ' . $request->nama_peminjam
                                . ($request->keterangan ? ' - ' . $request->keterangan : ''),
                'total'      => $total,
                'id_metode'  => $request->id_metode,
                'id_user'    => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Catat ke tabel piutang
            DB::table('piutang')->insert([
                'nama_peminjam'  => $request->nama_peminjam,
                'no_hp'          => $request->no_hp,
                'keterangan'     => $request->keterangan,
                'total'          => $total,
                'terbayar'       => 0,
                'status'         => 'belum',
                'id_pengeluaran' => $idPengeluaran,
                'id_user'        => Auth::id(),
                'tanggal'        => $request->tanggal,
                'jatuh_tempo'    => $request->jatuh_tempo ?: null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan piutang: ' . $e->getMessage());
        }

        return redirect('/admin/piutang')->with('success', 'Piutang berhasil ditambahkan dan otomatis masuk ke pengeluaran!');
    }

    // ── Detail piutang + riwayat pengembalian ───────────────────────────────
    public function detail($id)
    {
        $piutang = DB::table('piutang as p')
            ->leftJoin('users as u', 'p.id_user', '=', 'u.id')
            ->select('p.*', 'u.name as nama_kasir', DB::raw('(p.total - p.terbayar) as sisa'))
            ->where('p.id', $id)
            ->first();

        if (!$piutang) {
            return redirect('/admin/piutang')->with('error', 'Data piutang tidak ditemukan!');
        }

        $riwayat = DB::table('piutang_bayar as pb')
            ->leftJoin('users as u', 'pb.id_user', '=', 'u.id')
            ->leftJoin('metode as m', 'pb.id_metode', '=', 'm.id')
            ->select('pb.*', 'u.name as nama_kasir', 'm.nama as nama_metode')
            ->where('pb.id_piutang', $id)
            ->orderBy('pb.id', 'DESC')
            ->get();

        // Data pengeluaran asal
        $pengeluaran = null;
        if ($piutang->id_pengeluaran) {
            $pengeluaran = DB::table('pengeluaran as pe')
                ->leftJoin('metode as m', 'pe.id_metode', '=', 'm.id')
                ->select('pe.*', 'm.nama as nama_metode')
                ->where('pe.id', $piutang->id_pengeluaran)
                ->first();
        }

        $metode = DB::table('metode')->orderBy('nama')->get();

        return view('admin.piutang.detail', compact('piutang', 'riwayat', 'pengeluaran', 'metode'));
    }

    // ── Catat pengembalian piutang ───────────────────────────────────────────
    // Pengembalian masuk ke pemasukan
    public function bayar(Request $request, $id)
    {
        $request->validate([
            'jumlah'    => 'required',
            'id_metode' => 'required|integer|exists:metode,id',
        ]);

        $piutang = DB::table('piutang')->where('id', $id)->first();
        if (!$piutang) {
            return redirect('/admin/piutang')->with('error', 'Piutang tidak ditemukan!');
        }

        $sisa   = $piutang->total - $piutang->terbayar;
        $jumlah = intval(preg_replace('/\D/', '', $request->jumlah));

        if ($jumlah <= 0) {
            return back()->with('error', 'Jumlah harus lebih dari 0!');
        }

        // Tidak boleh melebihi sisa
        $jumlah = min($jumlah, $sisa);

        DB::beginTransaction();
        try {
            // 1. Catat riwayat pengembalian
            DB::table('piutang_bayar')->insert([
                'id_piutang'  => $id,
                'jumlah'      => $jumlah,
                'keterangan'  => $request->keterangan,
                'id_metode'   => $request->id_metode,
                'id_user'     => Auth::id(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $terbayarBaru = $piutang->terbayar + $jumlah;
            $statusBaru   = ($terbayarBaru >= $piutang->total) ? 'lunas' : 'belum';

            // 2. Update status piutang saja — tidak masuk ke pemasukan
            DB::table('piutang')->where('id', $id)->update([
                'terbayar'   => $terbayarBaru,
                'status'     => $statusBaru,
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan pengembalian: ' . $e->getMessage());
        }

        return redirect("/admin/piutang/detail/{$id}")
            ->with('success', 'Pengembalian piutang berhasil dicatat!');
    }

    // ── Hapus piutang (hanya jika belum ada riwayat pengembalian) ───────────
    public function delete($id)
    {
        $cek = DB::table('piutang_bayar')->where('id_piutang', $id)->count();
        if ($cek > 0) {
            return redirect('/admin/piutang')
                ->with('error', 'Piutang tidak bisa dihapus karena sudah ada riwayat pengembalian!');
        }

        $piutang = DB::table('piutang')->where('id', $id)->first();

        DB::beginTransaction();
        try {
            // Hapus juga dari pengeluaran jika ada referensinya
            if ($piutang && $piutang->id_pengeluaran) {
                DB::table('pengeluaran')->where('id', $piutang->id_pengeluaran)->delete();
            }
            DB::table('piutang')->where('id', $id)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }

        return redirect('/admin/piutang')->with('success', 'Data piutang dan pengeluaran terkait berhasil dihapus!');
    }
}