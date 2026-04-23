<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mitra_menus', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->decimal('original_price', 12, 2)->default(0)->after('price');
            $table->unsignedInteger('stock')->default(0)->after('original_price');
            $table->string('pickup_time')->nullable()->after('stock');
            $table->string('image_url', 2048)->nullable()->after('pickup_time');
        });
    }

    public function down(): void
    {
        Schema::table('mitra_menus', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'original_price',
                'stock',
                'pickup_time',
                'image_url',
            ]);
        });
    }
};
