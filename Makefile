
SHELL = /bin/sh

CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

export CURRENT_UID
export CURRENT_GID

.PHONY: all build up down

all: build up

# Storage

up-storage:
	@docker compose up postgresql -d

down-storage:
	@docker compose down

# Backend

build-backend:
	echo 'Build dei container backend...'
	@cd backend && git clone https://github.com/gtergeomatica/py-alert-system.git backend/py-alert-system 2> /dev/null || git -C "py-alert-system" pull
	@cd backend && docker compose build --build-arg UID=${CURRENT_UID} --build-arg GID=${CURRENT_GID}

up-backend-only:
	@cd backend && docker compose up -d

up-backend: up-storage up-backend-only

down-backend:
	@cd backend && docker compose down

# Bot

build-bot:
	@cd telegram && docker compose build

up-bot-only:
	@cd telegram && docker compose up -d

restart-bot:
	@cd telegram && docker compose restart

up-bot: up-storage up-bot-only

down-bot:
	@cd telegram && docker compose down

build: build-backend build-bot

up: up-storage up-backend-only up-bot-only

down: down-storage down-backend down-backenddown-bot
