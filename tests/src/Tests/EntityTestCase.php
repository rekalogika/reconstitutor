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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Proxy;
use Rekalogika\Reconstitutor\Tests\Entity\Post;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\DoctrinePostReconstitutor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class EntityTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<Post>
     */
    protected EntityRepository $repository;
    protected UnitOfWork $unitOfWork;
    protected DoctrinePostReconstitutor $reconstitutor;

    protected ?Post $post = null;
    protected ?string $id = null;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->unitOfWork = $this->entityManager->getUnitOfWork();
        $this->repository = $this->entityManager->getRepository(Post::class);

        /** @var list<ClassMetadata<object>> */
        $allMetadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($allMetadatas);

        // reconstitutor

        $reconstitutor = static::getContainer()
            ->get(DoctrinePostReconstitutor::class);

        $this->assertInstanceOf(DoctrinePostReconstitutor::class, $reconstitutor);
        $this->reconstitutor = $reconstitutor;
    }

    //
    // basic ops
    //

    protected function createPostWithImage(): Post
    {
        $post = new Post('title');
        $post->setImage('someImage');

        return $post;
    }

    //
    // entity ops
    //

    /**
     * Initialize the test case by instantiating a Post entity with an image,
     */
    protected function init(): void
    {
        $this->instantiate();
        $this->persist();
        $this->flush();
        $this->clear();
        $this->post = null;
        $this->reconstitutor->resetEvents();
    }

    protected function instantiate(): void
    {
        $this->post = $this->createPostWithImage();
        $this->id = $this->post->getId();
    }

    protected function load(): void
    {
        $this->assertNull($this->post);
        $this->assertNotNull($this->id);
        $this->post = $this->repository->find($this->id);
        $this->assertInstanceOf(Post::class, $this->post);
        $this->id = $this->post->getId();
    }

    protected function reference(): void
    {
        $this->assertNull($this->post);
        $this->assertNotNull($this->id);
        $this->post = $this->entityManager->getReference(Post::class, $this->id);
        $this->assertInstanceOf(Post::class, $this->post);
        $this->id = $this->post->getId();
    }

    protected function getImage(): ?string
    {
        $this->assertNotNull($this->post, 'Post should not be null before getting image');
        return $this->post->getImage();
    }

    protected function removeImage(): void
    {
        $this->assertNotNull($this->post);
        $this->post->setImage(null);
    }

    protected function uploadImage(): void
    {
        $this->assertNotNull($this->post);
        $this->post->setImage('someImage');
    }

    //
    // object manager ops
    //

    protected function persist(): void
    {
        $this->assertNotNull($this->post, 'Post should not be null before persisting');
        $this->entityManager->persist($this->post);
    }

    protected function remove(): void
    {
        $this->assertNotNull($this->post, 'Post should not be null before removing');
        $this->entityManager->remove($this->post);
    }

    protected function flush(): void
    {
        $this->entityManager->flush();
    }

    protected function begin(): void
    {
        $this->entityManager->beginTransaction();
    }

    protected function commit(): void
    {
        $this->entityManager->commit();
    }

    protected function rollback(): void
    {
        $this->entityManager->rollback();
    }

    protected function clear(): void
    {
        $this->entityManager->clear();
    }

    protected function detach(): void
    {
        $this->assertNotNull($this->post, 'Post should not be null before detaching');
        $this->entityManager->detach($this->post);
    }

    protected function close(): void
    {
        $this->entityManager->close();
    }

    //
    // assertions
    //

    protected function assertImagePresent(): void
    {
        $this->assertNotNull($this->id, 'ID should not be null');
        $this->assertTrue($this->reconstitutor->isImageExists($this->id), 'Image should exist');
    }

    protected function assertImageNotPresent(): void
    {
        $this->assertNotNull($this->id, 'ID should not be null');
        $this->assertFalse($this->reconstitutor->isImageExists($this->id), 'Image should not exist');
    }

    /**
     * @param list<string> $expectedEvents
     */
    protected function assertEvents(array $expectedEvents): void
    {
        $this->assertEquals($expectedEvents, $this->reconstitutor->getEvents(), 'Events should match expected events');
    }

    protected function assertIsProxy(): void
    {
        $object = $this->post;

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

    protected function assertNotProxy(): void
    {
        $object = $this->post;

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
