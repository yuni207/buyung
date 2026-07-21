<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel utama piutang (uang yang kita pinjamkan ke orang lain)
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peminjam');          // nama orang yang meminjam
            $table->string('no_hp')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('total');              // jumlah total yang dipinjamkan
            $table->bigInteger('terbayar')->default(0); // jumlah yang sudah dikembalikan
            $table->string('status')->default('belum'); // belum | lunas
            $table->string('id_pengeluaran')->nullable(); // referensi ke tabel pengeluaran
            $table->string('id_user');
            $table->string('tanggal');                // tanggal peminjaman
            $table->string('jatuh_tempo')->nullable(); // tanggal jatuh tempo pengembalian
            $table->timestamps();
        });

        // Tabel riwayat pembayaran kembali piutang
        Schema::create('piutang_bayar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_piutang');
            $table->bigInteger('jumlah');             // jumlah yang dikembalikan
            $table->string('keterangan')->nullable();
            $table->string('id_metode');              // metode pengembalian uang
            $table->string('id_user');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('piutang_bayar');
        Schema::dropIfExists('piutang');
    }
};