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

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Rekalogika\Reconstitutor\Doctrine\DoctrineListener;
use Rekalogika\Reconstitutor\Doctrine\TransactionEventArgs;

final class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $wrappedConnection,
        private readonly DoctrineListener $listener,
        private readonly Driver $driver,
    ) {
        parent::__construct($wrappedConnection);
    }


    #[\Override]
    public function beginTransaction(): void
    {
        parent::beginTransaction();

        $this->listener->postBeginTransaction(new TransactionEventArgs($this->driver));
    }

    #[\Override]
    public function commit(): void
    {
        parent::commit();

        $this->listener->postCommit(new TransactionEventArgs($this->driver));
    }

    #[\Override]
    public function rollBack(): void
    {
        parent::rollBack();

        $this->listener->postRollBack(new TransactionEventArgs($this->driver));
    }

    #[\Override]
    public function exec(string $sql): int|string
    {
        if (str_starts_with($sql, 'ROLLBACK')) {
            $this->listener->postRollBack(new TransactionEventArgs($this->driver));
        } elseif (str_starts_with($sql, 'RELEASE')) {
            $this->listener->postCommit(new TransactionEventArgs($this->driver));
        } elseif (str_starts_with($sql, 'SAVEPOINT')) {
            $this->listener->postBeginTransaction(new TransactionEventArgs($this->driver));
        }

        return parent::exec($sql);
    }
}
