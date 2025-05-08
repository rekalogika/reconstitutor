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

trait ReconstitutorResolverTrait
{
    /**
     * @param class-string $class
     */
    public function hasReconstitutor(string $class): bool
    {
        return $this->getReconstitutors($class) !== [];
    }
}
