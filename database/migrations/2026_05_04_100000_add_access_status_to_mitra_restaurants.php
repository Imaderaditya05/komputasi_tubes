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

        if (! Schema::hasColumn('mitra_restaurants', 'access_status')) {
            Schema::table('mitra_restaurants', function (Blueprint $table) {
                $table->string('access_status', 20)->default('active')->after('pin');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mitra_restaurants')) {
            return;
        }

        if (Schema::hasColumn('mitra_restaurants', 'access_status')) {
            Schema::table('mitra_restaurants', function (Blueprint $table) {
                $table->dropColumn('access_status');
            });
        }
    }
};
