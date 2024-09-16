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

namespace Rekalogika\Reconstitutor\Tests;

use Rekalogika\DirectPropertyAccess\RekalogikaDirectPropertyAccessBundle;
use Rekalogika\Reconstitutor\RekalogikaReconstitutorBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class ReconstitutorKernel extends Kernel
{
    public function __construct()
    {
        $this->environment = 'test';
        $this->debug = true;
        parent::__construct('test', true);
    }

    #[\Override]
    public function registerBundles(): iterable
    {
        return [
            new RekalogikaDirectPropertyAccessBundle(),
            new RekalogikaReconstitutorBundle(),
        ];
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void {}
}
