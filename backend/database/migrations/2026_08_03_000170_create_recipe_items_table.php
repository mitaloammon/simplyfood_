<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->decimal('quantity', 12, 3);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['recipe_id', 'ingredient_id']);
            $table->index(['recipe_id', 'deleted_at']);
            $table->index(['ingredient_id', 'created_at']);
            $table->index(['ingredient_id', 'deleted_at']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
    }
};
