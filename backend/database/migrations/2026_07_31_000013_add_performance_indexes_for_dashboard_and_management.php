<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['user_id', 'whatsapp'], 'customers_user_whatsapp_idx');
            $table->index('whatsapp', 'customers_whatsapp_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'orders_user_status_created_at_idx');
            $table->index(['user_id', 'customer_id', 'created_at'], 'orders_user_customer_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['ativo', 'deleted_at'], 'products_active_deleted_idx');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->index(['delivered_at', 'order_id'], 'deliveries_delivered_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex('deliveries_delivered_order_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_deleted_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_created_at_idx');
            $table->dropIndex('orders_user_customer_created_at_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_user_whatsapp_idx');
            $table->dropIndex('customers_whatsapp_idx');
        });
    }
};
