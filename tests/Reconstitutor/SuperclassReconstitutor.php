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

use Rekalogika\Reconstitutor\Tests\Model\AbstractStub;

/**
 * @extends AbstractTestClassReconstitutor<AbstractStub>
 */
class SuperclassReconstitutor extends AbstractTestClassReconstitutor
{
    public static function getClass(): string
    {
        return AbstractStub::class;
    }
}
