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
        Schema::create('distribusi', function (Blueprint $table) {
            $table->integer('id_distribusi')->primary();
            $table->integer('id_lahan')->index('id_lahan');
            $table->integer('id_panen')->index('id_panen');
            $table->integer('id_tanam')->index('id_tanam');
            $table->integer('id_anggota')->index('id_anggota');
            $table->string('distribusi_ke', 100)->nullable();
            $table->date('tgl_distribusi')->nullable();
            $table->longText('keterangan_distribusi')->nullable();
            $table->decimal('total_distribusi', 10)->nullable();
            $table->enum('deletestatus', ['1', '2'])->nullable()->default('2');
            $table->dateTime('datetransaction')->nullable();
            $table->integer('valid_oleh')->nullable();
            $table->dateTime('tgl_valid')->nullable();
            $table->integer('edit_oleh')->nullable();
            $table->dateTime('tgl_edit')->nullable();
            $table->text('surat_edit')->nullable();
            $table->text('alasan_tolak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribusi');
    }
};
