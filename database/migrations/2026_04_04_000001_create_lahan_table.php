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
        Schema::create('lahan', function (Blueprint $table) {
            $table->integer('id_lahan')->primary();
            $table->unsignedBigInteger('id_poktan')->nullable();
            $table->string('id_tingkat', 13)->index('id_tingkat');
            $table->string('id_wilayah', 13)->index('id_wilayah');
            $table->integer('id_jenis_lahan')->index('id_jenis_lahan');
            $table->integer('id_anggota')->nullable()->index('id_anggota');
            $table->integer('id_komoditi')->nullable()->index('id_komoditi');
            $table->string('alamat_lahan')->nullable();
            $table->string('longitude', 25)->nullable();
            $table->string('latitude', 25)->nullable();
            $table->integer('poktan')->nullable();
            $table->string('cp_lahan')->nullable();
            $table->string('no_cp_lahan', 15)->nullable();
            $table->decimal('luas_lahan', 10)->nullable();
            $table->enum('ket_lahan', ['1', '2', '3'])->nullable()->default('3');
            $table->string('no_sk', 20)->nullable();
            $table->string('lembaga_lahan', 50)->nullable();
            $table->string('cp_polisi')->nullable();
            $table->string('no_cp_polisi', 15)->nullable();
            $table->longText('keterangan_lahan')->nullable();
            $table->string('sumber_data_lahan')->nullable();
            $table->string('dokumentasi_lahan')->nullable();
            $table->integer('jml_petani')->nullable();
            $table->dateTime('datetransaction')->nullable();
            $table->enum('deletestatus', ['1', '2'])->nullable()->default('2');
            $table->decimal('con_lahan', 10)->nullable();
            $table->string('ket_polisi')->nullable();
            $table->enum('status_lahan', ['1', '2', '3', '4'])->nullable();
            $table->enum('status_pakai', ['1', '2'])->nullable()->default('1');
            $table->text('surat_edit_lahan')->nullable();
            $table->string('edit_oleh')->nullable();
            $table->dateTime('tgl_edit')->nullable();
            $table->string('valid_oleh')->nullable();
            $table->dateTime('tgl_valid')->nullable();
            $table->year('tahun_lahan')->nullable();
            $table->enum('status_aktif', ['1', '2'])->nullable()->default('2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lahan');
    }
};
