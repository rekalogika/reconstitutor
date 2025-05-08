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

namespace Rekalogika\Reconstitutor\Tests\EventRecorder;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class Event
{
    public function __construct(
        private object $object,
        private EventType $type,
    ) {}

    public function getObject(): object
    {
        return $this->object;
    }

    public function getObjectId(): string
    {
        if (!method_exists($this->object, 'getId')) {
            throw new \InvalidArgumentException('Object must have getId() method');
        }

        /**
         * @psalm-suppress MixedArgument
         * @phpstan-ignore argument.type
         */
        return \strval($this->object->getId());
    }

    public function getType(): EventType
    {
        return $this->type;
    }
}
