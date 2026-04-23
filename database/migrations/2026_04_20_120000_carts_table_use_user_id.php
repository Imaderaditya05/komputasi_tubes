<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengganti carts.customer_id (tabel customers) dengan user_id (users) agar selaras dengan login Laravel.
     * Membuat ulang cart_items karena SQLite tidak mendukung alter FK yang andal.
     */
    public function up(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->string('box_slug');
            $table->string('box_title');
            $table->string('restaurant_name');
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->integer('stock_available')->default(0);
            $table->timestamps();

            $table->index(['cart_id', 'box_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('customers')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->string('box_slug');
            $table->string('box_title');
            $table->string('restaurant_name');
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->integer('stock_available')->default(0);
            $table->timestamps();

            $table->index(['cart_id', 'box_slug']);
        });
    }
};
