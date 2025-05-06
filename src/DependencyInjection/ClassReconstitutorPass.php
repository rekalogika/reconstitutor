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
use Rekalogika\Reconstitutor\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ClassReconstitutorPass implements CompilerPassInterface
{
    public const TAG_NAME = 'rekalogika.reconstitutor.class';

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $reconstitutorResolver = $container
            ->findDefinition('rekalogika.reconstitutor.resolver.class');

        $classReconstitutors = $container->findTaggedServiceIds(self::TAG_NAME, true);

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
                throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $reconstitutorClass, $id));
            }

            if (!$r->isSubclassOf(ClassReconstitutorInterface::class)) {
                throw new InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, ClassReconstitutorInterface::class));
            }

            $reconstitutorClass = $r->name;
            $targetClass = $reconstitutorClass::getClass();

            if (($r = $container->getReflectionClass($targetClass)) === null) {
                throw new InvalidArgumentException(\sprintf('Class "%s" used by reconstitutor "%s" cannot be found.', $targetClass, $reconstitutorClass));
            }

            $classMap[$targetClass][] = $id;

            // inject direct property accessor if it asks for that

            if (is_a($reconstitutorClass, DirectPropertyAccessorAwareInterface::class, true)) {
                $definition->addMethodCall(
                    'setDirectPropertyAccessor',
                    [$directPropertyAccessor],
                );
            }
        }

        $reconstitutorResolver->setArgument('$classMap', $classMap);
    }
}
