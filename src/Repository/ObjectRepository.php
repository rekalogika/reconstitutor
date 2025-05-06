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

use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Service\ResetInterface;

final class ObjectRepository implements ResetInterface, \Countable
{
    /**
     * @var \WeakMap<object,ObjectManager>
     */
    private \WeakMap $objectToManager;

    /**
     * @var \WeakMap<ObjectManager,\WeakMap<object,true>>
     */
    private \WeakMap $managerToObjects;

    public function __construct()
    {
        $this->init();
    }

    public function count(): int
    {
        return \count($this->objectToManager);
    }

    private function init(): void
    {
        /** @var \WeakMap<object,ObjectManager> */
        $objectToObjectManager = new \WeakMap();
        $this->objectToManager = $objectToObjectManager;

        /** @var \WeakMap<ObjectManager,\WeakMap<object,true>> */
        $objectManagerToObjects = new \WeakMap();
        $this->managerToObjects = $objectManagerToObjects;
    }

    #[\Override]
    public function reset(): void
    {
        $this->init();
    }

    public function clear(ObjectManager $manager): void
    {
        if (!isset($this->managerToObjects[$manager])) {
            return;
        }

        /** @var \WeakMap<object,true> */
        $objectMap = $this->managerToObjects[$manager];

        foreach ($objectMap as $object => $_) {
            unset($this->objectToManager[$object]);
        }

        unset($this->managerToObjects[$manager]);
    }

    public function add(object $object, ObjectManager $manager): void
    {
        // add object to objectToObjectManager
        $this->objectToManager[$object] = $manager;

        // add object to objectManagerToObjects
        if (!isset($this->managerToObjects[$manager])) {
            /** @var \WeakMap<object,true> */
            $objectMap = new \WeakMap();
            $this->managerToObjects[$manager] = $objectMap;
        } else {
            /** @var \WeakMap<object,true> */
            $objectMap = $this->managerToObjects[$manager];
        }

        $objectMap->offsetSet($object, true);
    }

    public function exists(object $object, ObjectManager $manager): bool
    {
        return ($this->objectToManager[$object] ?? null) === $manager;
    }

    public function remove(object $object, ObjectManager $manager): void
    {
        unset($this->objectToManager[$object]);

        $managerToObjects = $this->managerToObjects[$manager];

        if (isset($managerToObjects[$object])) {
            unset($managerToObjects[$object]);
        }
    }

    /**
     * @return iterable<object>
     */
    public function getObjectsByManager(ObjectManager $manager): iterable
    {
        if (!isset($this->managerToObjects[$manager])) {
            return [];
        }

        /** @var \WeakMap<object,true> */
        $objectMap = $this->managerToObjects[$manager];

        foreach ($objectMap as $object => $_) {
            yield $object;
        }
    }
}
