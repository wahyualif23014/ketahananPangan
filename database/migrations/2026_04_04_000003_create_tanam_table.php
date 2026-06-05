<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tanam', function (Blueprint $table) {
            $table->integer('id_tanam')->primary();
            $table->integer('id_lahan')->nullable();
            $table->decimal('luas_tanam', 10)->nullable();
            $table->dateTime('tgl_tanam')->nullable();
            $table->integer('id_anggota')->nullable();
            $table->enum('deletestatus', ['1', '2'])->default('2');
            $table->dateTime('datetransaction')->nullable();
            $table->longText('keterangan_tanam')->nullable();
            $table->decimal('kebutuhan_bibit', 10)->nullable();
            $table->string('nama_bibit', 100)->nullable();
            $table->integer('valid_oleh')->nullable();
            $table->dateTime('tgl_valid')->nullable();
            $table->enum('panen', ['1', '2'])->default('2');
            $table->integer('edit_oleh')->nullable();
            $table->dateTime('tgl_edit')->nullable();
            $table->string('surat_edit')->nullable();
            $table->dateTime('est_awal_panen')->nullable();
            $table->dateTime('est_akhir_panen')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->text('alasan_tolak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanam');
    }
};
