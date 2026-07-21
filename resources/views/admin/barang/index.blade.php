@extends('admin.layouts.app', [
'activePage' => 'barang',
])
@section('content')
<div class="min-height-200px">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-12">
            <div class="title">
               <h4>Data Barang</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Data Input</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Barang</li>
               </ol>
            </nav>
         </div>
      </div>
   </div>

   <div class="pd-20 card-box mb-30">
      <div class="clearfix">
         <div class="pull-left">
            <h2 class="text-primary h2"><i class="icon-copy dw dw-list"></i> List Data Barang</h2>
         </div>
         <div class="pull-right">
            <a href="/admin/barang/add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Data</a>
         </div>
      </div>
      <hr style="margin-top: 0px;">

      
      

      {{-- Controls: show per page + search --}}
      <div class="row mb-3">
         {{-- Bagian Kiri: Tampilkan Data --}}
         <div class="col-12 col-md-6 d-flex align-items-center mb-3 mb-md-0">
            <span class="mr-2 text-muted">Tampilkan</span>
            <select id="ctrl-perpage" class="form-control form-control-sm" style="width: 80px;">
               <option value="10">10</option>
               <option value="25" selected>25</option>
               <option value="50">50</option>
               <option value="100">100</option>
               <option value="-1">Semua</option>
            </select>
            <span class="ml-2 text-muted">data</span>
         </div>
         
         {{-- Bagian Kanan: Pencarian --}}
         <div class="col-12 col-md-6 d-flex align-items-center justify-content-md-end">
            <div class="input-group input-group-sm mb-0" style="max-width: 300px; width: 100%;">
               <input type="text" id="ctrl-search" class="form-control" placeholder="Cari barcode, nama, satuan...">
               <div class="input-group-append">
                  <span class="input-group-text"><i class="fa fa-search"></i></span>
               </div>
            </div>
         </div>
      </div>

      <div class="table-responsive">
         <table class="table table-striped table-bordered" id="tabel-barang" style="width:100%">
            <thead class="bg-primary text-white">
               <tr>
                  <th class="align-middle text-center" width="4%">#</th>
                  <th class="align-middle text-center" style="cursor:pointer;" data-col="barcode">
                     Barcode <i class="fa fa-sort ml-1 sort-icon" data-col="barcode"></i>
                  </th>
                  <th class="align-middle text-center" style="cursor:pointer;" data-col="nama">
                     Nama Barang <i class="fa fa-sort ml-1 sort-icon" data-col="nama"></i>
                  </th>
                  <th class="align-middle text-center">Satuan</th>
                  <th class="align-middle text-center" style="cursor:pointer;" data-col="stock">
                     Stock <i class="fa fa-sort ml-1 sort-icon" data-col="stock"></i>
                  </th>
                  <th class="align-middle text-center">Harga Modal</th>
                  <th class="align-middle text-center">Harga Jual</th>
                  <th class="align-middle text-center">Harga Khusus</th>
                  @if(Auth::User()->level != '3')
                  <th class="align-middle text-center">Penginput</th>
                  @endif
                  <th class="text-center align-middle" style="min-width:90px;">Action</th>
               </tr>
            </thead>
            <tbody id="tabel-body">
               {{-- diisi oleh JS --}}
            </tbody>
         </table>
      </div>

      {{-- Info + Pagination --}}
      <div class="row align-items-center mt-2" id="pagination-wrap">
         <div class="col-md-5">
            <small id="info-text" class="text-muted"></small>
         </div>
         <div class="col-md-7">
            <ul class="pagination pagination-sm justify-content-end mb-0" id="pagination-list"></ul>
         </div>
      </div>
   </div>
</div>

