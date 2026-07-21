@extends('admin.layouts.app', [
'activePage' => 'barang_masuk',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-12 col-sm-12">
            <div class="title">
               <h4>Data Barang Masuk</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item"><a href="/admin/barang_masuk">Data Barang Masuk</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Tambah Data Barang Masuk</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Striped table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-add-file-1"></i> Tambah Data Barang Masuk</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang_masuk" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
         </div>
      </div>
      <hr style="margin-top: 0px">
      <form action="/admin/barang_masuk/create" method="POST" enctype="multipart/form-data">
         {{ csrf_field() }}
         <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Barang <span class="text-danger">*</span></label>
                    <select autofocus class="select2 form-control" name="id_barang" id="id_barang" required>
                        <option value="">-- Pilih Nama Barang --</option>
                        @foreach($barang as $data)
                            <option value="{{ $data->id }}">{{ $data->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Satuan <span class="text-danger">*</span></label>
                    <select class="select2 form-control" name="ukuran" id="ukuran" required disabled>
                        <option value="">-- Pilih Satuan --</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Stock Saat Ini</label>
                    <input type="text" id="stock" class="form-control" placeholder="Otomatis terisi..." readonly style="background-color: #e9ecef; pointer-events: none;">
                </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Tanggal Barang Masuk <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal" required class="form-control" value="{{date('Y-m-d')}}">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label>Jumlah Barang Masuk <span class="text-danger">*</span></label>
                  <input type="text" name="jumlah" id="jumlah" required class="form-control" placeholder="Masukkan Jumlah Barang Masuk....." oninput="formatNumber(this)">
               </div>
            </div>
         </div>
         <button type="submit" class="btn btn-primary mt-1 mr-2"><span class="icon-copy ti-save"></span> Tambah Data</button>
      </form>
   </div>
   <!-- Striped table End -->
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {

        // Ketika barang dipilih, ambil daftar satuan
        $("#id_barang").on("change", function () {
            let id_barang = $(this).val();
            let satuanSelect = $("#ukuran");
            let stockInput  = $("#stock");

            satuanSelect.empty().append('<option value="">-- Pilih Satuan --</option>').prop("disabled", true);
            stockInput.val("");

            if (!id_barang) return;

            satuanSelect.append('<option disabled>Loading...</option>');

            $.ajax({
                url: `/admin/barang/get-ukuran/${id_barang}`,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    satuanSelect.empty().append('<option value="">-- Pilih Satuan --</option>');

                    if (data.length === 0) {
                        alert("Tidak ada satuan tersedia untuk barang ini!");
                    } else {
                        data.forEach(function (item) {
                            satuanSelect.append(new Option(item.satuan, item.id));
                        });
                        satuanSelect.prop("disabled", false);
                    }
                },
                error: function () {
                    satuanSelect.empty().append('<option value="">-- Pilih Satuan --</option>');
                    alert("Terjadi kesalahan saat mengambil data satuan.");
                }
            });
        });

        // Ketika satuan dipilih, ambil stok otomatis
        $("#ukuran").on("change", function () {
            let id_detail = $(this).val();
            let stockInput = $("#stock");

            if (!id_detail) {
                stockInput.val("");
                return;
            }

            stockInput.val("Loading...");

            $.ajax({
                url: `/admin/barang/get-stock/${id_detail}`,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    stockInput.val(data && data.stock !== undefined ? data.stock : "0");
                },
                error: function () {
                    stockInput.val("Error");
                    alert("Terjadi kesalahan saat mengambil data stok.");
                }
            });
        });
    });

    function formatNumber(input) {
        let value = input.value.replace(/\D/g, '');
        input.value = new Intl.NumberFormat('id-ID').format(value);
    }
</script>
@endsection