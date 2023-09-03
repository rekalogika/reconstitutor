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

use Rekalogika\Reconstitutor\Contract\ClassReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class ClassReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @param array<class-string,array<int,ClassReconstitutorInterface<object>>> $classMap
     */
    public function __construct(private array $classMap)
    {
    }

    /**
     * @return array<array-key,class-string>
     */
    private static function getAllClasses(object $object): array
    {
        $classes = \array_merge(
            [\get_class($object)],
            class_parents($object),
            class_implements($object)
        );

        return \array_unique($classes);
    }

    /**
     * @return iterable<int,ClassReconstitutorInterface<object>>
     */
    public function getReconstitutors(object $object): iterable
    {
        $classes = self::getAllClasses($object);

        $reconstitutors = [];

        foreach ($classes as $class) {
            if (!isset($this->classMap[$class])) {
                continue;
            }

            $reconstitutors = \array_merge($reconstitutors, $this->classMap[$class]);
        }

        return $reconstitutors;
    }
}
