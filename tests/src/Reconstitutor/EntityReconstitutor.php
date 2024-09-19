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

namespace Rekalogika\Reconstitutor\Tests\Reconstitutor;

use Rekalogika\Reconstitutor\Tests\Model\Entity;

/**
 * @extends AbstractTestClassReconstitutor<Entity>
 */
class EntityReconstitutor extends AbstractTestClassReconstitutor
{
    #[\Override]
    public static function getClass(): string
    {
        return Entity::class;
    }
}