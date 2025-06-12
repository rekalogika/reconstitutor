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
     * @var \WeakMap<object,true>
     */
    private \WeakMap $objectsToRemove;

    /**
     * @var array<int,object>
     */
    private array $objectsInUnitOfWork = [];

    private ?self $transactionScope = null;

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

        /** @var \WeakMap<object,true> */
        $objectsToRemove = new \WeakMap();
        $this->objectsToRemove = $objectsToRemove;

        $this->objectsInUnitOfWork = [];
    }

    //
    // operation
    //

    public function clear(): void
    {
        // clear only on top level scope

        if ($this->transactionScope === null) {
            $this->init();
        }
    }

    public function add(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we add the object to the
            // transaction scope instead of the current scope

            $this->transactionScope->add($object);
            return;
        }

        $this->objects[$object] = true;
        unset($this->objectsToRemove[$object]);
    }

    public function exists(object $object): bool
    {
        return
            isset($this->objects[$object])
            || (
                $this->transactionScope !== null
                && $this->transactionScope->exists($object)
            );
    }

    public function remove(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->remove($object);
            return;
        }

        unset($this->objects[$object]);
        $this->objectsToRemove[$object] = true;
    }

    //
    // transaction
    //

    public function beginTransaction(): void
    {
        if ($this->transactionScope === null) {
            $this->transactionScope = new self();
        } else {
            $this->transactionScope->beginTransaction();
        }
    }

    /**
     * @return bool false if there is no transaction under this scope
     */
    public function commit(): bool
    {
        if ($this->transactionScope === null) {
            return false;
        }

        $result = $this->transactionScope->commit();

        // if the transaction is committed, we merge the state of the
        // transaction scope to the current scope

        if ($result === false) {
            $transactionScope = $this->transactionScope;
            $this->transactionScope = null;

            foreach ($transactionScope->objects as $object => $_) {
                $this->add($object);
            }

            foreach ($transactionScope->objectsToRemove as $object => $_) {
                $this->remove($object);
            }

        }

        return true;
    }

    /**
     * @return bool false if there is no transaction in progress
     */
    public function rollback(): bool
    {
        if ($this->transactionScope === null) {
            return false;
        }

        $result = $this->transactionScope->rollback();

        // if the transaction is rolled back, we clear the transaction scope

        if ($result === false) {
            $this->transactionScope = null;
        }

        return true;
    }

    //
    // reconcilliation
    // 

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
