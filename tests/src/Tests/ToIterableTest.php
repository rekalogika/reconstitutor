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
use Rekalogika\Reconstitutor\Tests\EventRecorder\EventType;

final class ToIterableTest extends DoctrineTestCase
{
    private function createPosts(): void
    {
        // create the entities
        $a = new Post('a');
        $b = new Post('b');
        $i = new Post('c');
        $d = new Post('d');
        $e = new Post('e');

        $this->entityManager->persist($a);
        $this->entityManager->persist($b);
        $this->entityManager->persist($i);
        $this->entityManager->persist($d);
        $this->entityManager->persist($e);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->reset();
    }

    /**
     * @return iterable<Post>
     */
    private function getIterator(): iterable
    {
        /** @var iterable<Post> */
        return $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Post::class, 'p')
            ->getQuery()
            ->toIterable();
    }

    public function testToIterableNoChange(): void
    {
        $this->createPosts();

        // load using toIterable
        $posts = $this->getIterator();

        // onLoad event should not be called
        $this->assertCountEvents(0, type: EventType::onLoad);

        // loop
        $i = 0;
        foreach ($posts as $post) {
            $this->assertInstanceOf(Post::class, $post);
            $this->assertNotProxy($post);

            $i++;
        }

        $this->assertEquals(5, $i);
        $this->assertCountEvents(5, type: EventType::onLoad);
    }

    public function testToIterableWithUpdate(): void
    {
        $this->createPosts();

        // load using toIterable
        $posts = $this->getIterator();

        // onLoad event should not be called
        $this->assertCountEvents(0, type: EventType::onLoad);

        // loop
        $i = 0;
        foreach ($posts as $post) {
            $this->assertInstanceOf(Post::class, $post);
            $this->assertNotProxy($post);

            $currentTitle = $post->getTitle();
            $post->setTitle($currentTitle . ' updated');

            $this->entityManager->flush();
            $this->entityManager->clear();

            $i++;
        }

        $this->assertEquals(5, $i);
        $this->assertCountEvents(5, type: EventType::onLoad);
        $this->assertCountEvents(5, type: EventType::onSave);
    }

    public function testToIterableWithRemove(): void
    {
        $this->createPosts();

        // load using toIterable
        $posts = $this->getIterator();

        // onLoad event should not be called
        $this->assertCountEvents(0, type: EventType::onLoad);

        // loop
        $i = 0;
        foreach ($posts as $post) {
            $this->assertInstanceOf(Post::class, $post);
            $this->assertNotProxy($post);

            $this->entityManager->remove($post);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $i++;
        }

        $this->assertEquals(5, $i);
        $this->assertCountEvents(5, type: EventType::onLoad);
        $this->assertCountEvents(5, type: EventType::onRemove);
    }
}
