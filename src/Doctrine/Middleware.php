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

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;

final readonly class Middleware implements MiddlewareInterface
{
    public function __construct(
        private readonly DoctrineListener $listener,
    ) {}

    #[\Override]
    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver(
            wrappedDriver: $driver,
            listener: $this->listener,
        );
    }
}
