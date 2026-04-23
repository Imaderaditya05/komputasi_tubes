<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('mitra_restaurants')->cascadeOnDelete();
            $table->string('customer_name');
            $table->decimal('total_price', 12, 2);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_orders');
    }
};
