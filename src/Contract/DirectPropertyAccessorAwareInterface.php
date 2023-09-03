<?php

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor\Contract;

use Rekalogika\DirectPropertyAccess\DirectPropertyAccessor;

interface DirectPropertyAccessorAwareInterface
{
    public function setDirectPropertyAccessor(
        DirectPropertyAccessor $propertyAccessor
    ): void;
}
