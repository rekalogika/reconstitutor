<?php

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
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Rekalogika\Reconstitutor\ReconstitutorProcessor;

class DoctrineListener
{
    public function __construct(private ReconstitutorProcessor $processor)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $object = $args->getObject();
        $this->processor->onCreate($object);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $object = $args->getObject();
        $this->processor->onRemove($object);
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $object = $args->getObject();
        $this->processor->onLoad($object);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();

        if (!$em instanceof EntityManagerInterface) {
            return;
        }

        $uow = $em->getUnitOfWork();
        foreach ($uow->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $this->processor->onSave($entity);
            }
        }
    }
}
