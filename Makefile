
SHELL = /bin/sh

CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

export CURRENT_UID
export CURRENT_GID

.PHONY: all build up down

all: build up

build:
	@echo 'Build dei container backend...'
	@cd backend && git clone https://github.com/gtergeomatica/py-alert-system.git backend/py-alert-system 2> /dev/null || git -C "py-alert-system" pull
	@cd backend && docker compose build --build-arg UID=${CURRENT_UID} --build-arg GID=${CURRENT_UID}

up-storage:
	@docker compose up postgresql

up:
	@docker compose up postgresql -d
	@cd backend && docker compose up -d

down:
	@docker compose down
	@cd backend && docker compose down

stop:
	@cd backend && docker compose stop