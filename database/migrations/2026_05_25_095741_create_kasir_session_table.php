<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kasir_session', function (Blueprint $table) {
            $table->id();

            // Menggunakan string seperti pola migration proyek ini
            $table->string('id_user');

            // Modal awal saat buka kasir
            $table->bigInteger('modal_awal')->default(0);

            // Snapshot pemasukan sesi (diisi saat tutup kasir)
            $table->bigInteger('pemasukan_sesi')->default(0);

            // Pengeluaran sesi (snapshot saat tutup)
            $table->bigInteger('pengeluaran_sesi')->default(0);

            // Uang fisik yang dihitung kasir saat tutup
            $table->bigInteger('uang_akhir')->nullable();
            $table->bigInteger('setor_owner')->default(0);

            // Selisih = uang_akhir - (modal_awal + pemasukan_sesi - pengeluaran_sesi)
            $table->bigInteger('selisih')->nullable();

            // Keterangan saat buka dan tutup (opsional)
            $table->string('keterangan_buka')->nullable();
            $table->string('keterangan_tutup')->nullable();

            // Metode modal awal (referensi ke tabel metode)
            $table->string('id_metode')->nullable();

            // Referensi ke baris pemasukan yang otomatis dibuat saat buka kasir
            $table->string('id_pemasukan_buka')->nullable();

            // Waktu buka & tutup kasir
            $table->string('waktu_buka');
            $table->string('waktu_tutup')->nullable();

            // Status sesi: buka | tutup
            $table->string('status')->default('buka');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kasir_session');
    }
};