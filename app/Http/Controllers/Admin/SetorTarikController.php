<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SetorTarikController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function read()
    {
        date_default_timezone_set('Asia/Jakarta');
        $bln = date('Y-m');

        if (Auth::User()->level == '1') {
            $data = DB::table('setor_tarik as st')
                ->leftJoin('users as u', 'st.id_user', '=', 'u.id')
                ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
                ->select('st.*', 'u.name as nama_kasir', 'm.nama as nama_metode')
                ->where('st.tanggal', 'LIKE', $bln . '%')
                ->orderBy('st.id', 'DESC')
                ->get();
        } else {
            $data = DB::table('setor_tarik as st')
                ->leftJoin('users as u', 'st.id_user', '=', 'u.id')
                ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
                ->select('st.*', 'u.name as nama_kasir', 'm.nama as nama_metode')
                ->where('st.id_user', Auth::User()->id)
                ->where('st.tanggal', 'LIKE', $bln . '%')
                ->orderBy('st.id', 'DESC')
                ->get();
        }

        $metode = DB::table('metode')->orderBy('nama')->get();

        return view('admin.setor_tarik.index', ['data' => $data, 'metode' => $metode, 'bln' => $bln]);
    }

    public function read_filter($bln)
    {
        if (Auth::User()->level == '1') {
            $data = DB::table('setor_tarik as st')
                ->leftJoin('users as u', 'st.id_user', '=', 'u.id')
                ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
                ->select('st.*', 'u.name as nama_kasir', 'm.nama as nama_metode')
                ->where('st.tanggal', 'LIKE', $bln . '%')
                ->orderBy('st.id', 'DESC')
                ->get();
        } else {
            $data = DB::table('setor_tarik as st')
                ->leftJoin('users as u', 'st.id_user', '=', 'u.id')
                ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
                ->select('st.*', 'u.name as nama_kasir', 'm.nama as nama_metode')
                ->where('st.id_user', Auth::User()->id)
                ->where('st.tanggal', 'LIKE', $bln . '%')
                ->orderBy('st.id', 'DESC')
                ->get();
        }

        $metode = DB::table('metode')->orderBy('nama')->get();

        return view('admin.setor_tarik.index', ['data' => $data, 'metode' => $metode, 'bln' => $bln]);
    }

    public function add()
    {
        $metode = DB::table('metode')->get();
        return view('admin.setor_tarik.tambah', ['metode' => $metode]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'jenis'          => 'required|in:setor tunai,tarik tunai,setor,tarik',
            'total'          => 'required',
            'biaya_admin'    => ['nullable', 'regex:/^[0-9\.,]+$/'],
            'id_metode'      => 'required',
            'tanggal'        => 'required|date',
            'bukti'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $total      = preg_replace('/\D/', '', $request->total);
        $biayaAdmin = $request->biaya_admin ? preg_replace('/\D/', '', $request->biaya_admin) : 0;
        $tanggal    = $request->tanggal;
        $keterangan = '[' . strtoupper($request->jenis) . '] ' . $request->nama_pelanggan
                      . ($request->keterangan ? ' — ' . $request->keterangan : '');

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $file     = $request->file('bukti');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $folder   = public_path('uploads/setor_tarik');
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
            $file->move($folder, $filename);
            $buktiPath = 'uploads/setor_tarik/' . $filename;
        }

        DB::beginTransaction();
        try {
            DB::table('setor_tarik')->insert([
                'tanggal'        => $tanggal,
                'nama_pelanggan' => $request->nama_pelanggan,
                'jenis'          => $request->jenis,
                'total'          => $total,
                'biaya_admin'    => $biayaAdmin,
                'keterangan'     => $request->keterangan,
                'id_metode'      => $request->id_metode,
                'id_user'        => Auth::User()->id,
                'bukti'          => $buktiPath,
            ]);

            if (Str::contains(strtolower($request->jenis), 'setor')) {
                DB::table('pemasukan')->insert([
                    'tanggal'      => $tanggal,
                    'keterangan'   => $keterangan,
                    'total'        => $total,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => 0,
                    'id_user'      => Auth::User()->id,
                ]);
            } else {
                DB::table('pengeluaran')->insert([
                    'tanggal'    => $tanggal,
                    'keterangan' => $keterangan,
                    'total'      => $total,
                    'id_metode'  => $request->id_metode,
                    'id_user'    => Auth::User()->id,
                ]);
            }

            if ($biayaAdmin > 0) {
                $keteranganAdmin = '[BIAYA ADMIN] ' . strtoupper($request->jenis) . ' ' . $request->nama_pelanggan
                    . ($request->keterangan ? ' — ' . $request->keterangan : '');
                DB::table('pemasukan')->insert([
                    'tanggal'      => $tanggal,
                    'keterangan'   => $keteranganAdmin,
                    'total'        => $biayaAdmin,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => 0,
                    'id_user'      => Auth::User()->id,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        return redirect('/admin/setor_tarik')->with('success', 'Data Berhasil Ditambah !');
    }

    public function edit($id)
    {
        $setor  = DB::table('setor_tarik')->where('id', $id)->first();
        $metode = DB::table('metode')->get();

        return view('admin.setor_tarik.edit', ['setor' => $setor, 'metode' => $metode]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'jenis'          => 'required|in:setor tunai,tarik tunai,setor,tarik',
            'total'          => 'required',
            'biaya_admin'    => ['nullable', 'regex:/^[0-9\.,]+$/'],
            'id_metode'      => 'required',
            'tanggal'        => 'required|date',
            'bukti'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lama           = DB::table('setor_tarik')->where('id', $id)->first();
        $total          = preg_replace('/\D/', '', $request->total);
        $biayaAdmin     = $request->biaya_admin ? preg_replace('/\D/', '', $request->biaya_admin) : 0;
        $tanggal        = $request->tanggal;
        $keteranganLama = '[' . strtoupper($lama->jenis) . '] ' . $lama->nama_pelanggan;
        $keteranganBaru = '[' . strtoupper($request->jenis) . '] ' . $request->nama_pelanggan
                          . ($request->keterangan ? ' — ' . $request->keterangan : '');

        $buktiPath = $lama->bukti ?? null;
        if ($request->hasFile('bukti')) {
            if ($buktiPath && file_exists(public_path($buktiPath))) {
                unlink(public_path($buktiPath));
            }
            $file     = $request->file('bukti');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $folder   = public_path('uploads/setor_tarik');
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
            $file->move($folder, $filename);
            $buktiPath = 'uploads/setor_tarik/' . $filename;
        }

        DB::beginTransaction();
        try {
            DB::table('setor_tarik')->where('id', $id)->update([
                'tanggal'        => $tanggal,
                'nama_pelanggan' => $request->nama_pelanggan,
                'jenis'          => $request->jenis,
                'total'          => $total,
                'biaya_admin'    => $biayaAdmin,
                'keterangan'     => $request->keterangan,
                'id_metode'      => $request->id_metode,
                'bukti'          => $buktiPath,
            ]);

            // Hapus entri lama di pemasukan/pengeluaran
            if (Str::contains(strtolower($lama->jenis), 'setor')) {
                DB::table('pemasukan')
                    ->where('tanggal', $lama->tanggal)
                    ->where('total', $lama->total)
                    ->where('id_metode', $lama->id_metode)
                    ->where('keterangan', 'LIKE', $keteranganLama . '%')
                    ->where('id_user', $lama->id_user)
                    ->limit(1)->delete();
            } else {
                DB::table('pengeluaran')
                    ->where('tanggal', $lama->tanggal)
                    ->where('total', $lama->total)
                    ->where('id_metode', $lama->id_metode)
                    ->where('keterangan', 'LIKE', $keteranganLama . '%')
                    ->where('id_user', $lama->id_user)
                    ->limit(1)->delete();
            }

            if ($lama->biaya_admin > 0) {
                $keteranganLamaAdmin = '[BIAYA ADMIN] ' . strtoupper($lama->jenis) . ' ' . $lama->nama_pelanggan
                    . ($lama->keterangan ? ' — ' . $lama->keterangan : '');
                DB::table('pemasukan')
                    ->where('tanggal', $lama->tanggal)
                    ->where('total', $lama->biaya_admin)
                    ->where('id_metode', $lama->id_metode)
                    ->where('keterangan', 'LIKE', $keteranganLamaAdmin . '%')
                    ->where('id_user', $lama->id_user)
                    ->limit(1)->delete();
            }

            // Insert entri baru di pemasukan/pengeluaran
            if (Str::contains(strtolower($request->jenis), 'setor')) {
                DB::table('pemasukan')->insert([
                    'tanggal'      => $tanggal,
                    'keterangan'   => $keteranganBaru,
                    'total'        => $total,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => 0,
                    'id_user'      => $lama->id_user,
                ]);
            } else {
                DB::table('pengeluaran')->insert([
                    'tanggal'    => $tanggal,
                    'keterangan' => $keteranganBaru,
                    'total'      => $total,
                    'id_metode'  => $request->id_metode,
                    'id_user'    => $lama->id_user,
                ]);
            }

            if ($biayaAdmin > 0) {
                $keteranganAdminBaru = '[BIAYA ADMIN] ' . strtoupper($request->jenis) . ' ' . $request->nama_pelanggan
                    . ($request->keterangan ? ' — ' . $request->keterangan : '');
                DB::table('pemasukan')->insert([
                    'tanggal'      => $tanggal,
                    'keterangan'   => $keteranganAdminBaru,
                    'total'        => $biayaAdmin,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => 0,
                    'id_user'      => $lama->id_user,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate: ' . $e->getMessage());
        }

        return redirect('/admin/setor_tarik')->with('success', 'Data Berhasil Diupdate !');
    }

    public function hapus($id)
    {
        $setor = DB::table('setor_tarik')->where('id', $id)->first();
        return view('admin.setor_tarik.hapus', ['setor' => $setor]);
    }

    public function delete($id)
    {
        $data       = DB::table('setor_tarik')->where('id', $id)->first();
        $keterangan = '[' . strtoupper($data->jenis) . '] ' . $data->nama_pelanggan;

        if ($data && $data->bukti && file_exists(public_path($data->bukti))) {
            unlink(public_path($data->bukti));
        }

        DB::beginTransaction();
        try {
            DB::table('setor_tarik')->where('id', $id)->delete();

            if (Str::contains(strtolower($data->jenis), 'setor')) {
                DB::table('pemasukan')
                    ->where('tanggal', $data->tanggal)
                    ->where('total', $data->total)
                    ->where('id_metode', $data->id_metode)
                    ->where('keterangan', 'LIKE', $keterangan . '%')
                    ->where('id_user', $data->id_user)
                    ->limit(1)->delete();
            } else {
                DB::table('pengeluaran')
                    ->where('tanggal', $data->tanggal)
                    ->where('total', $data->total)
                    ->where('id_metode', $data->id_metode)
                    ->where('keterangan', 'LIKE', $keterangan . '%')
                    ->where('id_user', $data->id_user)
                    ->limit(1)->delete();
            }

            if ($data->biaya_admin > 0) {
                $keteranganAdmin = '[BIAYA ADMIN] ' . strtoupper($data->jenis) . ' ' . $data->nama_pelanggan
                    . ($data->keterangan ? ' — ' . $data->keterangan : '');
                DB::table('pemasukan')
                    ->where('tanggal', $data->tanggal)
                    ->where('total', $data->biaya_admin)
                    ->where('id_metode', $data->id_metode)
                    ->where('keterangan', 'LIKE', $keteranganAdmin . '%')
                    ->where('id_user', $data->id_user)
                    ->limit(1)->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }

        return redirect('/admin/setor_tarik')->with('success', 'Data Berhasil Dihapus !');
    }
}