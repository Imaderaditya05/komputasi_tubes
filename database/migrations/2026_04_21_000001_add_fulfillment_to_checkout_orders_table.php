<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('checkout_orders', 'fulfillment_status')) {
                $table->string('fulfillment_status', 32)->nullable()->after('payment_redirect_url');
            }
            if (! Schema::hasColumn('checkout_orders', 'pickup_time')) {
                $table->string('pickup_time')->nullable()->after('fulfillment_status');
            }
            if (! Schema::hasColumn('checkout_orders', 'reviewed')) {
                $table->boolean('reviewed')->default(false)->after('pickup_time');
            }
        });

        if (Schema::hasColumn('checkout_orders', 'fulfillment_status')) {
            DB::table('checkout_orders')
                ->whereNull('fulfillment_status')
                ->whereIn('payment_status', ['PAID', 'PENDING_COD'])
                ->update(['fulfillment_status' => 'pending_confirmation']);

            DB::table('checkout_orders')
                ->whereNull('fulfillment_status')
                ->where('payment_status', 'PENDING')
                ->update(['fulfillment_status' => 'awaiting_payment']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_orders', 'reviewed')) {
                $table->dropColumn('reviewed');
            }
            if (Schema::hasColumn('checkout_orders', 'pickup_time')) {
                $table->dropColumn('pickup_time');
            }
            if (Schema::hasColumn('checkout_orders', 'fulfillment_status')) {
                $table->dropColumn('fulfillment_status');
            }
        });
    }
};
