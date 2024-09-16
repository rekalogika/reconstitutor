.PHONY: test
test: phpstan psalm phpunit

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse

.PHONY: psalm
psalm:
	vendor/bin/psalm

.PHONY: phpunit
phpunit:
	vendor/bin/phpunit

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer
	$< fix --config=.php-cs-fixer.dist.php --verbose --allow-risky=yes

.PHONY: tools/php-cs-fixer
tools/php-cs-fixer:
	phive install php-cs-fixer