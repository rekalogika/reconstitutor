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
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Proxy;
use Rekalogika\Reconstitutor\Repository\RepositoryRegistry;
use Rekalogika\Reconstitutor\Tests\Entity\Comment;
use Rekalogika\Reconstitutor\Tests\Entity\Post;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\DoctrinePostReconstitutor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

final class DoctrineTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RepositoryRegistry $registry;
    private DoctrinePostReconstitutor $reconstitutor;
    // private ServicesResetter $resetter;
    private UnitOfWork $unitOfWork;

    #[\Override] protected function setUp(): void
    {
        parent::setUp();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->unitOfWork = $this->entityManager->getUnitOfWork();

        $registry = static::getContainer()->get('rekalogika.reconstitutor.repository_registry');
        $this->assertInstanceOf(RepositoryRegistry::class, $registry);
        $this->registry = $registry;

        /** @var list<ClassMetadata<object>> */
        $allMetadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($allMetadatas);

        // reconstitutor

        $reconstitutor = static::getContainer()
            ->get(DoctrinePostReconstitutor::class);

        $this->assertInstanceOf(DoctrinePostReconstitutor::class, $reconstitutor);
        $this->reconstitutor = $reconstitutor;

        // resetter
        // $resetter = static::getContainer()->get('services_resetter');
        // $this->assertInstanceOf(ServicesResetter::class, $resetter);
        // $this->resetter = $resetter;
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
        $id = $post->getId();

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
        $this->assertNotProxy($post);
        $this->assertEquals($id, $post->getId());

        $this->entityManager->flush();
        $this->assertNotProxy($post);
        $this->assertEquals($id, $post->getId());

        // clear
        $this->entityManager->clear();

        // try to reload from database
        $post = $this->entityManager->find(Post::class, $post->getId());
        $this->assertNull($post);

        // check in reconstitutor
        $this->assertFalse($this->reconstitutor->isImageExists($id), 'Image should be removed');
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
        $this->assertTrue($this->reconstitutor->hasClearCalledOnObjectId($post->getId()));
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
        $this->reconstitutor->reset(); // reset our tracker
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
        $this->assertFalse($this->reconstitutor->hasClearCalledOnObjectId($post->getId()));
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
        $this->reconstitutor->reset(); // reset our tracker
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
        $this->assertTrue($this->reconstitutor->hasClearCalledOnObjectId($post->getId()));
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
        $this->reconstitutor->reset(); // reset our tracker
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
        $this->assertFalse($this->reconstitutor->hasClearCalledOnObjectId($post->getId()));
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
