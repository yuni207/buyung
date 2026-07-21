@extends('admin.layouts.app', [
'activePage' => 'piutang',
])
@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title"><h4>Tambah Piutang Baru</h4></div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                        <li class="breadcrumb-item"><a href="/admin/piutang">Data Piutang</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="pd-20 card-box mb-30">
                <h2 class="text-primary h2 mb-3">
                    <i class="icon-copy dw dw-add-file-1"></i> Form Piutang Baru
                </h2>
                <hr style="margin-top:0;">
                <p class="text-muted small mb-3">
                    Mencatat piutang akan <strong>otomatis membuat entri pengeluaran</strong> sesuai jumlah uang
                    yang dipinjamkan. Ketika piutang dikembalikan, uangnya akan masuk ke <strong>pemasukan</strong>.
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

                <form action="/admin/piutang/create" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Piutang <span class="text-danger">*</span></label>
                                <input type="text" name="nama_peminjam" class="form-control"
                                       value="{{ old('nama_peminjam') }}" placeholder="Nama piutang" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. HP <small class="text-muted">(opsional)</small></label>
                                <input type="text" name="no_hp" class="form-control"
                                       value="{{ old('no_hp') }}" placeholder="08xx..." maxlength="12">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Piutang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="text" name="total" id="inp-total" class="form-control"
                                   value="{{ old('total') }}" placeholder="0" required autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Peminjaman <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control"
                                       value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jatuh Tempo <small class="text-muted">(opsional)</small></label>
                                <input type="date" name="jatuh_tempo" class="form-control"
                                       value="{{ old('jatuh_tempo') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Metode Pengeluaran <span class="text-danger">*</span>
                            <small class="text-muted">(uang keluar via)</small>
                        </label>
                        <select name="id_metode" class="form-control select2" required>
                            <option value="">-- Pilih Metode --</option>
                            @foreach($metode as $m)
                                <option value="{{ $m->id }}" {{ old('id_metode') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            Otomatis masuk ke laporan pengeluaran.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="keterangan" class="form-control"
                               value="{{ old('keterangan') }}" placeholder="Catatan singkat tentang piutang ini">
                    </div>

                    <div class="row mt-4">
                        <div class="col-6">
                            <a href="/admin/piutang" class="btn btn-secondary btn-block">
                                <i class="fa fa-arrow-left"></i> Batal
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> Simpan Piutang
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