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

namespace Rekalogika\Reconstitutor\DependencyInjection;

use Rekalogika\Reconstitutor\Contract\AttributeReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ClassReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class RekalogikaReconstitutorExtension extends Extension
{
    /**
     * @param array<array-key,mixed> $configs
     */
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config'),
        );
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(ReconstitutorResolverInterface::class)
            ->addTag('rekalogika.reconstitutor.resolver');

        $container
            ->registerForAutoconfiguration(ClassReconstitutorInterface::class)
            ->addTag('rekalogika.reconstitutor.class');

        $container
            ->registerForAutoconfiguration(AttributeReconstitutorInterface::class)
            ->addTag('rekalogika.reconstitutor.attribute');
    }
}
