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
use Rekalogika\Reconstitutor\Exception\LogicException;

final class ClassReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @param array<class-string,list<string>> $classMap
     */
    public function __construct(private array $classMap) {}

    /**
     * @param class-string $class
     * @return array<array-key,class-string>
     */
    private function getAllClasses(string $class): array
    {
        $parents = class_parents($class);

        if ($parents === false) {
            throw new LogicException('Failed to get class parents');
        }

        $implements = class_implements($class);

        if ($implements === false) {
            throw new LogicException('Failed to get class implements');
        }

        $classes = array_merge(
            [$class],
            $parents,
            $implements,
        );

        return array_unique($classes);
    }

    #[\Override]
    public function getReconstitutors(string $class): array
    {
        $classes = $this->getAllClasses($class);

        $reconstitutors = [];

        foreach ($classes as $class) {
            $reconstitutors = array_merge(
                $reconstitutors,
                $this->classMap[$class] ?? [],
            );
        }

        return $reconstitutors;
    }
}
