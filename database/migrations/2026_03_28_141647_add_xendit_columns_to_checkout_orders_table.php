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
        Schema::table('checkout_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('checkout_orders', 'midtrans_transaction_id')) {
                $table->string('midtrans_transaction_id')->nullable()->unique()->after('delivery_address');
            }
            if (! Schema::hasColumn('checkout_orders', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('midtrans_transaction_id');
            }
            if (! Schema::hasColumn('checkout_orders', 'payment_redirect_url')) {
                $table->text('payment_redirect_url')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkout_orders', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('checkout_orders', 'payment_redirect_url')) {
                $cols[] = 'payment_redirect_url';
            }
            if (Schema::hasColumn('checkout_orders', 'payment_status')) {
                $cols[] = 'payment_status';
            }
            if (Schema::hasColumn('checkout_orders', 'midtrans_transaction_id')) {
                $cols[] = 'midtrans_transaction_id';
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
