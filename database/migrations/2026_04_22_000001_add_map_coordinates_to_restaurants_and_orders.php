<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_restaurants')) {
            Schema::table('admin_restaurants', function (Blueprint $table) {
                if (! Schema::hasColumn('admin_restaurants', 'address_line')) {
                    $table->text('address_line')->nullable()->after('image_url');
                }
                if (! Schema::hasColumn('admin_restaurants', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('address_line');
                }
                if (! Schema::hasColumn('admin_restaurants', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
            });
        }

        if (Schema::hasTable('checkout_orders')) {
            Schema::table('checkout_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('checkout_orders', 'restaurant_latitude')) {
                    $table->decimal('restaurant_latitude', 10, 7)->nullable()->after('delivery_address');
                }
                if (! Schema::hasColumn('checkout_orders', 'restaurant_longitude')) {
                    $table->decimal('restaurant_longitude', 10, 7)->nullable()->after('restaurant_latitude');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('checkout_orders')) {
            Schema::table('checkout_orders', function (Blueprint $table) {
                if (Schema::hasColumn('checkout_orders', 'restaurant_longitude')) {
                    $table->dropColumn('restaurant_longitude');
                }
                if (Schema::hasColumn('checkout_orders', 'restaurant_latitude')) {
                    $table->dropColumn('restaurant_latitude');
                }
            });
        }

        if (Schema::hasTable('admin_restaurants')) {
            Schema::table('admin_restaurants', function (Blueprint $table) {
                if (Schema::hasColumn('admin_restaurants', 'longitude')) {
                    $table->dropColumn('longitude');
                }
                if (Schema::hasColumn('admin_restaurants', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('admin_restaurants', 'address_line')) {
                    $table->dropColumn('address_line');
                }
            });
        }
    }
};
