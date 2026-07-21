<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\SatuanController;
use App\Http\Controllers\Admin\MetodeController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\BarangMasukController;
use App\Http\Controllers\Admin\BarangKeluarController;
use App\Http\Controllers\Admin\PengeluaranController;
use App\Http\Controllers\Admin\PemasukanController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\Admin\HutangController;
use App\Http\Controllers\Admin\PiutangController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\KasirController;
use App\Http\Controllers\Admin\SetorTarikController;

//Clear All:
Route::get('/clear', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('optimize');
    $exitCode = Artisan::call('route:cache');
    $exitCode = Artisan::call('route:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('config:cache');
    return '<h1>Berhasil dibersihkan</h1>';
});

Route::get('/', function () {
    return view('auth.login');
});

// Authentication
Route::get('/login', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/keluar', [HomeController::class, 'keluar']);
Route::get('/admin/home', [HomeController::class, 'index']);
Route::get('/admin/home/filter/{id}', [HomeController::class, 'index_filter']);
Route::get('/admin/change', [HomeController::class, 'change']);
Route::post('/admin/change_password', [HomeController::class, 'change_password']);
Route::get('/invoice/{id}', [HomepageController::class, 'invoice']);
Route::get('/cetak/{id}', [HomepageController::class, 'cetak']);
Route::get('/transaksi/{id}', [HomepageController::class, 'transaksi']);

// Satuan
Route::prefix('admin/satuan')
    ->name('admin.satuan.')
    ->middleware(['cekLevel:1 3', 'cekKasirBuka'])
    ->controller(SatuanController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
    });

// Metode
Route::prefix('admin/metode')
    ->name('admin.metode.')
    ->middleware(['cekLevel:1 2 ', 'cekKasirBuka'])
    ->controller(MetodeController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
    });

// Account
Route::prefix('admin/account')
    ->name('admin.account.')
    ->middleware(['cekLevel:1', 'cekKasirBuka'])
    ->controller(AccountController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
        Route::get('/reset/{id}', 'reset')->name('reset');
    });

// Barang
Route::prefix('admin/barang')
    ->name('admin.barang.')
    ->middleware(['cekLevel:1 3', 'cekKasirBuka'])
    ->controller(BarangController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
        Route::get('/detail/{id}', 'detail')->name('detail');
        Route::post('/detail/create/{id}', 'create_detail')->name('create_detail');
        Route::post('/detail/update/{id}', 'update_detail')->name('update_detail');
        Route::get('/detail/delete/{id}', 'delete_detail')->name('delete_detail');
        Route::get('/get-ukuran/{id}', 'getUkuran')->name('getUkuran');
        Route::get('/get-stock/{id}', 'getStock')->name('getStock');
        Route::get('/cari-barang', 'cariBarang')->name('cariBarang');
    });

// Barang Masuk
Route::prefix('admin/barang_masuk')
    ->name('admin.barang_masuk.')
    ->middleware(['cekLevel:1 3', 'cekKasirBuka'])
    ->controller(BarangMasukController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/delete/{id}', 'delete')->name('delete');
    });

// Barang Keluar
Route::prefix('admin/barang_keluar')
    ->name('admin.barang_keluar.')
    ->middleware(['cekLevel:1 3', 'cekKasirBuka'])
    ->controller(BarangKeluarController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/delete/{id}', 'delete')->name('delete');
    });

// Pengeluaran
Route::prefix('admin/pengeluaran')
    ->name('admin.pengeluaran.')
    ->middleware(['cekLevel:1 2', 'cekKasirBuka'])
    ->controller(PengeluaranController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/filter/{bln}', 'read_filter')->name('read_filter');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
        Route::get('/cetak/{bln}', 'cetak')->name('cetak');
    });

// Pemasukan
Route::prefix('admin/pemasukan')
    ->name('admin.pemasukan.')
    ->middleware(['cekLevel:1 2', 'cekKasirBuka'])
    ->controller(PemasukanController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/filter/{bln}', 'read_filter')->name('read_filter');
        Route::get('/cetak/{bln}', 'cetak')->name('cetak');
    });

// Transaksi
Route::prefix('admin/transaksi')
    ->name('admin.transaksi.')
    ->middleware(['cekLevel:1 2', 'cekKasirBuka'])
    ->controller(TransaksiController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
        Route::get('/search-barang', 'searchBarang')->name('searchBarang');
        Route::get('/cari/delete/{id}', 'delete_cari')->name('delete_cari');
        Route::get('/detail/{id}', 'detail')->name('detail');
        Route::post('/cari/create/{id_transaksi}/{id_barang}', 'create_cari')->name('create_cari');
        Route::post('/detail/cari/{id_transaksi}', 'detail_cari')->name('detail_cari');
        Route::get('/detail/delete/{id}', 'delete_detail')->name('delete_detail');
        Route::post('/bayar/{id}', 'bayar')->name('bayar');
    });

// Hutang
Route::prefix('admin/hutang')
    ->name('admin.hutang.')
    ->middleware(['cekLevel:1 2 ', 'cekKasirBuka'])
    ->controller(HutangController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/detail/{id}', 'detail')->name('detail');
        Route::get('/delete/{id}', 'delete')->name('delete');
        Route::post('/bayar/{id}', 'bayar')->name('bayar');
    });

// Piutang
Route::prefix('admin/piutang')
    ->name('admin.piutang.')
    ->middleware(['cekLevel:1 2 ', 'cekKasirBuka'])
    ->controller(PiutangController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/detail/{id}', 'detail')->name('detail');
        Route::get('/delete/{id}', 'delete')->name('delete');
        Route::post('/bayar/{id}', 'bayar')->name('bayar');
    });

// Income
Route::prefix('admin/income')
    ->name('admin.income.')
    ->middleware(['cekLevel:1 2 ', 'cekKasirBuka'])
    ->group(function () {
        Route::get('/', [IncomeController::class, 'read'])->name('read');
        Route::get('/filter/{tgl}', [IncomeController::class, 'read_filter'])->name('read_filter');
        Route::get('/harian', [IncomeController::class, 'read_harian'])->name('read_harian');
        Route::get('/harian/filter/{tgl}', [IncomeController::class, 'read_harian_filter'])->name('read_harian_filter');
        Route::get('/harian/cetak/{bln}', [IncomeController::class, 'cetak'])->name('cetak');
    });

    // Kasir Session — hanya owner (1) dan kasir (2), operator (3) tidak bisa buka/tutup kasir
Route::prefix('admin/kasir')
    ->name('admin.kasir.')
    ->middleware('cekLevel:1 2')
    ->controller(KasirController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/buka', 'formBuka')->name('form-buka');
        Route::post('/buka', 'buka')->name('buka');
        Route::get('/tutup/{id}', 'formTutup')->name('form-tutup');
        Route::post('/tutup/{id}', 'tutup')->name('tutup');
        Route::get('/detail/{id}', 'detail')->name('detail');
        Route::get('/cek-aktif', 'cekSesiAktif')->name('cek-aktif');
    });

    // tarik-setor kasir — hanya owner (1) dan kasir (2), operator (3) tidak bisa akses
Route::prefix('admin/setor_tarik')
    ->name('admin.setor_tarik.')
    ->middleware(['cekLevel:1 2', 'cekKasirBuka'])
    ->controller(SetorTarikController::class)
    ->group(function () {
        Route::get('/', 'read')->name('read');
        Route::get('/add', 'add')->name('add');
        Route::post('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/hapus/{id}', 'hapus')->name('hapus');
        Route::get('/delete/{id}', 'delete')->name('delete');
    });