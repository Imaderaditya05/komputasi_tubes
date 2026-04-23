<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => Hash::make('password'), 'role' => 'customer']
        );

        // Akun admin untuk /login/admin — memakai tabel users (bukan tabel admins legacy).
        $adminEmail = env('ADMIN_EMAIL', 'admin@surprisebite.test');
        $adminPassword = env('ADMIN_PASSWORD', 'password');
        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin SurpriseBite',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]
        );

        DB::table('admins')->updateOrInsert(
            ['email' => 'admin@surprisebite.test'],
            [
                'name' => 'Admin SurpriseBite',
                'password' => Hash::make('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('customers')->updateOrInsert(
            ['email' => 'user@surprisebite.test'],
            [
                'name' => 'User SurpriseBite',
                'password' => Hash::make('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->call(AdminCatalogSeeder::class);
    }
}
