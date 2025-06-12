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
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Rekalogika\Reconstitutor\Doctrine\DBAL\Driver;
use Symfony\Contracts\Service\ResetInterface;

final class ManagerContextRegistry implements ResetInterface, \Countable
{
    /**
     * @var \WeakMap<ObjectManager,ManagerContext>
     */
    private \WeakMap $managerToContext;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
        $this->init();
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->managerToContext);
    }

    private function init(): void
    {
        /** @var \WeakMap<ObjectManager,ManagerContext> */
        $managerToContext = new \WeakMap();
        $this->managerToContext = $managerToContext;
    }

    #[\Override]
    public function reset(): void
    {
        $this->init();
    }

    public function remove(ObjectManager $manager): void
    {
        unset($this->managerToContext[$manager]);
    }

    public function get(ObjectManager $manager): ManagerContext
    {
        if (!isset($this->managerToContext[$manager])) {
            $this->managerToContext[$manager] = new ManagerContext();
        }

        /** @var ManagerContext */
        return $this->managerToContext[$manager];
    }

    /**
     * @return iterable<ObjectManager>
     */
    public function getObjectManagersFromDriver(Driver $driver): iterable
    {
        foreach ($this->managerRegistry->getManagers() as $manager) {
            if (!$manager instanceof EntityManagerInterface) {
                continue;
            }

            if (
                $manager->getConnection()->getDriver() === $driver
            ) {
                yield $manager;
            }
        }
    }
}
