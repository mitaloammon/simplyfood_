<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('expected_amount', 12, 2);
            $table->decimal('declared_amount', 12, 2);
            $table->decimal('difference', 12, 2)->default(0);
            $table->boolean('blind_closing')->default(false);
            $table->string('notes', 500)->nullable();
            $table->timestamp('closed_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cash_register_id', 'created_at']);
            $table->index(['user_id', 'deleted_at']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
    }
};
