@extends('admin.layouts.app', [
    'activePage' => 'kasir',
])

@section('content')
<div class="min-height-200px">
    @php
        $sesiAktif = \Illuminate\Support\Facades\DB::table('kasir_session')
            ->where('id_user', Auth::id())
            ->where('status', 'buka')
            ->first();
    @endphp

    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Riwayat Sesi Kasir</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sesi Kasir</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                <div class="d-flex align-items-center justify-content-end">
                    @if($sesiAktif)
                        <a href="/admin/kasir/tutup/{{ $sesiAktif->id }}" class="btn btn-danger btn-sm mr-2">
                            <i class="fa fa-lock"></i> Tutup Kasir
                        </a>
                    @else
                        <a href="/admin/kasir/buka" class="btn btn-success btn-sm">
                            <i class="fa fa-unlock"></i> Buka Kasir
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    

    

    <div class="pd-20 card-box mb-30">
        <div class="clearfix">
            <div class="pull-left">
                <h2 class="text-primary h2">
                    <i class="icon-copy dw dw-briefcase"></i> Daftar Sesi Kasir
                </h2>
            </div>
            <div class="pull-right">
                <form method="GET" action="/admin/kasir" class="d-flex align-items-end">
                    <div class="form-group mb-0 mr-2">
                        <label class="mb-1">Filter Bulan</label>
                        <input type="month" name="bln" class="form-control"
                               value="{{ request('bln', date('Y-m')) }}">
                    </div>
                    <button class="btn btn-primary btn-sm mr-2">
                        <i class="fa fa-filter"></i> Tampilkan
                    </button>
                    <a href="/admin/kasir" class="btn btn-secondary btn-sm">Reset</a>
                </form>
            </div>
        </div>
        <hr style="margin-top: 0px;">

        @if($sesiAktif)
            <div class="alert alert-warning mb-3">
                <strong>Kasir sedang terbuka</strong> sejak
                <strong>{{ \Carbon\Carbon::parse($sesiAktif->waktu_buka)->format('d M Y, H:i') }}</strong>
                — Modal Awal: <strong>Rp {{ number_format($sesiAktif->modal_awal, 0, ',', '.') }}</strong>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-bordered hover mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="5%">#</th>
                        <th>Kasir</th>
                        <th>Waktu Buka</th>
                        <th>Waktu Tutup</th>
                        <th class="text-right">Modal Awal</th>
                        <th class="text-right">Pemasukan Sesi</th>
                        <th class="text-right">Uang Akhir</th>
                        <th class="text-right">Stor ke Owner</th>
                        <th class="text-right">Sisa Kas di Laci</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesi as $s)
                        @php
                            $selisih = $s->uang_akhir !== null
                                ? ($s->uang_akhir - ($s->modal_awal + $s->pemasukan_sesi - ($s->pengeluaran_sesi ?? 0)))
                                : null;
                            $sisaKas = ($s->status === 'tutup' && $s->uang_akhir !== null)
                                ? ($s->uang_akhir - ($s->setor_owner ?? 0))
                                : null;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $s->nama_kasir }}</td>
                            <td>{{ \Carbon\Carbon::parse($s->waktu_buka)->format('d M Y H:i') }}</td>
                            <td>
                                @if($s->waktu_tutup)
                                    {{ \Carbon\Carbon::parse($s->waktu_tutup)->format('d M Y H:i') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">Rp {{ number_format($s->modal_awal, 0, ',', '.') }}</td>
                            <td class="text-right">
                                @if($s->status === 'tutup')
                                    Rp {{ number_format($s->pemasukan_sesi, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">belum dihitung</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($s->uang_akhir !== null)
                                    Rp {{ number_format($s->uang_akhir, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($s->status === 'tutup')
                                    Rp {{ number_format($s->setor_owner ?? 0, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($sisaKas !== null)
                                    @if($sisaKas > 0)
                                        <span class="text-success font-weight-bold">
                                            Rp {{ number_format($sisaKas, 0, ',', '.') }}
                                        </span>
                                    @elseif($sisaKas < 0)
                                        <span class="text-danger font-weight-bold">
                                            -Rp {{ number_format(abs($sisaKas), 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted font-weight-bold">Rp 0</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->status === 'buka')
                                    <span class="badge badge-success">Buka</span>
                                @else
                                    <span class="badge badge-secondary">Tutup</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-xs">
                                    <a href="/admin/kasir/detail/{{ $s->id }}" class="btn btn-outline-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($s->status === 'buka' && (Auth::user()->level == '1' || $s->id_user == Auth::id()))
                                        <a href="/admin/kasir/tutup/{{ $s->id }}" class="btn btn-outline-danger">
                                            <i class="fa fa-lock"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Belum ada data sesi kasir.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection