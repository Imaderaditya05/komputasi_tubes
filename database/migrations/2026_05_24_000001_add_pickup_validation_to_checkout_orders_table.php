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
            if (! Schema::hasColumn('checkout_orders', 'pickup_validation_status')) {
                $table->string('pickup_validation_status', 48)->nullable()->after('pickup_time');
            }
            if (! Schema::hasColumn('checkout_orders', 'pickup_validation_started_at')) {
                $table->timestamp('pickup_validation_started_at')->nullable()->after('pickup_validation_status');
            }
            if (! Schema::hasColumn('checkout_orders', 'pickup_validation_deadline_at')) {
                $table->timestamp('pickup_validation_deadline_at')->nullable()->after('pickup_validation_started_at');
            }
            if (! Schema::hasColumn('checkout_orders', 'pickup_validated_at')) {
                $table->timestamp('pickup_validated_at')->nullable()->after('pickup_validation_deadline_at');
            }
            if (! Schema::hasColumn('checkout_orders', 'pickup_validation_note')) {
                $table->text('pickup_validation_note')->nullable()->after('pickup_validated_at');
            }
            if (! Schema::hasColumn('checkout_orders', 'pickup_validated_by')) {
                $table->unsignedBigInteger('pickup_validated_by')->nullable()->after('pickup_validation_note');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            $cols = [];
            foreach (
                [
                    'pickup_validated_by',
                    'pickup_validation_note',
                    'pickup_validated_at',
                    'pickup_validation_deadline_at',
                    'pickup_validation_started_at',
                    'pickup_validation_status',
                ] as $c
            ) {
                if (Schema::hasColumn('checkout_orders', $c)) {
                    $cols[] = $c;
                }
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
