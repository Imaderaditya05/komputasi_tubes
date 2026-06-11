<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mitra_approval_status', 20)->nullable()->index();
            $table->timestamp('mitra_approval_decided_at')->nullable();
        });

        if (Schema::hasColumn('users', 'mitra_approval_status')) {
            $stamp = now();
            DB::table('users')->where('role', 'mitra')->update([
                'mitra_approval_status' => 'approved',
                'mitra_approval_decided_at' => $stamp,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mitra_approval_status', 'mitra_approval_decided_at']);
        });
    }
};
