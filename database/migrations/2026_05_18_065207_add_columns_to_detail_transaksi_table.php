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
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->string('id_detail_barang')->nullable()->after('id_barang');
            $table->string('nama')->nullable()->after('id_detail_barang');
            $table->string('harga_modal')->nullable()->after('harga');
        });
    }

    public function down()
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn(['id_detail_barang', 'nama', 'harga_modal']);
        });
    }
};
