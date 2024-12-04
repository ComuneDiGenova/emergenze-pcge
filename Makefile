.PHONY: all build up down

all: build up

build:
	@echo 'Build dei container backend...'
	@git clone https://github.com/gtergeomatica/py-alert-system.git backend/py-alert-system
	@cd backend && docker compose build

up:
	@cd backend && docker compose up -d

down:
	@cd backend && docker compose down

stop:
	@cd backend && docker compose stop