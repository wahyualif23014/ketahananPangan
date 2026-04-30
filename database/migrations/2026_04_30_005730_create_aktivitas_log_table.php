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
        Schema::create('aktivitas_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username', 100)->nullable();
            $table->string('nama_user', 200)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('aksi', 20);                   // create | update | delete | validasi | unvalidasi | verify
            $table->string('modul', 100);                 // potensi_lahan | tanam | panen | serapan | anggota | dll
            $table->string('label_modul', 150)->nullable(); // e.g. "Data Tanam - Lahan #12"
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('data_lama')->nullable();         // JSON snapshot sebelum edit
            $table->text('data_baru')->nullable();         // JSON snapshot sesudah edit
            $table->string('keterangan', 500)->nullable(); // ringkasan human-readable
            $table->string('ip_address', 45)->nullable();
            $table->tinyInteger('bulan')->nullable();      // 1-12 untuk filter per bulan
            $table->smallInteger('tahun')->nullable();     // tahun
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['modul']);
            $table->index(['tahun', 'bulan']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_log');
    }
};
