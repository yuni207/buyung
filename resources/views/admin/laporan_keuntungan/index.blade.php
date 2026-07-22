@extends('admin.layouts.app', [
'activePage' => 'laporan_keuntungan',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Laporan Keuntungan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Laporan Keuntungan</li>
               </ol>
            </nav>
         </div>
         <div class="col-md-6 col-sm-12 text-right">
             <div class="d-flex align-items-center justify-content-end">
                 <div class="form-group mb-0 mr-2">
                     <select class="form-control" id="filterType" onchange="toggleKeuntunganFilterType()">
                         <option value="date" {{ strlen($bln) === 10 ? 'selected' : '' }}>Tanggal</option>
                         <option value="month" {{ strlen($bln) === 7 ? 'selected' : '' }}>Bulan</option>
                         <option value="year" {{ strlen($bln) === 4 ? 'selected' : '' }}>Tahun</option>
                     </select>
                 </div>
                 <div class="form-group mb-0">
                     <input type="date" required class="form-control" id="filterValue" onchange="location = '/admin/laporan-keuntungan/filter/'+this.value;" name="bln" value="{{$bln}}">
                 </div>
             </div>
         </div>
      </div>
   </div>

   <!-- Rincian per metode -->
   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> Rincian Per Metode Pembayaran</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/laporan-keuntungan/cetak/{{$bln}}" class="btn btn-dark btn-sm"><i class="fa fa-download"></i> Cetak &amp; Download</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">

      <table class="table table-striped table-bordered data-table hover">
         <thead class="bg-primary text-white">
            <tr>
               <th width="5%">#</th>
               <th>Metode</th>
               <th class="text-center">Pemasukan</th>
               <th class="text-center">Pengeluaran</th>
               <th class="text-center">Selisih</th>
            </tr>
         </thead>
         <tbody>
            <?php $no = 1; ?>
            @forelse($rincianMetode as $data)
            <tr>
               <td class="text-center">{{$no++}}</td>
               <td>{{ $data['nama_metode'] }}</td>
               <td class="text-right">{{ 'Rp ' . number_format($data['pemasukan'], 0, ',', '.') }}</td>
               <td class="text-right">{{ 'Rp ' . number_format($data['pengeluaran'], 0, ',', '.') }}</td>
               <td class="text-right {{ $data['selisih'] >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                  {{ 'Rp ' . number_format($data['selisih'], 0, ',', '.') }}
               </td>
            </tr>
            @empty
            <tr>
               <td colspan="5" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
         </tbody>
         <tfoot>
            <tr>
               <th colspan="2">Total Keseluruhan</th>
               <th class="text-right">{{ 'Rp ' . number_format($totalPemasukan, 0, ',', '.') }}</th>
               <th class="text-right">{{ 'Rp ' . number_format($totalPengeluaran, 0, ',', '.') }}</th>
               <th class="text-right">{{ 'Rp ' . number_format($keuntungan, 0, ',', '.') }}</th>
            </tr>
         </tfoot>
      </table>
   </div>
</div>
<script>
    function toggleKeuntunganFilterType() {
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
        toggleKeuntunganFilterType();
    });
</script>
@endsection