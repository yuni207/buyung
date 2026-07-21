@extends('admin.layouts.app', [
'activePage' => 'setor_tarik',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-12 col-sm-12">
            <div class="title">
               <h4>Data Setor & Tarik</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item"><a href="/admin/setor_tarik">Data Setor & Tarik</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Tambah Data Setor & Tarik</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Striped table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-add-file-1"></i> Tambah Data Setor & Tarik</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/setor_tarik" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
         </div>
      </div>
      <hr style="margin-top: 0px">
      <form action="/admin/setor_tarik/create" method="POST" enctype="multipart/form-data">
         {{ csrf_field() }}
         <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label>Nama Pelanggan <span class="text-danger">*</span></label>
                  <input type="text" name="nama_pelanggan" required class="form-control" placeholder="Masukkan Nama Pelanggan .....">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Jenis <span class="text-danger">*</span></label>
                  <select name="jenis" required class="form-control">
                     <option value="">-- Pilih Jenis --</option>
                     <option value="setor tunai">Setor Tunai (uang masuk)</option>
                     <option value="tarik tunai">Tarik Tunai (uang keluar)</option>
                  </select>
                  <small class="text-muted">
                     <i class="fa fa-info-circle"></i>
                     Setor otomatis masuk ke pemasukan, Tarik otomatis masuk ke pengeluaran.
                  </small>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Total <span class="text-danger">*</span></label>
                  <div class="input-group">
                     <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                     <input type="text" name="total" required oninput="formatNumber(this)" class="form-control" placeholder="Masukkan Total .....">
                  </div>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Biaya Admin</label>
                  <div class="input-group">
                     <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                     <input type="text" name="biaya_admin" oninput="formatNumber(this)" class="form-control" placeholder="Masukkan Biaya Admin .....">
                  </div>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Metode Pembayaran <span class="text-danger">*</span></label>
                  <select name="id_metode" required class="form-control">
                     <option value="">-- Pilih Metode --</option>
                     @foreach($metode as $m)
                     <option value="{{ $m->id }}">{{ $m->nama }}</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Bukti Tarik/Setor <small class="text-muted">(opsional, jpg/png/pdf max 2MB)</small></label>
                  <input type="file" name="bukti" class="form-control">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Tanggal <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal" required class="form-control" value="{{ date('Y-m-d') }}">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Keterangan <small class="text-muted">(opsional)</small></label>
                  <input type="text" name="keterangan" class="form-control" placeholder="Catatan singkat">
               </div>
            </div>
         </div>
         <button type="submit" class="btn btn-primary mt-1 mr-2"><span class="icon-copy ti-save"></span> Tambah Data</button>
      </form>
   </div>
   <!-- Striped table End -->
</div>
<script>
   function formatNumber(input) {
       // Menghapus semua karakter kecuali angka
       let value = input.value.replace(/\D/g, '');
       
       // Menambahkan format pemisah ribuan
       input.value = new Intl.NumberFormat().format(value);
   }
</script>
@endsection