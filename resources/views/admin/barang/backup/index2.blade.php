@extends('admin.layouts.app', [
'activePage' => 'barang',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Data Barang</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Barang</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Striped table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> List Data Barang</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang/add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Data</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">
      
      
      <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
             <tr>
                 <th class="align-middle text-center" rowspan="2" width="5%">#</th>
                 <!--<th class="align-middle text-center" rowspan="2">Kode</th>-->
                 <th class="align-middle text-center" rowspan="2">Barcode</th>
                 <th class="align-middle text-center" rowspan="2">Jenis</th>
                 <th class="align-middle text-center" rowspan="2">Model</th>
                 <th class="align-middle text-center" rowspan="2">Warna</th>
                 <th class="align-middle text-center" rowspan="2">Merk</th>
                 <th class="align-middle text-center" colspan="3">Keterangan</th>
                 @if(Auth::User()->level != '3') 
                 <th class="align-middle text-center" rowspan="2">Penginput</th>
                 @endif
                 <th class="table-plus datatable-nosort text-center align-middle" rowspan="2">Action</th>
             </tr>
             <tr>
                 <th class="text-center" width="15%">Ukuran</th>
                 <th class="text-center" width="15%">Stock</th>
                 <th class="text-center" width="15%">Harga</th>
             </tr>
         </thead>
         <tbody>
            <?php $no = 1; ?>
            @foreach($barang as $data)
            <?php
               $user = DB::table('users')->find($data->id_user);
               $jenis = DB::table('jenis')->find($data->id_jenis);
               $merk = DB::table('merk')->find($data->id_merk);
               $detail_barang = DB::table('detail_barang')->where('id_barang',$data->id)->get();
               $total_detail_barang = DB::table('detail_barang')->where('id_barang',$data->id)->count();
            ?>
            <tr>
               <td class="text-center align-middle">{{$no++}}</td>
               <!--<td class="align-middle">{{$data->kode}}</td>-->
               <td class="align-middle">{{$data->barcode ?? '-'}}</td>
               <td class="text-left align-middle">{{$jenis->nama ?? '-'}}</td>
               <td class="align-middle">{{$data->nama}}</td>
               <td class="text-center align-middle">{{$data->warna ?? '-'}}</td>
               <td class="text-left align-middle">{{$merk->nama ?? '-'}}</td>
               <td>
                  @forelse($detail_barang as $data2)
                     ◉ {{$data2->ukuran ?? ''}}<br>
                  @empty
                     ◉ Belum diisi<br>
                  @endforelse
               </td>
               <td>
                  @forelse($detail_barang as $data2)
                     ◉ {{ number_format(is_numeric($data2->stock) ? $data2->stock : 0, 0, ',', '.') }} Pcs<br>
                  @empty
                     ◉ Belum diisi<br>
                  @endforelse
               </td>
               <td>
                  @forelse($detail_barang as $data2)
                     ◉ {{ 'Rp ' . number_format(is_numeric($data2->harga) ? $data2->harga : 0, 0, ',', '.') }}<br>
                  @empty
                     ◉ Belum diisi<br>
                  @endforelse
               </td>
               @if(Auth::User()->level != '3')
               <td class="text-left align-middle">{{$user->name ?? '-'}}</td>
               @endif
               <td class="text-center" width="15%">
                  <a href="/admin/barang/detail/{{$data->id}}"><button class="btn btn-info btn-xs"><i class="fa fa-address-card" data-toggle="tooltip" data-placement="top" title="Detail Data"></i></button></a>
                  <a href="/admin/barang/edit/{{$data->id}}"><button class="btn btn-success btn-xs"><i class="fa fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Data"></i></button></a>
                  <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#data-{{$data->id}}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Delete Data"></i></button>
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
   <!-- Striped table End -->
</div>
<!-- Modal -->
@foreach($barang as $data)
<?php
   $jenis = DB::table('jenis')->find($data->id_jenis);
   $merk = DB::table('merk')->find($data->id_merk);
?>
<div class="modal fade" id="data-{{$data->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">
            Apakah Anda Yakin Menghapus Data Ini ?
            </h2>
            <hr>
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Nama Barang</label>
                     <input class="form-control" value="{{$data->nama}}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Warna Barang</label>
                     <input class="form-control" value="{{$data->warna ?? '-'}}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Jenis Barang</label>
                     <input class="form-control" value="{{$jenis->nama ?? '-'}}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Merk Barang</label>
                     <input class="form-control" value="{{$merk->nama ?? '-'}}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-md-6">
                  <a href="/admin/barang/delete/{{$data->id}}" style="text-decoration: none;">
                  <button type="button" class="btn btn-primary btn-block">Ya</button>
                  </a>
               </div>
               <div class="col-md-6">
                  <button type="button" class="btn btn-danger btn-block" data-dismiss="modal" aria-label="Close">Tidak</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endforeach
@endsection