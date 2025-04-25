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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Proxy;
use Rekalogika\Reconstitutor\Tests\Entity\Comment;
use Rekalogika\Reconstitutor\Tests\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineTest extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;

        /** @var list<ClassMetadata<object>> */
        $allMetadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($allMetadatas);
    }

    public function testPost(): void
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

    public function testProxy(): void
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
        $this->assertInstanceOf(Proxy::class, $post);
        $this->assertFalse($post->__isInitialized());

        if (!property_exists($post, '__initializer__')) {
            // lazy ghost proxy.
            // should be true. see https://github.com/doctrine/orm/pull/11606
            // remove this when upstream fixes the problem
            $post->getImage();
            $this->assertFalse($post->__isInitialized());

            $post->getTitle();
            $this->assertTrue($post->__isInitialized());
        }

        $this->assertEquals('someImage', $post->getImage());
    }

    public function testUninitializedProxyFlush(): void
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
        $this->assertInstanceOf(Proxy::class, $post);
        $this->assertFalse($post->__isInitialized());

        // this flush should not remove the image
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // reload from database
        $post = $this->entityManager->getReference(Post::class, $post->getId());
        $this->assertInstanceOf(Post::class, $post);
        $this->assertInstanceOf(Proxy::class, $post);
        $this->assertFalse($post->__isInitialized());

        if (!property_exists($post, '__initializer__')) {
            // lazy ghost proxy.
            // should be true. see https://github.com/doctrine/orm/pull/11606
            // remove this when upstream fixes the problem
            $post->getImage();
            $this->assertFalse($post->__isInitialized());

            $post->getTitle();
            $this->assertTrue($post->__isInitialized());
        }

        // make sure the flush did not remove the image
        $this->assertEquals('someImage', $post->getImage());
    }
}
