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
    private array $clearCalledOnObjectIds = [];

    /**
     * @var list<string>
     */
    private array $removeCalledOnObjectIds = [];

    #[\Override]
    public function reset(): void
    {
        $this->images = [];
        $this->clearCalledOnObjectIds = [];
    }

    #[\Override]
    public static function getClass(): string
    {
        return Post::class;
    }

    #[\Override]
    public function onLoad(object $object): void
    {
        $image = $this->images[$object->getId()] ?? null;

        $this->set($object, 'image', $image);
    }

    #[\Override]
    public function onSave(object $object): void
    {
        $image = $this->get($object, 'image');
        \assert(\is_string($image));

        $this->images[$object->getId()] = $image;
    }

    #[\Override]
    public function onRemove(object $object): void
    {
        unset($this->images[$object->getId()]);

        $this->removeCalledOnObjectIds[] = $object->getId();
    }

    #[\Override]
    public function onClear(object $object): void
    {
        $this->clearCalledOnObjectIds[] = $object->getId();
    }

    public function hasClearCalledOnObjectId(string $objectId): bool
    {
        return \in_array($objectId, $this->clearCalledOnObjectIds, true);
    }

    public function hasRemoveCalledOnObjectId(string $objectId): bool
    {
        return \in_array($objectId, $this->removeCalledOnObjectIds, true);
    }

    public function isImageExists(string $objectId): bool
    {
        return isset($this->images[$objectId]);
    }
}
