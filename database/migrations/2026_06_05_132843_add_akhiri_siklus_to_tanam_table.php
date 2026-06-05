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
        Schema::table('tanam', function (Blueprint $table) {
            $table->tinyInteger('status_akhiri_siklus')->default(0)->comment('0=Belum, 1=Diajukan, 2=Diterima, 3=Ditolak');
            $table->text('alasan_tolak_akhiri_siklus')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanam', function (Blueprint $table) {
            $table->dropColumn(['status_akhiri_siklus', 'alasan_tolak_akhiri_siklus']);
        });
    }
};
