<?php

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor;

use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;

final class ReconstitutorProcessor
{
    /**
     * @param iterable<ReconstitutorResolverInterface> $resolvers
     */
    public function __construct(private iterable $resolvers)
    {
    }

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
