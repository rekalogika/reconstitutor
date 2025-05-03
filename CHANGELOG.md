# Changelog

## 1.4.0

* test: use full Symfony framework-bundle for testing
* test: test new & legacy proxy
* deps: cleanup dependencies
* chore: update to phpstan 2 and psalm 6
* chore: rector run
* chore: chore: update pending issue URL
* fix: depends on `doctrine/orm` version containing important bug fix
  https://github.com/doctrine/orm/pull/11917
* ci: add recurring schedule
* deprecation: internalize `ReconstitutorResolverInterface`

## 1.3.2

* fix: handle cases where an uninitialized proxy gets a `postFlush` event.

## 1.3.0

* chore: php-cs-fixer run
* feat: php 8.4 compatibility by @priyadi in #4
* Create dependabot.yml by @priyadi in #5
* chore: rectorization by @priyadi in #7


## 1.2.0

* feat: Supports ORM 3

## 1.1.0

* Support Symfony 7

## 1.0.2

* `AttributeReconstitutorResolver` now caches results in memory
* Test reconstituting subclass of a class with the marker attribute