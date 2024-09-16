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

interface ReconstitutorResolverInterface
{
    /**
     * Gets all the applicable reconstitutors for the provided object
     *
     * @return iterable<int,ReconstitutorInterface<object>>
     */
    public function getReconstitutors(object $object): iterable;
}
