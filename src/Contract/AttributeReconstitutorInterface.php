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
 * @extends ReconstitutorInterface<object>
 */
interface AttributeReconstitutorInterface extends ReconstitutorInterface
{
    /**
     * The class name of the object that this reconstitutor is responsible for.
     *
     * @return class-string
     */
    public static function getAttributeClass(): string;
}
