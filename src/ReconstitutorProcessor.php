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

use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class ReconstitutorProcessor
{
    public function __construct(
        private readonly ReconstitutorResolverInterface $resolver,
        private readonly ReconstitutorContainer $container,
    ) {}

    /**
     * @return iterable<array-key,ReconstitutorInterface<object>>
     */
    private function getReconstitutors(object $object): iterable
    {
        $serviceIds = $this->resolver->getReconstitutors($object::class);

        foreach ($serviceIds as $serviceId) {
            yield $this->container->get($serviceId);
        }
    }

    public function onCreate(object $object): void
    {
        foreach ($this->getReconstitutors($object) as $reconstitutor) {
            $reconstitutor->onCreate($object);
        }
    }

    public function onLoad(object $object): void
    {
        foreach ($this->getReconstitutors($object) as $reconstitutor) {
            $reconstitutor->onLoad($object);
        }
    }

    public function onSave(object $object): void
    {
        foreach ($this->getReconstitutors($object) as $reconstitutor) {
            $reconstitutor->onSave($object);
        }
    }

    public function onRemove(object $object): void
    {
        foreach ($this->getReconstitutors($object) as $reconstitutor) {
            $reconstitutor->onRemove($object);
        }
    }
}
