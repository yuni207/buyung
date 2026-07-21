<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detail_barang', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_barang');
            $table->unsignedBigInteger('id_satuan')->nullable();

            $table->integer('stock')->default(0);

            $table->bigInteger('harga_modal')->default(0);
            $table->bigInteger('harga_jual')->default(0);
            $table->bigInteger('harga_khusus')->default(0);

            $table->unsignedBigInteger('id_user')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_barang');
    }
};