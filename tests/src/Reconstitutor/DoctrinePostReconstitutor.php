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

/**
 * @extends AbstractClassReconstitutor<Post>
 */
class DoctrinePostReconstitutor extends AbstractClassReconstitutor
{
    /**
     * @var array<string,string>
     */
    private array $images = [];

    public static function getClass(): string
    {
        return Post::class;
    }

    public function onLoad(object $object): void
    {
        $image = $this->images[$object->getId()] ?? null;

        $this->set($object, 'image', $image);
    }

    public function onSave(object $object): void
    {
        $image = $this->get($object, 'image');
        \assert(\is_string($image));

        $this->images[$object->getId()] = $image;
    }

    public function onRemove(object $object): void
    {
        unset($this->images[$object->getId()]);
    }
}
