@extends('admin.layouts.app', [
    'activePage' => 'kasir',
])

@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="title">
                    <h4>Tutup Kasir</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                        <li class="breadcrumb-item"><a href="/admin/kasir">Sesi Kasir</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tutup Kasir</li>
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
                            <i class="icon-copy dw dw-lock"></i> Tutup Kasir
                        </h2>
                    </div>
                    <div class="pull-right">
                        <a href="/admin/kasir" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <hr style="margin-top: 0px;">

                <div class="table-responsive mb-4">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td width="45%" class="text-muted">Kasir</td>
                                <td>{{ $sesi->nama_kasir }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Waktu Buka</td>
                                <td>{{ \Carbon\Carbon::parse($sesi->waktu_buka)->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Modal Awal</td>
                                <td class="text-primary font-weight-bold">Rp {{ number_format($sesi->modal_awal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Pemasukan Sesi</td>
                                <td class="text-success font-weight-bold">Rp {{ number_format($pemasukanSesi, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Pengeluaran Sesi</td>
                                <td class="text-danger font-weight-bold">Rp {{ number_format($pengeluaranSesi, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-light">
                                <td class="font-weight-bold">Uang yang Seharusnya Ada</td>
                                <td class="font-weight-bold text-primary" id="nilaiSeharusnya" data-nilai="{{ $seharusnya }}">
                                    Rp {{ number_format($seharusnya, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-muted mb-3">
                    <i class="fa fa-info-circle"></i>
                    <em>Uang yang seharusnya ada = Modal Awal + Pemasukan Sesi − Pengeluaran Sesi.</em>
                    Hitung uang di laci, lalu masukkan ke kolom di bawah.
                </p>

                <form action="/admin/kasir/tutup/{{ $sesi->id }}" method="POST" id="formTutupKasir">
                    @csrf

                    <div class="form-group">
                        <label>Uang Fisik di Laci <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" name="uang_akhir" id="uang_akhir"
                                   inputmode="numeric"
                                   class="form-control form-control-lg format-number @error('uang_akhir') is-invalid @enderror"
                                   placeholder="0" autocomplete="off" required>
                        </div>
                        @error('uang_akhir')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Stor ke Owner <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" name="setor_owner" id="setor_owner"
                                   inputmode="numeric"
                                   class="form-control form-control-lg format-number @error('setor_owner') is-invalid @enderror"
                                   placeholder="0" autocomplete="off" required>
                        </div>
                        @error('setor_owner')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="alert py-2 mb-3" id="previewSelisih" style="display:none;"></div>

                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <textarea name="keterangan_tutup" class="form-control" rows="3"
                                  placeholder="Catatan saat tutup kasir...">{{ old('keterangan_tutup') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-danger mr-2"
                            onclick="return confirm('Yakin ingin menutup sesi kasir?')">
                        <i class="fa fa-lock"></i> Tutup Kasir Sekarang
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

    // ========================
    // PREVIEW SELISIH
    // Ambil nilai numerik bersih (tanpa titik) langsung dari value yang sudah terformat
    // ========================
    const seharusnya  = parseInt(document.getElementById('nilaiSeharusnya').dataset.nilai) || 0;
    const inputAkhir  = document.getElementById('uang_akhir');
    const inputSetor  = document.getElementById('setor_owner');
    const previewEl   = document.getElementById('previewSelisih');

    function getNilai(input) {
        return parseInt(input.value.replace(/\./g, '')) || 0;
    }

    function hitungSelisih() {
        const uangAkhir  = getNilai(inputAkhir);
        const setorOwner = getNilai(inputSetor);
        const sisaKas    = uangAkhir - setorOwner;
        const selisih    = uangAkhir - seharusnya;

        previewEl.style.display = 'block';
        previewEl.className = 'alert py-2 mb-3 ' +
            (selisih > 0 ? 'alert-success' : selisih < 0 ? 'alert-danger' : 'alert-secondary');

        if (selisih > 0) {
            previewEl.innerHTML = '✅ Lebih Rp ' + Math.abs(selisih).toLocaleString('id-ID') + ' dari yang seharusnya. Sisa kas di laci: Rp ' + sisaKas.toLocaleString('id-ID');
        } else if (selisih < 0) {
            previewEl.innerHTML = '❌ Kurang Rp ' + Math.abs(selisih).toLocaleString('id-ID') + ' dari yang seharusnya. Sisa kas di laci: Rp ' + sisaKas.toLocaleString('id-ID');
        } else {
            previewEl.innerHTML = '✔ Pas — Tidak Ada Selisih. Sisa kas di laci: Rp ' + sisaKas.toLocaleString('id-ID');
        }
    }

    inputAkhir.addEventListener('input', hitungSelisih);
    inputSetor.addEventListener('input', hitungSelisih);

    // Hilangkan titik pemisah ribuan sebelum dikirim ke backend
    document.getElementById('formTutupKasir').addEventListener('submit', function () {
        document.querySelectorAll('.format-number').forEach(function (input) {
            input.value = input.value.replace(/\./g, '');
        });
    });
</script>
@endsection