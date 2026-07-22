<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use PDF;

class PengeluaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function read(){
        date_default_timezone_set('Asia/Jakarta');
        $bln = date('Y-m-d');
        $query = DB::table('pengeluaran');
        $this->applyReportFilter($query, $bln);

        if(Auth::User()->level != '1'){
            $query->where('id_user',Auth::User()->id);
        }

        $pengeluaran = $query->orderBy('id','DESC')->get();
        return view('admin.pengeluaran.index',['pengeluaran'=>$pengeluaran,'bln'=>$bln]);
    }

    public function read_filter($bln){
        $query = DB::table('pengeluaran');
        $this->applyReportFilter($query, $bln);

        if(Auth::User()->level != '1'){
            $query->where('id_user',Auth::User()->id);
        }

        $pengeluaran = $query->orderBy('id','DESC')->get();

        return view('admin.pengeluaran.index',['pengeluaran'=>$pengeluaran,'bln'=>$bln]);
    }

    public function add(){
        $metode = DB::table('metode')->get();
        return view('admin.pengeluaran.tambah',['metode'=>$metode]);
    }

    public function create(Request $request){
        $total = preg_replace('/\D/', '', $request->total);

        DB::table('pengeluaran')->insert([  
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'total' => $total,
            'id_metode' => $request->id_metode,
            'id_user' => Auth::User()->id 
        ]);

        return redirect('/admin/pengeluaran')->with("success","Data Berhasil Ditambah !");
    }

    public function edit($id){
        $pengeluaran= DB::table('pengeluaran')->where('id',$id)->first();
        $metode = DB::table('metode')->get();
        
        return view('admin.pengeluaran.edit',['pengeluaran'=>$pengeluaran,'metode'=>$metode]);
    }

    public function update(Request $request, $id){
        $total = preg_replace('/\D/', '', $request->total);

        DB::table('pengeluaran')  
            ->where('id', $id)
            ->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'total' => $total,
            'id_metode' => $request->id_metode
        ]);

        return redirect('/admin/pengeluaran')->with("success","Data Berhasil Diupdate !");
    }

    public function delete($id)
    {
        DB::table('pengeluaran')->where('id',$id)->delete();

        return redirect('/admin/pengeluaran')->with("success","Data Berhasil Dihapus !");
    }

    public function cetak($bln)
    {
        // Ambil data pengeluaran berdasarkan bulan yang diberikan
        $query = DB::table('pengeluaran');
        $this->applyReportFilter($query, $bln);
        $pengeluaran = $query->orderBy('id', 'DESC')->get();

        // Format filter untuk header PDF
        $formattedTanggal = $this->formatFilterLabel($bln);

        // Load view dan set paper untuk PDF
        $pdf = PDF::loadview('admin.pengeluaran.cetak', [
            'pengeluaran' => $pengeluaran,
            'formattedTanggal' => $formattedTanggal,
            'bln' => $bln,
        ]);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF dengan nama yang sudah diformat (langsung terdownload)
        return $pdf->download('Laporan Pengeluaran ' . $formattedTanggal . '.pdf');
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
