<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('preco_venda', 10, 2)->nullable()->after('preco');
            $table->decimal('custo', 10, 2)->nullable()->after('preco_venda');
            $table->string('unidade', 30)->default('UN')->after('custo');
            $table->string('codigo_barras', 64)->nullable()->after('unidade');
            $table->boolean('controla_estoque')->default(false)->after('ativo');
            $table->boolean('produzido_cozinha')->default(true)->after('controla_estoque');
            $table->boolean('delivery')->default(true)->after('produzido_cozinha');
            $table->boolean('balcao')->default(true)->after('delivery');
            $table->boolean('mesa')->default(true)->after('balcao');
            $table->boolean('retirada')->default(true)->after('mesa');
            $table->foreignId('created_by')->nullable()->after('retirada')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            $table->unique('codigo_barras', 'products_codigo_barras_unique');
            $table->index(['category_id', 'ativo'], 'products_category_active_idx');
        });

        DB::table('products')
            ->whereNull('preco_venda')
            ->update(['preco_venda' => DB::raw('preco')]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_active_idx');
            $table->dropUnique('products_codigo_barras_unique');

            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');

            $table->dropColumn([
                'preco_venda',
                'custo',
                'unidade',
                'codigo_barras',
                'controla_estoque',
                'produzido_cozinha',
                'delivery',
                'balcao',
                'mesa',
                'retirada',
            ]);
        });
    }
};
