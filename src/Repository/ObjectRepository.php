<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor\Repository;

/**
 * @implements \IteratorAggregate<int,object>
 */
final class ObjectRepository implements \Countable, \IteratorAggregate
{
    /**
     * @var \WeakMap<object,true>
     */
    private \WeakMap $objects;

    /**
     * @var array<int,object>
     */
    private array $objectsInUnitOfWork = [];

    public function __construct()
    {
        $this->init();
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->objects as $object => $_) {
            yield $object;
        }
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->objects);
    }

    private function init(): void
    {
        /** @var \WeakMap<object,true> */
        $objects = new \WeakMap();
        $this->objects = $objects;

        $this->objectsInUnitOfWork = [];
    }

    public function clear(): void
    {
        $this->init();
    }

    public function add(object $object): void
    {
        $this->objects[$object] = true;
    }

    public function exists(object $object): bool
    {
        return isset($this->objects[$object]);
    }

    public function remove(object $object): void
    {
        unset($this->objects[$object]);
    }

    /**
     * Records objects that are in doctrine's unit of work that is not an
     * uninitialized proxy
     *
     * @param object $object
     */
    public function addForReconciliation(object $object): void
    {
        $this->objectsInUnitOfWork[spl_object_id($object)] = $object;
    }

    /**
     * Returns objects that are in reconstitutor's repository but not in
     * Doctrine's unit of work. And removes them from the repository.
     *
     * @return list<object>
     */
    public function doReconciliation(): array
    {
        $missingObjects = [];

        foreach ($this->objects as $object => $_) {
            if (isset($this->objectsInUnitOfWork[spl_object_id($object)])) {
                continue;
            }

            $this->remove($object);
            $missingObjects[] = $object;
        }

        return $missingObjects;
    }
}
