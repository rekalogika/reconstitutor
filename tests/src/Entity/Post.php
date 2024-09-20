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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
class Post
{
    #[ORM\Id]
    #[ORM\Column(unique: true, nullable: false)]
    private string $id;

    /**
     * @var Collection<string,Comment>
     */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, fetch: 'EXTRA_LAZY')]
    private Collection $comments;

    private ?string $image = null;

    public function __construct(
        #[ORM\Column(nullable: false)]
        private string $title,
    ) {
        $this->id = Uuid::v6()->toRfc4122();
        $this->comments = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Collection<string,Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $comment->setPost($this);
            $this->comments->add($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        $this->comments->removeElement($comment);

        if ($comment->getPost() === $this) {
            $comment->setPost(null);
        }
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
