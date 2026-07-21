@extends('admin.layouts.app', [
'activePage' => 'hutang',
])
@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title"><h4>Data Hutang</h4></div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                        <li class="breadcrumb-item active">Data Hutang</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- ── Rekap Kartu ── --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card-box pd-20 text-center" style="border-left: 4px solid #e74c3c;">
                <div class="text-muted" style="font-size:13px;">Total Hutang Belum Lunas</div>
                <div class="font-weight-bold text-danger" style="font-size:22px;">
                    Rp {{ number_format($totalSisa, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-box pd-20 text-center" style="border-left: 4px solid #27ae60;">
                <div class="text-muted" style="font-size:13px;">Total Hutang Sudah Lunas</div>
                <div class="font-weight-bold text-success" style="font-size:22px;">
                    Rp {{ number_format($totalLunas, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-box pd-20 text-center" style="border-left: 4px solid #3498db;">
                <div class="text-muted" style="font-size:13px;">Total Data Hutang</div>
                <div class="font-weight-bold text-primary" style="font-size:22px;">
                    {{ $hutang->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="pd-20 card-box mb-30">
        <div class="clearfix mb-3">
            <div class="pull-left">
                <h2 class="text-primary h2"><i class="icon-copy dw dw-wallet-1"></i> Daftar Hutang</h2>
            </div>
            <div class="pull-right">
                <a href="/admin/hutang/add" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Tambah Hutang
                </a>
            </div>
        </div>
        <hr style="margin-top:0;">

        {{-- Filter Status --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="btn-group w-100" role="group">
                    <a href="/admin/hutang" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                        Semua
                    </a>
                    <a href="/admin/hutang?status=belum" class="btn btn-sm {{ request('status') === 'belum' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Belum Lunas
                    </a>
                    <a href="/admin/hutang?status=lunas" class="btn btn-sm {{ request('status') === 'lunas' ? 'btn-success' : 'btn-outline-success' }}">
                        Lunas
                    </a>
                </div>
            </div>
        </div>

        
        

        <div class="table-responsive">
            <table class="table table-striped table-bordered data-table hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-center" width="5%">#</th>
                        <th class="text-center">Pelanggan</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Jatuh Tempo</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Terbayar</th>
                        <th class="text-center">Sisa</th>
                        <th class="text-center">Status</th>
                        <th class="text-center datatable-nosort">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hutang as $i => $data)
                    @php
                        $sisa       = $data->total - $data->terbayar;
                        $isJatuhTpo = $data->jatuh_tempo && $data->status === 'belum' && $data->jatuh_tempo < date('Y-m-d');
                    @endphp
                    <tr class="{{ $isJatuhTpo ? 'table-danger' : '' }}">
                        <td class="text-center align-middle">{{ $i + 1 }}</td>
                        <td class="align-middle">
                            <strong>{{ $data->nama_pelanggan }}</strong>
                            @if($data->no_hp)
                                <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $data->no_hp }}</small>
                            @endif
                            @if($data->id_transaksi)
                                <br><span class="badge badge-secondary" style="font-size:10px;">dari transaksi</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            {{ \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') }}
                        </td>
                        <td class="text-center align-middle">
                            @if($data->jatuh_tempo)
                                <span class="{{ $isJatuhTpo ? 'text-danger font-weight-bold' : '' }}">
                                    {{ \Carbon\Carbon::parse($data->jatuh_tempo)->format('d/m/Y') }}
                                </span>
                                @if($isJatuhTpo)
                                    <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Jatuh Tempo!</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right align-middle">
                            Rp {{ number_format($data->total, 0, ',', '.') }}
                        </td>
                        <td class="text-right align-middle text-success">
                            Rp {{ number_format($data->terbayar, 0, ',', '.') }}
                        </td>
                        <td class="text-right align-middle font-weight-bold {{ $data->status === 'belum' ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($sisa, 0, ',', '.') }}
                        </td>
                        <td class="text-center align-middle">
                            @if($data->status === 'lunas')
                                <span class="badge badge-success" style="font-size:12px;">✔ Lunas</span>
                            @else
                                <span class="badge badge-danger" style="font-size:12px;">⚠ Belum Lunas</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            <a href="/admin/hutang/detail/{{ $data->id }}"><button class="btn btn-info btn-xs"><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="Lihat Detail"></i></button></a>
                            @if($data->status === 'belum')
                            <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#bayar-{{ $data->id }}"><i class="fa fa-money" data-toggle="tooltip" data-placement="top" title="Catat Pembayaran"></i></button>
                            @endif
                            <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#data-{{ $data->id }}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Hapus Data"></i></button>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Ambil daftar metode sekali untuk semua modal ── --}}
@php $metodes = \Illuminate\Support\Facades\DB::table('metode')->orderBy('nama')->get(); @endphp

{{-- ═══ MODAL BAYAR (per baris) ═════════════════════════════════════════════ --}}
@foreach($hutang as $data)
@if($data->status === 'belum')
@php $sisa = $data->total - $data->terbayar; @endphp
<div class="modal fade" id="bayar-{{ $data->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/admin/hutang/bayar/{{ $data->id }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa fa-money"></i> Catat Pembayaran Hutang</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        <strong>{{ $data->nama_pelanggan }}</strong>
                        &nbsp;|&nbsp; Sisa: <strong class="text-danger">Rp {{ number_format($sisa, 0, ',', '.') }}</strong>
                    </p>
                    <div class="form-group">
                        <label>Jumlah Bayar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="text" name="jumlah" class="form-control inp-bayar-modal"
                                   placeholder="0" required autocomplete="off"
                                   data-max="{{ $sisa }}">
                        </div>
                        <small class="text-muted">Maksimal: Rp {{ number_format($sisa, 0, ',', '.') }}</small>
                    </div>
                    <div class="form-group">
                        <label>Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="id_metode" class="form-control" required>
                            <option value="">-- Pilih Metode --</option>
                            @foreach($metodes as $m)
                            <option value="{{ $m->id }}">{{ $m->nama }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            Otomatis masuk ke laporan pemasukan.
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Misal: bayar sebagian">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal Hapus --}}
<div class="modal fade" id="data-{{ $data->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h4 class="mb-3">Hapus Data Hutang?</h4>
                <p class="text-muted">
                    Pelanggan: <strong>{{ $data->nama_pelanggan }}</strong><br>
                    Total: <strong>Rp {{ number_format($data->total, 0, ',', '.') }}</strong>
                </p>
                <p class="text-danger small">Hutang yang sudah ada riwayat pembayaran tidak bisa dihapus.</p>
                <div class="row mt-3">
                    <div class="col-6">
                        <a href="/admin/hutang/delete/{{ $data->id }}" class="btn btn-danger btn-block">Ya, Hapus</a>
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

<script>
// Format angka di input modal bayar
document.querySelectorAll('.inp-bayar-modal').forEach(function(el) {
    el.addEventListener('input', function() {
        var max = parseInt(this.getAttribute('data-max') || '0');
        var val = parseInt(this.value.replace(/[^0-9]/g, '') || '0');
        if (val > max) val = max;
        this.value = val > 0 ? val.toLocaleString('id-ID') : '';
    });
});
</script>
@endsection