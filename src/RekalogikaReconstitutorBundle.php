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

namespace Rekalogika\Reconstitutor;

use Rekalogika\Reconstitutor\DependencyInjection\AttributeReconstitutorPass;
use Rekalogika\Reconstitutor\DependencyInjection\ClassReconstitutorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RekalogikaReconstitutorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ClassReconstitutorPass())
            ->addCompilerPass(new AttributeReconstitutorPass());
    }
}
