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
         Nama otomatis terisi. Tambahkan satuan yang <strong>belum</strong> ada di bawah ini.
      </div>

      <form action="/admin/barang/create" method="POST" id="formTambah">
         {{ csrf_field() }}
         
         {{-- INFO UTAMA BARANG --}}
         <h5 class="mb-3 text-primary">Informasi Barang</h5>
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
                        style="position:absolute;top:6px;right:6px;z-index:10;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">✕</button>
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
                  <input type="text" id="nama" name="nama" required class="form-control"
                     placeholder="Masukkan Nama Barang .....">
                  <small id="keterangan-nama" class="text-muted"></small>
               </div>
            </div>
         </div>

         <hr>

         {{-- DETAIL SATUAN MULTIPLE --}}
         <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-primary mb-0">Variasi Satuan & Harga</h5>
            <button type="button" class="btn btn-sm btn-success" id="btnTambahSatuan">
               <i class="fa fa-plus"></i> Tambah Satuan
            </button>
         </div>

         <div id="satuan-container">
            {{-- Form Satuan Template (Baris 1) --}}
            <div class="satuan-row border p-3 mb-3 rounded position-relative" style="background: #fdfdfd;">
               <div class="row">
                  <div class="col-12 col-md-4">
                     <div class="form-group mb-md-0">
                        <label>Satuan <span class="text-danger">*</span></label>
                        <select class="form-control input-satuan" name="id_satuan[]" required>
                           <option value="" disabled selected>-- Pilih Satuan --</option>
                           @foreach($satuan as $data)
                              <option value="{{ $data->id }}">{{ $data->nama }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-6 col-md-2">
                     <div class="form-group mb-md-0">
                        <label>Stock <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="stock[]" required class="form-control format-number" placeholder="0" value="0">
                     </div>
                  </div>
                  <div class="col-6 col-md-2">
                     <div class="form-group mb-md-0">
                        <label>Harga Modal <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="harga_modal[]" required class="form-control format-number" placeholder="0" value="0">
                     </div>
                  </div>
                  <div class="col-6 col-md-2">
                     <div class="form-group mb-0">
                        <label>Harga Jual <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="harga_jual[]" required class="form-control format-number" placeholder="0" value="0">
                     </div>
                  </div>
                  <div class="col-6 col-md-2">
                     <div class="form-group mb-0">
                        <label>Harga Khusus <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="harga_khusus[]" required class="form-control format-number" placeholder="0" value="0">
                     </div>
                  </div>
               </div>
               {{-- Tombol hapus disembunyikan di baris pertama, ditampilkan di baris dinamis --}}
               <button type="button" class="btn btn-danger btn-sm btn-hapus-satuan position-absolute" style="top: 10px; right: 10px; display: none;" title="Hapus Baris">
                  <i class="fa fa-times"></i>
               </button>
            </div>
         </div>
         
         {{-- Pesan Peringatan Duplikasi Satuan --}}
         <div id="peringatan-duplikat" class="alert alert-warning py-2 mb-3" style="display: none;">
            <i class="fa fa-exclamation-triangle"></i> Terdapat satuan yang sama/duplikat, silakan periksa kembali.
         </div>

         <button type="submit" class="btn btn-primary btn-block mt-4" id="btnSimpan">
            <span class="icon-copy ti-save"></span> Simpan Semua Data
         </button>
      </form>
   </div>
</div>

{{-- ===== SCRIPTS ===== --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
// ========================
// FORMAT NUMBER (Event Delegation)
// ========================
// Pakai Event Delegation supaya elemen yang baru ditambah JS otomatis jalan formatnya
document.addEventListener('input', function (e) {
   if (e.target && e.target.classList.contains('format-number')) {
      let val = e.target.value.replace(/[^0-9]/g, '');
      e.target.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
   }
});

// Menghilangkan titik sebelum dikirim ke backend
document.getElementById('formTambah').addEventListener('submit', function (e) {
   if(document.getElementById('btnSimpan').disabled) {
      e.preventDefault();
      return;
   }
   document.querySelectorAll('.format-number').forEach(function (input) {
      input.value = input.value.replace(/\./g, '');
   });
});

// ========================
// DYNAMIC MULTIPLE SATUAN
// ========================
const container = document.getElementById('satuan-container');
const btnTambah = document.getElementById('btnTambahSatuan');

btnTambah.addEventListener('click', function() {
   // Clone baris pertama
   const firstRow = container.querySelector('.satuan-row');
   const newRow = firstRow.cloneNode(true);
   
   // Reset nilainya
   newRow.querySelectorAll('input').forEach(input => input.value = '0');
   newRow.querySelector('select').selectedIndex = 0;
   
   // Tampilkan tombol hapus
   newRow.querySelector('.btn-hapus-satuan').style.display = 'block';
   
   // Tambahkan ke container
   container.appendChild(newRow);
   validasiSatuan();
});

// Hapus baris dinamis
container.addEventListener('click', function(e) {
   if(e.target.closest('.btn-hapus-satuan')) {
      e.target.closest('.satuan-row').remove();
      validasiSatuan();
   }
});

// Cek perubahan Select untuk validasi duplikat
container.addEventListener('change', function(e) {
   if(e.target.classList.contains('input-satuan')) {
      validasiSatuan();
   }
});

// ========================
// CEK BARCODE (AJAX)
// ========================
let satuan_terpakai_ajax = [];
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
               document.getElementById('keterangan-nama').textContent = '✔ Nama otomatis dari barcode yang terdaftar.';
               document.getElementById('info-nama-barang').textContent = data.nama;
               document.getElementById('info-barang-ditemukan').style.setProperty('display', 'block', 'important');

               satuan_terpakai_ajax = data.satuan_terpakai.map(String);
               validasiSatuan();
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
   satuan_terpakai_ajax = [];
   validasiSatuan();
}

// ========================
// VALIDASI DUPLIKAT SATUAN
// ========================
function validasiSatuan() {
   const selects = document.querySelectorAll('.input-satuan');
   let terpilih = [];
   let adaDuplikat = false;
   
   // Hapus class error sebelumnya
   selects.forEach(sel => sel.classList.remove('is-invalid'));

   selects.forEach(select => {
      let val = select.value;
      if(val !== "") {
         // Cek duplikasi di form itu sendiri
         if(terpilih.includes(val) || satuan_terpakai_ajax.includes(val)) {
            adaDuplikat = true;
            select.classList.add('is-invalid');
         } else {
            terpilih.push(val);
         }
      }
   });

   // Kunci tombol simpan jika error
   if(adaDuplikat) {
      document.getElementById('peringatan-duplikat').style.display = 'block';
      document.getElementById('btnSimpan').disabled = true;
   } else {
      document.getElementById('peringatan-duplikat').style.display = 'none';
      document.getElementById('btnSimpan').disabled = false;
   }
}

// ... (KODE SCANNER ANDA TETAP SAMA SEPERTI SEBELUMNYA) ...
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
      fps: 10, qrbox: { width: 250, height: 120 },
      formatsToSupport: [
         Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.EAN_13,
         Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.UPC_A,
         Html5QrcodeSupportedFormats.UPC_E, Html5QrcodeSupportedFormats.CODE_39,
         Html5QrcodeSupportedFormats.ITF,
      ],
   };

   html5QrCode.start(
      { facingMode: "environment" }, config,
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