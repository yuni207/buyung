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
            <nav aria-label="breadcrumb">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item"><a href="/admin/barang">Data Barang</a></li>
                  <li class="breadcrumb-item active">Edit Data Barang</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>

   <div class="pd-20 card-box mb-30">

      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2">
               <i class="icon-copy dw dw-edit-1"></i> Edit Data Barang
            </h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang" class="btn btn-primary btn-sm">
               <i class="fa fa-arrow-left"></i> Back
            </a>
         </div>
      </div>
      <hr style="margin-top:0px">

      {{-- Info satuan lain pada barang yang sama --}}
      @php
         $satuan_lain = DB::table('detail_barang')
            ->join('satuan','satuan.id','=','detail_barang.id_satuan')
            ->where('detail_barang.id_barang', $detail_barang->id_barang)
            ->where('detail_barang.id', '!=', $detail_barang->id)
            ->select('satuan.nama')
            ->pluck('satuan.nama');
      @endphp
      @if($satuan_lain->count() > 0)
         <div class="alert alert-info" style="font-size:13px;">
            <i class="fa fa-info-circle"></i>
            Barang <strong>{{ $barang->nama }}</strong> juga memiliki satuan lain:
            @foreach($satuan_lain as $s)
               <span class="badge badge-secondary">{{ $s }}</span>
            @endforeach
            &mdash; Anda sedang mengedit satuan
            <span class="badge badge-info">{{ DB::table('satuan')->find($detail_barang->id_satuan)->nama ?? '-' }}</span>
         </div>
      @endif

      
      

      <form action="/admin/barang/update/{{ $detail_barang->id }}" method="POST" id="formEdit">
         {{ csrf_field() }}
         <div class="row">

            {{-- BARCODE --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label for="barcode">Barcode <span class="text-danger">*</span></label>
                  <div class="input-group">
                     <input type="text" id="barcode" name="barcode" class="form-control"
                        placeholder="Masukkan atau scan barcode..."
                        oninput="this.value = this.value.toUpperCase();"
                        value="{{ $barang->barcode }}" required>
                     <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="toggleScanner()">
                           <i class="fa fa-barcode"></i> Scan
                        </button>
                     </div>
                  </div>
                  <div id="scanner-box" style="display:none; margin-top:10px; border:2px dashed #007bff; border-radius:8px; overflow:hidden; position:relative; background:#000;">
                     <button type="button" onclick="stopScanner()"
                        style="position:absolute;top:6px;right:6px;z-index:10;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;">
                        ✕
                     </button>
                     <div id="reader" style="width:100%;"></div>
                  </div>
                  <div id="scan-success" style="display:none; margin-top:8px; padding:8px 12px; background:#e8f5e9; color:#2e7d32; border-radius:6px; font-weight:600; font-size:13px;">
                     ✅ Barcode berhasil discan!
                  </div>
               </div>
            </div>

            {{-- NAMA BARANG --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Nama Barang <span class="text-danger">*</span></label>
                  <input type="text" name="nama" required class="form-control"
                     value="{{ $barang->nama }}">
                  @if($satuan_lain->count() > 0)
                     <small class="text-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        Mengubah nama/barcode akan mempengaruhi semua satuan pada barang ini.
                     </small>
                  @endif
               </div>
            </div>

            {{-- SATUAN --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Satuan Barang <span class="text-danger">*</span></label>
                  <select class="select2" name="id_satuan" id="id_satuan" required>
                     @foreach($satuan as $data)
                        <option value="{{ $data->id }}"
                           @if($detail_barang->id_satuan == $data->id) selected @endif
                           @if(in_array($data->id, $satuan_terpakai)) data-terpakai="1" @endif>
                           {{ $data->nama }}
                           @if(in_array($data->id, $satuan_terpakai)) (sudah terpakai) @endif
                        </option>
                     @endforeach
                  </select>
                  <small id="warning-satuan" class="text-danger" style="display:none;">
                     <i class="fa fa-exclamation-circle"></i> Satuan ini sudah digunakan pada barang yang sama.
                  </small>
               </div>
            </div>

            {{-- STOCK --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Stock <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="stock" required class="form-control format-number"
                     value="{{ number_format($detail_barang->stock, 0, ',', '.') }}">
               </div>
            </div>

            {{-- HARGA MODAL --}}
            <div class="col-12 col-md-4">
               <div class="form-group">
                  <label>Harga Modal <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="harga_modal" required class="form-control format-number"
                     value="{{ number_format($detail_barang->harga_modal, 0, ',', '.') }}">
               </div>
            </div>

            {{-- HARGA JUAL --}}
            <div class="col-12 col-md-4">
               <div class="form-group">
                  <label>Harga Jual <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="harga_jual" required class="form-control format-number"
                     value="{{ number_format($detail_barang->harga_jual, 0, ',', '.') }}">
               </div>
            </div>

            {{-- HARGA KHUSUS --}}
            <div class="col-12 col-md-4">
               <div class="form-group">
                  <label>Harga Khusus <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="harga_khusus" required class="form-control format-number"
                     value="{{ number_format($detail_barang->harga_khusus, 0, ',', '.') }}">
               </div>
            </div>

         </div>

         <button type="submit" class="btn btn-primary btn-block mt-2" id="btnUpdate">
            <span class="icon-copy ti-save"></span> Update Data
         </button>
      </form>
   </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
// Daftar satuan terpakai (selain saat ini)
const satuanTerpakai = @json($satuan_terpakai).map(String);

document.getElementById('id_satuan').addEventListener('change', function () {
   const val = String(this.value);
   const warn = document.getElementById('warning-satuan');
   const btn  = document.getElementById('btnUpdate');
   if (satuanTerpakai.includes(val)) {
      warn.style.display = 'block';
      btn.disabled = true;
   } else {
      warn.style.display = 'none';
      btn.disabled = false;
   }
});

// Format number
document.querySelectorAll('.format-number').forEach(function (input) {
   input.addEventListener('input', function () {
      let val = this.value.replace(/[^0-9]/g, '');
      this.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
   });
});

document.getElementById('formEdit').addEventListener('submit', function () {
   document.querySelectorAll('.format-number').forEach(function (input) {
      input.value = input.value.replace(/\./g, '');
   });
});

// Scanner
let html5QrCode = null;
let scannerRunning = false;

function toggleScanner() {
   scannerRunning ? stopScanner() : startScanner();
}

function startScanner() {
   document.getElementById('scanner-box').style.display = 'block';
   html5QrCode = new Html5Qrcode("reader");
   scannerRunning = true;

   html5QrCode.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: { width: 250, height: 120 } },
      (decodedText) => {
         document.getElementById("barcode").value = decodedText.toUpperCase();
         stopScanner();
         const badge = document.getElementById('scan-success');
         badge.style.display = 'block';
         setTimeout(() => badge.style.display = 'none', 2500);
      },
      () => {}
   ).catch(err => {
      console.error(err);
      scannerRunning = false;
      document.getElementById('scanner-box').style.display = 'none';
      alert("Tidak bisa membuka kamera.");
   });
}

function stopScanner() {
   if (html5QrCode && scannerRunning) {
      html5QrCode.stop().then(() => {
         html5QrCode.clear();
         document.getElementById("reader").innerHTML = "";
         document.getElementById('scanner-box').style.display = 'none';
         scannerRunning = false;
         html5QrCode = null;
      }).catch(() => {
         document.getElementById('scanner-box').style.display = 'none';
         scannerRunning = false;
      });
   } else {
      document.getElementById('scanner-box').style.display = 'none';
   }
}
</script>
@endsection