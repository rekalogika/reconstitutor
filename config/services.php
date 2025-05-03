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
use Rekalogika\Reconstitutor\ReconstitutorContainer;
use Rekalogika\Reconstitutor\ReconstitutorProcessor;
use Rekalogika\Reconstitutor\Resolver\AttributeReconstitutorResolver;
use Rekalogika\Reconstitutor\Resolver\CachingReconstitutorResolver;
use Rekalogika\Reconstitutor\Resolver\ChainReconstitutorResolver;
use Rekalogika\Reconstitutor\Resolver\ClassReconstitutorResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    //
    // reconstitutor resolver
    //

    $services
        ->set('rekalogika.reconstitutor.resolver.class')
        ->class(ClassReconstitutorResolver::class)
        ->tag('rekalogika.reconstitutor.resolver');

    $services
        ->set('rekalogika.reconstitutor.resolver.attribute')
        ->class(AttributeReconstitutorResolver::class)
        ->tag('rekalogika.reconstitutor.resolver');

    $services
        ->set('rekalogika.reconstitutor.resolver')
        ->class(ChainReconstitutorResolver::class)
        ->args([
            tagged_iterator('rekalogika.reconstitutor.resolver'),
        ]);

    //
    // reconstitutor resolver caching
    //

    $services
        ->set('rekalogika.reconstitutor.resolver.cache')
        ->parent('cache.system')
        ->tag('cache.pool');

    $services
        ->set('rekalogika.reconstitutor.resolver.caching')
        ->class(CachingReconstitutorResolver::class)
        ->decorate('rekalogika.reconstitutor.resolver')
        ->args([
            service('rekalogika.reconstitutor.resolver.caching.inner'),
            service('rekalogika.reconstitutor.resolver.cache'),
        ])
    ;

    //
    // reconstitutor container
    //

    $services
        ->set('rekalogika.reconstitutor.container')
        ->class(ReconstitutorContainer::class)
        ->args([
            tagged_locator('rekalogika.reconstitutor'),
        ]);

    //
    // reconstitutor processor
    //

    $services
        ->set('rekalogika.reconstitutor.processor')
        ->class(ReconstitutorProcessor::class)
        ->args([
            service('rekalogika.reconstitutor.resolver'),
            service('rekalogika.reconstitutor.container'),
        ])
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('monolog.logger', ['channel' => 'rekalogika.reconstitutor'])
    ;

    //
    // doctrine event listener
    //

    $services
        ->set('rekalogika.reconstitutor.doctrine_listener')
        ->class(DoctrineListener::class)
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
            service('rekalogika.reconstitutor.processor'),
        ]);
};
