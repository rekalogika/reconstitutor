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
use Rekalogika\Reconstitutor\Tests\Entity\Comment;

/**
 * @extends AbstractClassReconstitutor<Comment>
 */
class DoctrineCommentReconstitutor extends AbstractClassReconstitutor
{
    /**
     * @var array<string,string>
     */
    private array $avatars = [];

    public static function getClass(): string
    {
        return Comment::class;
    }

    public function onLoad(object $object): void
    {
        $avatar = $this->avatars[$object->getId()] ?? null;

        $this->set($object, 'avatar', $avatar);
    }

    public function onSave(object $object): void
    {
        $avatar = $this->get($object, 'avatar');
        \assert(\is_string($avatar));

        $this->avatars[$object->getId()] = $avatar;
    }

    public function onRemove(object $object): void
    {
        unset($this->avatars[$object->getId()]);
    }
}
