@extends('admin.layouts.app', [
    'activePage' => 'kasir',
])

@section('content')
<div class="min-height-200px">
    @php
        $totalPemasukan = $listPemasukan->sum('total');
        $totalPengeluaran = $listPengeluaran->sum('total');
        $selisih = $sesi->uang_akhir !== null
            ? ($sesi->uang_akhir - $seharusnya)
            : null;
        $sisaKas = $sesi->uang_akhir !== null
            ? ($sesi->uang_akhir - ($sesi->setor_owner ?? 0))
            : null;
    @endphp

    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Detail Sesi Kasir</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                        <li class="breadcrumb-item"><a href="/admin/kasir">Sesi Kasir</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail #{{ $sesi->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                <a href="/admin/kasir" class="btn btn-primary btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-20">
            <div class="pd-20 card-box text-center h-100">
                <h5 class="text-primary mb-2"><i class="fa fa-money"></i> Modal Awal</h5>
                <div class="h4 text-primary mb-0">Rp {{ number_format($sesi->modal_awal, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-20">
            <div class="pd-20 card-box text-center h-100">
                <h5 class="text-success mb-2"><i class="fa fa-arrow-down"></i> Pemasukan Sesi</h5>
                <div class="h4 text-success mb-0">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-20">
            <div class="pd-20 card-box text-center h-100">
                <h5 class="text-danger mb-2"><i class="fa fa-arrow-up"></i> Pengeluaran Sesi</h5>
                <div class="h4 text-danger mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-20">
            <div class="pd-20 card-box text-center h-100">
                <h5 class="text-warning mb-2"><i class="fa fa-balance-scale"></i> Seharusnya Ada</h5>
                <div class="h4 text-warning mb-0">Rp {{ number_format($seharusnya, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="pd-20 card-box mb-30">
        <div class="clearfix">
            <div class="pull-left">
                <h2 class="text-primary h2">
                    <i class="icon-copy dw dw-info"></i> Informasi Sesi
                </h2>
            </div>
            <div class="pull-right">
                @if($sesi->status === 'buka')
                    <span class="badge badge-success">Sesi Aktif</span>
                @else
                    <span class="badge badge-secondary">Sesi Tutup</span>
                @endif
            </div>
        </div>
        <hr style="margin-top: 0px;">

        <div class="row">
            <div class="col-md-4 mb-20">
                <div class="text-muted mb-1">Kasir</div>
                <div class="font-weight-bold">{{ $sesi->nama_kasir }}</div>
            </div>
            <div class="col-md-4 mb-20">
                <div class="text-muted mb-1">Waktu Buka</div>
                <div class="font-weight-bold">{{ \Carbon\Carbon::parse($sesi->waktu_buka)->format('d M Y, H:i') }}</div>
                @if($sesi->keterangan_buka)
                    <div class="text-muted mt-1">{{ $sesi->keterangan_buka }}</div>
                @endif
            </div>
            <div class="col-md-4 mb-20">
                <div class="text-muted mb-1">Waktu Tutup</div>
                @if($sesi->waktu_tutup)
                    <div class="font-weight-bold">{{ \Carbon\Carbon::parse($sesi->waktu_tutup)->format('d M Y, H:i') }}</div>
                    @if($sesi->keterangan_tutup)
                        <div class="text-muted mt-1">{{ $sesi->keterangan_tutup }}</div>
                    @endif
                @else
                    <div class="text-muted">Belum ditutup</div>
                @endif
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4 mb-20">
                <div class="text-muted mb-1">Uang Fisik</div>
                @if($sesi->uang_akhir !== null)
                    <div class="font-weight-bold">Rp {{ number_format($sesi->uang_akhir, 0, ',', '.') }}</div>
                @else
                    <div class="text-muted">Belum ditutup</div>
                @endif
            </div>
            <div class="col-md-4 mb-20">
                <div class="text-muted mb-1">Setor ke Owner</div>
                @if($sesi->status === 'tutup')
                    <div class="font-weight-bold text-danger">Rp {{ number_format($sesi->setor_owner ?? 0, 0, ',', '.') }}</div>
                @else
                    <div class="text-muted">Belum ditutup</div>
                @endif
            </div>
            <div class="col-md-4 mb-20">
                <div class="text-muted mb-1">Sisa Kas di Laci</div>
                @if($sisaKas !== null)
                    <div class="font-weight-bold {{ $sisaKas < 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($sisaKas, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">(Uang Fisik − Setor Owner)</small>
                @else
                    <div class="text-muted">Belum ditutup</div>
                @endif
            </div>
        </div>
    </div>

    <div class="pd-20 card-box mb-30">
        <div class="clearfix">
            <div class="pull-left">
                <h2 class="text-success h2 mb-0">
                    <i class="fa fa-arrow-down"></i> Pemasukan Selama Sesi
                </h2>
            </div>
            <div class="pull-right">
                <span class="badge badge-success">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span>
            </div>
        </div>
        <hr style="margin-top: 0px;">
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead class="bg-success text-white">
                    <tr>
                        <th width="5%">#</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Metode</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listPemasukan as $p)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                            <td>{{ $p->keterangan }}</td>
                            <td>{{ $p->nama_metode ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Tidak ada pemasukan dalam sesi ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pd-20 card-box mb-30">
        <div class="clearfix">
            <div class="pull-left">
                <h2 class="text-danger h2 mb-0">
                    <i class="fa fa-arrow-up"></i> Pengeluaran Selama Sesi
                </h2>
            </div>
            <div class="pull-right">
                <span class="badge badge-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
            </div>
        </div>
        <hr style="margin-top: 0px;">
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead class="bg-danger text-white">
                    <tr>
                        <th width="5%">#</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Metode</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listPengeluaran as $pe)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($pe->tanggal)->format('d M Y') }}</td>
                            <td>{{ $pe->keterangan }}</td>
                            <td>{{ $pe->nama_metode ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($pe->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Tidak ada pengeluaran dalam sesi ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
