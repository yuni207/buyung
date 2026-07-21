@extends('admin.layouts.app', [
'activePage' => 'hutang',
])
@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
                <div class="title"><h4>Detail Hutang</h4></div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                        <li class="breadcrumb-item"><a href="/admin/hutang">Data Hutang</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 col-sm-12 text-right">
                <a href="/admin/hutang" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                @if($hutang->status === 'belum')
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-bayar" title="Catat Pembayaran Hutang">
                    <i class="fa fa-money"></i> Catat Pembayaran
                </button>
                @endif
            </div>
        </div>
    </div>

    
    

    <div class="row">
        {{-- ── Kartu Info Hutang ── --}}
        <div class="col-lg-5">
            <div class="pd-20 card-box mb-30">
                <h2 class="text-primary h2 mb-3">
                    <i class="icon-copy dw dw-user1"></i> Info Pelanggan
                </h2>
                <hr style="margin-top:0;">

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">Nama</td>
                        <td class="font-weight-bold">{{ $hutang->nama_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. HP</td>
                        <td>{{ $hutang->no_hp ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($hutang->tanggal)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jatuh Tempo</td>
                        <td>
                            @if($hutang->jatuh_tempo)
                                @php $lewat = $hutang->jatuh_tempo < date('Y-m-d') && $hutang->status === 'belum'; @endphp
                                <span class="{{ $lewat ? 'text-danger font-weight-bold' : '' }}">
                                    {{ \Carbon\Carbon::parse($hutang->jatuh_tempo)->format('d F Y') }}
                                </span>
                                @if($lewat) <span class="badge badge-danger ml-1">Lewat!</span> @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td>{{ $hutang->keterangan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dicatat oleh</td>
                        <td>{{ $hutang->nama_kasir ?? '-' }}</td>
                    </tr>
                    @if($hutang->id_transaksi)
                    <tr>
                        <td class="text-muted">Transaksi</td>
                        <td>
                            <a href="/admin/transaksi/detail/{{ $hutang->id_transaksi }}">
                                <span class="badge badge-secondary">Lihat Transaksi</span>
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>

                <hr>

                {{-- ── Ringkasan Keuangan ── --}}
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Hutang</td>
                        <td class="text-right font-weight-bold" style="font-size:15px;">
                            Rp {{ number_format($hutang->total, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sudah Dibayar</td>
                        <td class="text-right text-success font-weight-bold">
                            Rp {{ number_format($hutang->terbayar, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="border-top">
                        <td class="font-weight-bold" style="font-size:16px;">Sisa Hutang</td>
                        <td class="text-right font-weight-bold {{ $hutang->status === 'lunas' ? 'text-success' : 'text-danger' }}" style="font-size:16px;">
                            Rp {{ number_format($hutang->sisa, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                {{-- Progress bar pembayaran --}}
                @php
                    $pct = $hutang->total > 0 ? min(100, round($hutang->terbayar / $hutang->total * 100)) : 0;
                @endphp
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progres Pelunasan</small>
                        <small class="font-weight-bold">{{ $pct }}%</small>
                    </div>
                    <div class="progress" style="height:10px;">
                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-warning' }}"
                             style="width: {{ $pct }}%;"></div>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    @if($hutang->status === 'lunas')
                        <span class="badge badge-success" style="font-size:14px; padding:8px 16px;">✔ LUNAS</span>
                    @else
                        <span class="badge badge-danger" style="font-size:14px; padding:8px 16px;">⚠ BELUM LUNAS</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Riwayat Pembayaran ── --}}
        <div class="col-lg-7">
            <div class="pd-20 card-box mb-30">
                <h2 class="text-primary h2 mb-3">
                    <i class="icon-copy dw dw-list"></i> Riwayat Pembayaran
                </h2>
                <hr style="margin-top:0;">

                @if($riwayat->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                    Belum ada riwayat pembayaran
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center" width="5%">#</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-right">Jumlah Bayar</th>
                                <th>Keterangan</th>
                                <th class="text-center">Kasir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riwayat as $i => $byr)
                            <tr>
                                <td class="text-center align-middle">{{ $i + 1 }}</td>
                                <td class="text-center align-middle">
                                    {{ \Carbon\Carbon::parse($byr->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-right align-middle font-weight-bold text-success">
                                    Rp {{ number_format($byr->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="align-middle">{{ $byr->keterangan ?: '-' }}</td>
                                <td class="text-center align-middle">{{ $byr->nama_kasir ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="2" class="text-right font-weight-bold">Total Dibayar</td>
                                <td class="text-right font-weight-bold text-success">
                                    Rp {{ number_format($riwayat->sum('jumlah'), 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

            {{-- Detail Transaksi Asal (jika dari transaksi) --}}
            @if($transaksi)
            <div class="pd-20 card-box mb-30">
                <h2 class="text-primary h2 mb-3">
                    <i class="icon-copy dw dw-newspaper-1"></i> Transaksi Asal
                </h2>
                <hr style="margin-top:0;">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Kode</td>
                        <td><span class="badge badge-primary">{{ $transaksi->nama }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total</td>
                        <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                    </tr>
                </table>
                <a href="/admin/transaksi/detail/{{ $transaksi->id }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-eye"></i> Lihat Detail Transaksi
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══ MODAL BAYAR ══════════════════════════════════════════════════════════ --}}
@php
    $sisa    = $hutang->total - $hutang->terbayar;
    $metodes = \Illuminate\Support\Facades\DB::table('metode')->orderBy('nama')->get();
@endphp
<div class="modal fade" id="modal-bayar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/admin/hutang/bayar/{{ $hutang->id }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa fa-money"></i> Catat Pembayaran Hutang</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Pelanggan: <strong>{{ $hutang->nama_pelanggan }}</strong><br>
                        Sisa Hutang: <strong class="text-danger">Rp {{ number_format($sisa, 0, ',', '.') }}</strong>
                    </p>

                    <div class="form-group">
                        <label>Jumlah Bayar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="text" name="jumlah" id="inp-bayar-detail"
                                   class="form-control" placeholder="0" required autocomplete="off">
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
                            Pembayaran ini akan otomatis masuk ke laporan pemasukan.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Misal: cicilan ke-2">
                    </div>

                    {{-- Tombol cepat --}}
                    <div class="mb-2">
                        <small class="text-muted d-block mb-1">Bayar cepat:</small>
                        <button type="button" class="btn btn-outline-success btn-sm mr-1 btn-quick-bayar" data-val="{{ $sisa }}">
                            Lunas (Rp {{ number_format($sisa, 0, ',', '.') }})
                        </button>
                        @if($sisa > 10000)
                        <button type="button" class="btn btn-outline-warning btn-sm btn-quick-bayar" data-val="{{ round($sisa / 2) }}">
                            50% (Rp {{ number_format(round($sisa / 2), 0, ',', '.') }})
                        </button>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Format input bayar di modal
var inpBayar = document.getElementById('inp-bayar-detail');
if (inpBayar) {
    inpBayar.addEventListener('input', function() {
        var v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? parseInt(v).toLocaleString('id-ID') : '';
    });
}

// Tombol bayar cepat
document.querySelectorAll('.btn-quick-bayar').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var val = parseInt(this.getAttribute('data-val') || '0');
        if (inpBayar) inpBayar.value = val.toLocaleString('id-ID');
    });
});
</script>
@endsection