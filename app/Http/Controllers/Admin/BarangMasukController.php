<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class BarangMasukController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function read()
    {
        $barang_masuk = DB::table('barang_masuk')->orderBy('id', 'DESC')->get();

        return view('admin.barang_masuk.index', ['barang_masuk' => $barang_masuk]);
    }

    public function add()
    {
        $barang = DB::table('barang')->orderBy('nama', 'ASC')->get();

        return view('admin.barang_masuk.tambah', ['barang' => $barang]);
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

            $barang     = DB::table('barang')->find($request->id_barang);
            $satuan     = DB::table('satuan')->find($detail_barang->id_satuan);
            $keterangan = $barang->nama . ($satuan ? ' - ' . $satuan->nama : '');

            DB::table('barang_masuk')->insert([
                'tanggal'         => $request->tanggal,
                'id_detail_barang'=> $detail_barang->id,
                'keterangan'      => $keterangan,
                'jumlah'          => $jumlah,
                'id_user'         => Auth::user()->id,
            ]);

            DB::table('detail_barang')
                ->where('id', $detail_barang->id)
                ->update(['stock' => $detail_barang->stock + $jumlah]);
        });

        return redirect('/admin/barang_masuk')->with('success', 'Data Berhasil Ditambah!');
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $barang_masuk = DB::table('barang_masuk')->find($id);

            $detail_barang = DB::table('detail_barang')
                ->where('id', $barang_masuk->id_detail_barang)
                ->lockForUpdate()
                ->first();

            // Pastikan stock tidak minus saat barang masuk dihapus
            $stock_baru = max(0, $detail_barang->stock - $barang_masuk->jumlah);

            DB::table('barang_masuk')->where('id', $id)->delete();

            DB::table('detail_barang')
                ->where('id', $detail_barang->id)
                ->update(['stock' => $stock_baru]);
        });

        return redirect('/admin/barang_masuk')->with('success', 'Data Berhasil Dihapus!');
    }
}