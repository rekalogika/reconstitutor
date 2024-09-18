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

use Doctrine\Persistence\Proxy;
use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class ReconstitutorProcessor
{
    /**
     * @param iterable<ReconstitutorResolverInterface> $resolvers
     */
    public function __construct(private readonly iterable $resolvers) {}

    /**
     * @return iterable<array-key,ReconstitutorInterface<object>>
     */
    private function getReconstitutors(object $object): iterable
    {
        foreach ($this->resolvers as $resolver) {
            yield from $resolver->getReconstitutors($object);
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
        if ($object instanceof Proxy && !$object->__isInitialized()) {
            return;
        }

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
