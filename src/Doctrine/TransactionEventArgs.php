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

use Doctrine\DBAL\Driver\Connection;

final readonly class TransactionEventArgs
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
