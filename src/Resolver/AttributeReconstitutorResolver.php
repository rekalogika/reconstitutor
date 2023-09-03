<?php

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
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class AttributeReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @param array<class-string,array<int,AttributeReconstitutorInterface>> $classMap
     */
    public function __construct(private array $classMap)
    {
    }

    /**
     * @return array<array-key,class-string>
     */
    private function getAllApplicableAttributes(): array
    {
        return array_keys($this->classMap);
    }

    public function getReconstitutors(object $object): iterable
    {
        $applicableAttributes = $this->getAllApplicableAttributes();

        $reflectionClass = (new \ReflectionClass(get_class($object)));
        while ($reflectionClass instanceof \ReflectionClass) {
            $attributes = $reflectionClass->getAttributes();

            foreach ($attributes as $reflectionAttribute) {
                if (!\in_array($reflectionAttribute->getName(), $applicableAttributes, true)) {
                    continue;
                }

                yield from $this->classMap[$reflectionAttribute->getName()];
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }
    }
}
