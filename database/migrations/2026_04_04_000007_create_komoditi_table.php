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
        Schema::create('komoditi', function (Blueprint $table) {
            $table->integer('id_komoditi', true);
            $table->string('jenis_komoditi', 100)->nullable();
            $table->string('nama_komoditi', 100)->nullable();
            $table->dateTime('datetransaction')->nullable();
            $table->enum('deletestatus', ['1', '2'])->nullable()->default('2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komoditi');
    }
};
