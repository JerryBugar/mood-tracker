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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'individual', 'group', 'all'
            $table->text('message');
            $table->string('division')->nullable(); // Untuk group
            $table->timestamp('scheduled_at')->nullable(); // Untuk jadwal kirim
            $table->timestamps();
        });

        Schema::create('notification_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_user');
        Schema::dropIfExists('notifications');
    }
};
