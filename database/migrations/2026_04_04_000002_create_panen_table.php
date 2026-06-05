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
        Schema::create('panen', function (Blueprint $table) {
            $table->integer('id_panen')->primary();
            $table->integer('id_tanam')->nullable();
            $table->integer('id_lahan')->nullable();
            $table->decimal('luas_panen', 10)->nullable();
            $table->decimal('total_panen', 10)->nullable();
            $table->dateTime('tgl_panen')->nullable();
            $table->integer('id_anggota')->nullable();
            $table->enum('deletestatus', ['1', '2'])->default('2');
            $table->dateTime('datetransaction')->nullable();
            $table->longText('ket_panen')->nullable();
            $table->integer('valid_oleh')->nullable();
            $table->dateTime('tgl_valid')->nullable();
            $table->integer('edit_oleh')->nullable();
            $table->dateTime('tgl_edit')->nullable();
            $table->string('surat_edit')->nullable();
            $table->enum('status_panen', ['1', '2', '3', '4'])->default('1');
            $table->text('alasan_tolak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panen');
    }
};
