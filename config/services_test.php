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

use Rekalogika\Reconstitutor\Doctrine\DoctrineListener;
use Rekalogika\Reconstitutor\ReconstitutorProcessor;
use Rekalogika\Reconstitutor\Resolver\AttributeReconstitutorResolver;
use Rekalogika\Reconstitutor\Resolver\ClassReconstitutorResolver;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\AttributeReconstitutor;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\EntityReconstitutor;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\InterfaceReconstitutor;
use Rekalogika\Reconstitutor\Tests\Reconstitutor\SuperclassReconstitutor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->alias(
        'test.' . ClassReconstitutorResolver::class,
        ClassReconstitutorResolver::class,
    )->public();

    $services->alias(
        'test.' . AttributeReconstitutorResolver::class,
        AttributeReconstitutorResolver::class,
    )->public();

    $services->alias(
        'test.' . ReconstitutorProcessor::class,
        ReconstitutorProcessor::class,
    )->public();

    $services->alias(
        'test.' . DoctrineListener::class,
        DoctrineListener::class,
    )->public();

    if (class_exists(EntityReconstitutor::class)) {
        $services->set(EntityReconstitutor::class)
            ->args([
                __DIR__ . '/../var/storage.txt',
            ])
            ->tag('rekalogika.reconstitutor.class');
    }

    if (class_exists(InterfaceReconstitutor::class)) {
        $services->set(InterfaceReconstitutor::class)
            ->args([
                __DIR__ . '/../var/storage.txt',
            ])
            ->tag('rekalogika.reconstitutor.class');
    }

    if (class_exists(SuperclassReconstitutor::class)) {
        $services->set(SuperclassReconstitutor::class)
            ->args([
                __DIR__ . '/../var/storage.txt',
            ])
            ->tag('rekalogika.reconstitutor.class');
    }

    if (class_exists(AttributeReconstitutor::class)) {
        $services->set(AttributeReconstitutor::class)
            ->args([
                __DIR__ . '/../var/storage.txt',
            ])
            ->tag('rekalogika.reconstitutor.attribute');
    }
};
