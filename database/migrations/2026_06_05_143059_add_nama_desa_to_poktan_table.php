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
        Schema::table('poktan', function (Blueprint $table) {
            $table->string('nama_desa', 255)->nullable()->after('nama_poktan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poktan', function (Blueprint $table) {
            $table->dropColumn('nama_desa');
        });
    }
};
