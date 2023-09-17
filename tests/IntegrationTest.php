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
use Rekalogika\Reconstitutor\Resolver\AttributeReconstitutorResolver;
use Rekalogika\Reconstitutor\Resolver\ClassReconstitutorResolver;

class IntegrationTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new ReconstitutorKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $classReconstitutorResolver = $container
            ->get('test.' . ClassReconstitutorResolver::class);

        $this->assertInstanceOf(
            ClassReconstitutorResolver::class,
            $classReconstitutorResolver
        );

        $attributeReconstitutorResolver = $container
            ->get('test.' . AttributeReconstitutorResolver::class);

        $this->assertInstanceOf(
            AttributeReconstitutorResolver::class,
            $attributeReconstitutorResolver
        );
    }
}
