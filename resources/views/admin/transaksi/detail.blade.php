@extends('admin.layouts.app', [
'activePage' => 'transaksi',
])
@section('content')
<div class="min-height-200px">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
                <div class="title"><h4>Detail Transaksi</h4></div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                        <li class="breadcrumb-item"><a href="/admin/transaksi">Riwayat Transaksi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="pd-20 card-box mb-30">
                <div class="clearfix mb-3">
                    <div class="pull-left">
                        <h2 class="text-primary h2">
                            <i class="icon-copy dw dw-checked"></i> Struk Transaksi
                        </h2>
                    </div>
                    <div class="pull-right">
                        <a href="/admin/transaksi" class="btn btn-secondary btn-sm mr-1">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <a href="http://buyung.com/print-bill-detail/{{ $transaksi->id }}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fa fa-print"></i> Print Struk
                        </a>
                    </div>
                </div>
                <hr style="margin-top:0">

                {{-- ── Header Struk ── --}}
                <div class="text-center mb-3" id="struk-area">
                    <h4 class="mb-0 font-weight-bold">Toko Buyung</h4>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d F Y, H:i') }}
                    </small>
                    <br>
                    <span class="badge badge-primary mt-1">{{ $transaksi->nama }}</span>
                </div>

                <hr>

                {{-- ── Daftar Item ── --}}
                <table class="table table-sm table-borderless">
                    <thead>
                        <tr class="border-bottom">
                            <th>Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($details as $d)
                        <tr>
                            <td>{{ $d->nama_barang }}</td>
                            <td class="text-center">{{ $d->jumlah }}</td>
                            <td class="text-right">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Tidak ada item</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <hr>

                {{-- ── Ringkasan Harga ── --}}
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Subtotal</td>
                        <td class="text-right">Rp {{ number_format($transaksi->total + ($transaksi->potongan ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @if(($transaksi->potongan ?? 0) > 0)
                    <tr>
                        <td class="text-muted">Potongan</td>
                        <td class="text-right text-danger">
                            - Rp {{ number_format($transaksi->potongan, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td class="font-weight-bold" style="font-size:15px;">Total</td>
                        <td class="text-right font-weight-bold text-primary" style="font-size:15px;">
                            Rp {{ number_format($transaksi->total, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bayar</td>
                        <td class="text-right">Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Kembalian</td>
                        <td class="text-right font-weight-bold text-success">
                            Rp {{ number_format($transaksi->kembali, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                <hr>

                {{-- ── Footer Info ── --}}
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Metode Pembayaran</td>
                        <td class="text-right">
                            <span class="badge badge-secondary">
                                {{ $transaksi->nama_metode ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kasir</td>
                        <td class="text-right">{{ $transaksi->kasir ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td class="text-right">
                            @if(($transaksi->is_hutang ?? 0) == 2)
                                <span class="badge badge-warning">Hutang</span>
                            @elseif(($transaksi->is_hutang ?? 0) == 1)
                                <span class="badge badge-danger">Belum Bayar</span>
                            @else
                                <span class="badge badge-success">Lunas</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="text-center mt-3">
                    <small class="text-muted">Terima kasih telah berbelanja!</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection