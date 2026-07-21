@extends('admin.layouts.app', [
'activePage' => 'hutang',
])
@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title"><h4>Tambah Hutang Manual</h4></div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                        <li class="breadcrumb-item"><a href="/admin/hutang">Data Hutang</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="pd-20 card-box mb-30">
                <h2 class="text-primary h2 mb-3">
                    <i class="icon-copy dw dw-add-file-1"></i> Form Hutang Manual
                </h2>
                <hr style="margin-top:0;">
                <p class="text-muted small mb-3">
                    Gunakan form ini untuk mencatat hutang pelanggan yang <strong>tidak melalui transaksi barang</strong>
                    (misal: hutang uang, pinjaman, dll). Untuk hutang dari penjualan barang, gunakan menu
                    <a href="/admin/transaksi/add">Transaksi Baru</a> dengan toggle "Hutang" diaktifkan.
                </p>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="/admin/hutang/create" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nama Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pelanggan" class="form-control"
                               value="{{ old('nama_pelanggan') }}" placeholder="Nama pelanggan berhutang" required>
                    </div>
                    <div class="form-group">
                        <label>No. HP <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="no_hp" class="form-control"
                               value="{{ old('no_hp') }}" placeholder="08xx..." maxlength="12">
                    </div>
                    <div class="form-group">
                        <label>Total Hutang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="text" name="total" id="inp-total" class="form-control"
                                   value="{{ old('total') }}" placeholder="0" required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control"
                               value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jatuh Tempo <small class="text-muted">(opsional)</small></label>
                        <input type="date" name="jatuh_tempo" class="form-control"
                               value="{{ old('jatuh_tempo') }}">
                    </div>
                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="keterangan" class="form-control"
                               value="{{ old('keterangan') }}" placeholder="Catatan singkat tentang hutang ini">
                    </div>

                    <div class="row mt-4">
                        <div class="col-6">
                            <a href="/admin/hutang" class="btn btn-secondary btn-block">
                                <i class="fa fa-arrow-left"></i> Batal
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> Simpan Hutang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('inp-total').addEventListener('input', function() {
    var v = this.value.replace(/[^0-9]/g, '');
    this.value = v ? parseInt(v).toLocaleString('id-ID') : '';
});
</script>
@endsection