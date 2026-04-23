<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_order_id')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('customer_email');
            $table->string('box_slug');
            $table->string('box_title');
            $table->string('restaurant_name');
            $table->unsignedInteger('amount_idr');
            $table->string('payment_method', 32);
            $table->string('fulfillment_method', 16);
            $table->text('delivery_address')->nullable();
            
            // Xendit Payment Fields
            $table->string('xendit_invoice_id')->nullable()->unique();
            $table->string('xendit_status')->nullable();
            $table->text('xendit_payment_url')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_orders');
    }
};
