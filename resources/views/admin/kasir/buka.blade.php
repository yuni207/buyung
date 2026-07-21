@extends('admin.layouts.app', [
    'activePage' => 'kasir',
])

@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="title">
                    <h4>Buka Kasir</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                        <li class="breadcrumb-item"><a href="/admin/kasir">Sesi Kasir</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Buka Kasir</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="pd-20 card-box mb-30">
                <div class="clearfix">
                    <div class="pull-left">
                        <h2 class="text-primary h2">
                            <i class="icon-copy dw dw-lock-open"></i> Buka Kasir
                        </h2>
                    </div>
                    <div class="pull-right">
                        <a href="/admin/kasir" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <hr style="margin-top: 0px;">

                <p class="text-muted mb-3">
                    Masukkan jumlah uang modal awal yang ada di laci kasir saat ini. Nominal ini akan otomatis tercatat sebagai
                    <strong>Pemasukan — Modal Awal Buka Kasir</strong>.
                </p>

                <form action="/admin/kasir/buka" method="POST" id="formBukaKasir">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Modal Awal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" name="modal_awal" id="modal_awal"
                                           inputmode="numeric"
                                           class="form-control format-number @error('modal_awal') is-invalid @enderror"
                                           placeholder="0" autocomplete="off" required>
                                </div>
                                @error('modal_awal')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                <select name="id_metode" class="form-control @error('id_metode') is-invalid @enderror" required>
                                    <option value="">-- Pilih Metode --</option>
                                    @foreach($metode as $m)
                                        <option value="{{ $m->id }}" {{ old('id_metode') == $m->id ? 'selected' : '' }}>
                                            {{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_metode')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <textarea name="keterangan_buka" class="form-control" rows="3"
                                  placeholder="mis. Shift pagi, kasir A...">{{ old('keterangan_buka') }}</textarea>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="fa fa-clock-o"></i>
                        Waktu buka akan dicatat: <strong id="waktuSekarang"></strong>
                    </div>

                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fa fa-unlock"></i> Buka Kasir Sekarang
                    </button>
                    <a href="/admin/kasir" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== SCRIPTS ===== --}}
<script>
    // ========================
    // FORMAT NUMBER (Event Delegation)
    // Sama persis dengan pola di tambah.blade.php
    // ========================
    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('format-number')) {
            let val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
        }
    });

    // Hilangkan titik pemisah ribuan sebelum dikirim ke backend
    document.getElementById('formBukaKasir').addEventListener('submit', function () {
        document.querySelectorAll('.format-number').forEach(function (input) {
            input.value = input.value.replace(/\./g, '');
        });
    });

    // ========================
    // JAM REAL-TIME
    // ========================
    function updateWaktu() {
        const now = new Date();
        document.getElementById('waktuSekarang').textContent =
            now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) +
            ', ' + now.toLocaleTimeString('id-ID');
    }

    updateWaktu();
    setInterval(updateWaktu, 1000);
</script>
@endsection