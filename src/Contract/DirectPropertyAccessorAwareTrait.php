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

use Rekalogika\DirectPropertyAccess\DirectPropertyAccessor;

trait DirectPropertyAccessorAwareTrait
{
    private DirectPropertyAccessor $propertyAccessor;

    public function setDirectPropertyAccessor(
        DirectPropertyAccessor $propertyAccessor,
    ): void {
        $this->propertyAccessor = $propertyAccessor;
    }

    protected function set(object $object, string $property, mixed $value): void
    {
        $this->propertyAccessor->setValue($object, $property, $value);
    }

    protected function get(object $object, string $property): mixed
    {
        return $this->propertyAccessor->getValue($object, $property);
    }
}
