.PHONY: test
test: phpstan psalm phpunit

.PHONY: clean
clean:
	rm -rf tests/var/cache/*

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse

.PHONY: psalm
psalm:
	vendor/bin/psalm

.PHONY: phpunit
phpunit: clean
	vendor/bin/phpunit

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer
	PHP_CS_FIXER_IGNORE_ENV=1 $< fix --config=.php-cs-fixer.dist.php --verbose --allow-risky=yes

.PHONY: tools/php-cs-fixer
tools/php-cs-fixer:
	phive install php-cs-fixer

.PHONY: rector
rector:
	$(PHP) vendor/bin/rector process > rector.log
	make php-cs-fixer
