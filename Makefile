up:
	docker compose up -d --build
down:
	docker compose down
sh:
	docker compose exec app sh
key:
	docker compose exec app php artisan key:generate
migrate:
	docker compose exec app php artisan migrate --seed
t:
	docker compose exec app ./vendor/bin/pest
