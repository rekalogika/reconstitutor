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

namespace Rekalogika\Reconstitutor\Tests\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
final class Comment
{
    #[ORM\Id]
    #[ORM\Column(unique: true, nullable: false)]
    private string $id;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private ?string $avatar = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    private ?Post $post = null;

    public function __construct(
        #[ORM\Column(nullable: false)]
        private string $content,
    ) {
        $this->id = Uuid::v7()->toRfc4122();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): void
    {
        $this->post = $post;
    }
}
