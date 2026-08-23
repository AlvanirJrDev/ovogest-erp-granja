.PHONY: up down build setup migrate seed fresh test bash logs assets

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

setup: ## Sobe o ambiente do zero: build, containers, migrations, seed e assets
	docker compose up -d --build
	docker compose exec app composer install
	docker compose exec app php artisan key:generate --no-interaction
	docker compose exec app php artisan migrate --seed
	docker compose exec app php artisan filament:assets
	docker compose exec app php artisan storage:link
	@echo "Pronto! Acesse http://localhost:8000/admin (admin@granja.test / password)"

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

fresh: ## Recria o banco do zero com seeds
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app php artisan test

bash:
	docker compose exec app sh

logs:
	docker compose logs -f --tail=100

assets:
	docker compose exec app php artisan filament:assets
