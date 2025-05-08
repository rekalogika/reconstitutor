# Changelog

## 2.3.0

* feat: call `onClear` on `detach()` and `flush()`
* perf: non-managed objects should not trigger `preRemove`.
* fix: convert proxy class names to the real class names

## 2.2.1

* fix: generics on `AttributeReconstitutor`

## 2.2.0

* fix: initialize object on `preRemove`

## 2.1.0

* feat: object tracking. call `onSave` and `onRemove` only if we previously
  called `onLoad` or `onCreate`.
* fix: clear our repository when doctrine clears
* refactor: simplify object repository
* feat: `onClear` method
* refactor: refactor exception
* fix: call `onRemove` if uninitialized

## 2.0.2

* fix: reconstitutor without implementing `DirectPropertyAccessAwareInterface`

## 2.0.1

* build: add bin/console
* test: ensure lazy ghost is always enabled on ORM 2.x
* build: enable monologbundle
* refactor: don't burden caller without autoconfiguration with additional tags

## 2.0.0

* refactor: move `DirectPropertyAccess` processing to separate compiler pass
* ci: remove tests for PHP 8.1
* refactor: change `ReconstitutorResolverInterface` to return service IDs
* chore: rector run
* perf: add caching for `ReconstitutorResolverInterface`
* feat: add debug logging

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
* feat: add native PHP 8.4 lazy object checking to anticipate its support in
  Doctrine ORM in the future.
* refactor: move proxy checking to `DoctrineListener`

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