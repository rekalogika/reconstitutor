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

use Symfony\Contracts\Service\ResetInterface;

final class EventRecorder implements ResetInterface
{
    /**
     * @var list<Event>
     */
    private array $events = [];

    #[\Override]
    public function reset(): void
    {
        $this->events = [];
    }

    public function record(Event $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return list<Event>
     */
    public function filterEvents(?object $object, ?string $id, ?EventType $type): array
    {
        return array_values(array_filter(
            $this->events,
            static fn(Event $event) => (
                ($object === null || $event->getObject() === $object)
                && ($type === null || $event->getType() === $type)
                && ($id === null || $event->getObjectId() === $id)
            ),
        ));
    }
}
