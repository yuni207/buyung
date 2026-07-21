<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('tanggal');
            $table->string('pukul')->nullable();
            $table->string('nama')->nullable();
            $table->string('total')->nullable();
            $table->string('potongan')->nullable();
            $table->string('bayar')->nullable();
            $table->string('kembali')->nullable();
            $table->string('status');
            $table->string('id_metode')->nullable();
            $table->string('id_user');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
};
