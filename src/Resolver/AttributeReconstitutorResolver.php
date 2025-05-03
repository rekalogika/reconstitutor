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

use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final readonly class AttributeReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @param array<class-string,list<string>> $classMap
     */
    public function __construct(private array $classMap) {}

    #[\Override]
    public function getReconstitutors(string $class): array
    {
        $reconstitutors = [];
        $reflectionClass = new \ReflectionClass($class);

        while ($reflectionClass instanceof \ReflectionClass) {
            $attributes = $reflectionClass->getAttributes();

            foreach ($attributes as $reflectionAttribute) {
                $attributeClass = $reflectionAttribute->getName();

                $reconstitutors = array_merge(
                    $reconstitutors,
                    $this->classMap[$attributeClass] ?? [],
                );
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return $reconstitutors;
    }
}
