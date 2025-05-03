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
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @return ReconstitutorInterface<object>
     */
    public function get(string $id): ReconstitutorInterface
    {
        /** @psalm-suppress MixedAssignment */
        $result = $this->container->get($id);

        if ($result instanceof ReconstitutorInterface) {
            return $result;
        }

        throw new \RuntimeException(\sprintf('Service "%s" is not a valid reconstitutor', $id));
    }
}
