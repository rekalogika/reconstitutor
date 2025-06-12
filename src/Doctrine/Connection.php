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

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;

final class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $wrappedConnection,
        private readonly DoctrineListener $listener,
    ) {
        parent::__construct($wrappedConnection);
    }


    #[\Override]
    public function beginTransaction(): void
    {
        parent::beginTransaction();

        $this->listener->postBeginTransaction(new TransactionEventArgs($this));
    }

    #[\Override]
    public function commit(): void
    {
        parent::commit();

        $this->listener->postCommit(new TransactionEventArgs($this));
    }

    #[\Override]
    public function rollBack(): void
    {
        parent::rollBack();

        $this->listener->postRollBack(new TransactionEventArgs($this));
    }
}
