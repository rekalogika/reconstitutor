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

namespace Rekalogika\Reconstitutor\Resolver;

use Rekalogika\Reconstitutor\Contract\AttributeReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class AttributeReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @var array<class-string,iterable<int,ReconstitutorInterface<object>>>
     */
    private array $cache = [];

    /**
     * @param array<class-string,array<int,AttributeReconstitutorInterface>> $classMap
     */
    public function __construct(private array $classMap) {}

    #[\Override]
    public function getReconstitutors(object $object): iterable
    {
        $class = $object::class;

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        $reconstitutors = [];

        $reflectionClass = new \ReflectionClass($class);
        while ($reflectionClass instanceof \ReflectionClass) {
            $attributes = $reflectionClass->getAttributes();

            foreach ($attributes as $reflectionAttribute) {
                $attributeClass = $reflectionAttribute->getName();
                $reconstitutor = $this->classMap[$attributeClass] ?? [];

                if (empty($reconstitutor)) {
                    continue;
                }

                $reconstitutors += $reconstitutor;
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return $this->cache[$class] = $reconstitutors;
    }
}
