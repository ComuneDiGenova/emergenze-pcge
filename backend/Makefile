include .env
requirements-update:
	git clone "https://github.com/gtergeomatica/py-alert-system.git" 2> /dev/null || git -C "py-alert-system" pull
build:
	docker-compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)
build-4-test:
	docker-compose build --build-arg UID=418419718 --build-arg GID=418419718
up-d:
	docker-compose up -d
down-v:
	docker-compose down -v
