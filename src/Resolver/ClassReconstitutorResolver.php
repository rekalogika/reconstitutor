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

use Rekalogika\Reconstitutor\Contract\ClassReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class ClassReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @param array<class-string,array<int,ClassReconstitutorInterface<object>>> $classMap
     */
    public function __construct(private array $classMap) {}

    /**
     * @return array<array-key,class-string>
     */
    private function getAllClasses(object $object): array
    {
        $parents = class_parents($object);

        // @phpstan-ignore identical.alwaysFalse
        if ($parents === false) {
            throw new \LogicException('Failed to get class parents');
        }

        $implements = class_implements($object);

        // @phpstan-ignore identical.alwaysFalse
        if ($implements === false) {
            throw new \LogicException('Failed to get class implements');
        }

        $classes = array_merge(
            [$object::class],
            $parents,
            $implements,
        );

        return array_unique($classes);
    }

    /**
     * @return iterable<int,ClassReconstitutorInterface<object>>
     */
    #[\Override]
    public function getReconstitutors(object $object): iterable
    {
        $classes = $this->getAllClasses($object);

        $reconstitutors = [];

        foreach ($classes as $class) {
            if (!isset($this->classMap[$class])) {
                continue;
            }

            $reconstitutors = array_merge($reconstitutors, $this->classMap[$class]);
        }

        return $reconstitutors;
    }
}
