<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedInteger('number');
            $table->unsignedInteger('capacity')->default(2);
            $table->string('location', 120)->nullable();
            $table->string('status', 30)->default('LIVRE');
            $table->string('description', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'number']);
            $table->index(['status', 'deleted_at']);
            $table->index(['user_id', 'deleted_at']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
