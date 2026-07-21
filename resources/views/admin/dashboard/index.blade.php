@extends('admin.layouts.app', [
'activePage' => 'dashboard',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Dashboard</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
               </ol>
            </nav>
         </div>
         <div class="col-md-6 col-sm-12 text-right">
            <div class="d-flex align-items-center justify-content-end gap-2">

               {{-- ══ TOMBOL BUKA / TUTUP KASIR (hanya owner & kasir, bukan operator) ══ --}}
               @if(in_array(Auth::User()->level, ['1', '2']))
               @php
                  $sesiAktif = \Illuminate\Support\Facades\DB::table('kasir_session')
                     ->where('id_user', Auth::id())
                     ->where('status', 'buka')
                     ->first();
               @endphp
               @if($sesiAktif)
                  <a href="/admin/kasir/tutup/{{ $sesiAktif->id }}"
                     class="btn btn-danger"
                     title="Kasir dibuka sejak {{ \Carbon\Carbon::parse($sesiAktif->waktu_buka)->format('H:i') }}">
                     <i class="fa fa-lock"></i> Tutup Kasir
                  </a>
               @else
                  <a href="/admin/kasir/buka" class="btn btn-success">
                     <i class="fa fa-unlock"></i> Buka Kasir
                  </a>
               @endif
               @endif
               {{-- ══ END TOMBOL KASIR ══ --}}

               @if(Auth::User()->level == '1')
               <div class="form-group mb-0 ml-2">
                  <input type="month" required class="form-control"
                     onchange="location = '/admin/home/filter/'+this.value;"
                     name="bln" value="{{$bln}}">
               </div>
               @endif

            </div>
         </div>
      </div>

      {{-- ══ BANNER INFO SESI AKTIF (hanya owner & kasir) ══ --}}
      @if(in_array(Auth::User()->level, ['1', '2']) && isset($sesiAktif) && $sesiAktif)
      <div class="row mt-2">
         <div class="col-12">
            <div class="alert alert-success d-flex align-items-center justify-content-between py-2 mb-0" style="border-radius:8px;">
               <div>
                  <i class="fa fa-check-circle mr-1"></i>
                  <strong>Kasir sedang terbuka</strong> —
                  Dibuka sejak <strong>{{ \Carbon\Carbon::parse($sesiAktif->waktu_buka)->format('d M Y, H:i') }}</strong> |
                  Modal Awal: <strong>Rp {{ number_format($sesiAktif->modal_awal, 0, ',', '.') }}</strong>
               </div>
               <a href="/admin/kasir/detail/{{ $sesiAktif->id }}" class="btn btn-sm btn-outline-success">
                  <i class="fa fa-eye"></i> Lihat Detail Sesi
               </a>
            </div>
         </div>
      </div>
      @endif

   @if(Auth::User()->level == '1')
   {{-- ══ DASHBOARD OWNER ══ --}}
   <div class="row">
      <div class="col-xl-3 col-lg-3 col-md-6 mb-10">
         <div class="card-box pd-20 mb-20" data-toggle="modal" data-target="#pemasukan_cash" data-bgcolor="#7978e9">
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Pemasukan Cash</div>
                  <div class="font-24 weight-500">Rp {{number_format($pemasukan_cash,0, ".", ".")}}</div>
               </div>
               <div class="max-width-150">
                  <div id="appointment-chart"></div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xl-3 col-lg-3 col-md-6 mb-10">
         <div class="card-box pd-20 mb-20" data-toggle="modal" data-target="#pemasukan_transfer" data-bgcolor="#00bcd4">
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Pemasukan QRIS</div>
                  <div class="font-24 weight-500">Rp {{number_format($pemasukan_transfer,0, ".", ".")}}</div>
               </div>
               <div class="max-width-150">
                  <div id="appointment-chart"></div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xl-3 col-lg-3 col-md-6 mb-10">
         <div class="card-box pd-20 mb-20" data-toggle="modal" data-target="#pengeluaran_cash" data-bgcolor="#FFB347">
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Pengeluaran Cash</div>
                  <div class="font-24 weight-500">Rp {{number_format($pengeluaran_cash,0, ".", ".")}}</div>
               </div>
               <div class="max-width-150">
                  <div id="appointment-chart"></div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xl-3 col-lg-3 col-md-6 mb-10">
         <div class="card-box pd-20 mb-20" data-toggle="modal" data-target="#pengeluaran_transfer" data-bgcolor="#f96332">
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Pengeluaran QRIS</div>
                  <div class="font-24 weight-500">Rp {{number_format($pengeluaran_transfer,0, ".", ".")}}</div>
               </div>
               <div class="max-width-150">
                  <div id="appointment-chart"></div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="row pb-10">
      <div class="col-md-8 mb-20">
         <div class="card-box height-100-p pd-20">
            <div class="d-flex flex-wrap justify-content-between align-items-center pb-0 pb-md-3">
               <div class="h5 mb-md-0">TOP 10 Pemesanan Terbanyak</div>
            </div>
            <hr style="margin-top: 0px;">
            <canvas id="topMenuChart"></canvas>
         </div>
      </div>
      <div class="col-md-4 mb-20">
         <div class="card-box min-height-200px pd-20 mb-20" data-toggle="modal" data-target="#pemasukan" data-bgcolor="#265ed7">
            <div class="d-flex justify-content-between pb-20 text-white">
               <div class="icon h1 text-white">
                  <i class="icon-copy dw dw-wallet-1"></i>
               </div>
               <div class="font-14 text-right">
                  <div>
                    @if($keterangan_pemasukan == "Naik")
                    <i class="icon-copy ion-arrow-up-c"></i> {{round($persentase_pemasukan,2)}}%
                    @elseif($keterangan_pemasukan == "Turun")
                    <i class="icon-copy ion-arrow-down-c"></i> {{round($persentase_pemasukan,2)}}%
                    @else
                    <i class="icon-copy ion-arrow-swap"></i> 100%
                    @endif
                  </div>
                  <div class="font-12">Dari Bulan Kemarin</div>
               </div>
            </div>
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Total Pemasukan Bulan Ini</div>
                  <div class="font-24 weight-500">Rp {{number_format($pemasukan,0, ".", ".")}}</div>
               </div>
               <div class="max-width-150">
                  <div id="appointment-chart"></div>
               </div>
            </div>
         </div>
         <div class="card-box min-height-200px pd-20" data-toggle="modal" data-target="#pengeluaran" data-bgcolor="#F3797E">
            <div class="d-flex justify-content-between pb-20 text-white">
               <div class="icon h1 text-white">
                  <i class="icon-copy dw dw-invoice-1"></i>
               </div>
               <div class="font-14 text-right">
                  <div>
                    @if($keterangan_pengeluaran == "Naik")
                    <i class="icon-copy ion-arrow-up-c"></i> {{round($persentase_pengeluaran,2)}}%
                    @elseif($keterangan_pengeluaran == "Turun")
                    <i class="icon-copy ion-arrow-down-c"></i> {{round($persentase_pengeluaran,2)}}%
                    @else
                    <i class="icon-copy ion-arrow-swap"></i> 100%
                    @endif
                  </div>
                  <div class="font-12">Dari Bulan Kemarin</div>
               </div>
            </div>
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Total Pengeluaran Bulan Ini</div>
                  <div class="font-24 weight-500">Rp {{number_format($pengeluaran,0, ".", ".")}}</div>
               </div>
               <div class="max-width-150">
                  <div id="surgery-chart"></div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-xl-6 col-lg-6 col-md-6 mb-10">
         <div class="card-box pd-20 mb-20" data-toggle="modal" data-target="#laba_bersih" data-bgcolor="#333333" style="cursor:pointer;">
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Total Laba Bersih (Harga Jual - Harga Modal) + Pemasukan Admin</div>
                  <div class="font-24 weight-500">Rp {{number_format($laba,0, ".", ".")}}</div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xl-6 col-lg-6 col-md-6 mb-10">
         <div class="card-box pd-20 mb-20 card-success" data-toggle="modal" data-target="#income" data-bgcolor="#4caf50">
            <div class="d-flex justify-content-between align-items-end">
               <div class="text-white">
                  <div class="font-14">Total Profit (Pemasukan - Pengeluaran)</div>
                  <div class="font-24 weight-500">Rp {{number_format($pemasukan - $pengeluaran,0, ".", ".")}}</div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="row pb-10">
      <div class="col-md-12 mb-20">
         <div class="card-box height-100-p pd-20">
            <div class="d-flex flex-wrap justify-content-between align-items-center pb-0 pb-md-3">
               <div class="h5 mb-md-0">Grafik Arus Kas Perhari</div>
            </div>
            <hr style="margin-top: 0px;">
            <canvas id="topIncomeChart"></canvas>
         </div>
      </div>
      <div class="col-md-12 mb-20">
         <div class="card-box height-100-p pd-20">
            <div class="d-flex flex-wrap justify-content-between align-items-center pb-0 pb-md-3">
               <div class="h5 mb-md-0">Grafik Arus Kas Perminggu</div>
            </div>
            <hr style="margin-top: 0px;">
            <canvas id="topWeekChart"></canvas>
         </div>
      </div>
   </div>
   @elseif(Auth::User()->level == '2')
   {{-- ══ DASHBOARD KASIR (level 2) ══ --}}
   <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-8 col-md-10 mb-30">
         <div class="card-box pd-20 height-100-p mb-30 text-center">
            <h4 class="font-20 weight-500 mb-10 text-capitalize">
               Selamat datang, <span class="weight-600 font-30 text-blue">{{Auth::User()->name}}</span>
            </h4>
            <p class="font-18 max-width-600 text-justify mx-auto">
               Toko Buyung Lubuk Basung adalah toko sembako yang berlokasi di Lubuk Basung. Toko ini menyediakan berbagai kebutuhan pokok dan perlengkapan harian untuk masyarakat sekitar dengan pelayanan yang ramah dan produk yang lengkap.
            </p>

         </div>
      </div>
   </div>
   @elseif(Auth::User()->level == '3')
   {{-- ══ DASHBOARD OPERATOR (level 3) — tanpa buka/tutup kasir ══ --}}
   <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-8 col-md-10 mb-30">
         <div class="card-box pd-20 height-100-p mb-30 text-center">
            <h4 class="font-20 weight-500 mb-10 text-capitalize">
               Selamat datang, <span class="weight-600 font-30 text-blue">{{Auth::User()->name}}</span>
            </h4>
            <p class="font-18 max-width-600 text-justify mx-auto">
               Toko Buyung Lubuk Basung adalah toko sembako yang berlokasi di Lubuk Basung. Toko ini menyediakan berbagai kebutuhan pokok dan perlengkapan harian untuk masyarakat sekitar dengan pelayanan yang ramah dan produk yang lengkap.
            </p>
            <div class="alert alert-warning mt-3" style="border-radius:8px;">
               <i class="fa fa-info-circle mr-1"></i>
               Silakan hubungi kasir atau owner untuk membuka sesi kasir sebelum mengakses fitur lain.
            </div>
         </div>
      </div>
   </div>
   @endif {{-- endif level 1 / 2 / 3 --}}
</div>

@if(Auth::User()->level == '1')

{{-- ══ MODAL PEMASUKAN CASH ══════════════════════════════════════════════════ --}}
<div class="modal fade" id="pemasukan_cash" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Pemasukan Cash</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center">Penginput</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total = 0; ?>
                        @foreach($list_pemasukan_cash as $data)
                        <?php
                           $metode = DB::table('metode')->find($data->id_metode);
                           $users = DB::table('users')->find($data->id_user);
                           $total += $data->total;
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">{{$users->name ?? '-'}}</td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              <a href="/admin/transaksi/detail/{{$data->id_transaksi}}" target="_blank"><button class="btn btn-info btn-xs"><i class="fa fa-address-card"></i></button></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pemasukan Cash</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL PEMASUKAN QRIS ══════════════════════════════════════════════════ --}}
<div class="modal fade" id="pemasukan_transfer" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Pemasukan QRIS</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center">Penginput</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total = 0; ?>
                        @foreach($list_pemasukan_transfer as $data)
                        <?php
                           $metode = DB::table('metode')->find($data->id_metode);
                           $users = DB::table('users')->find($data->id_user);
                           $total += $data->total;
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">{{$users->name ?? '-'}}</td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              <a href="/admin/transaksi/detail/{{$data->id_transaksi}}" target="_blank"><button class="btn btn-info btn-xs"><i class="fa fa-address-card"></i></button></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pemasukan QRIS</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL PENGELUARAN CASH ════════════════════════════════════════════════ --}}
<div class="modal fade" id="pengeluaran_cash" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Pengeluaran Cash</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center">Penginput</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total = 0; ?>
                        @foreach($list_pengeluaran_cash as $data)
                        <?php
                           $metode = DB::table('metode')->find($data->id_metode);
                           $users = DB::table('users')->find($data->id_user);
                           $total += $data->total;
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">{{$users->name ?? '-'}}</td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              <a href="/admin/pengeluaran/edit/{{$data->id}}" target="_blank"><button class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></button></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pengeluaran Cash</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL PENGELUARAN QRIS ════════════════════════════════════════════════ --}}
<div class="modal fade" id="pengeluaran_transfer" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Pengeluaran QRIS</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center">Penginput</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total = 0; ?>
                        @foreach($list_pengeluaran_transfer as $data)
                        <?php
                           $metode = DB::table('metode')->find($data->id_metode);
                           $users = DB::table('users')->find($data->id_user);
                           $total += $data->total;
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">{{$users->name ?? '-'}}</td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              <a href="/admin/pengeluaran/edit/{{$data->id}}" target="_blank"><button class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></button></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pengeluaran QRIS</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL TOTAL PEMASUKAN ═════════════════════════════════════════════════ --}}
<div class="modal fade" id="pemasukan" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Pemasukan</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center" width="20%">Metode Pemasukan</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total = 0; ?>
                        @foreach($list_pemasukan as $data)
                        <?php
                           $metode = DB::table('metode')->find($data->id_metode);
                           $users = DB::table('users')->find($data->id_user);
                           $total += $data->total;
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">
                              @if($metode->id == '1')
                                 <button class="btn btn-success btn-xs">{{$metode->nama ?? '-'}}</button>
                              @else
                                 <button class="btn btn-info btn-xs">{{$metode->nama ?? '-'}}</button>
                              @endif
                           </td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              <a href="/admin/transaksi/detail/{{$data->id_transaksi}}" target="_blank"><button class="btn btn-info btn-xs"><i class="fa fa-address-card"></i></button></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pemasukan Keseluruhan</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL TOTAL PENGELUARAN ══════════════════════════════════════════════ --}}
