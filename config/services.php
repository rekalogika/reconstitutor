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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ClassReconstitutorResolver::class)
        ->tag('rekalogika.reconstitutor.resolver');

    $services->set(AttributeReconstitutorResolver::class)
        ->tag('rekalogika.reconstitutor.resolver');

    $services->set(ReconstitutorProcessor::class)
        ->args([
            tagged_iterator('rekalogika.reconstitutor.resolver'),
        ]);

    $services->set(DoctrineListener::class)
        ->tag('doctrine.event_listener', [
            'event' => 'prePersist',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'postRemove',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'postLoad',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'postFlush',
        ])
        ->args([
            service(ReconstitutorProcessor::class),
        ]);
};
