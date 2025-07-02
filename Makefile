# .PHONY: init
# .PHONY: mb
.PHONY: build
.PHONY: up
.PHONY: stop
.PHONY: down
.PHONY: bash

# init:
# 	docker-compose run app rails new . --force --database=mysql -T --javascript=esbuild --skip-bundle
# 	@make build

# mb:
# 	docker-compose run app rails db:create

build:
	docker compose build

up:
	docker compose up -d

stop:
	docker compose stop

down:
	docker compose down

bash:
	docker compose exec php bash

dbbash:
	docker compose exec db bash