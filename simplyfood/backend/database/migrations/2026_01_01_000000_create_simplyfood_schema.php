<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(database_path('sql/simplyfood-db.sql'));
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (preg_split('/;\s*/', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }
            DB::unprepared($stmt);
        }

        $triggers = file_get_contents(database_path('sql/simplyfood-triggers.sql'));
        $triggers = preg_replace('/^DELIMITER .*$/m', '', $triggers);
        $triggers = preg_replace('/^\s*--.*$/m', '', $triggers);
        $triggers = str_replace('$$', ';', $triggers);
        DB::unprepared($triggers);
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
};
