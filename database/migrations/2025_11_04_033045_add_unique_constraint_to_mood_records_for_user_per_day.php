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
        // Menambahkan kolom date_recorded untuk menyimpan tanggal dari created_at
        Schema::table('mood_records', function (Blueprint $table) {
            $table->date('date_recorded')->nullable();
        });
        
        // Mengisi kolom date_recorded dengan tanggal dari created_at untuk data yang sudah ada
        \DB::table('mood_records')->update([
            'date_recorded' => \DB::raw('DATE(created_at)')
        ]);
        
        // Sekarang buat kolom menjadi tidak nullable dan tambahkan indeks unik
        Schema::table('mood_records', function (Blueprint $table) {
            $table->date('date_recorded')->nullable(false)->change();
            $table->unique(['user_id', 'date_recorded'], 'unique_user_mood_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mood_records', function (Blueprint $table) {
            $table->dropUnique('unique_user_mood_per_day');
            $table->dropColumn('date_recorded');
        });
    }
};
