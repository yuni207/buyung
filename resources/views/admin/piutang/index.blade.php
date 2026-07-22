@extends('admin.layouts.app', [
'activePage' => 'piutang',
])
@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title"><h4>Data Piutang</h4></div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                        <li class="breadcrumb-item active">Data Piutang</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- ── Rekap Kartu ── --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card-box pd-20 text-center" style="border-left: 4px solid #e74c3c;">
                <div class="text-muted" style="font-size:13px;">Total Piutang Belum Kembali</div>
                <div class="font-weight-bold text-danger" style="font-size:22px;">
                    Rp {{ number_format($totalSisa, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-box pd-20 text-center" style="border-left: 4px solid #27ae60;">
                <div class="text-muted" style="font-size:13px;">Total Piutang Sudah Lunas</div>
                <div class="font-weight-bold text-success" style="font-size:22px;">
                    Rp {{ number_format($totalLunas, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-box pd-20 text-center" style="border-left: 4px solid #3498db;">
                <div class="text-muted" style="font-size:13px;">Total Data Piutang</div>
                <div class="font-weight-bold text-primary" style="font-size:22px;">
                    {{ $piutang->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="pd-20 card-box mb-30">
        <div class="clearfix mb-3">
            <div class="pull-left">
                <h2 class="text-primary h2"><i class="icon-copy dw dw-wallet-1"></i> Daftar Piutang</h2>
            </div>
            <div class="pull-right">
                <a href="/admin/piutang/add" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Tambah Piutang
                </a>
            </div>
        </div>
        <hr style="margin-top:0;">

        {{-- Filter Status + Bulan --}}
        <form method="GET" action="/admin/piutang" class="row mb-3">
            <div class="col-md-3">
                <input type="month" name="bln" class="form-control form-control-sm"
                       value="{{ request('bln') }}" placeholder="Filter Bulan">
            </div>
            <div class="col-md-4">
                <div class="btn-group w-100" role="group">
                    <button type="submit" name="status" value=""
                        class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                        Semua
                    </button>
                    <button type="submit" name="status" value="belum"
                        class="btn btn-sm {{ request('status') === 'belum' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Belum Lunas
                    </button>
                    <button type="submit" name="status" value="lunas"
                        class="btn btn-sm {{ request('status') === 'lunas' ? 'btn-success' : 'btn-outline-success' }}">
                        Lunas
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <a href="/admin/piutang" class="btn btn-secondary btn-sm">Reset</a>
            </div>
            <div class="col-md-3 text-right">
                <a href="/admin/piutang/cetak?status={{ request('status') }}&bln={{ request('bln') }}" class="btn btn-dark btn-sm">
                    <i class="fa fa-download"></i> Cetak &amp; Download
                </a>
            </div>
        </form>

        
        

        <div class="table-responsive">
            <table class="table table-striped table-bordered data-table hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-center" width="5%">#</th>
                        <th class="text-center">Nama Piutang</th>
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
                    @forelse($piutang as $i => $p)
                    @php
                        $jt         = $p->jatuh_tempo ? \Carbon\Carbon::parse($p->jatuh_tempo) : null;
                        $isJatuhTpo = $jt && $p->status === 'belum' && $jt->isPast();
                    @endphp
                    <tr class="{{ $isJatuhTpo ? 'table-danger' : '' }}">
                        <td class="text-center align-middle">{{ $i + 1 }}</td>
                        <td class="align-middle">
                            <strong>{{ $p->nama_peminjam }}</strong>
                            @if($p->no_hp)
                                <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $p->no_hp }}</small>
                            @endif
                            @if($p->keterangan)
                                <br><small class="text-muted">{{ $p->keterangan }}</small>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                        </td>
                        <td class="text-center align-middle">
                            @if($jt)
                                <span class="{{ $isJatuhTpo ? 'text-danger font-weight-bold' : '' }}">
                                    {{ $jt->format('d/m/Y') }}
                                </span>
                                @if($isJatuhTpo)
                                    <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Jatuh Tempo!</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right align-middle">
                            Rp {{ number_format($p->total, 0, ',', '.') }}
                        </td>
                        <td class="text-right align-middle text-success">
                            Rp {{ number_format($p->terbayar, 0, ',', '.') }}
                        </td>
                        <td class="text-right align-middle font-weight-bold {{ $p->status === 'belum' ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($p->sisa, 0, ',', '.') }}
                        </td>
                        <td class="text-center align-middle">
                            @if($p->status === 'lunas')
                                <span class="badge badge-success" style="font-size:12px;">✔ Lunas</span>
                            @else
                                <span class="badge badge-danger" style="font-size:12px;">⚠ Belum Lunas</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            <a href="/admin/piutang/detail/{{ $p->id }}"><button class="btn btn-info btn-xs"><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="Lihat Detail"></i></button></a>
                            <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#data-{{ $p->id }}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Hapus Data"></i></button>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ MODAL HAPUS (per baris) ═══════════════════════════════════════════════ --}}
@foreach($piutang as $p)
<div class="modal fade" id="data-{{ $p->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h4 class="mb-3">Hapus Data Piutang?</h4>
                <p class="text-muted">
                    Peminjam: <strong>{{ $p->nama_peminjam }}</strong><br>
                    Total: <strong>Rp {{ number_format($p->total, 0, ',', '.') }}</strong>
                </p>
                <p class="text-danger small">Data pengeluaran terkait juga akan dihapus.</p>
                <div class="row mt-3">
                    <div class="col-6">
                        <a href="/admin/piutang/delete/{{ $p->id }}" class="btn btn-danger btn-block">Ya, Hapus</a>
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

@endsection