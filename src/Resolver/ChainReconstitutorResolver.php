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

final class ChainReconstitutorResolver implements ReconstitutorResolverInterface
{
    /**
     * @param iterable<ReconstitutorResolverInterface> $resolvers
     */
    public function __construct(private readonly iterable $resolvers) {}

    #[\Override]
    public function getReconstitutors(string $class): array
    {
        $ids = [];

        foreach ($this->resolvers as $resolver) {
            foreach ($resolver->getReconstitutors($class) as $id) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    #[\Override]
    public function hasReconstitutor(string $class): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasReconstitutor($class)) {
                return true;
            }
        }

        return false;
    }
}
