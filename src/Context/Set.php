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

/**
 * @implements \IteratorAggregate<int,object>
 */
final class Set implements \Countable, \IteratorAggregate
{
    /**
     * @var array<int,object>
     */
    private array $objects = [];

    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->objects as $object) {
            yield $object;
        }
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->objects);
    }

    public function add(object $object): void
    {
        $this->objects[spl_object_id($object)] = $object;
    }

    public function remove(object $object): void
    {
        $id = spl_object_id($object);

        if (isset($this->objects[$id])) {
            unset($this->objects[$id]);
        }
    }

    public function contains(object $object): bool
    {
        return isset($this->objects[spl_object_id($object)]);
    }
}
