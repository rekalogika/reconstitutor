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

use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareInterface;
use Rekalogika\Reconstitutor\Contract\DirectPropertyAccessorAwareTrait;
use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;

/**
 * @template T of object
 * @implements ReconstitutorInterface<T>
 */
abstract class AbstractReconstitutor implements
    DirectPropertyAccessorAwareInterface,
    ReconstitutorInterface
{
    use DirectPropertyAccessorAwareTrait;

    #[\Override]
    public function onCreate(object $object): void {}

    #[\Override]
    public function onLoad(object $object): void {}

    #[\Override]
    public function onSave(object $object): void {}

    #[\Override]
    public function onRemove(object $object): void {}

    #[\Override]
    public function onClear(object $object): void {}
}
