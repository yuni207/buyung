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

   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-add-file-1"></i> Tambah Data</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
         </div>
      </div>
      <hr style="margin-top: 0px">

      
      

      {{-- Info: barang ditemukan --}}
      <div id="info-barang-ditemukan" class="alert alert-info alert-dismissible fade show" style="display:none!important;">
         <i class="fa fa-info-circle"></i>
         Barcode ini sudah terdaftar sebagai <strong id="info-nama-barang"></strong>.
         Nama otomatis terisi. Pilih satuan yang <strong>belum</strong> ada untuk menambah satuan baru.
      </div>

      <form action="/admin/barang/create" method="POST" id="formTambah">
         {{ csrf_field() }}
         <div class="row">

            {{-- BARCODE --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label for="barcode">Barcode <span class="text-danger">*</span></label>
                  <div class="input-group">
                     <input type="text" id="barcode" name="barcode" class="form-control"
                        placeholder="Masukkan atau scan barcode..."
                        oninput="this.value = this.value.toUpperCase(); cariBarcode(this.value);"
                        required>
                     <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="toggleScanner()">
                           <i class="fa fa-barcode"></i> Scan
                        </button>
                     </div>
                  </div>

                  {{-- Scanner Box --}}
                  <div id="scanner-box" style="display:none; margin-top:10px; border:2px dashed #007bff; border-radius:8px; overflow:hidden; position:relative; background:#000;">
                     <button type="button" onclick="stopScanner()"
                        style="position:absolute;top:6px;right:6px;z-index:10;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;">
                        ✕
                     </button>
                     <div id="reader" style="width:100%;"></div>
                  </div>

                  {{-- Notif sukses scan --}}
                  <div id="scan-success" style="display:none; margin-top:8px; padding:8px 12px; background:#e8f5e9; color:#2e7d32; border-radius:6px; font-weight:600; font-size:13px;">
                     ✅ Barcode berhasil discan!
                  </div>
               </div>
            </div>

            {{-- NAMA BARANG --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Nama Barang <span class="text-danger">*</span></label>
                  <input type="text" id="nama" name="nama" required class="form-control"
                     placeholder="Masukkan Nama Barang .....">
                  <small id="keterangan-nama" class="text-muted"></small>
               </div>
            </div>

            {{-- SATUAN --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Satuan Barang <span class="text-danger">*</span></label>
                  <select class="select2" name="id_satuan" id="id_satuan" required>
                     @foreach($satuan as $data)
                        <option value="{{ $data->id }}" data-nama="{{ $data->nama }}">{{ $data->nama }}</option>
                     @endforeach
                  </select>
                  <small id="keterangan-satuan" class="text-danger" style="display:none;">
                     <i class="fa fa-exclamation-circle"></i> Satuan ini sudah ada untuk barang ini.
                  </small>
               </div>
            </div>

            {{-- STOCK --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Stock <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="stock" required class="form-control format-number"
                     placeholder="Masukkan Stock .....">
               </div>
            </div>

            {{-- HARGA MODAL --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Harga Modal <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="harga_modal" required class="form-control format-number"
                     placeholder="Masukkan Harga Modal ....." value="0">
               </div>
            </div>

            {{-- HARGA JUAL --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Harga Jual <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="harga_jual" required class="form-control format-number"
                     placeholder="Masukkan Harga Jual ....." value="0">
               </div>
            </div>

            {{-- HARGA KHUSUS --}}
            <div class="col-12 col-md-6">
               <div class="form-group">
                  <label>Harga Khusus <span class="text-danger">*</span></label>
                  <input type="text" inputmode="numeric" name="harga_khusus" required class="form-control format-number"
                     placeholder="Masukkan Harga Khusus ....." value="0">
               </div>
            </div>

         </div>

         <button type="submit" class="btn btn-primary btn-block mt-2" id="btnSimpan">
            <span class="icon-copy ti-save"></span> Tambah Data
         </button>
      </form>

      {{-- ===== TABEL DAFTAR BARANG (grouped) ===== --}}
      <hr class="mt-4">
      <h5 class="text-primary"><i class="dw dw-list"></i> Daftar Barang Tersimpan</h5>
      <div class="table-responsive mt-2">
         <table class="table table-striped table-bordered hover" id="tabel-barang-tambah">
            <thead class="bg-primary text-white">
               <tr>
                  <th class="text-center align-middle" width="4%">#</th>
                  <th class="text-center align-middle">Barcode</th>
                  <th class="text-center align-middle">Nama Barang</th>
                  <th class="text-center align-middle">Satuan</th>
                  <th class="text-center align-middle">Stock</th>
                  <th class="text-center align-middle">Harga Modal</th>
                  <th class="text-center align-middle">Harga Jual</th>
                  <th class="datatable-nosort text-center align-middle">Action</th>
               </tr>
            </thead>
            <tbody>
            <?php $no = 1; ?>
            @foreach($grouped as $id_barang => $rows)
               <?php
                  $barang   = DB::table('barang')->find($id_barang);
                  $rowCount = count($rows);
                  $first    = true;
               ?>
               @foreach($rows as $data)
                  <?php $satuan_row = DB::table('satuan')->find($data->id_satuan); ?>
                  <tr>
                     @if($first)
                        <td class="text-center align-middle" rowspan="{{ $rowCount }}">{{ $no++ }}</td>
                        <td class="align-middle" rowspan="{{ $rowCount }}">{{ $barang->barcode ?? '-' }}</td>
                        <td class="align-middle" rowspan="{{ $rowCount }}">{{ $barang->nama ?? '-' }}</td>
                        <?php $first = false; ?>
                     @endif
                     <td class="text-center align-middle">
                        <span class="badge badge-info" style="font-size:12px;">{{ $satuan_row->nama ?? '-' }}</span>
                     </td>
                     <td class="text-center align-middle">{{ number_format($data->stock, 0, ',', '.') }}</td>
                     <td class="text-center align-middle">Rp {{ number_format($data->harga_modal, 0, ',', '.') }}</td>
                     <td class="text-center align-middle">Rp {{ number_format($data->harga_jual, 0, ',', '.') }}</td>
                     <td class="text-center align-middle">
                        <a href="/admin/barang/edit/{{ $data->id }}" title="Edit">
                           <button class="btn btn-success btn-xs"><i class="fa fa-edit"></i></button>
                        </a>
                        <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#hapus-{{ $data->id }}" title="Hapus">
                           <i class="fa fa-trash"></i>
                        </button>
                     </td>
                  </tr>
               @endforeach
            @endforeach
            </tbody>
         </table>
      </div>
   </div>
</div>

{{-- Modal Hapus --}}
@foreach($grouped as $id_barang => $rows)
   <?php $barang_modal = DB::table('barang')->find($id_barang); ?>
   @foreach($rows as $data)
      <?php $sat = DB::table('satuan')->find($data->id_satuan); ?>
      <div class="modal fade" id="hapus-{{ $data->id }}" tabindex="-1" role="dialog" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-body">
                  <h5 class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Hapus Data</h5>
                  <hr>
                  <p class="text-center mb-1">Yakin menghapus satuan <strong>{{ $sat->nama ?? '-' }}</strong> dari:</p>
                  <p class="text-center font-weight-bold" style="font-size:16px;">{{ $barang_modal->nama ?? '' }}</p>
                  @if(DB::table('detail_barang')->where('id_barang',$id_barang)->count() == 1)
                     <div class="alert alert-warning text-center" style="font-size:12px;">
                        <i class="fa fa-info-circle"></i> Ini satuan terakhir. Data barang juga akan ikut terhapus.
                     </div>
                  @endif
                  <div class="row mt-3">
                     <div class="col-6">
                        <a href="/admin/barang/delete/{{ $data->id }}" style="text-decoration:none;">
                           <button class="btn btn-primary btn-block">Ya, Hapus</button>
                        </a>
                     </div>
                     <div class="col-6">
                        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">Batal</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   @endforeach
@endforeach

{{-- ===== SCRIPTS ===== --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
// ========================
// FORMAT NUMBER
// ========================
document.querySelectorAll('.format-number').forEach(function (input) {
   input.addEventListener('input', function () {
      let val = this.value.replace(/[^0-9]/g, '');
      this.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
   });
});

document.getElementById('formTambah').addEventListener('submit', function () {
   document.querySelectorAll('.format-number').forEach(function (input) {
      input.value = input.value.replace(/\./g, '');
   });
});

// ========================
// CEK BARCODE (AJAX)
// ========================
let satuan_terpakai = [];
let debounceTimer = null;

function cariBarcode(val) {
   clearTimeout(debounceTimer);
   if (val.length < 3) {
      resetBarcodeCek();
      return;
   }
   debounceTimer = setTimeout(function () {
      fetch('/admin/barang/cari-barang?barcode=' + encodeURIComponent(val))
         .then(r => r.json())
         .then(data => {
            if (data.found) {
               document.getElementById('nama').value = data.nama;
               document.getElementById('nama').readOnly = true;
               document.getElementById('keterangan-nama').textContent = '✔ Nama otomatis dari barcode yang sudah terdaftar.';
               document.getElementById('info-nama-barang').textContent = data.nama;
               document.getElementById('info-barang-ditemukan').style.setProperty('display', 'block', 'important');

               satuan_terpakai = data.satuan_terpakai.map(String);
               updateSatuanWarning();
            } else {
               resetBarcodeCek();
            }
         })
         .catch(() => resetBarcodeCek());
   }, 400);
}

function resetBarcodeCek() {
   document.getElementById('nama').readOnly = false;
   document.getElementById('keterangan-nama').textContent = '';
   document.getElementById('info-barang-ditemukan').style.setProperty('display', 'none', 'important');
   satuan_terpakai = [];
   updateSatuanWarning();
}

// Pantau perubahan select satuan
document.getElementById('id_satuan').addEventListener('change', updateSatuanWarning);

function updateSatuanWarning() {
   const selected = String(document.getElementById('id_satuan').value);
   const ket = document.getElementById('keterangan-satuan');
   const btn = document.getElementById('btnSimpan');

   if (satuan_terpakai.includes(selected)) {
      ket.style.display = 'block';
      btn.disabled = true;
   } else {
      ket.style.display = 'none';
      btn.disabled = false;
   }
}

// ========================
// SCANNER
// ========================
let html5QrCode   = null;
let scannerRunning = false;

function toggleScanner() {
   scannerRunning ? stopScanner() : startScanner();
}

function startScanner() {
   document.getElementById('scanner-box').style.display = 'block';
   html5QrCode   = new Html5Qrcode("reader");
   scannerRunning = true;

   const config = {
      fps: 10,
      qrbox: { width: 250, height: 120 },
      formatsToSupport: [
         Html5QrcodeSupportedFormats.CODE_128,
         Html5QrcodeSupportedFormats.EAN_13,
         Html5QrcodeSupportedFormats.EAN_8,
         Html5QrcodeSupportedFormats.UPC_A,
         Html5QrcodeSupportedFormats.UPC_E,
         Html5QrcodeSupportedFormats.CODE_39,
         Html5QrcodeSupportedFormats.ITF,
      ],
   };

   html5QrCode.start(
      { facingMode: "environment" },
      config,
      (decodedText) => {
         const barcodeInput = document.getElementById("barcode");
         barcodeInput.value = decodedText.toUpperCase();
         stopScanner();
         cariBarcode(barcodeInput.value);
         const badge = document.getElementById('scan-success');
         badge.style.display = 'block';
         setTimeout(() => badge.style.display = 'none', 2500);
      },
      () => {}
   ).catch(err => {
      console.error(err);
      scannerRunning = false;
      document.getElementById('scanner-box').style.display = 'none';
      alert("Tidak bisa membuka kamera. Pastikan izin kamera sudah diberikan.");
   });
}

function stopScanner() {
   if (html5QrCode && scannerRunning) {
      html5QrCode.stop().then(() => {
         html5QrCode.clear();
         document.getElementById("reader").innerHTML = "";
         document.getElementById('scanner-box').style.display = 'none';
         scannerRunning = false;
         html5QrCode   = null;
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