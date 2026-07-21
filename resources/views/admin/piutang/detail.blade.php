@extends('admin.layouts.app')
@section('content')
@php $activePage = 'piutang'; @endphp

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/home">Home</a></li>
                    <li class="breadcrumb-item"><a href="/admin/piutang">Data Piutang</a></li>
                    <li class="breadcrumb-item active">Detail Piutang</li>
                </ol>
            </nav>
            <h3 class="page-title">Detail Piutang — {{ $piutang->nama_peminjam }}</h3>
        </div>
        <div class="col-md-4 text-right">
            <a href="/admin/piutang" class="btn btn-secondary btn-sm">← Kembali</a>
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Info Piutang ── --}}
    <div class="col-md-6">
        <div class="pd-20 card-box mb-30">
            <h5 class="text-primary font-weight-bold mb-20">Informasi Piutang</h5>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="40%"><strong>Peminjam</strong></td>
                    <td>{{ $piutang->nama_peminjam }}</td>
                </tr>
                <tr>
                    <td><strong>No HP</strong></td>
                    <td>{{ $piutang->no_hp ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Keterangan</strong></td>
                    <td>{{ $piutang->keterangan ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Tanggal</strong></td>
                    <td>{{ \Carbon\Carbon::parse($piutang->tanggal)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Jatuh Tempo</strong></td>
                    <td>
                        @if($piutang->jatuh_tempo)
                            @php $jt = \Carbon\Carbon::parse($piutang->jatuh_tempo); @endphp
                            <span class="{{ ($piutang->status == 'belum' && $jt->isPast()) ? 'text-danger font-weight-bold' : '' }}">
                                {{ $jt->format('d/m/Y') }}
                                @if($piutang->status == 'belum' && $jt->isPast())
                                    <span class="badge badge-danger">⚠ Jatuh Tempo!</span>
                                @endif
                            </span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Dicatat oleh</strong></td>
                    <td>{{ $piutang->nama_kasir }}</td>
                </tr>
            </table>

            <hr>

            <div class="row text-center">
                <div class="col-4">
                    <small class="text-muted d-block">Total Piutang</small>
                    <strong class="text-dark">Rp {{ number_format($piutang->total,0,',','.') }}</strong>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Sudah Kembali</small>
                    <strong class="text-success">Rp {{ number_format($piutang->terbayar,0,',','.') }}</strong>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Sisa</small>
                    <strong class="{{ $piutang->sisa > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($piutang->sisa,0,',','.') }}
                    </strong>
                </div>
            </div>

            <div class="text-center mt-15">
                @if($piutang->status == 'lunas')
                    <span class="badge badge-success" style="font-size:14px; padding:8px 18px;">✓ LUNAS</span>
                @else
                    <span class="badge badge-danger" style="font-size:14px; padding:8px 18px;">BELUM LUNAS</span>
                @endif
            </div>

            {{-- Progress bar --}}
            @php $pct = $piutang->total > 0 ? round(($piutang->terbayar / $piutang->total) * 100) : 0; @endphp
            <div class="progress mt-15" style="height:10px;">
                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
            </div>
            <small class="text-muted">{{ $pct }}% sudah dikembalikan</small>
        </div>

        {{-- Pengeluaran terkait --}}
        @if($pengeluaran)
        <div class="pd-20 card-box mb-30" style="border-left:4px solid #e74c3c;">
            <h6 class="text-danger font-weight-bold">📤 Entri Pengeluaran Terkait</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="40%">Tanggal</td>
                    <td>{{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Keterangan</td>
                    <td>{{ $pengeluaran->keterangan }}</td>
                </tr>
                <tr>
                    <td>Metode</td>
                    <td>{{ $pengeluaran->nama_metode }}</td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><strong class="text-danger">Rp {{ number_format($pengeluaran->total,0,',','.') }}</strong></td>
                </tr>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Form & Riwayat ── --}}
    <div class="col-md-6">

        {{-- Form catat pengembalian --}}
        @if($piutang->status == 'belum')
        <div class="pd-20 card-box mb-30" id="bayar" style="border-left:4px solid #27ae60;">
            <h5 class="text-success font-weight-bold mb-15">💰 Catat Pengembalian</h5>
            <p class="text-muted" style="font-size:12px;">Catat jumlah uang yang sudah dikembalikan oleh peminjam.</p>
            <form action="/admin/piutang/bayar/{{ $piutang->id }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Jumlah Dikembalikan <span class="text-danger">*</span></label>
                    <input type="text" name="jumlah" id="jumlah_bayar" class="form-control"
                           placeholder="Rp 0"
                           max="{{ $piutang->sisa }}" required>
                    <small class="text-muted">Sisa: <strong>Rp {{ number_format($piutang->sisa,0,',','.') }}</strong></small>
                </div>
                <div class="form-group">
                    <label>Metode Penerimaan <span class="text-danger">*</span></label>
                    <select name="id_metode" class="form-control select2" required>
                        <option value="">-- Pilih Metode --</option>
                        @foreach($metode as $m)
                            <option value="{{ $m->id }}">{{ $m->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" class="form-control"
                           placeholder="Keterangan (opsional)">
                </div>
                <button type="submit" class="btn btn-success btn-sm" title="Simpan Pengembalian">
                    <i class="dw dw-check"></i> Simpan Pengembalian
                </button>
            </form>
        </div>
        @endif

        {{-- Riwayat pengembalian --}}
        <div class="pd-20 card-box mb-30">
            <h5 class="font-weight-bold mb-15">Riwayat Pengembalian</h5>
            @if($riwayat->isEmpty())
                <p class="text-muted text-center">Belum ada riwayat pengembalian.</p>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr style="background:#f5f6fa;">
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Keterangan</th>
                            <th>Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $r)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-success font-weight-bold">
                                Rp {{ number_format($r->jumlah,0,',','.') }}
                            </td>
                            <td>{{ $r->nama_metode }}</td>
                            <td>{{ $r->keterangan ?? '-' }}</td>
                            <td>{{ $r->nama_kasir }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total Kembali</th>
                            <th class="text-success">
                                Rp {{ number_format($riwayat->sum('jumlah'),0,',','.') }}
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('jumlah_bayar').addEventListener('input', function() {
    let val = this.value.replace(/\D/g, '');
    this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
});
</script>
@endsection