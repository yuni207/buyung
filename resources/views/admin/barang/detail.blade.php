@extends('admin.layouts.app', [
'activePage' => 'barang',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-12 col-sm-12">
            <div class="title">
               <h4>Data Barang</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item"><a href="/admin/barang">Data Barang</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Tambah Data Barang</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Striped table start -->
<div class="row">
      <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 mb-30">
         <div class="pd-20 card-box height-100-p">
            <div class="profile-photo">
               <a
                  href="/admin/barang/edit/{{$barang->id}}"
                  class="edit-avatar"
                  ><i class="fa fa-pencil"></i
                  ></a>
                  <img
                      src="{{url('assets-admin/vendors/images/browsing.png')}}"
                      alt=""
                      class="avatar-photo" style="width: 160px; height: 160px; object-fit: cover; object-position: center;"
                   />
            </div>
            <h5 class="text-center h5 mb-2">{{$barang->nama}}</h5>
            @if($barang->kode != "")
               <p class="text-center text-muted font-14">
                  <button class="btn btn-dark btn-xs">{{$barang->barcode}}</button>   
               </p>
            @endif   
            <div class="profile-info">
               <h5 class="mb-20 h5 text-blue">Detail Barang</h5>
               <ul>
                  @if($barang->kode != "")
                  <li>
                     <span>Kode Barang :</span>
                     {{$barang->kode ?? '-'}}
                  </li>
                  @endif
                  <li>
                     <span>Jenis Barang :</span>
                     {{$jenis->nama ?? '-'}}
                  </li>
                  <li>
                     <span>Merk Barang :</span>
                     {{$merk->nama ?? '-'}}
                  </li>
                  <li>
                     <span>Warna Barang :</span>
                     {{$barang->warna ?? '-'}}
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="col-xl-9 col-lg-9 col-md-9 col-sm-12 mb-30">
         <div class="pd-20 card-box height-100-p">
            <div class="clearfix">
               <div class="pull-left">
                  <h2 class="text-primary h2"><i class="icon-copy fa fa-address-card"></i> List Detail Barang</h2>
               </div>
               <div class="pull-right">
                  <a href="/admin/barang" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
               </div>
            </div>
            <hr style="margin-top: 0px;">
            
            
            <form action="/admin/barang/detail/create/{{$barang->id}}" method="POST" enctype="multipart/form-data">
               {{ csrf_field() }}
               <div class="row">
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Ukuran<span class="text-danger">*</span></label>
                        <input type="text" name="ukuran" required class="form-control" placeholder="Masukkan Ukuran .....">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Stock<span class="text-danger">*</span></label>
                        <input type="text" name="stock" required class="form-control" placeholder="Masukkan Stock ....." oninput="formatNumber(this)">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Harga<span class="text-danger">*</span></label>
                        <input type="text" name="harga" required class="form-control" placeholder="Masukkan Harga ....." oninput="formatNumber(this)">
                     </div>
                  </div>
                  <div class="col-md-2">
                     <div class="form-group">
                        <label style="color:white">Action</label>
                        <button type="submit" class="btn btn-primary btn-block"><span class="icon-copy ti-plus"></span></button>
                     </div>
                  </div>
               </div>
            </form>
            <table class="table table-bordered table-striped data-table hover">
               <thead class="bg-primary text-white">
                  <tr>
                     <th width="5%" class="text-center align-middle text-center">#</th>
                     <th class="align-middle text-center">Ukuran</th>
                     <th class="align-middle text-center">Stock</th>
                     <th class="text-center align-middle text-center">Harga</th>
                     <th class="table-plus datatable-nosort text-center align-middle">Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php $no = 1; ?>
                  @foreach($detail_barang as $data)
                  <tr>
                     <td class="text-center">{{$no++}}</td>
                     <td class="text-center">{{$data->ukuran ?? '-'}}</td>
                     <td class="text-center">{{ number_format(is_numeric($data->stock) ? $data->stock : 0, 0, ',', '.') }}</td>
                     <td class="text-center">{{ 'Rp ' . number_format(is_numeric($data->harga) ? $data->harga : 0, 0, ',', '.') }}</td>
                     <td class="text-center" width="10%">
                        <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#edit-{{$data->id}}"><i class="fa fa-pencil" data-toggle="tooltip" data-placement="top" title="Edit Data"></i></button>
                        <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-{{$data->id}}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Delete Data"></i></button>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>
   <!-- Striped table End -->
</div>
@foreach($detail_barang as $data)
<div class="modal fade" id="edit-{{$data->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">
            Apakah Anda Yakin Mengupdate Data Ini ?
            </h2>
            <hr>
            <form action="/admin/barang/detail/update/{{$data->id}}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row">
               <div class="col-md-4">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Ukuran<span class="text-danger">*</span></label>
                     <input type="text" name="ukuran" required class="form-control" placeholder="Masukkan Ukuran ....." value="{{$data->ukuran}}">
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Stock<span class="text-danger">*</span></label>
                     <input type="text" name="stock" class="form-control" placeholder="Masukkan Stock ....." oninput="formatNumber(this)" value="{{ number_format($data->stock, 0, ',', '.') }}">
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Harga<span class="text-danger">*</span></label>
                     <input type="text" name="harga" class="form-control" placeholder="Masukkan Harga ....." oninput="formatNumber(this)" value="{{ number_format($data->harga, 0, ',', '.') }}">
                  </div>
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-md-6">
                  <button type="submit" class="btn btn-primary btn-block">Ya</button>
               </div>
               <div class="col-md-6">
                  <button type="button" class="btn btn-danger btn-block" data-dismiss="modal" aria-label="Close">Tidak</button>
               </div>
            </div>
            </form>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="delete-{{$data->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">
            Apakah Anda Yakin Menghapus Data Ini ?
            </h2>
            <hr>
            <div class="row">
               <div class="col-md-4">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Ukuran</label>
                     <input class="form-control" value="{{$data->ukuran}}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Stock</label>
                     <input class="form-control" value="{{$data->stock}}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Harga</label>
                     <input class="form-control" value="{{ 'Rp ' . number_format($data->harga, 0, ',', '.') }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-md-6">
                  <a href="/admin/barang/detail/delete/{{$data->id}}" style="text-decoration: none;">
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
<script>
   function formatNumber(input) {
       // Menghapus semua karakter kecuali angka
       let value = input.value.replace(/\D/g, '');
       
       // Menambahkan format pemisah ribuan
       input.value = new Intl.NumberFormat().format(value);
   }
</script>
@endsection