@extends('admin.layouts.app', [
'activePage' => 'barang_masuk',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Data Barang Masuk</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Barang Masuk</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> List Data Barang Masuk</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang_masuk/add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Data</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">
      
      
      <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
            <tr>
               <th class="text-center" width="5%">#</th>
               <th class="text-center">Tanggal</th>
               <th class="text-center">Keterangan</th>
               <th class="text-center">Jumlah</th>
               <th class="table-plus datatable-nosort text-center">Action</th>
            </tr>
         </thead>
         <tbody>
            @forelse($barang_masuk as $no => $data)
            <tr>
               <td class="text-center">{{ $no + 1 }}</td>
               <td>{{ date('d M Y', strtotime($data->tanggal)) }}</td>
               <td>{{ $data->keterangan }}</td>
               <td class="text-center">{{ number_format($data->jumlah, 0, ',', '.') }}</td>
               <td class="text-center" width="15%">
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
</div>

@foreach($barang_masuk as $data)
<div class="modal fade" id="data-{{ $data->id }}" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-center">Apakah Anda Yakin Menghapus Data Ini?</h2>
            <hr>
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group">
                     <label>Tanggal</label>
                     <input class="form-control" value="{{ date('d M Y', strtotime($data->tanggal)) }}" readonly style="background-color: #e9ecef; pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group">
                     <label>Jumlah</label>
                     <input class="form-control" value="{{ number_format($data->jumlah, 0, ',', '.') }}" readonly style="background-color: #e9ecef; pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="form-group">
                     <label>Keterangan</label>
                     <input class="form-control" value="{{ $data->keterangan }}" readonly style="background-color: #e9ecef; pointer-events: none;">
                  </div>
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-md-6">
                  <a href="/admin/barang_masuk/delete/{{ $data->id }}" style="text-decoration: none;">
                     <button type="button" class="btn btn-primary btn-block">Ya, Hapus</button>
                  </a>
               </div>
               <div class="col-md-6">
                  <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">Tidak</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endforeach
@endsection