<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hutang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pelanggan');
            $table->string('no_hp')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('total');
            $table->bigInteger('terbayar')->default(0);
            $table->string('status')->default('belum'); // belum | lunas
            $table->string('id_transaksi')->nullable();
            $table->string('id_user');
            $table->string('tanggal');
            $table->string('jatuh_tempo')->nullable();
            $table->timestamps();
        });

        // Tabel riwayat pembayaran hutang
        Schema::create('hutang_bayar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_hutang');
            $table->bigInteger('jumlah');
            $table->string('keterangan')->nullable();
            $table->string('id_user');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hutang_bayar');
        Schema::dropIfExists('hutang');
    }
};