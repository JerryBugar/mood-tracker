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
        Schema::create('mood_records', function (Blueprint $table) {
            $table->id();
            
            // Kolom "Siapa yang mencatat"
            // Ini menghubungkan ke tabel 'users'
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Kolom untuk data dari modal
            $table->string('mood'); // 'netral', 'senyum', 'sedih', dll.
            $table->text('reason')->nullable(); // Catatan 1 (Kenapa kamu...)
            $table->text('suggestion_action')->nullable(); // Catatan 2 (Apa yang bisa...)
            
            // Kolom "Jam berapa tanggal berapa"
            // 'timestamps()' otomatis membuat 'created_at' dan 'updated_at'
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_records');
    }
};
