<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use PDF;

class LaporanKeuntunganController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function read()
    {
        date_default_timezone_set('Asia/Jakarta');
        $bln = date('Y-m-d');
        $data = $this->buildLaporan($bln);
        return view('admin.laporan_keuntungan.index', $data);
    }

    public function read_filter($bln)
    {
        $data = $this->buildLaporan($bln);
        return view('admin.laporan_keuntungan.index', $data);
    }

    public function cetak($bln)
    {
        $data = $this->buildLaporan($bln);
        $data['formattedTanggal'] = $this->formatFilterLabel($bln);

        $pdf = PDF::loadview('admin.laporan_keuntungan.cetak', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan Keuntungan ' . $data['formattedTanggal'] . '.pdf');
    }

    /**
     * Menyiapkan seluruh data laporan keuntungan (pemasukan, pengeluaran,
     * rincian per metode, serta selisih/keuntungan) untuk periode $bln.
     * $bln bisa berupa YYYY (tahun), YYYY-MM (bulan), atau YYYY-MM-DD (tanggal).
     */
    private function buildLaporan(string $bln): array
    {
        $isAdmin = Auth::user()->level == '1';

        // Total pemasukan & pengeluaran keseluruhan pada periode terpilih
        $totalPemasukan = $this->applyReportFilter(
            DB::table('pemasukan'), $bln
        )->when(!$isAdmin, fn($q) => $q->where('id_user', Auth::id()))
         ->sum('total');

        $totalPengeluaran = $this->applyReportFilter(
            DB::table('pengeluaran'), $bln
        )->when(!$isAdmin, fn($q) => $q->where('id_user', Auth::id()))
         ->sum('total');

        $keuntungan = $totalPemasukan - $totalPengeluaran;

        // Rincian per metode pembayaran
        $metodeList = DB::table('metode')->get();

        $rincianMetode = $metodeList->map(function ($metode) use ($bln, $isAdmin) {
            $pemasukanMetode = $this->applyReportFilter(
                DB::table('pemasukan')->where('id_metode', $metode->id), $bln
            )->when(!$isAdmin, fn($q) => $q->where('id_user', Auth::id()))
             ->sum('total');

            $pengeluaranMetode = $this->applyReportFilter(
                DB::table('pengeluaran')->where('id_metode', $metode->id), $bln
            )->when(!$isAdmin, fn($q) => $q->where('id_user', Auth::id()))
             ->sum('total');

            return [
                'nama_metode'  => $metode->nama,
                'pemasukan'    => $pemasukanMetode,
                'pengeluaran'  => $pengeluaranMetode,
                'selisih'      => $pemasukanMetode - $pengeluaranMetode,
            ];
        });

        return [
            'bln'              => $bln,
            'totalPemasukan'   => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'keuntungan'       => $keuntungan,
            'rincianMetode'    => $rincianMetode,
        ];
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
