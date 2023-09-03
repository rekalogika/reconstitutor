<?php

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor\Tests\Reconstitutor;

use Rekalogika\Reconstitutor\AbstractClassReconstitutor;

/**
 * @template T of object
 * @extends AbstractClassReconstitutor<T>
 */
abstract class AbstractTestClassReconstitutor extends AbstractClassReconstitutor
{
    use CommonTrait;
}
