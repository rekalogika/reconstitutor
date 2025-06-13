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
    private Set $objectsToRemove;
    private Set $objectsToClear;

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
        $this->objectsToRemove = new Set();
        $this->objectsToClear = new Set();
        $this->transactionScope = null;
        $this->inFlush = false;
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
        $this->objectsToRemove->remove($object);
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
        $this->objectsToRemove->remove($object);
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
            $this->objectsToRemove->moveObjectsTo($this->transactionScope->objectsToRemove);
            $this->objectsToClear->moveObjectsTo($this->transactionScope->objectsToClear);
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

            foreach ($transactionScope->objectsToRemove as $object) {
                $this->addForRemoval($object);
            }

            foreach ($transactionScope->objectsToClear as $object) {
                $this->addForClearance($object);
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

        return $this->objectsToRemove;
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
        $this->objectsToRemove->add($object);
    }

    public function removeForRemoval(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->removeForRemoval($object);
            return;
        }

        $this->objectsToRemove->remove($object);
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

        foreach ($this->objectsToRemove as $object) {
            $objects[] = $object;
            $this->objectsToRemove->remove($object);
        }

        return $objects;
    }

    //
    // clearance
    //

    public function clear(): void
    {
        $this->init();
    }

    /**
     * @return iterable<object>
     */
    public function getObjectsForClearance(): iterable
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, nothing is cleared yet

            return [];
        }

        return $this->objectsToClear;
    }

    public function addForClearance(object $object): void
    {
        if ($this->transactionScope !== null) {
            // if we are in a transaction scope, we remove the object from the
            // transaction scope instead of the current scope

            $this->transactionScope->addForClearance($object);
            return;
        }

        $this->objectsToClear->add($object);
    }

    /**
     * @return list<object>
     */
    public function popObjectsForClearance(): array
    {
        $objects = [];

        foreach ($this->objectsToClear as $object) {
            $objects[] = $object;
            $this->objectsToClear->remove($object);
        }

        return $objects;
    }

    public function isPendingForClearance(object $object): bool
    {
        return $this->objectsToClear->contains($object);
    }
}
