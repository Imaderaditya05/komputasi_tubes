<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportSqliteToMysql extends Command
{
    protected $signature = 'db:import-sqlite
                            {--path= : Path ke file .sqlite (default: database/database.sqlite)}
                            {--fresh : Kosongkan MySQL dengan migrate:fresh lalu impor (disarankan)}
                            {--force : Tanpa konfirmasi (untuk --fresh)}';

    protected $description = 'Salin data dari database SQLite lama ke MySQL (koneksi default)';

    /** Urutan tabel: induk dulu agar foreign key terpenuhi. */
    private const TABLE_ORDER = [
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'admins',
        'customers',
        'sellers',
        'restaurants',
        'mystery_boxes',
        'orders',
        'payments',
        'deliveries',
        'reviews',
        'checkout_orders',
        'stores',
        'products',
        'store_orders',
        'mitra_restaurants',
        'mitra_menus',
        'mitra_orders',
        'carts',
        'cart_items',
        'settings',
        'admin_restaurants',
    ];

    public function handle(): int
    {
        $path = $this->option('path') ?: database_path('database.sqlite');
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (! is_readable($path)) {
            $this->error('File SQLite tidak ditemukan atau tidak bisa dibaca: '.$path);
            $this->line('Salin backup .sqlite ke path tersebut, atau gunakan --path=...');

            return self::FAILURE;
        }

        config(['database.connections.sqlite_legacy.database' => $path]);
        DB::purge('sqlite_legacy');
        DB::reconnect('sqlite_legacy');

        $mysql = config('database.default');
        if (! in_array(config("database.connections.{$mysql}.driver"), ['mysql', 'mariadb'], true)) {
            $this->error('Koneksi default harus mysql/mariadb (sekarang: '.config("database.connections.{$mysql}.driver").').');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->option('force') && ! $this->confirm('Ini akan menghapus SEMUA data di database MySQL saat ini, lalu mengimpor dari SQLite. Lanjutkan?', false)) {
                return self::FAILURE;
            }
            $this->info('Menjalankan migrate:fresh...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->output->write(Artisan::output());
        }

        $sqliteTables = $this->sqliteTableNames();
        if ($sqliteTables === []) {
            $this->error('Tidak ada tabel di file SQLite.');

            return self::FAILURE;
        }

        $ordered = $this->orderedTables($sqliteTables);
        $this->info('Mengimpor ke '.$mysql.' dari '.$path);

        Schema::connection($mysql)->disableForeignKeyConstraints();

        try {
            foreach ($ordered as $table) {
                if ($table === 'migrations') {
                    continue;
                }
                if (! Schema::connection($mysql)->hasTable($table)) {
                    $this->warn("Lewati {$table}: tidak ada di MySQL.");

                    continue;
                }
                $n = $this->copyTable($table, $mysql);
                if ($n > 0) {
                    $this->line("  {$table}: {$n} baris");
                }
            }
        } finally {
            Schema::connection($mysql)->enableForeignKeyConstraints();
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function sqliteTableNames(): array
    {
        $rows = DB::connection('sqlite_legacy')->select(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        );

        return array_values(array_map(fn ($r) => $r->name, $rows));
    }

    /**
     * @param  list<string>  $sqliteTableNames
     * @return list<string>
     */
    private function orderedTables(array $sqliteTableNames): array
    {
        $set = array_flip($sqliteTableNames);
        $ordered = [];
        foreach (self::TABLE_ORDER as $t) {
            if (isset($set[$t])) {
                $ordered[] = $t;
            }
        }
        $remaining = array_diff($sqliteTableNames, $ordered);
        sort($remaining);

        return array_merge($ordered, $remaining);
    }

    private function copyTable(string $table, string $mysqlConnection): int
    {
        if (! Schema::connection('sqlite_legacy')->hasTable($table)) {
            return 0;
        }

        $dstCols = Schema::connection($mysqlConnection)->getColumnListing($table);

        $rows = DB::connection('sqlite_legacy')->table($table)->get();
        $count = 0;

        foreach ($rows as $row) {
            $data = $this->prepareRow($table, (array) $row, $dstCols);
            if ($data === null) {
                continue;
            }
            try {
                DB::connection($mysqlConnection)->table($table)->insert($data);
                $count++;
            } catch (\Throwable $e) {
                $this->warn("  {$table}: lewati satu baris — ".$e->getMessage());
            }
        }

        return $count;
    }

    /**
     * @param  list<string>  $dstCols
     * @return array<string, mixed>|null
     */
    private function prepareRow(string $table, array $row, array $dstCols): ?array
    {
        if ($table === 'carts') {
            $row = $this->mapCartsRow($row);
            if (isset($row['customer_id']) && ! isset($row['user_id'])) {
                $this->warn('  carts: lewati baris (customer_id tidak dipetakan ke users).');

                return null;
            }
        }

        $out = [];
        foreach ($dstCols as $col) {
            if (! array_key_exists($col, $row)) {
                continue;
            }
            $out[$col] = $this->normalizeValue($row[$col]);
        }

        return $out === [] ? null : $out;
    }

    /**
     * Migrasi lama: carts.customer_id → carts.user_id (email customers ↔ users).
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapCartsRow(array $row): array
    {
        if (isset($row['customer_id']) && ! isset($row['user_id'])) {
            $cid = $row['customer_id'];
            $email = DB::connection('sqlite_legacy')->table('customers')->where('id', $cid)->value('email');
            if ($email) {
                $uid = DB::connection(config('database.default'))->table('users')->where('email', $email)->value('id');
                if ($uid) {
                    $row['user_id'] = $uid;
                }
            }
            unset($row['customer_id']);
        }

        return $row;
    }

    private function normalizeValue(mixed $v): mixed
    {
        if ($v === '') {
            return $v;
        }

        return $v;
    }
}
