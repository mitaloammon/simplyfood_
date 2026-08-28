<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->runSqlFile(database_path('sql/simplyfood-db.sql'));
        $this->runSqlFile(database_path('sql/simplyfood-triggers.sql'));
    }

    public function down(): void
    {
        $tables = [
            'stock_movements', 'product_ingredients', 'inventory_items', 'payments',
            'order_status_history', 'order_items', 'orders', 'cash_movements',
            'cash_register_shifts', 'cash_registers', 'commands', 'tables',
            'products', 'categories', 'customers', 'users', 'establishments',
        ];
        foreach ($tables as $table) {
            DB::unprepared("DROP TABLE IF EXISTS `{$table}`");
        }
    }

    private function runSqlFile(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException("Unable to read SQL file: {$path}");
        }

        $delimiter = ';';
        $statement = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            if (preg_match('/^DELIMITER\s+(\S+)$/i', $trimmed, $matches) === 1) {
                $delimiter = $matches[1];
                continue;
            }

            $statement .= $line."\n";

            if (! str_ends_with(rtrim($statement), $delimiter)) {
                continue;
            }

            $statement = rtrim($statement);
            $statement = substr($statement, 0, -strlen($delimiter));
            DB::unprepared(trim($statement));
            $statement = '';
        }

        if (trim($statement) !== '') {
            DB::unprepared(trim($statement));
        }
    }
};
