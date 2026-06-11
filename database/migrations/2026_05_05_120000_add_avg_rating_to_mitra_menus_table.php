<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mitra_menus')) {
            return;
        }

        Schema::table('mitra_menus', function (Blueprint $table) {
            if (! Schema::hasColumn('mitra_menus', 'avg_rating')) {
                $table->decimal('avg_rating', 3, 2)->nullable();
            }
            if (! Schema::hasColumn('mitra_menus', 'ratings_count')) {
                $table->unsignedInteger('ratings_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mitra_menus')) {
            return;
        }

        Schema::table('mitra_menus', function (Blueprint $table) {
            if (Schema::hasColumn('mitra_menus', 'ratings_count')) {
                $table->dropColumn('ratings_count');
            }
            if (Schema::hasColumn('mitra_menus', 'avg_rating')) {
                $table->dropColumn('avg_rating');
            }
        });
    }
};
