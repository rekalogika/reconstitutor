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

namespace Rekalogika\Reconstitutor;

use Rekalogika\Reconstitutor\Contract\AttributeReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareTrait;

/**
 * @extends AbstractReconstitutor<object>
 */
abstract class AbstractAttributeReconstitutor extends AbstractReconstitutor implements
    DirectPropertyAccessorAwareInterface,
    AttributeReconstitutorInterface
{
    use DirectPropertyAccessorAwareTrait;

    #[\Override]
    abstract public static function getAttributeClass(): string;
}
