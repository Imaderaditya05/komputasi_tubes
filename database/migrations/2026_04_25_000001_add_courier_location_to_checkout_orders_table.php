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
            if (! Schema::hasColumn('checkout_orders', 'courier_latitude')) {
                $table->decimal('courier_latitude', 10, 7)->nullable()->after('restaurant_longitude');
            }
            if (! Schema::hasColumn('checkout_orders', 'courier_longitude')) {
                $table->decimal('courier_longitude', 10, 7)->nullable()->after('courier_latitude');
            }
            if (! Schema::hasColumn('checkout_orders', 'courier_updated_at')) {
                $table->timestamp('courier_updated_at')->nullable()->after('courier_longitude');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_orders', 'courier_updated_at')) {
                $table->dropColumn('courier_updated_at');
            }
            if (Schema::hasColumn('checkout_orders', 'courier_longitude')) {
                $table->dropColumn('courier_longitude');
            }
            if (Schema::hasColumn('checkout_orders', 'courier_latitude')) {
                $table->dropColumn('courier_latitude');
            }
        });
    }
};
