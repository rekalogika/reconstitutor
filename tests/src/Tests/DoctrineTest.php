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

use Rekalogika\Reconstitutor\Tests\Entity\Comment;
use Rekalogika\Reconstitutor\Tests\Entity\Other;
use Rekalogika\Reconstitutor\Tests\Entity\Post;
use Rekalogika\Reconstitutor\Tests\EventRecorder\EventType;

final class DoctrineTest extends DoctrineTestCase
{
    public function testLifeCycle(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');

        $comment = new Comment('content');
        $post->addComment($comment);
        $this->entityManager->persist($post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $id = $post->getId();
        $post = $this->entityManager->find(Post::class, $id);
        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('title', $post->getTitle());
        $this->assertSame('someImage', $post->getImage());
        $this->assertCount(1, $post->getComments());

        // remove image
        $post->setImage(null);
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $post = $this->entityManager->find(Post::class, $id);
        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('title', $post->getTitle());
        $this->assertNull($post->getImage());
        $this->assertCount(1, $post->getComments());
    }

    public function testProxyInitializationOnNonDoctrineManagedProperty(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertIsProxy($post);
        $this->assertEquals('someImage', $post->getImage());
        $this->assertNotProxy($post);
    }

    public function testFlushUninitializedProxy(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertIsProxy($post);

        // this flush should not remove the image
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertIsProxy($post);

        // make sure the flush did not remove the image
        $this->assertEquals('someImage', $post->getImage());
        $this->assertNotProxy($post);
    }

    public function testRemove(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');
        $id = $post->getId();

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        // remove the post
        $this->entityManager->remove($post);
        $this->assertNotProxy($post);
        $this->assertEquals($id, $post->getId());
        $this->entityManager->flush();

        // check in reconstitutor
        $this->assertEventRecorded($post, type: EventType::onRemove);
    }

    public function testRemoveUninitializedProxy(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');
        $id = $post->getId();

        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->assertPostImageExists($id);

        // clear
        $this->entityManager->clear();

        // reload from database
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertIsProxy($post);

        // remove the post
        $this->entityManager->remove($post);
        $this->assertNotProxy($post);
        $this->assertEquals($id, $post->getId());
        $this->assertPostImageExists($id);

        $this->entityManager->flush();
        $this->assertPostImageNotExists($id);

        // clear
        $this->entityManager->clear();

        // try to reload from database
        $post = $this->entityManager->find(Post::class, $post->getId());
        $this->assertNull($post);

        // check in reconstitutor
        $this->assertPostImageNotExists($id);
    }

    public function testRemoveUninitializedProxyWithoutReconstitutor(): void
    {
        // create the entities
        $entity = new Other();
        $id = $entity->getId();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $entity = $this->entityManager->getReference(Other::class, $entity->getId());
        $this->assertInstanceOf(Other::class, $entity);
        $this->assertIsProxy($entity);

        // remove the entity, the entity should not be initialized
        $this->entityManager->remove($entity);
        $this->assertIsProxy($entity);
        $this->assertEquals($id, $entity->getId());

        $this->entityManager->flush();
        $this->assertIsProxy($entity);
        $this->assertEquals($id, $entity->getId());
    }

    public function testClear(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');
        $this->entityManager->persist($post);
        $this->assertCount(1, $this->registry->get($this->entityManager));

        // clear
        $this->entityManager->clear();
        $this->assertCount(0, $this->registry->get($this->entityManager));

        // check in reconstitutor
        $this->assertEventRecorded($post, type: EventType::onClear);
    }

    public function testClearProxy(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');
        $this->entityManager->persist($post);
        $this->assertCount(1, $this->registry->get($this->entityManager));
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->eventRecorder->reset(); // reset our tracker
        $this->assertCount(0, $this->registry->get($this->entityManager));

        // reload
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertIsProxy($post);
        $this->assertCount(0, $this->registry->get($this->entityManager));

        // clear should not call onClear on proxy
        $this->entityManager->clear();
        $this->assertCount(0, $this->registry->get($this->entityManager));

        // check in reconstitutor
        $this->assertCountEvents(0, type: EventType::onClear, id: $post->getId());
    }

    public function testDetachInitialized(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');
        $this->entityManager->persist($post);
        $this->assertCount(1, $this->registry->get($this->entityManager));
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->eventRecorder->reset(); // reset our tracker
        $this->assertCount(0, $this->registry->get($this->entityManager));

        // reload
        $post = $this->entityManager->find(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotProxy($post);
        $this->assertCount(1, $this->registry->get($this->entityManager));

        // detach and flush should call onClear
        $this->entityManager->detach($post);
        $this->entityManager->flush();

        // check in reconstitutor
        $this->assertCountEvents(1, type: EventType::onClear, id: $post->getId());
    }

    public function testDetachProxy(): void
    {
        // create the entities
        $post = new Post('title');
        $post->setImage('someImage');
        $this->entityManager->persist($post);
        $this->assertCount(1, $this->registry->get($this->entityManager));
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->eventRecorder->reset(); // reset our tracker
        $this->assertCount(0, $this->registry->get($this->entityManager));

        // reload
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertCount(0, $this->registry->get($this->entityManager));
        $this->assertIsProxy($post);

        // detach and flush should not call onClear on proxy
        $this->entityManager->detach($post);
        $this->entityManager->flush();
        $this->assertIsProxy($post);

        // check in reconstitutor
        $this->assertCountEvents(0, type: EventType::onClear, id: $post->getId());
    }

    public function testRemoveInTransactionRollback(): void
    {
        // create the entity
        $post = new Post('title');
        $post->setImage('someImage');
        $id = $post->getId();

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        // remove the post in a transaction
        $this->assertPostImageExists($id);
        $this->entityManager->beginTransaction();
        $this->assertPostImageExists($id);
        $this->entityManager->remove($post);
        $this->assertPostImageExists($id);
        $this->entityManager->flush();
        $this->assertPostImageExists($id);
        $this->entityManager->rollback();
        $this->assertPostImageExists($id);
    }

    public function testRemoveInTransactionCommit(): void
    {
        // create the entity
        $post = new Post('title');
        $post->setImage('someImage');
        $id = $post->getId();

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        // remove the post in a transaction
        $this->assertPostImageExists($id);
        $this->entityManager->beginTransaction();
        $this->assertPostImageExists($id);
        $this->entityManager->remove($post);
        $this->assertPostImageExists($id);
        $this->entityManager->flush();
        $this->assertPostImageExists($id);
        $this->entityManager->commit();
        $this->assertPostImageNotExists($id);
    }
}
