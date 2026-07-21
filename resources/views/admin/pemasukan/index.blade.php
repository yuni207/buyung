@extends('admin.layouts.app', [
'activePage' => 'pemasukan',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Data Pemasukan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Pemasukan</li>
               </ol>
            </nav>
         </div>
         <div class="col-md-6 col-sm-12 text-right">
             <div class="d-flex align-items-center justify-content-end">
                 <div class="form-group mb-0 mr-2">
                     <select class="form-control" id="filterType" onchange="togglePemasukanFilterType()">
                         <option value="date" {{ strlen($bln) === 10 ? 'selected' : '' }}>Tanggal</option>
                         <option value="month" {{ strlen($bln) === 7 ? 'selected' : '' }}>Bulan</option>
                         <option value="year" {{ strlen($bln) === 4 ? 'selected' : '' }}>Tahun</option>
                     </select>
                 </div>
                 <div class="form-group mb-0">
                     <input type="date" required class="form-control" id="filterValue" onchange="location = '/admin/pemasukan/filter/'+this.value;" name="bln" value="{{$bln}}">
                 </div>
             </div>
         </div>
      </div>
   </div>
   <!-- Striped table start -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> List Data Pemasukan</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/pemasukan/cetak/{{$bln}}" target="_blank" class="btn btn-dark btn-sm"><i class="fa fa-print"></i> Cetak Data</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">
      
      
      <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
            <tr>
               <th width="5%" >#</th>
               <th>Tanggal</th>
               <th>Keterangan</th>
               @if(in_array(Auth::user()->level, ['1','2']))
               <th class="text-center">Penginput</th>
               @endif
               <th class="text-center" width="20%">Metode Pemasukan</th>
               <th class="text-center" width="15%">Total</th>
            </tr>
         </thead>
         <tbody>
            <?php $no = 1; ?>
            @foreach($pemasukan as $data)
            <?php
               $metode = DB::table('metode')->find($data->id_metode);
               $users = DB::table('users')->find($data->id_user);
            ?>
            <tr>
               <td class="text-center">{{$no++}}</td>
               <td>{{date ('d M Y', strtotime($data->tanggal))}}</td>
               <td>{{ ucwords(strtolower($data->keterangan)) }}</td>
               @if(in_array(Auth::user()->level, ['1','2']))
               <td class="text-center">{{$users->name}}</td>
               @endif
               <td class="text-center">
                  @if($metode->id == '1')
                     <button class="btn btn-success btn-xs">{{$metode->nama ?? '-'}}</button>
                  @else
                     <button class="btn btn-info btn-xs">{{$metode->nama ?? '-'}}</button>
                  @endif
               </td>
               <td>{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
         </tbody>
         <tfoot>
         <?php
            $metode = DB::table('metode')->get();
            $total_seluruhnya = DB::table('pemasukan')->where('tanggal', 'LIKE', $bln . '%')->sum('total');
         ?>
         @foreach($metode as $data)
         <?php
            $total = DB::table('pemasukan')->where('tanggal', 'LIKE', $bln . '%')->where('id_metode',$data->id)->sum('total');
         ?>
         <tr>
            <th colspan="5">Total Pemasukan {{$data->nama}}</th>
            <th>{{ 'Rp ' . number_format($total, 0, ',', '.') }}</th>
         </tr>
         @endforeach
         <tr>
            <th colspan="5">Total Pemasukan Keseluruhan</th>
            <th>{{ 'Rp ' . number_format($total_seluruhnya, 0, ',', '.') }}</th>
         </tr>
         </tfoot>
      </table>
   </div>
   <!-- Striped table End -->
</div>
<script>
    function togglePemasukanFilterType() {
        const type = document.getElementById('filterType').value;
        const input = document.getElementById('filterValue');
        const currentValue = '{{ $bln }}';

        if (type === 'month') {
            input.type = 'month';
            input.value = currentValue.length >= 7 ? currentValue.slice(0, 7) : currentValue;
        } else if (type === 'year') {
            input.type = 'number';
            input.min = '2000';
            input.max = '{{ date("Y") }}';
            input.value = currentValue.length >= 4 ? currentValue.slice(0, 4) : currentValue;
        } else {
            input.type = 'date';
            input.value = currentValue.length >= 10 ? currentValue.slice(0, 10) : currentValue;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        togglePemasukanFilterType();
    });
</script>
@endsection