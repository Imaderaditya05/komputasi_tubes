<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mitra_restaurants')) {
            return;
        }

        Schema::table('mitra_restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('mitra_restaurants', 'address_line')) {
                $table->text('address_line')->nullable()->after('description');
            }
            if (! Schema::hasColumn('mitra_restaurants', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address_line');
            }
            if (! Schema::hasColumn('mitra_restaurants', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mitra_restaurants')) {
            return;
        }

        Schema::table('mitra_restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('mitra_restaurants', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('mitra_restaurants', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('mitra_restaurants', 'address_line')) {
                $table->dropColumn('address_line');
            }
        });
    }
};
