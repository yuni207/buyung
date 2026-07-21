<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class BarangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function read()
    {
        if (Auth::user()->level == '3') {
            $detail_barang = DB::table('detail_barang')
                ->where('detail_barang.id_user', Auth::user()->id)
                ->join('barang', 'barang.id', '=', 'detail_barang.id_barang')
                ->select('detail_barang.*', 'barang.barcode', 'barang.nama')
                ->orderBy('barang.nama', 'ASC')
                ->orderBy('detail_barang.id', 'ASC')
                ->get();
        } else {
            $detail_barang = DB::table('detail_barang')
                ->join('barang', 'barang.id', '=', 'detail_barang.id_barang')
                ->select('detail_barang.*', 'barang.barcode', 'barang.nama')
                ->orderBy('barang.nama', 'ASC')
                ->orderBy('detail_barang.id', 'ASC')
                ->get();
        }

        // Group by id_barang
        $grouped = $detail_barang->groupBy('id_barang');

        return view('admin.barang.index', ['grouped' => $grouped]);
    }

    public function add()
    {
        $satuan = DB::table('satuan')->orderBy('id', 'ASC')->get();

        // Ambil daftar barang unik untuk autocomplete / dropdown
        $daftar_barang = DB::table('barang')->orderBy('nama', 'ASC')->get();

        if (Auth::user()->level == '3') {
            $detail_barang = DB::table('detail_barang')
                ->where('detail_barang.id_user', Auth::user()->id)
                ->join('barang', 'barang.id', '=', 'detail_barang.id_barang')
                ->select('detail_barang.*', 'barang.barcode', 'barang.nama')
                ->orderBy('detail_barang.id', 'DESC')
                ->get();
        } else {
            $detail_barang = DB::table('detail_barang')
                ->join('barang', 'barang.id', '=', 'detail_barang.id_barang')
                ->select('detail_barang.*', 'barang.barcode', 'barang.nama')
                ->orderBy('detail_barang.id', 'DESC')
                ->get();
        }

        $grouped = $detail_barang->groupBy('id_barang');

        return view('admin.barang.tambah', [
            'grouped'       => $grouped,
            'satuan'        => $satuan,
            'daftar_barang' => $daftar_barang,
        ]);
    }

    public function create(Request $request)
    {
        // 1. Cek apakah barang dengan barcode + nama sudah ada
        $cek = DB::table('barang')
            ->where('barcode', $request->barcode)
            ->where('nama', $request->nama)
            ->first();

        if ($cek == "") {
            $id_barang = DB::table('barang')->insertGetId([
                'barcode' => $request->barcode,
                'nama'    => $request->nama,
                'id_user' => Auth::user()->id,
            ]);
        } else {
            $id_barang = $cek->id;
        }

        // 2. Ambil data array dari form
        $id_satuans     = $request->id_satuan;    // Array
        $stocks         = $request->stock;        // Array
        $harga_modals   = $request->harga_modal;  // Array
        $harga_juals    = $request->harga_jual;   // Array
        $harga_khususes = $request->harga_khusus; // Array

        $successCount = 0;
        $errorMsgs    = [];

        // 3. Looping untuk menyimpan multi satuan
        for ($i = 0; $i < count($id_satuans); $i++) {
            $id_sat = $id_satuans[$i];

            // Cek apakah satuan yang sama sudah ada untuk barang ini
            $cek_detail = DB::table('detail_barang')
                ->where('id_barang', $id_barang)
                ->where('id_satuan', $id_sat)
                ->first();

            if ($cek_detail) {
                $nama_sat    = DB::table('satuan')->where('id', $id_sat)->value('nama');
                $errorMsgs[] = "Satuan $nama_sat sudah ada";
                continue;
            }

            // Bersihkan format angka (hapus titik)
            $stock        = preg_replace('/\D/', '', $stocks[$i]);
            $harga_modal  = preg_replace('/\D/', '', $harga_modals[$i]);
            $harga_jual   = preg_replace('/\D/', '', $harga_juals[$i]);
            $harga_khusus = preg_replace('/\D/', '', $harga_khususes[$i]);

            DB::table('detail_barang')->insert([
                'id_barang'    => $id_barang,
                'id_satuan'    => $id_sat,
                'stock'        => $stock ?: 0,
                'harga_modal'  => $harga_modal ?: 0,
                'harga_jual'   => $harga_jual ?: 0,
                'harga_khusus' => $harga_khusus ?: 0,
                'id_user'      => Auth::user()->id,
            ]);

            $successCount++;
        }

        // 4. Return response berdasarkan hasil loop
        if (count($errorMsgs) > 0 && $successCount == 0) {
            return redirect("/admin/barang/add")
                ->with("error", "Gagal menyimpan: " . implode(", ", $errorMsgs));
        } elseif (count($errorMsgs) > 0 && $successCount > 0) {
            return redirect("/admin/barang/add")
                ->with("success", "$successCount data berhasil ditambah. Peringatan: " . implode(", ", $errorMsgs));
        }

        return redirect("/admin/barang/add")
            ->with("success", "Semua Data Satuan Berhasil Ditambah!");
    }

    public function edit($id)
    {
        $detail_barang = DB::table('detail_barang')->where('id', $id)->first();
        $barang        = DB::table('barang')->where('id', $detail_barang->id_barang)->first();
        $satuan        = DB::table('satuan')->orderBy('nama', 'ASC')->get();

        // Satuan yang sudah dipakai barang ini (selain satuan saat ini)
        $satuan_terpakai = DB::table('detail_barang')
            ->where('id_barang', $detail_barang->id_barang)
            ->where('id', '!=', $id)
            ->pluck('id_satuan')
            ->toArray();

        return view('admin.barang.edit', [
            'barang'          => $barang,
            'detail_barang'   => $detail_barang,
            'satuan'          => $satuan,
            'satuan_terpakai' => $satuan_terpakai,
        ]);
    }

    public function update(Request $request, $id)
    {
        $detail_barang = DB::table('detail_barang')->where('id', $id)->first();

        // Cek apakah satuan baru sudah dipakai detail lain pada barang yang sama
        $cek_satuan = DB::table('detail_barang')
            ->where('id_barang', $detail_barang->id_barang)
            ->where('id_satuan', $request->id_satuan)
            ->where('id', '!=', $id)
            ->first();

        if ($cek_satuan) {
            return redirect("/admin/barang/edit/$id")
                ->with("error", "Satuan tersebut sudah digunakan untuk barang ini!");
        }

        DB::table('barang')
            ->where('id', $detail_barang->id_barang)
            ->update([
                'barcode' => $request->barcode,
                'nama'    => $request->nama,
            ]);

        $stock        = preg_replace('/\D/', '', $request->stock);
        $harga_modal  = preg_replace('/\D/', '', $request->harga_modal);
        $harga_jual   = preg_replace('/\D/', '', $request->harga_jual);
        $harga_khusus = preg_replace('/\D/', '', $request->harga_khusus);

        DB::table('detail_barang')
            ->where('id', $id)
            ->update([
                'id_satuan'    => $request->id_satuan,
                'stock'        => $stock,
                'harga_modal'  => $harga_modal,
                'harga_jual'   => $harga_jual,
                'harga_khusus' => $harga_khusus,
            ]);

        return redirect('/admin/barang')
            ->with("success", "Data Berhasil Diupdate!");
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $detail_barang = DB::table('detail_barang')->find($id);
            $cek = DB::table('detail_barang')
                ->where('id_barang', $detail_barang->id_barang)
                ->count();

            if ($cek == 1) {
                DB::table('barang')->where('id', $detail_barang->id_barang)->delete();
            }

            DB::table('detail_barang')->where('id', $id)->delete();
        });

        return redirect('/admin/barang')->with('success', 'Data Berhasil Dihapus!');
    }

    public function detail($id)
    {
        $barang        = DB::table('barang')->where('id', $id)->first();
        $detail_barang = DB::table('detail_barang')
            ->where('id_barang', $id)
            ->orderBy('id', 'DESC')
            ->get();
        $jenis = DB::table('jenis')->find($barang->id_jenis ?? '');
        $merk  = DB::table('merk')->find($barang->id_merk ?? '');

        return view('admin.barang.detail', [
            'barang'        => $barang,
            'detail_barang' => $detail_barang,
            'jenis'         => $jenis,
            'merk'          => $merk,
        ]);
    }

    public function create_detail(Request $request, $id)
    {
        $request->validate([
            'id_satuan'    => 'required|exists:satuan,id',
            'stock'        => 'required',
            'harga_modal'  => 'required',
            'harga_jual'   => 'required',
            'harga_khusus' => 'nullable',
        ]);

        $stock        = (int) preg_replace('/\D/', '', $request->stock);
        $harga_modal  = (int) preg_replace('/\D/', '', $request->harga_modal);
        $harga_jual   = (int) preg_replace('/\D/', '', $request->harga_jual);
        $harga_khusus = (int) preg_replace('/\D/', '', $request->harga_khusus ?? 0);

        DB::table('detail_barang')->insert([
            'id_barang'    => $id,
            'id_satuan'    => $request->id_satuan,
            'stock'        => $stock,
            'harga_modal'  => $harga_modal,
            'harga_jual'   => $harga_jual,
            'harga_khusus' => $harga_khusus,
            'id_user'      => Auth::user()->id,
        ]);

        return redirect("/admin/barang/detail/$id")->with('success', 'Data Berhasil Ditambah!');
    }

    public function update_detail(Request $request, $id)
    {
        $request->validate([
            'id_satuan'    => 'required|exists:satuan,id',
            'stock'        => 'required',
            'harga_modal'  => 'required',
            'harga_jual'   => 'required',
            'harga_khusus' => 'nullable',
        ]);

        $stock        = (int) preg_replace('/\D/', '', $request->stock);
        $harga_modal  = (int) preg_replace('/\D/', '', $request->harga_modal);
        $harga_jual   = (int) preg_replace('/\D/', '', $request->harga_jual);
        $harga_khusus = (int) preg_replace('/\D/', '', $request->harga_khusus ?? 0);

        $detail_barang = DB::table('detail_barang')->find($id);

        DB::table('detail_barang')->where('id', $id)->update([
            'id_satuan'    => $request->id_satuan,
            'stock'        => $stock,
            'harga_modal'  => $harga_modal,
            'harga_jual'   => $harga_jual,
            'harga_khusus' => $harga_khusus,
        ]);

        return redirect("/admin/barang/detail/$detail_barang->id_barang")->with('success', 'Data Berhasil Diupdate!');
    }

    public function delete_detail($id)
    {
        $detail_barang = DB::table('detail_barang')->find($id);
        DB::table('detail_barang')->where('id', $id)->delete();

        return redirect("/admin/barang/detail/$detail_barang->id_barang")->with('success', 'Data Berhasil Dihapus!');
    }

    public function getUkuran($id_barang)
    {
        $satuan = DB::table('detail_barang')
            ->join('satuan', 'detail_barang.id_satuan', '=', 'satuan.id')
            ->where('detail_barang.id_barang', $id_barang)
            ->select('detail_barang.id', 'satuan.nama as satuan')
            ->get();

        return response()->json($satuan);
    }

    public function getStock($id)
    {
        $detail = DB::table('detail_barang')
            ->join('satuan', 'detail_barang.id_satuan', '=', 'satuan.id')
            ->where('detail_barang.id', $id)
            ->select('detail_barang.stock', 'satuan.nama as satuan', 'detail_barang.harga_jual', 'detail_barang.harga_khusus')
            ->first();

        return response()->json($detail);
    }

    /**
     * Cari barang berdasarkan barcode (AJAX) untuk autocomplete tambah
     */
    public function cariBarang(Request $request)
    {
        $barcode = $request->barcode;
        $barang  = DB::table('barang')->where('barcode', $barcode)->first();

        if ($barang) {
            // Satuan yang sudah dipakai
            $satuan_terpakai = DB::table('detail_barang')
                ->where('id_barang', $barang->id)
                ->pluck('id_satuan')
                ->toArray();

            return response()->json([
                'found'           => true,
                'id'              => $barang->id,
                'nama'            => $barang->nama,
                'satuan_terpakai' => $satuan_terpakai,
            ]);
        }

        return response()->json(['found' => false]);
    }
}