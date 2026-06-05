<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            // Pengecekan kritikal: jangan buat kalau kolom sudah ada
            if (!Schema::hasColumn('lahan', 'id_poktan')) {
                $table->unsignedBigInteger('id_poktan')->nullable()->after('id_lahan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            if (Schema::hasColumn('lahan', 'id_poktan')) {
                $table->dropColumn('id_poktan');
            }
        });
    }
};