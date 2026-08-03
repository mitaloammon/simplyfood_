# Release Notes

## [3.1.0] - 2026-08-03

### Added
- Novo módulo de Caixa desacoplado com rotas:
	- `POST /api/cash/open`
	- `POST /api/cash/transaction`
	- `POST /api/cash/close`
	- `GET /api/cash/current`
	- `GET /api/cash/history`
- Novo módulo de Mesas desacoplado com rotas:
	- `GET /api/tables`
	- `POST /api/tables`
	- `PATCH /api/tables/{id}/status`
- Novo módulo de Comandas desacoplado com rotas:
	- `GET /api/commands`
	- `POST /api/commands`
	- `PATCH /api/commands/{id}/status`
- Novo módulo de Ficha Técnica e Estoque desacoplado com rotas:
	- `GET /api/ingredients`
	- `POST /api/ingredients`
	- `GET /api/recipes`
	- `POST /api/recipes`
	- `POST /api/recipes/{recipeId}/items`
	- `POST /api/recipes/{recipeId}/consume`

### Infrastructure
- Adicionadas migrations com SoftDeletes, FKs e índices para:
	- `cash_registers`, `cash_transactions`, `cash_closings`
	- `restaurant_tables`, `commands`
	- `ingredients`, `recipes`, `recipe_items`, `stock_movements`

### Security
- Policies adicionadas para novos domínios:
	- `CashRegisterPolicy`
	- `RestaurantTablePolicy`
	- `CommandPolicy`
	- `RecipePolicy`
	- `IngredientPolicy`

### Tests
- Novos testes de feature:
	- `CashRegisterApiTest`
	- `RestaurantTableApiTest`
	- `CommandApiTest`
	- `RecipeApiTest`
- Regressão validada com suíte completa do backend passando.

## [Unreleased](https://github.com/laravel/laravel/compare/v12.0.0...master)

## [v12.0.0 (2025-??-??)](https://github.com/laravel/laravel/compare/v11.0.2...v12.0.0)

Laravel 12 includes a variety of changes to the application skeleton. Please consult the diff to see what's new.