<div class="modal fade" id="pengeluaran" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Pengeluaran</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center" width="20%">Metode Pengeluaran</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total = 0; ?>
                        @foreach($list_pengeluaran as $data)
                        <?php
                           $metode = DB::table('metode')->find($data->id_metode);
                           $users = DB::table('users')->find($data->id_user);
                           $total += $data->total;
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">
                              @if($metode->id == '1')
                                 <button class="btn btn-success btn-xs">{{$metode->nama ?? '-'}}</button>
                              @else
                                 <button class="btn btn-info btn-xs">{{$metode->nama ?? '-'}}</button>
                              @endif
                           </td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              <a href="/admin/pengeluaran/edit/{{$data->id}}" target="_blank"><button class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></button></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pengeluaran Keseluruhan</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL INCOME / PROFIT ════════════════════════════════════════════════ --}}
<div class="modal fade" id="income" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">List Data Income</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center" width="20%">Type</th>
                           <th class="align-middle text-center" width="20%">Total</th>
                           <th class="table-plus datatable-nosort text-center align-middle"><i class="fa fa-cogs"></i></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $no = 1; $total_pemasukan = 0; $total_pengeluaran = 0; ?>
                        @foreach($list_income as $data)
                        <?php
                           $users = DB::table('users')->find($data->id_user);
                           if($data->type == 'pemasukan'){
                              $total_pemasukan += $data->total;
                           } else {
                              $total_pengeluaran += $data->total;
                           }
                        ?>
                        <tr>
                           <td class="text-center">{{$no++}}</td>
                           <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
                           <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
                           <td class="text-center">
                              @if($data->type == 'pemasukan')
                                 <button class="btn btn-success btn-xs">Pemasukan</button>
                              @else
                                 <button class="btn btn-danger btn-xs">Pengeluaran</button>
                              @endif
                           </td>
                           <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
                           <td class="text-center">
                              @if($data->type == 'pemasukan')
                                 <a href="/admin/transaksi/detail/{{$data->id_transaksi}}" target="_blank"><button class="btn btn-info btn-xs"><i class="fa fa-address-card"></i></button></a>
                              @else
                                 <a href="/admin/pengeluaran/edit/{{$data->id}}" target="_blank"><button class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></button></a>
                              @endif
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <th colspan="4">Total Pemasukan</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total_pemasukan, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                           <th colspan="4">Total Pengeluaran</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total_pengeluaran, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                           <th colspan="4">Total Income</th>
                           <th colspan="2">{{ 'Rp ' . number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') }}</th>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ MODAL LABA BERSIH ════════════════════════════════════════════════════ --}}
<div class="modal fade" id="laba_bersih" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">Detail Laba Bersih</h2>
            <hr>
            <div class="row">
               <div class="col-md-12">
                  <table class="table table-striped table-bordered data-table hover" id="tabel-laba-modal">
                     <thead class="bg-primary text-white">
                        <tr>
                           <th class="align-middle" width="5%">#</th>
                           <th class="align-middle">Tanggal</th>
                           <th class="align-middle">Keterangan</th>
                           <th class="align-middle text-center">Metode</th>
                           <th class="align-middle text-center">Harga Jual</th>
                           <th class="align-middle text-center">Harga Modal</th>
                           <th class="align-middle text-center">Laba</th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $no = 1; $total_laba = 0; @endphp
                        @forelse($detail_transaksi_laba as $row)
                        @php 
                           $total_laba += $row->laba;
                           $keterangan = $row->nama ?? 'Transaksi #' . $row->id;
                        @endphp
                        <tr>
                           <td class="text-center">{{ $no++ }}</td>
                           <td>{{ date('d M Y', strtotime($row->tanggal)) }}</td>
                           <td>{{ $keterangan }}</td>
                           <td class="text-center">
                              @if(($row->id_metode ?? '') == '1')
                                 <button class="btn btn-success btn-xs">{{ $row->nama_metode ?? '-' }}</button>
                              @else
                                 <button class="btn btn-info btn-xs">{{ $row->nama_metode ?? '-' }}</button>
                              @endif
                           </td>
                           <td class="text-center">
                              @if($row->tipe == 'setor_tarik')
                                 <small class="text-muted">-</small>
                              @else
                                 Rp {{ number_format($row->harga_jual ?? 0, 0, ',', '.') }}
                              @endif
                           </td>
                           <td class="text-center">
                              @if($row->tipe == 'setor_tarik')
                                 <small class="text-muted">-</small>
                              @else
                                 Rp {{ number_format($row->harga_modal ?? 0, 0, ',', '.') }}
                              @endif
                           </td>
                           <td class="text-center {{ $row->laba >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                              Rp {{ number_format($row->laba, 0, ',', '.') }}
                           </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">Tidak ada transaksi pada bulan ini</td></tr>
                        @endforelse
                     </tbody>
                     @if($detail_transaksi_laba->count() > 0)
                     <tfoot>
                        <tr>
                           <th colspan="6" class="text-right">Total Laba Bersih :</th>
                           <th class="text-center {{ $total_laba >= 0 ? 'text-success' : 'text-danger' }}">
                              Rp {{ number_format($total_laba, 0, ',', '.') }}
                           </th>
                        </tr>
                     </tfoot>
                     @endif
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

{{-- ══ SCRIPT CHARTS ════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
   const topMenuData = @json($topMenu);
   const labels = topMenuData.map(item => item.nama);
   const data = topMenuData.map(item => item.total_jumlah);
   const ctx = document.getElementById('topMenuChart').getContext('2d');
   new Chart(ctx, {
       type: 'bar',
       data: {
           labels: labels,
           datasets: [{
               label: 'Total Pemesanan ',
               data: data,
               backgroundColor: 'rgba(75, 192, 192, 0.2)',
               borderColor: 'rgba(75, 192, 192, 1)',
               borderWidth: 1
           }]
       },
       options: { responsive: true, scales: { x: { beginAtZero: true }, y: { beginAtZero: true } } }
   });
</script>
<script>
   document.addEventListener("DOMContentLoaded", function() {
       var topIncome = @json($topIncome);
       var labels = Object.keys(topIncome);
       var pemasukanCashData = [], pemasukanTransferData = [], pengeluaranCashData = [], pengeluaranTransferData = [];
       labels.forEach(function(day) {
           pemasukanCashData.push(topIncome[day]['Pemasukan Cash']);
           pemasukanTransferData.push(topIncome[day]['Pemasukan QRIS']);
           pengeluaranCashData.push(topIncome[day]['Pengeluaran Cash']);
           pengeluaranTransferData.push(topIncome[day]['Pengeluaran QRIS']);
       });
       var ctx = document.getElementById('topIncomeChart').getContext('2d');
       new Chart(ctx, {
           type: 'bar',
           data: {
               labels: labels,
               datasets: [
                   { label: 'Pemasukan Cash', data: pemasukanCashData, backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgba(255, 99, 132, 1)', borderWidth: 1 },
                   { label: 'Pemasukan QRIS', data: pemasukanTransferData, backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 },
                   { label: 'Pengeluaran Cash', data: pengeluaranCashData, backgroundColor: 'rgba(255, 206, 86, 0.2)', borderColor: 'rgba(255, 206, 86, 1)', borderWidth: 1 },
                   { label: 'Pengeluaran QRIS', data: pengeluaranTransferData, backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1 }
               ]
           },
           options: { responsive: true, scales: { x: { beginAtZero: true }, y: { beginAtZero: true } }, plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } } }
       });
   });
</script>
<script>
   document.addEventListener("DOMContentLoaded", function() {
       var topWeek = @json($topWeek);
       var labels = Object.keys(topWeek);
       var pemasukanCashData = [], pemasukanTransferData = [], pengeluaranCashData = [], pengeluaranTransferData = [];
       var labelWithRange = labels.map(function(week) { return week + ' | \n' + topWeek[week]['range']; });
       labels.forEach(function(week) {
           pemasukanCashData.push(topWeek[week]['Pemasukan Cash']);
           pemasukanTransferData.push(topWeek[week]['Pemasukan QRIS']);
           pengeluaranCashData.push(topWeek[week]['Pengeluaran Cash']);
           pengeluaranTransferData.push(topWeek[week]['Pengeluaran QRIS']);
       });
       var ctx = document.getElementById('topWeekChart').getContext('2d');
       new Chart(ctx, {
           type: 'bar',
           data: {
               labels: labelWithRange,
               datasets: [
                   { label: 'Pemasukan Cash', data: pemasukanCashData, backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgba(255, 99, 132, 1)', borderWidth: 1 },
                   { label: 'Pemasukan QRIS', data: pemasukanTransferData, backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 },
                   { label: 'Pengeluaran Cash', data: pengeluaranCashData, backgroundColor: 'rgba(255, 206, 86, 0.2)', borderColor: 'rgba(255, 206, 86, 1)', borderWidth: 1 },
                   { label: 'Pengeluaran QRIS', data: pengeluaranTransferData, backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1 }
               ]
           },
           options: { responsive: true, indexAxis: 'y', scales: { x: { beginAtZero: true }, y: { beginAtZero: true } }, plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } } }
       });
   });
</script>
<script>
$(document).ready(function () {
   $('#laba_bersih').on('shown.bs.modal', function () {
      if (!$.fn.DataTable.isDataTable('#tabel-laba-modal')) {
         $('#tabel-laba-modal').DataTable({
            responsive: true, pageLength: 10, order: [],
            language: { search: "Search:", lengthMenu: "Show _MENU_ entries", zeroRecords: "No data available in table", info: "Showing _START_ to _END_ of _TOTAL_ entries", infoEmpty: "No entries available" },
            columnDefs: [{ orderable: false, targets: [] }]
         });
      }
   });
});
</script>

@endif
@endsection