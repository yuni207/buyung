<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon;
use PDF;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function read()
    {
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d');
        $tanggal_prev = date('Y-m-d', strtotime($tanggal . "-1 day"));

        $pemasukan_cash = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->sum('total');
        $pemasukan_transfer = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->sum('total');
        $pengeluaran_cash = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->sum('total');
        $pengeluaran_transfer = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->sum('total');

        // Hitung pemasukan dan pengeluaran bulan ini
        $pemasukan = DB::table('pemasukan')->where('tanggal', 'LIKE', $tanggal . '%')->sum('total');
        $pengeluaran = DB::table('pengeluaran')->where('tanggal', 'LIKE', $tanggal . '%')->sum('total');

        // Hitung pemasukan dan pengeluaran bulan sebelumnya
        $pemasukan_prev = DB::table('pemasukan')->where('tanggal', 'LIKE', $tanggal_prev . '%')->sum('total');
        $pengeluaran_prev = DB::table('pengeluaran')->where('tanggal', 'LIKE', $tanggal_prev . '%')->sum('total');

        // Hitung income bulan ini
        $income = $pemasukan - $pengeluaran;

        // Hitung perubahan pemasukan
        if ($pemasukan > $pemasukan_prev) {
            $keterangan_pemasukan = "Naik";
        } elseif ($pemasukan < $pemasukan_prev) {
            $keterangan_pemasukan = "Turun";
        } else {
            $keterangan_pemasukan = "Tetap";
        }

        // Hitung perubahan pengeluaran
        if ($pengeluaran > $pengeluaran_prev) {
            $keterangan_pengeluaran = "Naik";
        } elseif ($pengeluaran < $pengeluaran_prev) {
            $keterangan_pengeluaran = "Turun";
        } else {
            $keterangan_pengeluaran = "Tetap";
        }

        // Hitung persentase pemasukan (hindari pembagian nol)
        if ($pemasukan_prev > 0) {
            $persentase_pemasukan = abs((($pemasukan - $pemasukan_prev) / $pemasukan_prev) * 100);
        } else {
            $persentase_pemasukan = $pemasukan > 0 ? 100 : 0;
        }

        // Hitung persentase pengeluaran (hindari pembagian nol)
        if ($pengeluaran_prev > 0) {
            $persentase_pengeluaran = abs((($pengeluaran - $pengeluaran_prev) / $pengeluaran_prev) * 100);
        } else {
            $persentase_pengeluaran = $pengeluaran > 0 ? 100 : 0;
        }

        // Get transactions for the current month
        $transaksi = DB::table('transaksi')->where('tanggal', 'LIKE', $tanggal . '%')->get();

        // Get all detail transactions related to the transactions for the current month
        $detail_transaksi = DB::table('detail_transaksi')
            ->whereIn('id_transaksi', $transaksi->pluck('id')) // Get transactions for the current month
            ->get();

        // Get top 10 barangs based on total 'jumlah'
        $top_barangs = DB::table('detail_transaksi')
            ->select('id_barang', DB::raw('SUM(jumlah) as total_jumlah'))
            ->whereIn('id_transaksi', $transaksi->pluck('id'))
            ->groupBy('id_barang')
            ->orderByDesc('total_jumlah')
            ->limit(10)
            ->get();

        // Get the name from the barang table based on id_barang
        $topMenu = $top_barangs->map(function ($item) {
            $barang = DB::table('barang')->find($item->id_barang);
            return [
                'id_barang' => $item->id_barang,
                'nama' => trim(($barang->nama ?? '')),
                'total_jumlah' => $item->total_jumlah
            ];
        });

        // Start and end of the month
        $startDate = Carbon::createFromFormat('Y-m-d', $tanggal)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m-d', $tanggal)->endOfMonth();

        // Initialize the date range with 0 for each category
        $topIncome = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Store the day of the month without leading zero (e.g., 1, 2, 3)
            $topIncome[$date->format('j')] = [
                'Pemasukan Cash' => 0,
                'Pemasukan Transfer BSI' => 0,
                'Pengeluaran Cash' => 0,
                'Pengeluaran Transfer BSI' => 0,
            ];
        }

        // Loop over each day and fetch the sum for each category
        foreach ($topIncome as $day => &$progress) {
            // Concatenate the month and day to form the full date (e.g., '2025-02-01')
            $fullDate = $tanggal . '-' . str_pad($day, 2, '0', STR_PAD_LEFT); // Ensure the day is two digits

            // Pemasukan Cash (ID Metode 1)
            $progress['Pemasukan Cash'] = DB::table('pemasukan')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pemasukan Transfer BSI (ID Metode 5)
            $progress['Pemasukan Transfer BSI'] = DB::table('pemasukan')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');

            // Pengeluaran Cash (ID Metode 1)
            $progress['Pengeluaran Cash'] = DB::table('pengeluaran')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pengeluaran Transfer BSI (ID Metode 5)
            $progress['Pengeluaran Transfer BSI'] = DB::table('pengeluaran')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');
        }

        // Start and end of the month
        $startDate = Carbon::createFromFormat('Y-m-d', $tanggal)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m-d', $tanggal)->endOfMonth();

        // Initialize the week range with 0 for each category
        $topWeek = [];
        $weekNumber = 1;

        // Loop through weeks of the month and initialize empty categories
        while ($startDate->lte($endDate)) {
            // Get the start and end date of the current week
            $startOfWeek = $startDate->copy()->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            // Ensure the week is within the month range
            if ($endOfWeek->gt($endDate)) {
                $endOfWeek = $endDate;
            }

            // Store each week key as "Minggu Ke-1", "Minggu Ke-2", etc.
            $topWeek["Minggu Ke-{$weekNumber}"] = [
                'range' => $startOfWeek->format('d M Y') . ' - ' . $endOfWeek->format('d M Y'),  // Date range
                'Pemasukan Cash' => 0,
                'Pemasukan Transfer BSI' => 0,
                'Pengeluaran Cash' => 0,
                'Pengeluaran Transfer BSI' => 0,
            ];

            // Pemasukan Cash (ID Metode 1)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pemasukan Cash'] = DB::table('pemasukan')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pemasukan Transfer BSI (ID Metode 5)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pemasukan Transfer BSI'] = DB::table('pemasukan')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');

            // Pengeluaran Cash (ID Metode 1)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pengeluaran Cash'] = DB::table('pengeluaran')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pengeluaran Transfer BSI (ID Metode 5)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pengeluaran Transfer BSI'] = DB::table('pengeluaran')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');

            // Move to the next week
            $startDate = $endOfWeek->copy()->addDay();
            $weekNumber++;
        }

        $income = $pemasukan - $pengeluaran;

        $list_pemasukan_cash = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->get();
        $list_pemasukan_transfer = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->get();
        $list_pengeluaran_cash = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->get();
        $list_pengeluaran_transfer = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->get();

        $list_pemasukan = DB::table('pemasukan')->where('tanggal', 'LIKE', $tanggal . '%')->get();
        $list_pengeluaran = DB::table('pengeluaran')->where('tanggal', 'LIKE', $tanggal . '%')->get();

        $list_income = DB::table('pemasukan')
            ->select('id', 'tanggal', 'keterangan', 'total', 'id_user', 'id_transaksi', DB::raw('"pemasukan" as type'))
            ->where('tanggal', 'LIKE', $tanggal . '%')
            ->union(
                DB::table('pengeluaran')
                    ->select('id', 'tanggal', 'keterangan', 'total', 'id_user', DB::raw('NULL as id_transaksi'), DB::raw('"pengeluaran" as type'))
                    ->where('tanggal', 'LIKE', $tanggal . '%')
            )
            ->orderBy('tanggal')
            ->get();
        
        $total_barang = DB::table('detail_barang')->sum('stock');
        
        $laba_transaksi = DB::table('transaksi')
            ->where('tanggal', 'LIKE', $tanggal . '%')
            ->selectRaw('SUM(total - modal) as laba')
            ->first()
            ->laba ?? 0;
        
        $laba_admin = DB::table('setor_tarik')
            ->where('tanggal', 'LIKE', $tanggal . '%')
            ->sum('biaya_admin');
        
        $laba = $laba_transaksi + $laba_admin;
        
        // Data untuk detail laba bersih
        $detail_laba_transaksi = DB::table('transaksi as t')
            ->leftJoin('metode as m', 't.id_metode', '=', 'm.id')
            ->select('t.id', 't.tanggal', 't.nama', 't.total as harga_jual', 't.modal as harga_modal', 
                     DB::raw('(t.total - t.modal) as laba'), 'm.nama as nama_metode', 't.id_metode', 
                     DB::raw('"transaksi" as tipe'))
            ->where('t.tanggal', 'LIKE', $tanggal . '%')
            ->get();
        
        $detail_laba_admin = DB::table('setor_tarik as st')
            ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
            ->select(
                'st.id', 
                'st.tanggal',
                DB::raw('CONCAT("[BIAYA ADMIN] ", UPPER(st.jenis), " - ", st.nama_pelanggan) as nama'),
                DB::raw('0 as harga_jual'),
                DB::raw('0 as harga_modal'),
                DB::raw('st.biaya_admin as laba'),
                'm.nama as nama_metode',
                'st.id_metode',
                DB::raw('"setor_tarik" as tipe')
            )
            ->where('st.tanggal', 'LIKE', $tanggal . '%')
            ->where('st.biaya_admin', '>', 0)
            ->get();
        
        $detail_transaksi_laba = $detail_laba_transaksi->concat($detail_laba_admin)
            ->sortByDesc('tanggal')
            ->values();

        return view('admin.income.index',['tanggal'=>$tanggal,'pemasukan_cash'=>$pemasukan_cash,'pemasukan_transfer'=>$pemasukan_transfer,'pengeluaran_cash'=>$pengeluaran_cash,'pengeluaran_transfer'=>$pengeluaran_transfer,'pemasukan'=>$pemasukan,'pengeluaran'=>$pengeluaran,'income'=>$income,'keterangan_pemasukan'=>$keterangan_pemasukan,'persentase_pemasukan'=>$persentase_pemasukan,'keterangan_pengeluaran'=>$keterangan_pengeluaran,'persentase_pengeluaran'=>$persentase_pengeluaran,'topMenu'=>$topMenu,'topIncome' => $topIncome,'topWeek' => $topWeek,'income' => $income,'list_pemasukan_cash' => $list_pemasukan_cash,'list_pemasukan_transfer' => $list_pemasukan_transfer,'list_pengeluaran_cash' => $list_pengeluaran_cash,'list_pengeluaran_transfer' => $list_pengeluaran_transfer,'list_pemasukan' => $list_pemasukan,'list_pengeluaran' => $list_pengeluaran,'list_income' => $list_income,'total_barang' => $total_barang,'laba' => $laba, 'detail_transaksi_laba' => $detail_transaksi_laba]);
    }

    public function read_filter($tanggal)
    {
        $tanggal_prev = date('Y-m-d', strtotime($tanggal . "-1 day"));

        $pemasukan_cash = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->sum('total');
        $pemasukan_transfer = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->sum('total');
        $pengeluaran_cash = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->sum('total');
        $pengeluaran_transfer = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->sum('total');

        // Hitung pemasukan dan pengeluaran bulan ini
        $pemasukan = DB::table('pemasukan')->where('tanggal', 'LIKE', $tanggal . '%')->sum('total');
        $pengeluaran = DB::table('pengeluaran')->where('tanggal', 'LIKE', $tanggal . '%')->sum('total');

        // Hitung pemasukan dan pengeluaran bulan sebelumnya
        $pemasukan_prev = DB::table('pemasukan')->where('tanggal', 'LIKE', $tanggal_prev . '%')->sum('total');
        $pengeluaran_prev = DB::table('pengeluaran')->where('tanggal', 'LIKE', $tanggal_prev . '%')->sum('total');

        // Hitung income bulan ini
        $income = $pemasukan - $pengeluaran;

        // Hitung perubahan pemasukan
        if ($pemasukan > $pemasukan_prev) {
            $keterangan_pemasukan = "Naik";
        } elseif ($pemasukan < $pemasukan_prev) {
            $keterangan_pemasukan = "Turun";
        } else {
            $keterangan_pemasukan = "Tetap";
        }

        // Hitung perubahan pengeluaran
        if ($pengeluaran > $pengeluaran_prev) {
            $keterangan_pengeluaran = "Naik";
        } elseif ($pengeluaran < $pengeluaran_prev) {
            $keterangan_pengeluaran = "Turun";
        } else {
            $keterangan_pengeluaran = "Tetap";
        }

        // Hitung persentase pemasukan (hindari pembagian nol)
        if ($pemasukan_prev > 0) {
            $persentase_pemasukan = abs((($pemasukan - $pemasukan_prev) / $pemasukan_prev) * 100);
        } else {
            $persentase_pemasukan = $pemasukan > 0 ? 100 : 0;
        }

        // Hitung persentase pengeluaran (hindari pembagian nol)
        if ($pengeluaran_prev > 0) {
            $persentase_pengeluaran = abs((($pengeluaran - $pengeluaran_prev) / $pengeluaran_prev) * 100);
        } else {
            $persentase_pengeluaran = $pengeluaran > 0 ? 100 : 0;
        }

        // Get transactions for the current month
        $transaksi = DB::table('transaksi')->where('tanggal', 'LIKE', $tanggal . '%')->get();

        // Get all detail transactions related to the transactions for the current month
        $detail_transaksi = DB::table('detail_transaksi')
            ->whereIn('id_transaksi', $transaksi->pluck('id')) // Get transactions for the current month
            ->get();

        // Get top 10 barangs based on total 'jumlah'
        $top_barangs = DB::table('detail_transaksi')
            ->select('id_barang', DB::raw('SUM(jumlah) as total_jumlah'))
            ->whereIn('id_transaksi', $transaksi->pluck('id'))
            ->groupBy('id_barang')
            ->orderByDesc('total_jumlah')
            ->limit(10)
            ->get();

        // Get the name from the barang table based on id_barang
        $topMenu = $top_barangs->map(function ($item) {
            $barang = DB::table('barang')->find($item->id_barang);
            return [
                'id_barang' => $item->id_barang,
                'nama' => trim(($barang->nama ?? '' )),
                'total_jumlah' => $item->total_jumlah
            ];
        });

        // Start and end of the month
        $startDate = Carbon::createFromFormat('Y-m-d', $tanggal)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m-d', $tanggal)->endOfMonth();

        // Initialize the date range with 0 for each category
        $topIncome = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Store the day of the month without leading zero (e.g., 1, 2, 3)
            $topIncome[$date->format('j')] = [
                'Pemasukan Cash' => 0,
                'Pemasukan Transfer BSI' => 0,
                'Pengeluaran Cash' => 0,
                'Pengeluaran Transfer BSI' => 0,
            ];
        }

        // Loop over each day and fetch the sum for each category
        foreach ($topIncome as $day => &$progress) {
            // Concatenate the month and day to form the full date (e.g., '2025-02-01')
            $fullDate = $tanggal . '-' . str_pad($day, 2, '0', STR_PAD_LEFT); // Ensure the day is two digits

            // Pemasukan Cash (ID Metode 1)
            $progress['Pemasukan Cash'] = DB::table('pemasukan')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pemasukan Transfer BSI (ID Metode 5)
            $progress['Pemasukan Transfer BSI'] = DB::table('pemasukan')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');

            // Pengeluaran Cash (ID Metode 1)
            $progress['Pengeluaran Cash'] = DB::table('pengeluaran')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pengeluaran Transfer BSI (ID Metode 5)
            $progress['Pengeluaran Transfer BSI'] = DB::table('pengeluaran')
                ->where('tanggal', 'LIKE', $fullDate . '%')  // Match only this specific date
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');
        }

        // Start and end of the month
        $startDate = Carbon::createFromFormat('Y-m-d', $tanggal)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m-d', $tanggal)->endOfMonth();

        // Initialize the week range with 0 for each category
        $topWeek = [];
        $weekNumber = 1;

        // Loop through weeks of the month and initialize empty categories
        while ($startDate->lte($endDate)) {
            // Get the start and end date of the current week
            $startOfWeek = $startDate->copy()->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            // Ensure the week is within the month range
            if ($endOfWeek->gt($endDate)) {
                $endOfWeek = $endDate;
            }

            // Store each week key as "Minggu Ke-1", "Minggu Ke-2", etc.
            $topWeek["Minggu Ke-{$weekNumber}"] = [
                'range' => $startOfWeek->format('d M Y') . ' - ' . $endOfWeek->format('d M Y'),  // Date range
                'Pemasukan Cash' => 0,
                'Pemasukan Transfer BSI' => 0,
                'Pengeluaran Cash' => 0,
                'Pengeluaran Transfer BSI' => 0,
            ];

            // Pemasukan Cash (ID Metode 1)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pemasukan Cash'] = DB::table('pemasukan')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pemasukan Transfer BSI (ID Metode 5)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pemasukan Transfer BSI'] = DB::table('pemasukan')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');

            // Pengeluaran Cash (ID Metode 1)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pengeluaran Cash'] = DB::table('pengeluaran')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '1')  // ID Metode for Cash
                ->sum('total');

            // Pengeluaran Transfer BSI (ID Metode 5)
            $topWeek["Minggu Ke-{$weekNumber}"]['Pengeluaran Transfer BSI'] = DB::table('pengeluaran')
                ->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->where('id_metode', '5')  // ID Metode for Transfer BSI
                ->sum('total');

            // Move to the next week
            $startDate = $endOfWeek->copy()->addDay();
            $weekNumber++;
        }

        $income = $pemasukan - $pengeluaran;

        $list_pemasukan_cash = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->get();
        $list_pemasukan_transfer = DB::table('pemasukan')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->get();
        $list_pengeluaran_cash = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','1')->get();
        $list_pengeluaran_transfer = DB::table('pengeluaran')->where('tanggal','LIKE',$tanggal.'%')->where('id_metode','!=','1')->get();

        $list_pemasukan = DB::table('pemasukan')->where('tanggal', 'LIKE', $tanggal . '%')->get();
        $list_pengeluaran = DB::table('pengeluaran')->where('tanggal', 'LIKE', $tanggal . '%')->get();

        $list_income = DB::table('pemasukan')
            ->select('id', 'tanggal', 'keterangan', 'total', 'id_user', 'id_transaksi', DB::raw('"pemasukan" as type'))
            ->where('tanggal', 'LIKE', $tanggal . '%')
            ->union(
                DB::table('pengeluaran')
                    ->select('id', 'tanggal', 'keterangan', 'total', 'id_user', DB::raw('NULL as id_transaksi'), DB::raw('"pengeluaran" as type'))
                    ->where('tanggal', 'LIKE', $tanggal . '%')
            )
            ->orderBy('tanggal')
            ->get();

        $total_barang = DB::table('detail_barang')->sum('stock');
        
        $laba_transaksi = DB::table('transaksi')
            ->where('tanggal', 'LIKE', $tanggal . '%')
            ->selectRaw('SUM(total - modal) as laba')
            ->first()
            ->laba ?? 0;
        
        $laba_admin = DB::table('setor_tarik')
            ->where('tanggal', 'LIKE', $tanggal . '%')
            ->sum('biaya_admin');
        
        $laba = $laba_transaksi + $laba_admin;
        
        // Data untuk detail laba bersih
        $detail_laba_transaksi = DB::table('transaksi as t')
            ->leftJoin('metode as m', 't.id_metode', '=', 'm.id')
            ->select('t.id', 't.tanggal', 't.nama', 't.total as harga_jual', 't.modal as harga_modal', 
                     DB::raw('(t.total - t.modal) as laba'), 'm.nama as nama_metode', 't.id_metode', 
                     DB::raw('"transaksi" as tipe'))
            ->where('t.tanggal', 'LIKE', $tanggal . '%')
            ->get();
        
        $detail_laba_admin = DB::table('setor_tarik as st')
            ->leftJoin('metode as m', 'st.id_metode', '=', 'm.id')
            ->select(
                'st.id', 
                'st.tanggal',
                DB::raw('CONCAT("[BIAYA ADMIN] ", UPPER(st.jenis), " - ", st.nama_pelanggan) as nama'),
                DB::raw('0 as harga_jual'),
                DB::raw('0 as harga_modal'),
                DB::raw('st.biaya_admin as laba'),
                'm.nama as nama_metode',
                'st.id_metode',
                DB::raw('"setor_tarik" as tipe')
            )
            ->where('st.tanggal', 'LIKE', $tanggal . '%')
            ->where('st.biaya_admin', '>', 0)
            ->get();
        
        $detail_transaksi_laba = $detail_laba_transaksi->concat($detail_laba_admin)
            ->sortByDesc('tanggal')
            ->values();

        return view('admin.income.index',['tanggal'=>$tanggal,'pemasukan_cash'=>$pemasukan_cash,'pemasukan_transfer'=>$pemasukan_transfer,'pengeluaran_cash'=>$pengeluaran_cash,'pengeluaran_transfer'=>$pengeluaran_transfer,'pemasukan'=>$pemasukan,'pengeluaran'=>$pengeluaran,'income'=>$income,'keterangan_pemasukan'=>$keterangan_pemasukan,'persentase_pemasukan'=>$persentase_pemasukan,'keterangan_pengeluaran'=>$keterangan_pengeluaran,'persentase_pengeluaran'=>$persentase_pengeluaran,'topMenu'=>$topMenu,'topIncome' => $topIncome,'topWeek' => $topWeek,'income' => $income,'list_pemasukan_cash' => $list_pemasukan_cash,'list_pemasukan_transfer' => $list_pemasukan_transfer,'list_pengeluaran_cash' => $list_pengeluaran_cash,'list_pengeluaran_transfer' => $list_pengeluaran_transfer,'list_pemasukan' => $list_pemasukan,'list_pengeluaran' => $list_pengeluaran,'list_income' => $list_income,'total_barang' => $total_barang,'laba' => $laba, 'detail_transaksi_laba' => $detail_transaksi_laba]);
    }
}