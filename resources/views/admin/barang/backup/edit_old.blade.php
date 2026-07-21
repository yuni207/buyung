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
                  <li class="breadcrumb-item"><a href="/admin/barang">Data Barang</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Edit Data Barang</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Striped table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-edit-1"></i> Edit Data Barang</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
         </div>
      </div>
      <hr style="margin-top: 0px">
      <form action="/admin/barang/update/{{$detail_barang->id}}" method="POST" enctype="multipart/form-data">
         {{ csrf_field() }}
         <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label for="barcode">Barcode<span class="text-danger">*</span></label>
                  <div class="input-group">
                     <input type="text" id="barcode" name="barcode" class="form-control" placeholder="Masukkan Barcode ....." value="{{$barang->barcode}}" required oninput="this.value = this.value.toUpperCase();">
                     <div class="input-group-append">
                        <button type="button" class="btn btn-primary" onclick="startScanner()">Scan Barcode</button>
                     </div>
                  </div>
                  <div id="reader" style="width:300px; margin-top: 10px;"></div>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Model Barang</label>
                  <select class="form-control" name="nama">
                     <option value="">-- Pilih Model Barang --</option>
                     <option value="Panjang" @if($barang->nama == 'Panjang') selected @endif>Panjang</option>
                     <option value="Pendek" @if($barang->nama == 'Pendek') selected @endif>Pendek</option>
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Warna Barang</label>
                  <input type="text" name="warna" class="form-control" placeholder="Masukkan Warna Barang ....." value="{{$barang->warna}}">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Jenis Barang<span class="text-danger">*</span></label>
                  <select class="select2" name="id_jenis" required>
                     <option value="">-- Pilih Jenis Barang --</option>
                     @foreach($jenis as $data)
                        <option value="{{$data->id}}" @if($barang->id_jenis == $data->id) selected @endif>{{$data->nama}}</option>
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
                        <option value="{{$data->id}}" @if($barang->id_merk == $data->id) selected @endif>{{$data->nama}}</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Ukuran<span class="text-danger">*</span></label>
                  <input type="text" name="ukuran" required class="form-control" placeholder="Masukkan Ukuran Barang ....." value="{{$detail_barang->ukuran}}" oninput="this.value = this.value.toUpperCase();">
               </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                  <label>Stock<span class="text-danger">*</span></label>
                  <input type="text" name="stock" required class="form-control" placeholder="Masukkan Stock ....." oninput="formatNumber(this)" value="{{$detail_barang->stock}}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                   <label>Harga<span class="text-danger">*</span></label>
                   <input type="text" name="harga" required class="form-control" placeholder="Masukkan Harga ....." oninput="formatNumber(this)" value="{{$detail_barang->harga}}">
                </div>
            </div>
         </div>
         <button type="submit" class="btn btn-primary mt-1 mr-2"><span class="icon-copy ti-save"></span> Update Data</button>
      </form>
   </div>
   <!-- Striped table End -->
</div>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
function startScanner() {
    const html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" }, // rear camera
        {
            fps: 10,
            qrbox: 250
        },
        (decodedText, decodedResult) => {
            document.querySelector('input[name="barcode"]').value = decodedText;
            html5QrCode.stop(); // Stop after successful scan
            document.getElementById("reader").innerHTML = ""; // Clear preview
        },
        (errorMessage) => {
            // optional: console.log(errorMessage);
        }
    );
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
@endsection