<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->foreignId('customer_id')->nullable()->change();
                $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 30)->default('BALCAO')->after('status');
            }

            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('total');
            }

            if (!Schema::hasColumn('orders', 'surcharge')) {
                $table->decimal('surcharge', 10, 2)->default(0)->after('discount');
            }

            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('surcharge');
            }

            $table->index(['user_id', 'order_type', 'status', 'created_at'], 'orders_user_type_status_created_idx');
            $table->index(['user_id', 'total', 'created_at'], 'orders_user_total_created_idx');
        });

        DB::table('orders')->whereNull('order_type')->update(['order_type' => 'BALCAO']);

        Schema::create('order_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at'], 'order_timelines_order_created_idx');
            $table->index(['event_type'], 'order_timelines_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_timelines');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_type_status_created_idx');
            $table->dropIndex('orders_user_total_created_idx');

            if (Schema::hasColumn('orders', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('orders', 'surcharge')) {
                $table->dropColumn('surcharge');
            }

            if (Schema::hasColumn('orders', 'discount')) {
                $table->dropColumn('discount');
            }

            if (Schema::hasColumn('orders', 'order_type')) {
                $table->dropColumn('order_type');
            }

            if (Schema::hasColumn('orders', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->foreignId('customer_id')->nullable(false)->change();
                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            }
        });
    }
};
