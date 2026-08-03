<?php

namespace App\Providers;

use App\Domains\CashRegister\CashRegister;
use App\Domains\Commands\CommandTicket;
use App\Domains\Product\Product;
use App\Domains\Recipe\Ingredient;
use App\Domains\Recipe\Recipe;
use App\Domains\Tables\RestaurantTable;
use App\Policies\CashRegisterPolicy;
use App\Policies\CommandPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RecipePolicy;
use App\Policies\RestaurantTablePolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(CashRegister::class, CashRegisterPolicy::class);
        Gate::policy(RestaurantTable::class, RestaurantTablePolicy::class);
        Gate::policy(CommandTicket::class, CommandPolicy::class);
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(Ingredient::class, IngredientPolicy::class);
    }
}
