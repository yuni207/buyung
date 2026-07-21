<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class BarangKeluarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function read()
    {
        $barang_keluar = DB::table('barang_keluar')->orderBy('id', 'DESC')->get();

        return view('admin.barang_keluar.index', ['barang_keluar' => $barang_keluar]);
    }

    public function add()
    {
        $barang = DB::table('barang')->orderBy('nama', 'ASC')->get();

        return view('admin.barang_keluar.tambah', ['barang' => $barang]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id',
            'ukuran'    => 'required|exists:detail_barang,id',
            'tanggal'   => 'required|date',
            'jumlah'    => 'required',
        ]);

        $jumlah = (int) preg_replace('/\D/', '', $request->jumlah);

        DB::transaction(function () use ($request, $jumlah) {
            // Kunci baris agar tidak terjadi race condition
            $detail_barang = DB::table('detail_barang')
                ->where('id', $request->ukuran)
                ->lockForUpdate()
                ->first();

            if ($detail_barang->stock < $jumlah) {
                // Lempar exception agar transaksi otomatis di-rollback
                throw new \Exception('Stok tidak mencukupi.');
            }

            $barang     = DB::table('barang')->find($request->id_barang);
            $satuan     = DB::table('satuan')->find($detail_barang->id_satuan);
            $keterangan = $barang->nama . ($satuan ? ' - ' . $satuan->nama : '');

            DB::table('barang_keluar')->insert([
                'tanggal'         => $request->tanggal,
                'id_detail_barang'=> $detail_barang->id,
                'keterangan'      => $keterangan,
                'jumlah'          => $jumlah,
                'id_user'         => Auth::user()->id,
            ]);

            DB::table('detail_barang')
                ->where('id', $detail_barang->id)
                ->update(['stock' => $detail_barang->stock - $jumlah]);
        });

        return redirect('/admin/barang_keluar')->with('success', 'Data Berhasil Ditambah!');
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $barang_keluar = DB::table('barang_keluar')->find($id);

            $detail_barang = DB::table('detail_barang')
                ->where('id', $barang_keluar->id_detail_barang)
                ->lockForUpdate()
                ->first();

            DB::table('barang_keluar')->where('id', $id)->delete();

            DB::table('detail_barang')
                ->where('id', $detail_barang->id)
                ->update(['stock' => $detail_barang->stock + $barang_keluar->jumlah]);
        });

        return redirect('/admin/barang_keluar')->with('success', 'Data Berhasil Dihapus!');
    }
}