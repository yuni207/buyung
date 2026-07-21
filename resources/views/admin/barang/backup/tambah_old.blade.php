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
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-add-file-1"></i> Tambah Data Barang</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i></a>
         </div>
      </div>
      <hr style="margin-top: 0px">
            
            
      <form action="/admin/barang/create" method="POST" enctype="multipart/form-data">
         {{ csrf_field() }}
         <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="barcode">Barcode <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" id="barcode" name="barcode" class="form-control" placeholder="Masukkan Barcode ....." required oninput="this.value = this.value.toUpperCase();">
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="startScanner()">Scan Barcode</button>
                  </div>
                </div>
                <div id="reader" style="width: 100%; max-width: 300px; margin-top: 10px;"></div>
              </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Model Barang</label>
                  <select class="form-control" name="nama">
                     <option value="">-- Pilih Model Barang --</option>
                     <option value="Panjang">Panjang</option>
                     <option value="Pendek">Pendek</option>
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Warna Barang</label>
                  <input type="text" name="warna" class="form-control" placeholder="Masukkan Warna Barang .....">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Jenis Barang<span class="text-danger">*</span></label>
                  <select class="select2" name="id_jenis" required>
                     <option value="">-- Pilih Jenis Barang --</option>
                     @foreach($jenis as $data)
                        <option value="{{$data->id}}">{{$data->nama}}</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Merk Barang<span class="text-danger">*</span></label>
                  <select class="select2" name="id_merk" required>
                     <option value="">-- Pilih Merk Barang --</option>
                     @foreach($merk as $data)
                        <option value="{{$data->id}}">{{$data->nama}}</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Ukuran<span class="text-danger">*</span></label>
                  <input type="text" name="ukuran" required class="form-control" placeholder="Masukkan Ukuran Barang ....." oninput="this.value = this.value.toUpperCase();">
               </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                  <label>Stock<span class="text-danger">*</span></label>
                  <input type="text" name="stock" required class="form-control" placeholder="Masukkan Stock ....." oninput="formatNumber(this)">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                   <label>Harga<span class="text-danger">*</span></label>
                   <input type="text" name="harga" required class="form-control" placeholder="Masukkan Harga ....." oninput="formatNumber(this)">
                </div>
            </div>
         </div>
         <button type="submit" class="btn btn-primary mt-1 mr-2"><span class="icon-copy ti-save"></span> Tambah Data</button> 
      </form>
      @if(Auth::User()->level == '3')
      <div class="mt-4">
          <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
             <tr>
                 <th class="align-middle text-center" width="5%">#</th>
                 <th class="align-middle text-center">Barcode</th>
                 <th class="align-middle text-center">Ukuran</th>
                 <th class="align-middle text-center">Nama Barang</th>
                 <th class="align-middle text-center">Stock</th>
                 <th class="align-middle text-center">Harga</th>
                 @if(Auth::User()->level != '3') 
                 <th class="align-middle text-center">Penginput</th>
                 @endif
                 <th class="table-plus datatable-nosort text-center align-middle">Action</th>
             </tr>
         </thead>
         <tbody>
            <?php $no = 1; ?>
            @foreach($detail_barang as $data)
            <?php
               $barang = DB::table('barang')->find($data->id_barang);
               $user = DB::table('users')->find($data->id_user);
               $jenis = DB::table('jenis')->find($barang->id_jenis);
               $merk = DB::table('merk')->find($barang->id_merk);
            ?>
            <tr>
               <td class="text-center align-middle">{{$no++}}</td>
               <td class="align-middle">{{$barang->barcode ?? '-'}}</td>
               <td class="text-center align-middle">{{$data->ukuran ?? '-'}}</td>
               <td class="text-left align-middle">{{ $jenis->nama ?? '' }} {{ $merk->nama ?? '' }} {{ $barang->nama }} {{ $barang->warna }}</td>
               <td class="text-center align-middle">{{$data->stock ?? '-'}} Pcs</td>
               <td class="text-center align-middle">{{ 'Rp ' . number_format($data->harga, 0, ',', '.') }}</td>
               @if(Auth::User()->level != '3')
               <td class="text-left align-middle">{{$user->name ?? '-'}}</td>
               @endif
               <td class="text-center">
                  <a href="/admin/barang/edit/{{$data->id}}"><button class="btn btn-success btn-xs"><i class="fa fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Data"></i></button></a>
                  <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#data-{{$data->id}}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Delete Data"></i></button>
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
      </div>
      @endif
   </div>
   <!-- Striped table End -->
</div>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
  let html5QrCode;
  let scannerRunning = false;

  function startScanner() {
    if (scannerRunning) return;

    html5QrCode = new Html5Qrcode("reader");
    scannerRunning = true;

    // Konfigurasi hanya untuk 1D barcode (bukan QR Code)
    const config = {
      fps: 10,
      qrbox: 250,
      formatsToSupport: [
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.UPC_A,
        Html5QrcodeSupportedFormats.UPC_E,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.ITF
      ]
    };

    html5QrCode.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        document.getElementById("barcode").value = decodedText;
        stopScanner();
      },
      (errorMessage) => {
        // Optional: log error
      }
    ).catch(err => {
      console.error("Gagal memulai scanner: ", err);
      scannerRunning = false;
    });
  }

  function stopScanner() {
    if (html5QrCode) {
      html5QrCode.stop().then(() => {
        html5QrCode.clear();
        document.getElementById("reader").innerHTML = "";
        scannerRunning = false;
      }).catch(err => {
        console.error("Gagal menghentikan scanner: ", err);
      });
    }
  }
</script>

<script>
   function formatNumber(input) {
       // Menghapus semua karakter kecuali angka
       let value = input.value.replace(/\D/g, '');
       
       // Menambahkan format pemisah ribuan
       input.value = new Intl.NumberFormat().format(value);
   }
</script>
<!-- Modal -->
@foreach($detail_barang as $data)
<?php
   $barang = DB::table('barang')->find($data->id_barang);
   $jenis = DB::table('jenis')->find($barang->id_jenis);
   $merk = DB::table('merk')->find($barang->id_merk);
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
               <div class="col-md-12">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Nama Barang</label>
                     <input class="form-control" value="{{ $jenis->nama ?? '' }} {{ $merk->nama ?? '' }} {{ $barang->nama }} {{ $barang->warna }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div><div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Barcode</label>
                     <input class="form-control" value="{{ $barang->barcode ?? '' }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label for="exampleInputUsername1">Ukuran</label>
                     <input class="form-control" value="{{$data->ukuran ?? '-'}}" readonly style="background-color: white;pointer-events: none;">
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