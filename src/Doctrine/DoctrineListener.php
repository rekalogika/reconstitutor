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

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Proxy;
use Rekalogika\Reconstitutor\ReconstitutorProcessor;
use Rekalogika\Reconstitutor\Repository\RepositoryRegistry;

final class DoctrineListener
{
    public function __construct(
        private readonly ReconstitutorProcessor $processor,
        private readonly RepositoryRegistry $registry,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $object = $args->getObject();
        $objectManager = $args->getObjectManager();

        $this->registry->get($objectManager)->add($object);
        $this->processor->onCreate($object);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $object = $args->getObject();
        $objectManager = $args->getObjectManager();

        // do not call onRemove if we don't know anything about the object
        if (!$this->registry->get($objectManager)->exists($object)) {
            return;
        }

        // unlike onSave, we'll call onRemove even if the object is in
        // uninitialized proxy

        $this->registry->get($objectManager)->remove($object);
        $this->processor->onRemove($object);
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $object = $args->getObject();
        $objectManager = $args->getObjectManager();

        $this->registry->get($objectManager)->add($object);
        $this->processor->onLoad($object);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();

        $unitOfWork = $objectManager->getUnitOfWork();
        foreach ($unitOfWork->getIdentityMap() as $objects) {
            foreach ($objects as $object) {
                // do not call onSave if we don't know anything about the object
                if (!$this->registry->get($objectManager)->exists($object)) {
                    continue;
                }

                // do not call onSave if the object is an uninitialized proxy.
                // should never happen, but we check anyway as a safeguard.
                if ($this->isUninitializedProxy($object)) {
                    continue;
                }

                $this->processor->onSave($object);
            }
        }
    }

    public function onClear(OnClearEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();

        foreach ($this->registry->get($objectManager) as $object) {
            $this->processor->onClear($object);
        }

        $this->registry->get($objectManager)->clear();
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
}
