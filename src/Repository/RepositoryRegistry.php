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

final class RepositoryRegistry implements ResetInterface, \Countable
{
    /**
     * @var \WeakMap<ObjectManager,ObjectRepository>
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
        /** @var \WeakMap<ObjectManager,ObjectRepository> */
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

    public function get(ObjectManager $manager): ObjectRepository
    {
        if (!isset($this->managerToRepository[$manager])) {
            $this->managerToRepository[$manager] = new ObjectRepository();
        }

        /** @var ObjectRepository */
        return $this->managerToRepository[$manager];
    }
}
