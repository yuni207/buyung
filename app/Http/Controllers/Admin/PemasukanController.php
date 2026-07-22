<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use PDF;

class PemasukanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function read()
    {
        date_default_timezone_set('Asia/Jakarta');
        $bln = date('Y-m-d');
        $pemasukan = $this->queryPemasukan($bln);
        return view('admin.pemasukan.index', ['pemasukan' => $pemasukan, 'bln' => $bln]);
    }

    public function read_filter($bln)
    {
        $pemasukan = $this->queryPemasukan($bln);
        return view('admin.pemasukan.index', ['pemasukan' => $pemasukan, 'bln' => $bln]);
    }

    public function cetak($bln)
    {
        $pemasukan      = $this->queryPemasukanCetak($bln);
        $formattedTanggal = $this->formatFilterLabel($bln);

        $pdf = PDF::loadview('admin.pemasukan.cetak', [
            'pemasukan'      => $pemasukan,
            'formattedTanggal' => $formattedTanggal,
            'bln'            => $bln,
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan Pemasukan ' . $formattedTanggal . '.pdf');
    }

    /**
     * Query untuk halaman index — kolom id_metode & id_user tetap tersedia
     * sehingga blade index yang melakukan DB::table('metode')->find($data->id_metode)
     * dan DB::table('users')->find($data->id_user) tidak perlu diubah.
     */
    private function queryPemasukan(string $bln)
    {
        $isAdmin = Auth::user()->level == '1';

        $query = DB::table('pemasukan');
        $this->applyReportFilter($query, $bln);

        return $query
            ->when(!$isAdmin, fn($q) => $q->where('id_user', Auth::id()))
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Query untuk cetak PDF — sudah di-join dengan metode & transaksi
     * sehingga blade cetak tidak perlu query tambahan per baris.
     * Kolom tambahan yang tersedia:
     *   - nama_metode   : nama metode pembayaran
     *   - kode_transaksi: kode transaksi asal (null jika manual)
     *   - sumber        : 'transaksi' jika berasal dari transaksi kasir/hutang, 'manual' jika tidak
     */
    private function queryPemasukanCetak(string $bln)
    {
        $isAdmin = Auth::user()->level == '1';

        $query = DB::table('pemasukan as p')
            ->leftJoin('metode as m', 'p.id_metode', '=', 'm.id')
            ->leftJoin('transaksi as t', 'p.id_transaksi', '=', 't.id')
            ->select(
                'p.*',
                'm.nama as nama_metode',
                't.nama as kode_transaksi',
                DB::raw("CASE WHEN p.id_transaksi IS NOT NULL AND p.id_transaksi != '0' THEN 'transaksi' ELSE 'manual' END as sumber")
            );

        $this->applyReportFilter($query, $bln, 'p.tanggal');

        return $query
            ->when(!$isAdmin, fn($q) => $q->where('p.id_user', Auth::id()))
            ->orderBy('p.id', 'DESC')
            ->get();
    }

    private function applyReportFilter($query, string $filter, string $column = 'tanggal')
    {
        $filter = trim($filter);

        if (preg_match('/^\d{4}$/', $filter) || preg_match('/^\d{4}-\d{2}$/', $filter) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter)) {
            $query->where($column, 'LIKE', $filter . '%');
        } else {
            $query->whereDate($column, date('Y-m-d'));
        }

        return $query;
    }

    private function formatFilterLabel(string $filter): string
    {
        $filter = trim($filter);

        if (preg_match('/^\d{4}$/', $filter)) {
            return 'TAHUN ' . \Carbon\Carbon::parse($filter . '-01-01')->locale('id')->translatedFormat('Y');
        }

        if (preg_match('/^\d{4}-\d{2}$/', $filter)) {
            return 'BULAN ' . \Carbon\Carbon::parse($filter . '-01')->locale('id')->translatedFormat('F Y');
        }

        return 'TANGGAL ' . \Carbon\Carbon::parse($filter)->locale('id')->translatedFormat('d F Y');
    }
}