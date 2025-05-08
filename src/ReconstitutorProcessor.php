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

namespace Rekalogika\Reconstitutor;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class ReconstitutorProcessor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ReconstitutorResolverInterface $resolver,
        private readonly ReconstitutorContainer $container,
    ) {}

    /**
     * @return iterable<array{string,ReconstitutorInterface<object>}>
     */
    private function getReconstitutors(object $object): iterable
    {
        $serviceIds = $this->resolver->getReconstitutors($object::class);

        foreach ($serviceIds as $serviceId) {
            yield [$serviceId, $this->container->get($serviceId)];
        }
    }

    public function hasReconstitutor(object $object): bool
    {
        return $this->resolver->hasReconstitutor($object::class);
    }

    private function log(
        object $object,
        string $serviceId,
        string $method,
    ): void {
        $this->logger?->debug(
            'Calling {method} of reconstitutor {serviceId} on {object}',
            [
                'method' => $method,
                'serviceId' => $serviceId,
                'object' => $object::class,
            ],
        );
    }

    public function onCreate(object $object): void
    {
        foreach ($this->getReconstitutors($object) as [$id, $reconstitutor]) {
            $this->log($object, $id, 'onCreate');
            $reconstitutor->onCreate($object);
        }
    }

    public function onLoad(object $object): void
    {
        foreach ($this->getReconstitutors($object) as [$id, $reconstitutor]) {
            $this->log($object, $id, 'onLoad');
            $reconstitutor->onLoad($object);
        }
    }

    public function onSave(object $object): void
    {
        foreach ($this->getReconstitutors($object) as [$id, $reconstitutor]) {
            $this->log($object, $id, 'onSave');
            $reconstitutor->onSave($object);
        }
    }

    public function onRemove(object $object): void
    {
        foreach ($this->getReconstitutors($object) as [$id, $reconstitutor]) {
            $this->log($object, $id, 'onRemove');
            $reconstitutor->onRemove($object);
        }
    }

    public function onClear(object $object): void
    {
        foreach ($this->getReconstitutors($object) as [$id, $reconstitutor]) {
            $this->log($object, $id, 'onClear');
            $reconstitutor->onClear($object);
        }
    }
}
