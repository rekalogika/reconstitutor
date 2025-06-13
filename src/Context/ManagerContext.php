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

namespace Rekalogika\Reconstitutor\Context;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Rekalogika\Reconstitutor\Exception\LogicException;

/**
 * Tracks the objects managed by an object manager.
 */
final class ManagerContext implements \Countable
{
    private Set $objects;

    /**
     * Objects that are scheduled for removal when flush() or commit() is
     * called.
     */
    private Set $removedObjects;

    /**
     * Objects that were cleared during a transaction, so we haven't called
     * `onClear` yet, because we don't know if the transaction will be
     * committed or rolled back.
     */
    private Set $clearedObjects;

    /**
     * Objects that are flushed inside a transaction, but we haven't called
     * `onSave` yet, because we don't know if the transaction will be committed
     * or rolled back.
     */
    private Set $flushedObjects;

    private ?self $transactionScope = null;
    private bool $inFlush = false;

    public function __construct()
    {
        $this->init();
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->objects);
    }

    private function init(): void
    {
        $this->objects = new Set();
        $this->removedObjects = new Set();
        $this->clearedObjects = new Set();
        $this->flushedObjects = new Set();
        $this->transactionScope = null;
        $this->inFlush = false;
    }

    public function clear(): void
    {
        $this->init();
    }

    //
    // operation
    //

    public function add(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we add the object to the
            // transaction scope instead of the current scope

            $this->transactionScope->add($object);
            return;
        }

        $this->objects->add($object);
        $this->removedObjects->remove($object);
    }

    public function remove(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->remove($object);
            return;
        }

        $this->objects->remove($object);
        $this->removedObjects->remove($object);
    }

    /**
     * @return \Traversable<object>
     */
    public function getObjects(): \Traversable
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we return the objects from the
            // transaction scope instead of the current scope

            return $this->transactionScope->getObjects();
        }

        return $this->objects;
    }

    public function contains(object $object): bool
    {
        return
            $this->objects->contains($object)
            || (
                $this->transactionScope !== null
                && $this->transactionScope->contains($object)
            );
    }

    //
    // flush state
    //

    public function isInFlush(): bool
    {
        return $this->inFlush;
    }

    public function setInFlush(bool $inFlush): void
    {
        $this->inFlush = $inFlush;
    }

    //
    // transaction
    //

    public function isInTransaction(): bool
    {
        return $this->transactionScope !== null;
    }

    public function beginTransaction(): void
    {
        if ($this->transactionScope === null) {
            $this->transactionScope = new self();

            $this->objects->moveObjectsTo($this->transactionScope->objects);
            $this->removedObjects->moveObjectsTo($this->transactionScope->removedObjects);
            $this->clearedObjects->moveObjectsTo($this->transactionScope->clearedObjects);
            $this->flushedObjects->moveObjectsTo($this->transactionScope->flushedObjects);
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

            foreach ($transactionScope->objects as $object) {
                $this->add($object);
            }

            foreach ($transactionScope->removedObjects as $object) {
                $this->addForRemoval($object);
            }

            foreach ($transactionScope->clearedObjects as $object) {
                $this->addForClearance($object);
            }

            foreach ($transactionScope->flushedObjects as $object) {
                $this->addFlushedObject($object);
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
     * Returns objects that are in reconstitutor's repository but not in
     * Doctrine's unit of work. And removes them from the repository.
     *
     * @return list<object>
     */
    public function reconcile(ObjectManager $objectManager): array
    {
        if (!$objectManager instanceof EntityManagerInterface) {
            throw new LogicException('Reconstitutor currently only works with EntityManagerInterface.');
        }

        $unitOfWork = $objectManager->getUnitOfWork();
        $missingObjects = [];

        foreach ($this->objects as $object) {
            if ($unitOfWork->isInIdentityMap($object)) {
                continue;
            }

            $this->remove($object);
            $missingObjects[] = $object;
        }

        return $missingObjects;
    }

    //
    // removal
    //

    /**
     * @return iterable<object>
     */
    public function getObjectsForRemoval(): iterable
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we return the objects from the
            // transaction scope instead of the current scope

            return $this->transactionScope->getObjectsForRemoval();
        }

        return $this->removedObjects;
    }

    public function addForRemoval(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->addForRemoval($object);
            return;
        }

        $this->objects->remove($object);
        $this->removedObjects->add($object);
    }

    public function removeForRemoval(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->removeForRemoval($object);
            return;
        }

        $this->removedObjects->remove($object);
        $this->objects->add($object);
    }

    /**
     * When the caller does remove() and persist(), Doctrine does not call
     * prePersist, so we need to objects pending removal if they are not
     * scheduled
     */
    public function reconcileObjectsForRemoval(ObjectManager $objectManager): void
    {
        if (!$objectManager instanceof EntityManagerInterface) {
            throw new LogicException('Reconstitutor currently only works with EntityManagerInterface.');
        }

        $unitOfWork = $objectManager->getUnitOfWork();

        foreach ($this->getObjectsForRemoval() as $object) {
            // if the object is not in the unit of work, we can remove it
            // from the repository

            if (!$unitOfWork->isScheduledForDelete($object)) {
                $this->removeForRemoval($object);
            }
        }
    }

    /**
     * @return list<object>
     */
    public function popObjectsForRemoval(): array
    {
        $objects = [];

        foreach ($this->removedObjects as $object) {
            $objects[] = $object;
            $this->removedObjects->remove($object);
        }

        return $objects;
    }

    //
    // clearance
    //

    /**
     * @return iterable<object>
     */
    public function getObjectsForClearance(): iterable
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, nothing is cleared yet

            return [];
        }

        return $this->clearedObjects;
    }

    public function addForClearance(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->addForClearance($object);
            return;
        }

        $this->clearedObjects->add($object);
    }

    /**
     * @return list<object>
     */
    public function popObjectsForClearance(): array
    {
        $objects = [];

        foreach ($this->clearedObjects as $object) {
            $objects[] = $object;
            $this->clearedObjects->remove($object);
        }

        return $objects;
    }

    //
    // flushed objects
    //

    public function addFlushedObject(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we add the object to the
            // transaction scope instead of the current scope

            $this->transactionScope->addFlushedObject($object);
            return;
        }

        $this->flushedObjects->add($object);
    }

    public function removeFlushedObject(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->removeFlushedObject($object);
            return;
        }

        $this->flushedObjects->remove($object);
    }

    /**
     * @return list<object>
     */
    public function popFlushedObjects(): array
    {
        $objects = [];

        foreach ($this->flushedObjects as $object) {
            $objects[] = $object;
            $this->flushedObjects->remove($object);
        }

        return $objects;
    }
}
