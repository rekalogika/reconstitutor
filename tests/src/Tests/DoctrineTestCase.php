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
use Rekalogika\Reconstitutor\Context\ManagerContextRegistry;
use Rekalogika\Reconstitutor\Tests\EventRecorder\EventRecorder;
use Rekalogika\Reconstitutor\Tests\EventRecorder\EventType;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\DoctrinePostReconstitutor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

abstract class DoctrineTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ManagerContextRegistry $registry;
    protected DoctrinePostReconstitutor $reconstitutor;
    protected ServicesResetter $resetter;
    protected UnitOfWork $unitOfWork;
    protected EventRecorder $eventRecorder;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->unitOfWork = $this->entityManager->getUnitOfWork();

        $registry = static::getContainer()->get('rekalogika.reconstitutor.manager_context_registry');
        $this->assertInstanceOf(ManagerContextRegistry::class, $registry);
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
        $resetter = static::getContainer()->get('services_resetter');
        $this->assertInstanceOf(ServicesResetter::class, $resetter);
        $this->resetter = $resetter;

        // event recorder
        $eventRecorder = static::getContainer()->get(EventRecorder::class);
        $this->assertInstanceOf(EventRecorder::class, $eventRecorder);
        $this->eventRecorder = $eventRecorder;
    }

    protected function reset(): void
    {
        $this->resetter->reset();
    }

    protected function assertIsProxy(mixed $object): void
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

    protected function assertNotProxy(mixed $object): void
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

    protected function assertEventRecorded(
        ?object $object = null,
        ?string $id = null,
        ?EventType $type = null,
    ): void {
        $events = $this->eventRecorder->filterEvents($object, $id, $type);
        $this->assertNotEmpty($events, 'No event recorded');
    }

    protected function assertCountEvents(
        int $count,
        ?object $object = null,
        ?string $id = null,
        ?EventType $type = null,
    ): void {
        $events = $this->eventRecorder->filterEvents($object, $id, $type);
        $this->assertCount($count, $events, 'Event count mismatch');
    }

    protected function assertPostImageExists(string $objectId): void
    {
        $this->assertTrue($this->reconstitutor->isImageExists($objectId), 'Image should exist');
    }

    protected function assertPostImageNotExists(string $objectId): void
    {
        $this->assertFalse($this->reconstitutor->isImageExists($objectId), 'Image should not exist');
    }
}
