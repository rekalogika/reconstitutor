<?php

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rekalogika\Reconstitutor\ReconstitutorProcessor;
use Rekalogika\Reconstitutor\Tests\Model\Entity;
use Rekalogika\Reconstitutor\Tests\Model\EntityExtendingAbstractStub;
use Rekalogika\Reconstitutor\Tests\Model\EntityImplementingStubInterface;
use Rekalogika\Reconstitutor\Tests\Model\EntityWithAttribute;
use Rekalogika\Reconstitutor\Tests\Model\EntityWithAttributeSubclass;

class ReconstitutorTest extends TestCase
{
    private ?ContainerInterface $container = null;

    public function setUp(): void
    {
        $kernel = new ReconstitutorKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
    }

    public function testClassReconstitutor(): void
    {
        $this->testOne(Entity::class);
    }

    public function testInterfaceReconstitutor(): void
    {
        $this->testOne(EntityImplementingStubInterface::class);
    }

    public function testSuperclassReconstitutor(): void
    {
        $this->testOne(EntityExtendingAbstractStub::class);
    }

    public function testAttributeReconstitutor(): void
    {
        $this->testOne(EntityWithAttribute::class);
    }

    public function testAttributeSubclassReconstitutor(): void
    {
        $this->testOne(EntityWithAttributeSubclass::class);
    }

    /**
     * @param class-string<Entity|EntityExtendingAbstractStub|EntityImplementingStubInterface|EntityWithAttribute> $entityClass
     */
    private function testOne(string $entityClass): void
    {
        $tmp = __DIR__ . '/../var/storage.txt';
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        $processor = $this->container?->get('test.' . ReconstitutorProcessor::class);
        $this->assertInstanceOf(ReconstitutorProcessor::class, $processor);

        // test create and save

        $this->assertTrue(class_exists($entityClass));
        $entity = new $entityClass;
        $entity->setAttribute('foo');

        $processor->onSave($entity);

        $this->assertFileExists($tmp);
        $content = file_get_contents($tmp);
        $this->assertSame('foo', $content);

        unset($entity);

        // test load

        $entity = new Entity();
        $processor->onLoad($entity);
        $this->assertSame('foo', $entity->getAttribute());

        // test remove

        $processor->onRemove($entity);
        $this->assertFileDoesNotExist($tmp);
    }
}
