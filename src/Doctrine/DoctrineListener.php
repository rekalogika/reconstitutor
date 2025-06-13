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

namespace Rekalogika\Reconstitutor\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\Proxy;
use Rekalogika\Reconstitutor\Context\ManagerContextRegistry;
use Rekalogika\Reconstitutor\Exception\LogicException;
use Rekalogika\Reconstitutor\ReconstitutorProcessor;

final readonly class DoctrineListener
{
    public function __construct(
        private ReconstitutorProcessor $processor,
        private ManagerContextRegistry $registry,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $object = $args->getObject();
        $objectManager = $args->getObjectManager();

        $this->registry->get($objectManager)->add($object);
        $this->processor->onCreate($object);
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $object = $args->getObject();
        $objectManager = $args->getObjectManager();

        $this->registry->get($objectManager)->add($object);
        $this->processor->onLoad($object);
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $context = $this->registry->get($objectManager);

        // indicates that we are in the middle of a flush operation.
        $context->setInFlush(true);

        // we take the opportunity to reconcile our list of objects for removal
        // with unit of work
        $context->reconcileObjectsForRemoval($objectManager);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $context = $this->registry->get($objectManager);

        // indicates that we are done with the flush operation.
        $context->setInFlush(false);

        // if the flush was a lone flush, we call finish here
        if ($context->isInTransaction()) {
            return;
        }

        $this->finish($objectManager);
    }

    private function finish(ObjectManager $objectManager): void
    {
        if (!$objectManager instanceof EntityManagerInterface) {
            throw new LogicException('Reconstitutor currently only works with EntityManagerInterface.');
        }

        $unitOfWork = $objectManager->getUnitOfWork();

        // save

        foreach ($unitOfWork->getIdentityMap() as $objects) {
            foreach ($objects as $object) {
                // do not call onSave if we don't know anything about the object,
                // i.e. it is an uninitialized proxy

                if (!$this->registry->get($objectManager)->contains($object)) {
                    continue;
                }

                // do not call onSave if the object is an uninitialized proxy.
                // should never happen, but we check anyway as a safeguard.

                if ($this->isUninitializedProxy($object)) {
                    continue;
                }

                // reconcile objects
                $this->registry->get($objectManager)->addForReconciliation($object);

                // call onSave
                $this->processor->onSave($object);
            }
        }

        // removal

        $objectsToRemove = $this->registry
            ->get($objectManager)
            ->popObjectsForRemoval();

        foreach ($objectsToRemove as $object) {
            $this->processor->onRemove($object);
        }

        // missing objects are the object that was previously `detach()`ed

        $missingObjects = $this->registry
            ->get($objectManager)
            ->reconcile();

        foreach ($missingObjects as $object) {
            $this->processor->onClear($object);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $object = $args->getObject();

        // if the object does not have a reconstitutor, we don't need to do
        // anything

        if (!$this->processor->hasReconstitutor($object)) {
            return;
        }

        // if the object being removed is a proxy, the `postRemove` event will
        // contain an uninitializable proxy, unless we initialize it here first.

        $this->initializeProxy($object);

        // add to context

        $objectManager = $args->getObjectManager();
        $this->registry->get($objectManager)->addForRemoval($object);
    }

    public function onClear(OnClearEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();

        foreach ($this->registry->get($objectManager) as $object) {
            $this->processor->onClear($object);
        }

        $this->registry->get($objectManager)->clear();
    }

    public function postBeginTransaction(TransactionEventArgs $args): void
    {
        $objectManagers = $this->registry
            ->getObjectManagersFromDriver($args->getDriver());

        foreach ($objectManagers as $objectManager) {
            $context = $this->registry->get($objectManager);

            if ($context->isInFlush()) {
                continue;
            }

            $context->beginTransaction();
        }
    }

    public function postCommit(TransactionEventArgs $args): void
    {
        $objectManagers = $this->registry
            ->getObjectManagersFromDriver($args->getDriver());

        foreach ($objectManagers as $objectManager) {
            $context = $this->registry->get($objectManager);

            if ($context->isInFlush()) {
                continue;
            }

            $context->commit();

            $this->finish($objectManager);
        }
    }

    public function postRollback(TransactionEventArgs $args): void
    {
        $objectManagers = $this->registry
            ->getObjectManagersFromDriver($args->getDriver());

        foreach ($objectManagers as $objectManager) {
            $context = $this->registry->get($objectManager);

            if ($context->isInFlush()) {
                continue;
            }

            $context->rollback();
        }
    }

    private function isUninitializedProxy(object $object): bool
    {
        /**
         * @psalm-suppress UndefinedMethod
         */
        return ($object instanceof Proxy && !$object->__isInitialized())
            || (
                \PHP_VERSION_ID >= 80400
                && (new \ReflectionClass($object))->isUninitializedLazyObject($object)
            );
    }

    private function initializeProxy(object $object): void
    {
        if ($object instanceof Proxy) {
            $object->__load();

            return;
        }

        if (\PHP_VERSION_ID >= 80400) {
            $reflection = new \ReflectionClass($object);

            /** @psalm-suppress UndefinedMethod */
            if ($reflection->isUninitializedLazyObject($object)) {
                /** @psalm-suppress UndefinedMethod */
                $reflection->initializeLazyObject($object);
            }
        }
    }
}
