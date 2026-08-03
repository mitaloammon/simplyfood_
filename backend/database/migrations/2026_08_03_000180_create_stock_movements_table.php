<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->string('movement_type', 30);
            $table->decimal('quantity', 12, 3);
            $table->decimal('balance_before', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('moved_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ingredient_id', 'created_at']);
            $table->index(['user_id', 'deleted_at']);
            $table->index(['movement_type', 'deleted_at']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
