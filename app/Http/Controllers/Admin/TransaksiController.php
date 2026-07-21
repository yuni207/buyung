<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    private function generateKode(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $last = DB::table('transaksi')
            ->where('nama', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'DESC')
            ->value('nama');

        $next = $last ? (intval(substr($last, -4)) + 1) : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function read(Request $request)
    {
        $query = DB::table('transaksi as t')
            ->leftJoin('metode as mp', 't.id_metode', '=', 'mp.id')
            ->leftJoin('users as u', 't.id_user', '=', 'u.id')
            ->select('t.id', 't.nama as kode_transaksi', 't.created_at', 't.total', 't.potongan',
                     't.is_hutang', 'mp.nama as nama_metode', 'u.name as kasir');

        if ($request->filled('metode_id')) $query->where('t.id_metode', $request->metode_id);
        if ($request->filled('dari'))      $query->whereDate('t.created_at', '>=', $request->dari);
        if ($request->filled('sampai'))    $query->whereDate('t.created_at', '<=', $request->sampai);

        $transaksi   = $query->orderBy('t.id', 'DESC')->get();
        $rekapMetode = DB::table('transaksi as t')
            ->leftJoin('metode as mp', 't.id_metode', '=', 'mp.id')
            ->select('mp.nama as nama_metode',
                     DB::raw('SUM(t.total) as total_pendapatan'),
                     DB::raw('COUNT(t.id) as jumlah_transaksi'))
            ->where('t.is_hutang', 0)
            ->groupBy('t.id_metode', 'mp.nama')
            ->orderBy('total_pendapatan', 'DESC')
            ->get();

        $metodes = DB::table('metode')->orderBy('nama')->get();

        return view('admin.transaksi.index', compact('transaksi', 'rekapMetode', 'metodes'));
    }

    public function add()
    {
        $barangs = DB::table('barang as b')
            ->leftJoin('detail_barang as db', 'b.id', '=', 'db.id_barang')
            ->leftJoin('satuan as s', 'db.id_satuan', '=', 's.id')
            ->select('b.*', 'db.stock', 'db.harga_jual as harga', 'db.harga_khusus', 'db.id as detail_id',
                     's.nama as nama_satuan')
            ->where('db.stock', '>', 0)
            ->orderBy('b.nama')
            ->get();

        $metodes = DB::table('metode')->orderBy('nama')->get();

        return view('admin.transaksi.tambah', compact('barangs', 'metodes'));
    }

    public function create(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');

        $isHutang = $request->boolean('is_hutang');
        $isPrint  = $request->boolean('is_print');

        $rules = [
            'items'                    => 'required|array|min:1',
            'items.*.barang_id'        => 'required|integer',
            'items.*.qty'              => 'required|integer|min:1',
            // pakai_harga_khusus: '1' = pakai harga khusus, '0' = harga normal
            'items.*.pakai_harga_khusus' => 'nullable|in:0,1',
            'id_metode'                => $isHutang ? 'nullable' : 'required|integer|exists:metode,id',
            'bayar'                    => $isHutang ? 'nullable|integer|min:0' : 'required|integer|min:0',
        ];

        if ($isHutang) {
            $rules['nama_pelanggan'] = 'required|string|max:100';
            $rules['no_hp']          = 'nullable|digits_between:1,12';
            $rules['id_metode_dp']   = 'nullable|integer|exists:metode,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse($request, implode(' ', $validator->errors()->all()), 422);
        }

        $subtotal      = 0;
        $totalModal    = 0;
        $itemsData     = [];
        $kodeTransaksi = '';
        $transaksiId   = null;
        $tanggalHari   = date('Y-m-d');
        $total         = 0;

        DB::beginTransaction();
        try {
            $kodeTransaksi = $this->generateKode();

            foreach ($request->items as $item) {
                $detail = DB::table('detail_barang')
                    ->where('id', $item['barang_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$detail) {
                    DB::rollBack();
                    return $this->errorResponse($request, 'Detail barang dengan ID ' . $item['barang_id'] . ' tidak ditemukan!');
                }

                $barang = DB::table('barang')->where('id', $detail->id_barang)->first();
                if (!$barang) {
                    DB::rollBack();
                    return $this->errorResponse($request, 'Barang tidak ditemukan!');
                }

                if ($item['qty'] > $detail->stock) {
                    DB::rollBack();
                    $sisa = $detail->stock;
                    return $this->errorResponse($request, "Stock barang \"{$barang->nama}\" tidak mencukupi! (Tersisa: {$sisa})");
                }

                // ── Tentukan harga yang dipakai ─────────────────────────
                $pakaiHargaKhusus = isset($item['pakai_harga_khusus']) && $item['pakai_harga_khusus'] == '1';
                $hargaKhususAda   = !empty($detail->harga_khusus) && intval($detail->harga_khusus) > 0;

                if ($pakaiHargaKhusus && $hargaKhususAda) {
                    $harga = intval($detail->harga_khusus);
                } else {
                    $harga = intval($detail->harga_jual);
                }

                $itemSubtotal = $harga * $item['qty'];
                $subtotal    += $itemSubtotal;
                $totalModal  += $detail->harga_modal * $item['qty'];

                // ── Ambil satuan dari detail_barang → tabel satuan ─────
                $satuan = '';
                if (!empty($detail->id_satuan)) {
                    $satuanRow = DB::table('satuan')->where('id', $detail->id_satuan)->value('nama');
                    $satuan    = $satuanRow ?? '';
                }
                $namaItem = $barang->nama . ($satuan ? ' (' . $satuan . ')' : '');

                $itemsData[] = [
                    'id_barang'           => $barang->id,
                    'id_detail_barang'    => $detail->id,
                    'nama'                => $namaItem,
                    'jumlah'              => $item['qty'],
                    'harga'               => $harga,
                    'harga_modal'         => $detail->harga_modal,
                    'total'               => $itemSubtotal,
                    'pakai_harga_khusus'  => ($pakaiHargaKhusus && $hargaKhususAda) ? 1 : 0,
                ];
            }

            $total = $subtotal;

            if ($isHutang) {
                $bayar     = intval($request->bayar ?? 0);
                $kembalian = 0;
                $bayar     = min($bayar, $total);
            } else {
                $bayar     = intval($request->bayar);
                $kembalian = $bayar - $total;

                if ($kembalian < 0) {
                    DB::rollBack();
                    return $this->errorResponse($request, 'Jumlah bayar kurang dari total transaksi!', 422);
                }
            }

            // is_hutang: 0 = Lunas, 1 = Belum Bayar, 2 = Bayar DP
            $statusHutangVal = 0;
            if ($isHutang) {
                $sisaHutang      = $total - $bayar;
                $statusHutangVal = ($sisaHutang <= 0) ? 0 : ($bayar > 0 ? 2 : 1);
            }

            $transaksiId = DB::table('transaksi')->insertGetId([
                'tanggal'    => $tanggalHari,
                'pukul'      => date('H:i:s'),
                'nama'       => $kodeTransaksi,
                'total'      => $total,
                'modal'      => $totalModal,
                'potongan'   => 0,
                'bayar'      => $bayar,
                'kembali'    => $isHutang ? 0 : $kembalian,
                'status'     => 'selesai',
                'is_hutang'  => $statusHutangVal,
                'id_metode'  => $isHutang ? null : $request->id_metode,
                'id_user'    => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($itemsData as $item) {
                DB::table('detail_transaksi')->insert([
                    'id_transaksi'        => $transaksiId,
                    'id_barang'           => $item['id_barang'],
                    'id_detail_barang'    => $item['id_detail_barang'],
                    'nama'                => $item['nama'],
                    'jumlah'              => $item['jumlah'],
                    'harga'               => $item['harga'],
                    'harga_modal'         => $item['harga_modal'],
                    'total'               => $item['total'],
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                DB::table('detail_barang')
                    ->where('id', $item['id_detail_barang'])
                    ->decrement('stock', $item['jumlah']);
            }

            // ── HUTANG ──────────────────────────────────────────────────
            if ($isHutang) {
                $statusHutang = ($bayar >= $total) ? 'lunas' : 'belum';

                $hutangId = DB::table('hutang')->insertGetId([
                    'nama_pelanggan' => $request->nama_pelanggan,
                    'no_hp'          => $request->no_hp,
                    'keterangan'     => 'Dari transaksi ' . $kodeTransaksi . ($request->keterangan ? ' - ' . $request->keterangan : ''),
                    'total'          => $total,
                    'terbayar'       => $bayar,
                    'status'         => $statusHutang,
                    'id_transaksi'   => $transaksiId,
                    'id_user'        => Auth::id(),
                    'tanggal'        => $tanggalHari,
                    'jatuh_tempo'    => $request->jatuh_tempo ?: null,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                if ($bayar > 0) {
                    DB::table('hutang_bayar')->insert([
                        'id_hutang'  => $hutangId,
                        'jumlah'     => $bayar,
                        'keterangan' => 'DP awal saat transaksi',
                        'id_user'    => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $idMetodeDp = intval($request->id_metode_dp ?? 0);
                    if (!$idMetodeDp) {
                        $idMetodeDp = DB::table('metode')->value('id') ?? 0;
                    }

                    $labelDp = ($statusHutang === 'lunas') ? 'Pelunasan' : 'DP';
                    DB::table('pemasukan')->insert([
                        'tanggal'      => $tanggalHari,
                        'keterangan'   => $labelDp . ' Hutang ' . $request->nama_pelanggan . ' (' . $kodeTransaksi . ')',
                        'total'        => $bayar,
                        'id_metode'    => $idMetodeDp,
                        'id_transaksi' => $transaksiId,
                        'id_user'      => Auth::id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

            } else {
                // ── TUNAI ────────────────────────────────────────────────
                DB::table('pemasukan')->insert([
                    'tanggal'      => $tanggalHari,
                    'keterangan'   => 'Penjualan - ' . $kodeTransaksi,
                    'total'        => $total,
                    'id_metode'    => $request->id_metode,
                    'id_transaksi' => $transaksiId,
                    'id_user'      => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($request, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }

        $redirectPrint   = 'http://buyung.com/print-bill/' . $transaksiId;
        $redirectNoPrint = '/admin/transaksi/add';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'kode_transaksi' => $kodeTransaksi,
                'transaksi_id'   => $transaksiId,
                'total'          => $total,
                'is_hutang'      => $isHutang,
                'redirect'       => $isPrint ? $redirectPrint : $redirectNoPrint,
                'message'        => $isHutang
                    ? "Transaksi {$kodeTransaksi} dicatat sebagai hutang!"
                    : 'Transaksi berhasil disimpan!',
            ]);
        }

        return $isPrint
            ? redirect($redirectPrint)
            : redirect($redirectNoPrint);
    }

    public function detail($id)
    {
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode as mp', 't.id_metode', '=', 'mp.id')
            ->leftJoin('users as u', 't.id_user', '=', 'u.id')
            ->select('t.*', 'mp.nama as nama_metode', 'u.name as kasir')
            ->where('t.id', $id)
            ->first();

        if (!$transaksi) {
            return redirect('/admin/transaksi')->with('error', 'Transaksi tidak ditemukan!');
        }

        $details = DB::table('detail_transaksi as dt')
            ->leftJoin('barang as b', 'dt.id_barang', '=', 'b.id')
            ->select('dt.*', 'b.nama as nama_barang')
            ->where('dt.id_transaksi', $id)
            ->get();

        $hutang = null;
        if ($transaksi->is_hutang ?? false) {
            $hutang = DB::table('hutang')->where('id_transaksi', $id)->first();
        }

        return view('admin.transaksi.detail', compact('transaksi', 'details', 'hutang'));
    }

    public function delete($id)
    {
        $transaksi = DB::table('transaksi')->where('id', $id)->first();

        if (!$transaksi) {
            return redirect('/admin/transaksi')->with('error', 'Transaksi tidak ditemukan!');
        }

        DB::beginTransaction();
        try {
            $details = DB::table('detail_transaksi')->where('id_transaksi', $id)->get();

            foreach ($details as $d) {
                DB::table('detail_barang')
                    ->where('id', $d->id_detail_barang ?: null)
                    ->orWhere(function ($q) use ($d) {
                        if (!$d->id_detail_barang) $q->where('id_barang', $d->id_barang);
                    })
                    ->increment('stock', $d->jumlah);
            }

            DB::table('detail_transaksi')->where('id_transaksi', $id)->delete();
            DB::table('pemasukan')->where('id_transaksi', $id)->delete();

            $hutang = DB::table('hutang')->where('id_transaksi', $id)->first();
            if ($hutang) {
                DB::table('hutang_bayar')->where('id_hutang', $hutang->id)->delete();
                DB::table('hutang')->where('id', $hutang->id)->delete();
            }

            DB::table('transaksi')->where('id', $id)->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }

        return redirect('/admin/transaksi')
            ->with('success', 'Transaksi berhasil dihapus dan stock dikembalikan!');
    }

    public function searchBarang(Request $request)
    {
        $q = trim($request->get('q', ''));

        $barangs = DB::table('barang as b')
            ->leftJoin('detail_barang as db', 'b.id', '=', 'db.id_barang')
            ->leftJoin('satuan as s', 'db.id_satuan', '=', 's.id')
            ->select(
                'b.id',
                'b.nama as nama_barang',
                'b.barcode',
                's.nama as satuan',
                'db.harga_jual as harga',
                'db.harga_khusus',   // ← tambah harga_khusus
                'db.stock',
                'db.id as detail_id'
            )
            ->where('db.stock', '>', 0)
            ->where(function ($query) use ($q) {
                $query->where('b.nama', 'like', "%{$q}%")
                      ->orWhere('b.barcode', 'like', "%{$q}%");
            })
            ->orderBy('b.nama')
            ->limit(15)
            ->get();

        return response()->json($barangs);
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }
        return back()->with('error', $message);
    }
}