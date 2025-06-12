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

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Service\ResetInterface;

final class ManagerContextRegistry implements ResetInterface, \Countable
{
    /**
     * @var \WeakMap<ObjectManager,ManagerContext>
     */
    private \WeakMap $managerToRepository;

    public function __construct()
    {
        $this->init();
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->managerToRepository);
    }

    private function init(): void
    {
        /** @var \WeakMap<ObjectManager,ManagerContext> */
        $objectManagerToRepository = new \WeakMap();
        $this->managerToRepository = $objectManagerToRepository;
    }

    #[\Override]
    public function reset(): void
    {
        $this->init();
    }

    public function remove(ObjectManager $manager): void
    {
        unset($this->managerToRepository[$manager]);
    }

    public function get(ObjectManager $manager): ManagerContext
    {
        if (!isset($this->managerToRepository[$manager])) {
            $this->managerToRepository[$manager] = new ManagerContext();
        }

        /** @var ManagerContext */
        return $this->managerToRepository[$manager];
    }

    /**
     * @return iterable<ObjectManager>
     */
    public function getObjectManagersFromDriverConnection(
        Connection $connection,
    ): iterable {
        foreach ($this->managerToRepository as $manager => $_) {
            if (!$manager instanceof EntityManagerInterface) {
                continue;
            }

            if ($manager->getConnection()->getNativeConnection() === $connection->getNativeConnection()) {
                yield $manager;
            }
        }
    }
}
