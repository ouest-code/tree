vendor: composer.json composer.lock
	docker run --rm --tty -it -w /app -v ${PWD}:/app composer install --ignore-platform-reqs

.PHONY: test
test: vendor
	docker run --rm --tty -it -w /app -v ${PWD}:/app php:8.1-cli ./vendor/bin/phpunit
