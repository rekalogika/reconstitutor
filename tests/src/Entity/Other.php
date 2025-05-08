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

namespace Rekalogika\Reconstitutor\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
class Other
{
    #[ORM\Id]
    #[ORM\Column(unique: true, nullable: false)]
    private string $id;

    public function __construct()
    {
        $this->id = Uuid::v6()->toRfc4122();
    }

    public function getId(): string
    {
        return $this->id;
    }
}
