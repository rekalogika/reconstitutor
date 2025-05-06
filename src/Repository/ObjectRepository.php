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

/**
 * @implements \IteratorAggregate<int,object>
 */
final class ObjectRepository implements \Countable, \IteratorAggregate
{
    /**
     * @var \WeakMap<object,true>
     */
    private \WeakMap $objects;

    public function __construct()
    {
        $this->init();
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->objects as $object => $_) {
            yield $object;
        }
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->objects);
    }

    private function init(): void
    {
        /** @var \WeakMap<object,true> */
        $objects = new \WeakMap();
        $this->objects = $objects;
    }

    public function clear(): void
    {
        $this->init();
    }

    public function add(object $object): void
    {
        $this->objects[$object] = true;
    }

    public function exists(object $object): bool
    {
        return isset($this->objects[$object]);
    }

    public function remove(object $object): void
    {
        unset($this->objects[$object]);
    }
}
