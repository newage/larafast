.PHONY: up down build console-php console-node

COMPOSE = docker compose --env-file ../.env

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

build:
	$(COMPOSE) build

console-php:
	$(COMPOSE) exec php bash

console-node:
	$(COMPOSE) exec node sh
