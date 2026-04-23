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
            if (! Schema::hasColumn('checkout_orders', 'item_quantity')) {
                $table->unsignedInteger('item_quantity')->default(1)->after('amount_idr');
            }
            if (! Schema::hasColumn('checkout_orders', 'menu_stock_applied')) {
                $table->boolean('menu_stock_applied')->default(false)->after('item_quantity');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_orders', 'menu_stock_applied')) {
                $table->dropColumn('menu_stock_applied');
            }
            if (Schema::hasColumn('checkout_orders', 'item_quantity')) {
                $table->dropColumn('item_quantity');
            }
        });
    }
};
