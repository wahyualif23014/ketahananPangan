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
        Schema::create('tingkatwilayah', function (Blueprint $table) {
            $table->integer('id_tingkat_wilayah')->primary();
            $table->string('id_tingkat', 8)->index('id_tingkat');
            $table->string('id_wilayah', 13)->index('id_wilayah');
            $table->integer('id_anggota');
            $table->timestamp('datetransaction');
            $table->enum('deletestatus', ['1', '2'])->default('2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tingkatwilayah');
    }
};
