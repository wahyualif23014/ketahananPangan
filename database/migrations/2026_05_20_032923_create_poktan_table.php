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
        Schema::create('poktan', function (Blueprint $table) {
            $table->id('id_poktan');
            $table->string('id_polda', 50)->nullable();
            $table->string('id_polres', 50)->nullable();
            $table->string('id_polsek', 50)->nullable();
            $table->string('nama_poktan', 255);
            $table->double('luas_lahan')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poktan');
    }
};
