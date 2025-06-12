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

namespace Rekalogika\Reconstitutor\Tests\Tests;

use Rekalogika\Reconstitutor\Tests\Entity\Post;

final class TransactionTest extends DoctrineTestCase
{
    private function createPostWithImage(): Post
    {
        $post = new Post('title');
        $post->setImage('someImage');

        return $post;
    }

    // private function createPostWithoutImage(): Post
    // {
    //     return new Post('title');
    // }

    private function loadPostWithImage(): Post
    {
        $post = new Post('title');
        $post->setImage('someImage');
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function testPersistBeginFlushRollback(): void
    {
        $post = $this->createPostWithImage();
        $id = $post->getId();
        $this->entityManager->persist($post);
        $this->entityManager->beginTransaction();
        $this->entityManager->flush();
        $this->entityManager->rollback();
        $this->assertPostImageNotExists($id);
    }

    public function testBeginPersistFlushRollback(): void
    {
        $post = $this->createPostWithImage();
        $id = $post->getId();
        $this->entityManager->beginTransaction();
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->entityManager->rollback();
        $this->assertPostImageNotExists($id);
    }

    public function testPersistBeginFlushCommit(): void
    {
        $post = $this->createPostWithImage();
        $id = $post->getId();
        $this->entityManager->persist($post);
        $this->entityManager->beginTransaction();
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->assertPostImageExists($id);
    }

    public function testBeginPersistFlushCommit(): void
    {
        $post = $this->createPostWithImage();
        $id = $post->getId();
        $this->entityManager->beginTransaction();
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->assertPostImageExists($id);
    }

    public function testLoadRemoveBeginFlushCommit(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->beginTransaction();
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->assertPostImageNotExists($id);
    }

    public function testLoadRemoveBeginFlushRollback(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->beginTransaction();
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        $this->entityManager->rollback();
        $this->assertPostImageExists($id);
    }

    public function testLoadRemoveBeginFlushClearCommit(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->beginTransaction();
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->entityManager->commit();
        $this->assertPostImageNotExists($id);
    }

    public function testLoadRemoveBeginFlushClearRollback(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->beginTransaction();
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->entityManager->rollback();
        $this->assertPostImageExists($id);
    }

    public function testLoadRemoveBeginBeginFlushCommitCommit(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->remove($post);
        $this->entityManager->beginTransaction();
        $this->entityManager->beginTransaction();
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->entityManager->commit();
        $this->assertPostImageNotExists($id);
    }

    public function testLoadRemoveBeginBeginFlushCommitRollback(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->remove($post);
        $this->entityManager->beginTransaction();
        $this->entityManager->beginTransaction();
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->entityManager->rollback();
        $this->assertPostImageExists($id);
    }

    public function testLoadRemoveBeginBeginFlushRollbackCommit(): void
    {
        $post = $this->loadPostWithImage();
        $id = $post->getId();
        $this->entityManager->remove($post);
        $this->entityManager->beginTransaction();
        $this->entityManager->beginTransaction();
        $this->entityManager->flush();
        $this->entityManager->rollback();
        $this->entityManager->commit();
        $this->assertPostImageExists($id);
    }
}
