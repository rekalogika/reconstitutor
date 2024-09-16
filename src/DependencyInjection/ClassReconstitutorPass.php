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
use Rekalogika\Reconstitutor\Contract\ClassReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareInterface;
use Rekalogika\Reconstitutor\Resolver\ClassReconstitutorResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ClassReconstitutorPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $reconstitutorResolver = $container
            ->findDefinition(ClassReconstitutorResolver::class);

        $classReconstitutors = $container
            ->findTaggedServiceIds('rekalogika.reconstitutor.class', true);

        $directPropertyAccessor = $container
            ->findDefinition(DirectPropertyAccessor::class);

        /**
         * @var array<class-string,array<int,Definition>>
         */
        $classMap = [];

        foreach (array_keys($classReconstitutors) as $id) {
            $definition = $container->getDefinition($id);
            $reconstitutorClass = $definition->getClass();
            \assert(\is_string($reconstitutorClass));
            \assert(class_exists($reconstitutorClass));

            if (($r = $container->getReflectionClass($reconstitutorClass)) === null) {
                throw new \InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $reconstitutorClass, $id));
            }

            if (!$r->isSubclassOf(ClassReconstitutorInterface::class)) {
                throw new \InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, ClassReconstitutorInterface::class));
            }

            $reconstitutorClass = $r->name;
            $targetClass = $reconstitutorClass::getClass();

            if (($r = $container->getReflectionClass($targetClass)) === null) {
                throw new \InvalidArgumentException(\sprintf('Class "%s" used by reconstitutor "%s" cannot be found.', $targetClass, $reconstitutorClass));
            }

            if (is_a($reconstitutorClass, DirectPropertyAccessorAwareInterface::class, true)) {
                $definition->addMethodCall(
                    'setDirectPropertyAccessor',
                    [$directPropertyAccessor],
                );
            }

            $classMap[$targetClass][] = $definition;
        }

        $reconstitutorResolver->setArgument('$classMap', $classMap);
    }
}
