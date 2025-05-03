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

use Rekalogika\Reconstitutor\Resolver\AttributeReconstitutorResolver;
use Rekalogika\Reconstitutor\Resolver\ClassReconstitutorResolver;
use Rekalogika\Reconstitutor\Tests\ReconstitutorKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IntegrationTest extends KernelTestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new ReconstitutorKernel();
        $kernel->boot();

        $container = static::getContainer();

        $classReconstitutorResolver = $container
            ->get('rekalogika.reconstitutor.resolver.class');

        $this->assertInstanceOf(
            ClassReconstitutorResolver::class,
            $classReconstitutorResolver,
        );

        $attributeReconstitutorResolver = $container
            ->get('rekalogika.reconstitutor.resolver.attribute');

        $this->assertInstanceOf(
            AttributeReconstitutorResolver::class,
            $attributeReconstitutorResolver,
        );
    }
}
