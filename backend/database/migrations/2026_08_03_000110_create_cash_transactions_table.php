<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers');
            $table->foreignId('user_id')->constrained('users');
            $table->string('type', 30);
            $table->decimal('amount', 12, 2);
            $table->string('description', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cash_register_id', 'created_at']);
            $table->index(['user_id', 'deleted_at']);
            $table->index(['type', 'deleted_at']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
