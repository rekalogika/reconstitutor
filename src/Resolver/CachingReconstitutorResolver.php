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

namespace Rekalogika\Reconstitutor\Resolver;

use Psr\Cache\CacheItemPoolInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorResolverInterface;

final class CachingReconstitutorResolver implements ReconstitutorResolverInterface
{
    use ReconstitutorResolverTrait;

    public function __construct(
        private readonly ReconstitutorResolverInterface $decorated,
        private readonly CacheItemPoolInterface $cache,
    ) {}

    #[\Override]
    public function getReconstitutors(string $class): array
    {
        $cacheKey = hash('xxh128', $class);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            /** @psalm-suppress MixedAssignment */
            $result = $cacheItem->get();

            if (\is_array($result)) {
                /** @var list<string> */
                return $result;
            }
        }

        $reconstitutors = $this->decorated->getReconstitutors($class);
        $cacheItem->set($reconstitutors);
        $this->cache->save($cacheItem);

        return $reconstitutors;
    }
}
