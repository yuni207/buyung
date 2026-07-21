@extends('admin.layouts.app', [
'activePage' => 'transaksi',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Transaksi</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Kasir</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Riwayat Transaksi</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <!-- Table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> Riwayat Transaksi</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/transaksi/add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Transaksi Baru</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">
      
      
      @if(request('hutang') == '1' && request('kode'))
      <div class="alert alert-warning alert-dismissible">
         <i class="fa fa-exclamation-circle"></i>
         Hutang berhasil dicatat! Kode transaksi: <strong>{{ request('kode') }}</strong>
         — Kelola di menu <a href="/admin/hutang" class="alert-link">Data Hutang</a>.
         <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
      </div>
      @endif
      <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
            <tr>
               <th class="align-middle text-center" width="5%">#</th>
               <th class="align-middle text-center">Kode</th>
               <th class="align-middle text-center">Tanggal</th>
               <th class="align-middle text-center">Metode</th>
               <th class="align-middle text-center">Total</th>
               <th class="align-middle text-center">Kasir</th>
               <th class="align-middle text-center">Status</th>
               <th class="table-plus datatable-nosort text-center align-middle">Action</th>
            </tr>
         </thead>
         <tbody>
            @forelse($transaksi as $i => $data)
            <tr>
               <td class="text-center align-middle">{{ $i+1 }}</td>
               <td class="align-middle">
                  <span class="badge badge-primary">{{ $data->kode_transaksi }}</span>
               </td>
               <td class="align-middle">
                  {{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}
               </td>
               <td class="text-center align-middle">{{ $data->nama_metode ?? '-' }}</td>
               <td class="text-right align-middle font-weight-bold">
                  Rp {{ number_format($data->total, 0, ',', '.') }}
               </td>
               <td class="text-center align-middle">{{ $data->kasir ?? '-' }}</td>
               <td class="text-center align-middle">
                  @if($data->is_hutang == 2)
                     <span class="badge badge-warning">Hutang</span>
                  @elseif($data->is_hutang == 1)
                     <span class="badge badge-danger">Belum Bayar</span>
                  @else
                     <span class="badge badge-success">Lunas</span>
                  @endif
               </td>
               <td class="text-center align-middle">
                  <a href="/admin/transaksi/detail/{{ $data->id }}">
                     <button class="btn btn-info btn-xs">
                        <i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="Detail Data"></i>
                     </button>
                  </a>
                  <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#data-{{ $data->id }}">
                     <i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Hapus Data"></i>
                  </button>
               </td>
            </tr>
            @empty
            @endforelse
         </tbody>
      </table>
   </div>
   <!-- Table End -->
</div>

<!-- Modal -->
@foreach($transaksi as $data)
<div class="modal fade" id="data-{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $data->id }}" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">
               Apakah Anda Yakin Menghapus Data Ini ?
            </h2>
            <hr>
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Kode Transaksi</label>
                     <input class="form-control" value="{{ $data->kode_transaksi }}" readonly style="background-color: white; pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Tanggal</label>
                     <input class="form-control" value="{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}" readonly style="background-color: white; pointer-events: none;">
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Metode</label>
                     <input class="form-control" value="{{ $data->nama_metode ?? '-' }}" readonly style="background-color: white; pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Total</label>
                     <input class="form-control" value="Rp {{ number_format($data->total, 0, ',', '.') }}" readonly style="background-color: white; pointer-events: none;">
                  </div>
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-md-6">
                  <a href="/admin/transaksi/delete/{{ $data->id }}" style="text-decoration: none;">
                     <button type="button" class="btn btn-primary btn-block">Ya</button>
                  </a>
               </div>
               <div class="col-md-6">
                  <button type="button" class="btn btn-danger btn-block" data-dismiss="modal" aria-label="Close">Tidak</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endforeach
@endsection