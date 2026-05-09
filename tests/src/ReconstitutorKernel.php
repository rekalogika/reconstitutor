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

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Rekalogika\DirectPropertyAccess\RekalogikaDirectPropertyAccessBundle;
use Rekalogika\Reconstitutor\RekalogikaReconstitutorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class ReconstitutorKernel extends Kernel
{
    use MicroKernelTrait {
        registerContainerConfiguration as private baseRegisterContainerConfiguration;
    }

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
            new FrameworkBundle(),
            new DoctrineBundle(),
            new RekalogikaDirectPropertyAccessBundle(),
            new RekalogikaReconstitutorBundle(),
            new MonologBundle(),
        ];
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $this->baseRegisterContainerConfiguration($loader);

        $versionParser = new VersionParser();

        $canUseNative = \PHP_VERSION_ID >= 80400
            && InstalledVersions::satisfies($versionParser, 'doctrine/orm', '>=3.4')
            && InstalledVersions::satisfies($versionParser, 'doctrine/doctrine-bundle', '>=2.15');

        if ($canUseNative) {
            $ormConfig = ['enable_native_lazy_objects' => true];
        } elseif (InstalledVersions::satisfies($versionParser, 'doctrine/doctrine-bundle', '<3.0')) {
            $ormConfig = ['enable_lazy_ghost_objects' => true];
        } else {
            $ormConfig = null;
        }

        $dbalConfig = InstalledVersions::satisfies($versionParser, 'doctrine/doctrine-bundle', '<3.0')
            ? ['use_savepoints' => true]
            : null;

        if ($ormConfig === null && $dbalConfig === null) {
            return;
        }

        $loader->load(static function (ContainerBuilder $container) use ($ormConfig, $dbalConfig): void {
            $config = [];
            if ($ormConfig !== null) {
                $config['orm'] = $ormConfig;
            }
            if ($dbalConfig !== null) {
                $config['dbal'] = $dbalConfig;
            }
            $container->loadFromExtension('doctrine', $config);
        });
    }

    #[\Override]
    public function getProjectDir(): string
    {
        return __DIR__ . '/../';
    }

    public function getConfigDir(): string
    {
        return __DIR__ . '/../config/';
    }
}
