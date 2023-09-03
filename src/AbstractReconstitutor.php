<?php

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor;

use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareTrait;

/**
 * @template T of object
 */
abstract class AbstractReconstitutor
implements DirectPropertyAccessorAwareInterface
{
    use DirectPropertyAccessorAwareTrait;

    public function onCreate(object $object): void
    {
    }

    public function onLoad(object $object): void
    {
    }

    public function onSave(object $object): void
    {
    }

    public function onRemove(object $object): void
    {
    }
}