{{-- Modal Hapus --}}
@foreach($grouped as $id_barang => $rows)
   <?php $barang_modal = DB::table('barang')->find($id_barang); ?>
   @foreach($rows as $data)
      <?php $sat = DB::table('satuan')->find($data->id_satuan); ?>
      <div class="modal fade" id="hapus-{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                           <label>Barcode</label>
                           <input class="form-control" value="{{ $barang_modal->barcode ?? '-' }}" readonly style="background-color: white;pointer-events: none;">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group" style="font-size: 17px;">
                           <label>Nama Barang</label>
                           <input class="form-control" value="{{ $barang_modal->nama ?? '-' }}" readonly style="background-color: white;pointer-events: none;">
                        </div>
                     </div>
                     <div class="col-md-12">
                        <div class="form-group" style="font-size: 17px;">
                           <label>Satuan</label>
                           <input class="form-control" value="{{ $sat->nama ?? '-' }}" readonly style="background-color: white;pointer-events: none;">
                        </div>
                     </div>
                  </div>
                  @if(DB::table('detail_barang')->where('id_barang',$id_barang)->count() == 1)
                     <div class="alert alert-warning text-center" style="font-size:12px;">
                        <i class="fa fa-info-circle"></i> Ini satuan terakhir. Data barang juga akan ikut terhapus.
                     </div>
                  @endif
                  <div class="row mt-2">
                     <div class="col-md-6">
                        <a href="/admin/barang/delete/{{ $data->id }}" style="text-decoration: none;">
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
@endforeach

