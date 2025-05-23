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

namespace Rekalogika\Reconstitutor\Contract;

/**
 * @internal
 */
interface ReconstitutorResolverInterface
{
    /**
     * Gets all the applicable reconstitutors for the provided object
     *
     * @param class-string $class
     * @return list<string> Service ID of reconstitutors
     */
    public function getReconstitutors(string $class): array;

    /**
     * Checks if the class has one or more reconstitutors
     *
     * @param class-string $class
     */
    public function hasReconstitutor(string $class): bool;
}
