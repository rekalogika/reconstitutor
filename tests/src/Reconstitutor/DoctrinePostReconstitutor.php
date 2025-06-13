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

namespace Rekalogika\Reconstitutor\Tests\Reconstitutor;

use Rekalogika\Reconstitutor\AbstractClassReconstitutor;
use Rekalogika\Reconstitutor\Tests\Entity\Post;
use Rekalogika\Reconstitutor\Tests\EventRecorder\Event;
use Rekalogika\Reconstitutor\Tests\EventRecorder\EventRecorder;
use Rekalogika\Reconstitutor\Tests\EventRecorder\EventType;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends AbstractClassReconstitutor<Post>
 */
final class DoctrinePostReconstitutor extends AbstractClassReconstitutor implements ResetInterface
{
    /**
     * @var array<string,string>
     */
    private array $images = [];

    /**
     * @var list<string>
     */
    private array $events = [];

    public function __construct(
        private readonly EventRecorder $eventRecorder,
    ) {}


    #[\Override]
    public function reset(): void
    {
        $this->images = [];
        $this->events = [];
    }

    #[\Override]
    public static function getClass(): string
    {
        return Post::class;
    }

    #[\Override]
    public function onCreate(object $object): void
    {
        $this->events[] = 'onCreate';
    }

    #[\Override]
    public function onLoad(object $object): void
    {
        $image = $this->images[$object->getId()] ?? null;

        $this->set($object, 'image', $image);
        $this->eventRecorder->record(new Event($object, EventType::onLoad));
        $this->events[] = 'onLoad';
    }

    #[\Override]
    public function onSave(object $object): void
    {
        $image = $this->get($object, 'image');
        \assert(\is_string($image));

        $this->images[$object->getId()] = $image;
        $this->eventRecorder->record(new Event($object, EventType::onSave));
        $this->events[] = 'onSave';
    }

    #[\Override]
    public function onRemove(object $object): void
    {
        unset($this->images[$object->getId()]);

        $this->eventRecorder->record(new Event($object, EventType::onRemove));
        $this->events[] = 'onRemove';
    }

    #[\Override]
    public function onClear(object $object): void
    {
        $this->eventRecorder->record(new Event($object, EventType::onClear));
        $this->events[] = 'onClear';
    }

    public function isImageExists(string $objectId): bool
    {
        return isset($this->images[$objectId]);
    }

    /**
     * @return list<string>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function resetEvents(): void
    {
        $this->events = [];
    }
}
