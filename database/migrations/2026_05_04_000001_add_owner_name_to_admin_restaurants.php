<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_restaurants') && ! Schema::hasColumn('admin_restaurants', 'owner_name')) {
            Schema::table('admin_restaurants', function (Blueprint $table) {
                $table->string('owner_name', 200)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin_restaurants') && Schema::hasColumn('admin_restaurants', 'owner_name')) {
            Schema::table('admin_restaurants', function (Blueprint $table) {
                $table->dropColumn('owner_name');
            });
        }
    }
};
