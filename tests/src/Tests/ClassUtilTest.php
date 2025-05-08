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

use MongoDBODMProxies\__PM__\Rekalogika\Reconstitutor\Tests\Tests\App\Entity\DummyEntity\Generated93deedc1e7b56ba9c8d5a337a376eda9;
use PHPUnit\Framework\TestCase;
use Rekalogika\Reconstitutor\Tests\Tests\App\Entity\DummyEntity;
use Rekalogika\Reconstitutor\Util\ClassUtil;

final class ClassUtilTest extends TestCase
{
    /**
     * @dataProvider provideTestProxy
     */
    public function testProxy(object $object, string $class): void
    {
        $this->assertTrue(class_exists($class), \sprintf(
            'Class "%s" does not exist',
            $class,
        ));

        $this->assertSame(ClassUtil::getClass($object), $class);
    }

    /**
     * @return iterable<string,array{object,string}>
     */
    public static function provideTestProxy(): iterable
    {
        yield 'Doctrine ORM' => [
            new \Proxies\__CG__\Rekalogika\Reconstitutor\Tests\Tests\App\Entity\DummyEntity(),
            DummyEntity::class,
        ];

        yield 'Doctrine ODM' => [
            new Generated93deedc1e7b56ba9c8d5a337a376eda9(),
            DummyEntity::class,
        ];

        yield 'Non-proxy' => [
            new DummyEntity(),
            DummyEntity::class,
        ];
    }
}

namespace Proxies\__CG__\Rekalogika\Reconstitutor\Tests\Tests\App\Entity;

class DummyEntity extends \Rekalogika\Reconstitutor\Tests\Tests\App\Entity\DummyEntity {}

namespace MongoDBODMProxies\__PM__\Rekalogika\Reconstitutor\Tests\Tests\App\Entity\DummyEntity;

use Rekalogika\Reconstitutor\Tests\Tests\App\Entity\DummyEntity;

class Generated93deedc1e7b56ba9c8d5a337a376eda9 extends DummyEntity {}

namespace Rekalogika\Reconstitutor\Tests\Tests\App\Entity;

class DummyEntity {}
