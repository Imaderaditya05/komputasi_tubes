<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mitra_notifications')) {
            return;
        }

        Schema::create('mitra_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->string('title');
            $table->text('body');
            $table->string('public_order_id', 64)->nullable()->index();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->unique(['user_id', 'type', 'public_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_notifications');
    }
};
