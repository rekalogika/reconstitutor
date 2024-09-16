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

use Rekalogika\Reconstitutor\Contract\ClassReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareTrait;

/**
 * @template T of object
 * @extends AbstractReconstitutor<T>
 * @implements ClassReconstitutorInterface<T>
 */
abstract class AbstractClassReconstitutor extends AbstractReconstitutor implements ClassReconstitutorInterface, DirectPropertyAccessorAwareInterface
{
    use DirectPropertyAccessorAwareTrait;

    #[\Override]
    abstract public static function getClass(): string;
}
