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

use Rekalogika\Reconstitutor\ReconstitutorProcessor;
use Rekalogika\Reconstitutor\Tests\Model\Entity;
use Rekalogika\Reconstitutor\Tests\Model\EntityExtendingAbstractStub;
use Rekalogika\Reconstitutor\Tests\Model\EntityImplementingStubInterface;
use Rekalogika\Reconstitutor\Tests\Model\EntityWithAttribute;
use Rekalogika\Reconstitutor\Tests\Model\EntityWithAttributeSubclass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ReconstitutorTest extends KernelTestCase
{
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
        $tmp = __DIR__ . '/../../var/storage.txt';
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        $container = static::getContainer();

        $processor = $container->get(ReconstitutorProcessor::class);
        $this->assertInstanceOf(ReconstitutorProcessor::class, $processor);

        // test create and save

        $this->assertTrue(class_exists($entityClass));
        $entity = new $entityClass();
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
