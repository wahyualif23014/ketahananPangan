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
    Schema::create('anggota', function (Blueprint $table) {
        $table->unsignedBigInteger('id_anggota')->primary(); // Manual ID sesuai gambar
        $table->unsignedBigInteger('id_jabatan');
        $table->unsignedBigInteger('id_tugas');
        $table->string('nama_anggota');
        $table->string('no_telp_anggota');
        $table->string('username')->unique();
        $table->string('password');
        $table->string('role'); // admin, operator, view
        $table->rememberToken();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
