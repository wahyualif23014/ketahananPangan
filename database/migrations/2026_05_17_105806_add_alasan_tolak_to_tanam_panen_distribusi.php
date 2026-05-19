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
            if (!Schema::hasColumn('tanam', 'alasan_tolak')) {
                $table->text('alasan_tolak')->nullable();
            }
        });
        Schema::table('panen', function (Blueprint $table) {
            if (!Schema::hasColumn('panen', 'alasan_tolak')) {
                $table->text('alasan_tolak')->nullable();
            }
        });
        Schema::table('distribusi', function (Blueprint $table) {
            if (!Schema::hasColumn('distribusi', 'alasan_tolak')) {
                $table->text('alasan_tolak')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanam', function (Blueprint $table) {
            if (Schema::hasColumn('tanam', 'alasan_tolak')) {
                $table->dropColumn('alasan_tolak');
            }
        });
        Schema::table('panen', function (Blueprint $table) {
            if (Schema::hasColumn('panen', 'alasan_tolak')) {
                $table->dropColumn('alasan_tolak');
            }
        });
        Schema::table('distribusi', function (Blueprint $table) {
            if (Schema::hasColumn('distribusi', 'alasan_tolak')) {
                $table->dropColumn('alasan_tolak');
            }
        });
    }
};
