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

use Psr\Container\ContainerInterface;
use Rekalogika\Reconstitutor\Contract\ReconstitutorInterface;

final class ReconstitutorContainer
{
    /**
     * @param list<ContainerInterface> $containers
     */
    public function __construct(
        private readonly array $containers,
    ) {}

    /**
     * @return ReconstitutorInterface<object>
     */
    public function get(string $id): ReconstitutorInterface
    {
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                /** @psalm-suppress MixedAssignment */
                $result = $container->get($id);

                if ($result instanceof ReconstitutorInterface) {
                    return $result;
                }

                throw new \RuntimeException(\sprintf('Service "%s" is not a valid reconstitutor', $id));
            }
        }

        throw new \RuntimeException(\sprintf('Service "%s" not found', $id));
    }
}