<style>
#tabel-barang tbody tr td { vertical-align: middle; }
#tabel-barang thead th[data-col]:hover { background-color: #1565c0; }
.sort-icon { opacity: .5; font-size: 11px; }
th.sort-asc  .sort-icon,
th.sort-desc .sort-icon { opacity: 1; color: #ffe082; }
/* border kanan tebal pada kolom barcode & nama sebagai pemisah visual grup */
#tabel-barang tbody td.td-barcode,
#tabel-barang tbody td.td-nama { border-right: 2px solid #b0bec5 !important; }
</style>

<script>
// =====================================================================
// DATA — bangun dari PHP, satu entry per grup (id_barang)
// =====================================================================
@php
   $groupsData = collect($grouped)->map(function($rows, $id_barang) {
      $barang = DB::table('barang')->find($id_barang);
      return [
         'id_barang' => $id_barang,
         'barcode'   => $barang->barcode ?? '',
         'nama'      => $barang->nama    ?? '',
         'rows'      => $rows->map(function($d) {
            $satuan = DB::table('satuan')->find($d->id_satuan);
            $user   = DB::table('users')->find($d->id_user);
            return [
               'id'           => $d->id,
               'satuan'       => $satuan->nama ?? '-',
               'stock'        => $d->stock,
               'harga_modal'  => $d->harga_modal,
               'harga_jual'   => $d->harga_jual,
               'harga_khusus' => $d->harga_khusus,
               'penginput'    => $user->name ?? '-',
            ];
         })->values()
      ];
   })->values();
@endphp

var GROUPS = @json($groupsData);

var SHOW_PENGINPUT = {{ Auth::User()->level != '3' ? 'true' : 'false' }};

// =====================================================================
// STATE
// =====================================================================
var state = {
   search  : '',
   perPage : 25,
   page    : 1,
   sortCol : 'nama',
   sortDir : 'asc',
};

// =====================================================================
// FILTER
// =====================================================================
function filterGroups() {
   var q = state.search.toLowerCase().trim();
   if (!q) return GROUPS.slice();

   return GROUPS.filter(function(g) {
      // Cocokkan di level grup (barcode, nama)
      var inGroup = g.barcode.toLowerCase().includes(q) || g.nama.toLowerCase().includes(q);
      if (inGroup) return true;
      // Atau cocok di salah satu baris satuan
      return g.rows.some(function(r) {
         return r.satuan.toLowerCase().includes(q) || r.penginput.toLowerCase().includes(q);
      });
   }).map(function(g) {
      // Jika cocok di grup, tampilkan semua baris; jika cocok di baris, filter barisnya
      var inGroup = g.barcode.toLowerCase().includes(q) || g.nama.toLowerCase().includes(q);
      if (inGroup) return g;
      return Object.assign({}, g, {
         rows: g.rows.filter(function(r) {
            return r.satuan.toLowerCase().includes(q) || r.penginput.toLowerCase().includes(q);
         })
      });
   });
}

// =====================================================================
// SORT
// =====================================================================
function sortGroups(groups) {
   return groups.slice().sort(function(a, b) {
      var va = a[state.sortCol] || '';
      var vb = b[state.sortCol] || '';
      if (state.sortCol === 'stock') {
         // sort berdasarkan total stock grup
         va = a.rows.reduce(function(s,r){ return s + parseFloat(r.stock||0); }, 0);
         vb = b.rows.reduce(function(s,r){ return s + parseFloat(r.stock||0); }, 0);
         return state.sortDir === 'asc' ? va - vb : vb - va;
      }
      va = va.toString().toLowerCase();
      vb = vb.toString().toLowerCase();
      if (va < vb) return state.sortDir === 'asc' ? -1 :  1;
      if (va > vb) return state.sortDir === 'asc' ?  1 : -1;
      return 0;
   });
}

// =====================================================================
// FORMAT ANGKA
// =====================================================================
function fmt(n) {
   return new Intl.NumberFormat('id-ID').format(n || 0);
}

// =====================================================================
// RENDER
// =====================================================================
function render() {
   var filtered = filterGroups();
   var sorted   = sortGroups(filtered);
   var total    = sorted.length;         // total grup
   var perPage  = state.perPage;
   var page     = state.page;

   // Clamp page
   var totalPages = perPage === -1 ? 1 : Math.max(1, Math.ceil(total / perPage));
   if (page > totalPages) { state.page = page = totalPages; }

   var visible = perPage === -1 ? sorted : sorted.slice((page-1)*perPage, page*perPage);

   // ---- Bangun HTML tabel ----
   var html = '';
   var no   = (perPage === -1 ? 0 : (page-1)*perPage);

   visible.forEach(function(g) {
      no++;
      var rowCount = g.rows.length;
      g.rows.forEach(function(r, i) {
         html += '<tr>';

         if (i === 0) {
            // Kolom rowspan: #, Barcode, Nama
            html += '<td class="text-center align-middle" rowspan="'+ rowCount +'">'+ no +'</td>';
            html += '<td class="align-middle td-barcode" rowspan="'+ rowCount +'">'+ (g.barcode || '-') +'</td>';
            html += '<td class="align-middle td-nama" rowspan="'+ rowCount +'">'+ (g.nama || '-') +'</td>';
         }

         html += '<td class="text-center align-middle"><span class="badge badge-info px-2 py-1" style="font-size:12px;">'+ r.satuan +'</span></td>';
         html += '<td class="text-center align-middle">'+ fmt(r.stock) +'</td>';
         html += '<td class="text-center align-middle">Rp '+ fmt(r.harga_modal) +'</td>';
         html += '<td class="text-center align-middle">Rp '+ fmt(r.harga_jual) +'</td>';
         html += '<td class="text-center align-middle">Rp '+ fmt(r.harga_khusus) +'</td>';

         if (SHOW_PENGINPUT) {
            html += '<td class="text-center align-middle">'+ r.penginput +'</td>';
         }

         html += '<td class="text-center align-middle" width="15%">'
               + '<a href="/admin/barang/edit/'+ r.id +'">'
               + '<button type="button" class="btn btn-success btn-xs"><i class="fa fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Data"></i></button>'
               + '</a> '
               + '<button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#hapus-'+ r.id +'">'
               + '<i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Hapus Data"></i></button>'
               + '</td>';

         html += '</tr>';
      });
   });

   if (html === '') {
      var colspan = SHOW_PENGINPUT ? 10 : 9;
      html = '<tr><td colspan="'+ colspan +'" class="text-center text-muted py-3">Tidak ada data ditemukan.</td></tr>';
   }

   document.getElementById('tabel-body').innerHTML = html;

   // ---- Info teks ----
   var totalRows = filtered.reduce(function(s,g){ return s+g.rows.length; }, 0);
   var startRow  = perPage === -1 ? 1 : Math.min((page-1)*perPage*1 + 1, total);
   var endRow    = perPage === -1 ? total : Math.min(page*perPage, total);
   document.getElementById('info-text').textContent =
      'Menampilkan '+ startRow +' - '+ endRow +' dari '+ total +' barang (' + totalRows + ' baris satuan)';

   // ---- Pagination ----
   renderPagination(page, totalPages);

   // ---- Sort icon ----
   document.querySelectorAll('#tabel-barang thead th').forEach(function(th) {
      th.classList.remove('sort-asc','sort-desc');
   });
   var activeTh = document.querySelector('#tabel-barang thead th[data-col="'+ state.sortCol +'"]');
   if (activeTh) activeTh.classList.add('sort-' + state.sortDir);
}

// =====================================================================
// PAGINATION
// =====================================================================
function renderPagination(page, totalPages) {
   var ul  = document.getElementById('pagination-list');
   var max = 5; // max tombol halaman yang ditampilkan
   if (totalPages <= 1) { ul.innerHTML = ''; return; }

   var html = '';
   html += '<li class="page-item '+ (page===1?'disabled':'') +'"><a class="page-link" href="#" data-page="'+(page-1)+'">‹</a></li>';

   var start = Math.max(1, page - Math.floor(max/2));
   var end   = Math.min(totalPages, start + max - 1);
   if (end - start < max - 1) start = Math.max(1, end - max + 1);

   if (start > 1) {
      html += '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
      if (start > 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
   }
   for (var p = start; p <= end; p++) {
      html += '<li class="page-item '+(p===page?'active':'')+'"><a class="page-link" href="#" data-page="'+p+'">'+p+'</a></li>';
   }
   if (end < totalPages) {
      if (end < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
      html += '<li class="page-item"><a class="page-link" href="#" data-page="'+totalPages+'">'+totalPages+'</a></li>';
   }

   html += '<li class="page-item '+(page===totalPages?'disabled':'')+'"><a class="page-link" href="#" data-page="'+(page+1)+'">›</a></li>';
   ul.innerHTML = html;

   // Event klik pagination
   ul.querySelectorAll('a[data-page]').forEach(function(a) {
      a.addEventListener('click', function(e) {
         e.preventDefault();
         var p = parseInt(this.getAttribute('data-page'));
         if (p < 1 || p > totalPages) return;
         state.page = p;
         render();
         window.scrollTo(0, document.getElementById('tabel-barang').offsetTop - 80);
      });
   });
}

// =====================================================================
// EVENT LISTENERS
// =====================================================================
document.addEventListener('DOMContentLoaded', function () {

   // Search
   var searchTimer;
   document.getElementById('ctrl-search').addEventListener('input', function() {
      clearTimeout(searchTimer);
      var val = this.value;
      searchTimer = setTimeout(function() {
         state.search = val;
         state.page   = 1;
         render();
      }, 250);
   });

   // Per page
   document.getElementById('ctrl-perpage').addEventListener('change', function() {
      state.perPage = parseInt(this.value) || -1;
      state.page    = 1;
      render();
   });

   // Sort header
   document.querySelectorAll('#tabel-barang thead th[data-col]').forEach(function(th) {
      th.addEventListener('click', function() {
         var col = this.getAttribute('data-col');
         if (state.sortCol === col) {
            state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
         } else {
            state.sortCol = col;
            state.sortDir = 'asc';
         }
         state.page = 1;
         render();
      });
   });

   // Render awal
   render();
});
</script>
@endsection