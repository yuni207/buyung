<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetorTarikController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/setor_tarik  -> route name: read (default bulan berjalan)
    // ─────────────────────────────────────────────────────────────────────────
    public function read()
    {
        return $this->renderIndex(date('Y-m'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/setor_tarik/filter/{bln} -> route name: read_filter
    // ─────────────────────────────────────────────────────────────────────────
    public function read_filter($bln)
    {
        return $this->renderIndex($bln);
    }

    private function renderIndex($bln)
    {
        $query = DB::table('setor_tarik as st')
            ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
            ->leftJoin('users as u', 'st.id_user', '=', 'u.id')
            ->select('st.*', 'm.nama as nama_metode', 'u.name as nama_kasir')
            ->where('st.tanggal', 'like', $bln . '%');

        if (Auth::user()->level != '1') {
            $query->where('st.id_user', Auth::id());
        }

        $data = $query->orderBy('st.id', 'DESC')->get();
        $metode = DB::table('metode')->get();

        return view('admin.setor_tarik.index', [
            'data'   => $data,
            'metode' => $metode,
            'bln'    => $bln,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM TAMBAH
    // ─────────────────────────────────────────────────────────────────────────
    public function add()
    {
        $metode = DB::table('metode')->orderBy('nama')->get();
        return view('admin.setor_tarik.tambah', compact('metode'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROSES TAMBAH
    // Setor -> otomatis dicatat ke pemasukan
    // Tarik -> otomatis dicatat ke pengeluaran
    // ─────────────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required',
            'jenis'          => 'required',
            'total'          => 'required',
            'id_metode'      => 'required|exists:metode,id',
            'tanggal'        => 'required|date',
            'bukti'          => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $total = intval(preg_replace('/\D/', '', $request->total));
        $biayaAdmin = intval(preg_replace('/\D/', '', $request->biaya_admin ?? '0'));

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_setor_tarik', 'public');
            $buktiPath = '/storage/' . $buktiPath;
        }

        DB::beginTransaction();
        try {
            $idPemasukan = null;
            $idPengeluaran = null;

            if (str_contains(strtolower($request->jenis), 'setor')) {
                $idPemasukan = DB::table('pemasukan')->insertGetId([
                    'tanggal'      => $request->tanggal,
                    'keterangan'   => 'Setor Tunai - ' . $request->nama_pelanggan
                                      . ($request->keterangan ? ' (' . $request->keterangan . ')' : ''),
                    'total'        => $total,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => 0,
                    'id_user'      => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            } else {
                $idPengeluaran = DB::table('pengeluaran')->insertGetId([
                    'tanggal'    => $request->tanggal,
                    'keterangan' => 'Tarik Tunai - ' . $request->nama_pelanggan
                                     . ($request->keterangan ? ' (' . $request->keterangan . ')' : ''),
                    'total'      => $total,
                    'id_metode'  => $request->id_metode,
                    'id_user'    => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('setor_tarik')->insert([
                'nama_pelanggan'  => $request->nama_pelanggan,
                'jenis'           => $request->jenis,
                'total'           => $total,
                'biaya_admin'     => $biayaAdmin,
                'id_metode'       => $request->id_metode,
                'bukti'           => $buktiPath,
                'tanggal'         => $request->tanggal,
                'keterangan'      => $request->keterangan,
                'id_pemasukan'    => $idPemasukan,
                'id_pengeluaran'  => $idPengeluaran,
                'id_user'         => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }

        return redirect('/admin/setor_tarik')->with('success', 'Data Berhasil Ditambah !');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM EDIT
    // ─────────────────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $setor = DB::table('setor_tarik')->where('id', $id)->first();
        $metode = DB::table('metode')->orderBy('nama')->get();

        if (!$setor) {
            return redirect('/admin/setor_tarik')->with('error', 'Data tidak ditemukan!');
        }

        return view('admin.setor_tarik.edit', compact('setor', 'metode'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROSES UPDATE
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $setor = DB::table('setor_tarik')->where('id', $id)->first();

        if (!$setor) {
            return redirect('/admin/setor_tarik')->with('error', 'Data tidak ditemukan!');
        }

        $total = intval(preg_replace('/\D/', '', $request->total));
        $biayaAdmin = intval(preg_replace('/\D/', '', $request->biaya_admin ?? '0'));

        $buktiPath = $setor->bukti;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_setor_tarik', 'public');
            $buktiPath = '/storage/' . $buktiPath;
        }

        DB::table('setor_tarik')->where('id', $id)->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'jenis'          => $request->jenis,
            'total'          => $total,
            'biaya_admin'    => $biayaAdmin,
            'id_metode'      => $request->id_metode,
            'bukti'          => $buktiPath,
            'tanggal'        => $request->tanggal,
            'keterangan'     => $request->keterangan,
            'updated_at'     => now(),
        ]);

        // Sinkronkan juga catatan pemasukan/pengeluaran terkait, kalau ada
        if ($setor->id_pemasukan) {
            DB::table('pemasukan')->where('id', $setor->id_pemasukan)->update([
                'tanggal'   => $request->tanggal,
                'total'     => $total,
                'id_metode' => $request->id_metode,
                'updated_at'=> now(),
            ]);
        }
        if ($setor->id_pengeluaran) {
            DB::table('pengeluaran')->where('id', $setor->id_pengeluaran)->update([
                'tanggal'   => $request->tanggal,
                'total'     => $total,
                'id_metode' => $request->id_metode,
                'updated_at'=> now(),
            ]);
        }

        return redirect('/admin/setor_tarik')->with('success', 'Data Berhasil Diupdate !');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE
    // Dua route mengarah ke sini: /delete/{id} (name: delete) dan
    // /hapus/{id} (name: hapus) — keduanya dipetakan ke logika yang sama.
    // ─────────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $setor = DB::table('setor_tarik')->where('id', $id)->first();

        if ($setor) {
            if ($setor->id_pemasukan) {
                DB::table('pemasukan')->where('id', $setor->id_pemasukan)->delete();
            }
            if ($setor->id_pengeluaran) {
                DB::table('pengeluaran')->where('id', $setor->id_pengeluaran)->delete();
            }
            DB::table('setor_tarik')->where('id', $id)->delete();
        }

        return redirect('/admin/setor_tarik')->with('success', 'Data Berhasil Dihapus !');
    }

    public function hapus($id)
    {
        return $this->delete($id);
    }
}