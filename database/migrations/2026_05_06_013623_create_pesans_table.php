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
        Schema::create('pesans', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesan', 50)->unique();
            $table->string('sender_id', 50);
            $table->string('recipient_id', 50);
            $table->string('judul')->nullable();
            $table->text('isi_pesan');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            
            // Foreign keys if necessary, assuming references to anggota
            // For now, no constraints in case we use string IDs or integer IDs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesans');
    }
};
