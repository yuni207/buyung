@extends('admin.layouts.app', [
'activePage' => 'setor_tarik',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Data Setor & Tarik</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Setor & Tarik</li>
               </ol>
            </nav>
         </div>
         <div class="col-md-6 col-sm-12 text-right">
            <div class="d-flex align-items-center justify-content-end">
               <div class="form-group mb-0">
                  <input type="month" required class="form-control" onchange="location = '/admin/setor_tarik/filter/'+this.value;" name="bln" value="{{ $bln }}">
               </div>
            </div>
         </div>
      </div>
   </div>


   <!-- Striped table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> List Data Setor & Tarik</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/setor_tarik/add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Data</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">

      <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
            <tr>
               <th width="5%">#</th>
               <th>Tanggal</th>
               <th>Nama Pelanggan</th>
               <th>Keterangan</th>
               @if(Auth::User()->level == '1')
               <th class="text-center">Penginput</th>
               @endif
               <th class="text-center">Jenis</th>
               <th class="text-center" width="15%">Metode</th>
               <th class="text-center" width="10%">Bukti</th>
               <th class="text-center" width="12%">Biaya Admin</th>
               <th class="text-center" width="15%">Total</th>
               <th class="table-plus datatable-nosort text-center">Action</th>
            </tr>
         </thead>
         <tbody>
            <?php $no = 1; ?>
            @foreach($data as $row)
            <tr>
               <td class="text-center">{{ $no++ }}</td>
               <td>{{ date('d M Y', strtotime($row->tanggal)) }}</td>
               <td>{{ $row->nama_pelanggan }}</td>
               <td>{{ ucwords(strtolower($row->keterangan ?? '-')) }}</td>
               @if(Auth::User()->level == '1')
               <td class="text-center">{{ $row->nama_kasir ?? '-' }}</td>
               @endif
               <td class="text-center">
                  @if(str_contains(strtolower($row->jenis), 'setor'))
                     <button class="btn btn-success btn-xs">{{ ucwords($row->jenis) }}</button>
                  @else
                     <button class="btn btn-danger btn-xs">{{ ucwords($row->jenis) }}</button>
                  @endif
               </td>
               <td class="text-center">
                  @if($row->id_metode == '1')
                     <button class="btn btn-success btn-xs">{{ $row->nama_metode ?? '-' }}</button>
                  @else
                     <button class="btn btn-info btn-xs">{{ $row->nama_metode ?? '-' }}</button>
                  @endif
               </td>
               <td class="text-center">
                  @if(!empty($row->bukti))
                     <a href="{{ url($row->bukti) }}" target="_blank" class="btn btn-secondary btn-xs">Lihat</a>
                  @else
                     -
                  @endif
               </td>
               <td class="text-center">{{ $row->biaya_admin ? 'Rp ' . number_format($row->biaya_admin, 0, ',', '.') : '-' }}</td>
               <td class="text-center">{{ 'Rp ' . number_format($row->total, 0, ',', '.') }}</td>
               <td class="text-center" width="15%">
                  <a href="/admin/setor_tarik/edit/{{ $row->id }}">
                     <button class="btn btn-success btn-xs"><i class="fa fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Data"></i></button>
                  </a>
                  <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#data-{{ $row->id }}"><i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Hapus Data"></i></button>
               </td>
            </tr>
            @endforeach
         </tbody>
         <tfoot>
         @foreach($metode as $mt)
         <?php
            $totalMt = $data->where('id_metode', $mt->id)->sum('total');
            $totalAdmin = $data->where('id_metode', $mt->id)->sum('biaya_admin');
         ?>
         <tr>
            <th colspan="{{ Auth::user()->level == '1' ? 9 : 8 }}">Total Setor & Tarik {{ $mt->nama }}</th>
            <th class="text-center">{{ 'Rp ' . number_format($totalAdmin, 0, ',', '.') }}</th>
            <th class="text-center">{{ 'Rp ' . number_format($totalMt, 0, ',', '.') }}</th>
         </tr>
         @endforeach
         <tr>
            <th colspan="{{ Auth::user()->level == '1' ? 9 : 8 }}">Total Keseluruhan</th>
            <th class="text-center">{{ 'Rp ' . number_format($data->sum('biaya_admin'), 0, ',', '.') }}</th>
            <th class="text-center">{{ 'Rp ' . number_format($data->sum('total'), 0, ',', '.') }}</th>
         </tr>
         </tfoot>
      </table>
   </div>
   <!-- Striped table End -->
</div>
<!-- Modal -->
@foreach($data as $row)
<div class="modal fade" id="data-{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                     <label>Tanggal</label>
                     <input class="form-control" value="{{ date('d M Y', strtotime($row->tanggal)) }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Nama Pelanggan</label>
                     <input class="form-control" value="{{ $row->nama_pelanggan }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Total</label>
                     <input class="form-control" value="{{ 'Rp ' . number_format($row->total, 0, ',', '.') }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="form-group" style="font-size: 17px;">
                     <label>Biaya Admin</label>
                     <input class="form-control" value="{{ $row->biaya_admin ? 'Rp ' . number_format($row->biaya_admin, 0, ',', '.') : '-' }}" readonly style="background-color: white;pointer-events: none;">
                  </div>
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-md-6">
                  <a href="/admin/setor_tarik/delete/{{ $row->id }}" style="text-decoration: none;">
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