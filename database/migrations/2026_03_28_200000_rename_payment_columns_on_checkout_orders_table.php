<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_orders', 'xendit_invoice_id')
                && ! Schema::hasColumn('checkout_orders', 'midtrans_transaction_id')) {
                $table->renameColumn('xendit_invoice_id', 'midtrans_transaction_id');
            }
            if (Schema::hasColumn('checkout_orders', 'xendit_status')
                && ! Schema::hasColumn('checkout_orders', 'payment_status')) {
                $table->renameColumn('xendit_status', 'payment_status');
            }
            if (Schema::hasColumn('checkout_orders', 'xendit_payment_url')
                && ! Schema::hasColumn('checkout_orders', 'payment_redirect_url')) {
                $table->renameColumn('xendit_payment_url', 'payment_redirect_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_orders', 'midtrans_transaction_id')
                && ! Schema::hasColumn('checkout_orders', 'xendit_invoice_id')) {
                $table->renameColumn('midtrans_transaction_id', 'xendit_invoice_id');
            }
            if (Schema::hasColumn('checkout_orders', 'payment_status')
                && ! Schema::hasColumn('checkout_orders', 'xendit_status')) {
                $table->renameColumn('payment_status', 'xendit_status');
            }
            if (Schema::hasColumn('checkout_orders', 'payment_redirect_url')
                && ! Schema::hasColumn('checkout_orders', 'xendit_payment_url')) {
                $table->renameColumn('payment_redirect_url', 'xendit_payment_url');
            }
        });
    }
};
