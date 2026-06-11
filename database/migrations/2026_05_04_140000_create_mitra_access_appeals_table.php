<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mitra_access_appeals')) {
            return;
        }

        Schema::create('mitra_access_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('mitra_restaurants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('status', 24)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_access_appeals');
    }
};
