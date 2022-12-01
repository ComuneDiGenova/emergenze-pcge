include .env
requirements-update:
	git clone "https://github.com/gtergeomatica/py-alert-system.git" 2> /dev/null || git -C "py-alert-system" pull
build:
	docker-compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)
up-d:
	docker-compose up -d
down-v:
	docker-compose down -v
