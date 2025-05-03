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

namespace Rekalogika\Reconstitutor\DependencyInjection;

use Rekalogika\DirectPropertyAccess\DirectPropertyAccessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DirectPropertyAccessorAwarePass implements CompilerPassInterface
{
    public function __construct(
        private string $tagName,
    ) {}

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $directPropertyAccessorAwares = $container
            ->findTaggedServiceIds($this->tagName, true);

        $directPropertyAccessor = $container
            ->findDefinition(DirectPropertyAccessor::class);

        foreach (array_keys($directPropertyAccessorAwares) as $id) {
            $definition = $container->getDefinition($id);

            $definition->addMethodCall(
                'setDirectPropertyAccessor',
                [$directPropertyAccessor],
            );
        }
    }
}
