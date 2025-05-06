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
use Rekalogika\Reconstitutor\Repository\RepositoryRegistry;
use Rekalogika\Reconstitutor\Tests\Entity\Comment;
use Rekalogika\Reconstitutor\Tests\Entity\Post;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\DoctrinePostReconstitutor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RepositoryRegistry $registry;

    #[\Override] protected function setUp(): void
    {
        parent::setUp();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;

        $registry = static::getContainer()->get('rekalogika.reconstitutor.repository_registry');
        $this->assertInstanceOf(RepositoryRegistry::class, $registry);
        $this->registry = $registry;

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

    public function testRemoveUninitializedProxy(): void
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

        // remove the post
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        // clear
        $this->entityManager->clear();

        // try to reload from database
        $post = $this->entityManager->find(Post::class, $post->getId());
        $this->assertNull($post);
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
        $reconstitutor = static::getContainer()
            ->get(DoctrinePostReconstitutor::class);

        $this->assertInstanceOf(DoctrinePostReconstitutor::class, $reconstitutor);
        $this->assertTrue($reconstitutor->hasClearCalledOnObjectId($post->getId()));
    }

    //
    // assertions
    //

    private function assertIsProxy(mixed $object): void
    {
        $this->assertIsObject($object, 'Expected an object');

        if (\PHP_VERSION_ID >= 80400) {
            $reflection = new \ReflectionClass($object);
            /**
             * @psalm-suppress UndefinedMethod
             * @psalm-suppress MixedAssignment
             */
            $isProxy = $reflection->isUninitializedLazyObject($object);

            if ($isProxy) {
                return;
            }
        }

        $this->assertInstanceOf(Proxy::class, $object, 'Object is not a proxy');
        $this->assertFalse($object->__isInitialized(), 'Object is not an uninitialized proxy');
    }

    private function assertNotProxy(mixed $object): void
    {
        $this->assertIsObject($object, 'Expected an object');

        if ($object instanceof Proxy) {
            $this->assertTrue($object->__isInitialized(), 'Object is a proxy, but should not be');

            return;
        }

        if (\PHP_VERSION_ID >= 80400) {
            $reflection = new \ReflectionClass($object);

            /**
             * @psalm-suppress UndefinedMethod
             */
            if ($reflection->isUninitializedLazyObject($object)) {
                static::fail('Object is a proxy, but should not be');
            }
        }
    }
}
