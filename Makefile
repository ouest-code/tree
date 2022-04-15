.PHONY: test
test:
	docker run --rm --tty -it -w /app -v $PWD:/app composer install
	docker run --rm --tty -it -w /app -v $PWD:/app php:8.1-cli ./vendor/bin/phpunit