<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('setor_tarik', function (Blueprint $table) {
            $table->id();
            $table->string('tanggal');
            $table->string('nama_pelanggan');
            $table->enum('jenis', ['setor', 'tarik', 'setor tunai', 'tarik tunai']);
            $table->bigInteger('total');
            $table->bigInteger('biaya_admin')->default(0);
            $table->string('keterangan')->nullable();
            $table->string('id_metode');
            $table->string('id_user');
            $table->string('bukti')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('setor_tarik');
    }
};