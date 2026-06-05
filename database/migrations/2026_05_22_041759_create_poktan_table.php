<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop dulu kalau ada, biar gak error "Table already exists"
        Schema::dropIfExists('poktan');

        Schema::create('poktan', function (Blueprint $table) {
            $table->bigIncrements('id_poktan');
            $table->string('id_polda', 50)->nullable();
            $table->string('id_polres', 50)->nullable();
            $table->string('id_polsek', 50)->nullable();
            $table->string('nama_poktan');
            $table->double('luas_lahan', null, 0)->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poktan');
    }
};