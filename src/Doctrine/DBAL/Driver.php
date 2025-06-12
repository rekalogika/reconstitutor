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

namespace Rekalogika\Reconstitutor\Doctrine\DBAL;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Rekalogika\Reconstitutor\Doctrine\DoctrineListener;

final class Driver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $wrappedDriver,
        private readonly DoctrineListener $listener,
    ) {
        parent::__construct($wrappedDriver);
    }

    #[\Override]
    public function connect(array $params): ConnectionInterface
    {
        $connection = parent::connect($params);

        return new Connection(
            wrappedConnection: $connection,
            listener: $this->listener,
            driver: $this,
        );
    }
}
