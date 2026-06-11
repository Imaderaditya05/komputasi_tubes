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
            if (! Schema::hasColumn('checkout_orders', 'customer_rating')) {
                $table->unsignedTinyInteger('customer_rating')->nullable();
            }
            if (! Schema::hasColumn('checkout_orders', 'customer_review_comment')) {
                $table->string('customer_review_comment', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_orders', 'customer_review_comment')) {
                $table->dropColumn('customer_review_comment');
            }
            if (Schema::hasColumn('checkout_orders', 'customer_rating')) {
                $table->dropColumn('customer_rating');
            }
        });
    }
};
