<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_partner_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('partner_key');
            $table->foreignId('mitra_restaurant_id')->nullable()->constrained('mitra_restaurants')->nullOnDelete();
            $table->foreignId('admin_restaurant_id')->nullable()->constrained('admin_restaurants')->nullOnDelete();
            $table->string('restaurant_display_name');
            $table->string('box_slug')->nullable();
            $table->string('category', 64)->nullable();
            $table->text('message');
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('partner_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_partner_reports');
    }
};
