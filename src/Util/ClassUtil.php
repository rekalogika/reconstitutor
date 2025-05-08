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

namespace Rekalogika\Reconstitutor\Util;

use Rekalogika\Reconstitutor\Exception\InvalidArgumentException;

final readonly class ClassUtil
{
    private function __construct() {}

    /**
     * @return class-string
     */
    public static function getClass(object $object): string
    {
        return self::normalizeClassName($object::class);
    }

    /**
     * Normalize a class name that may be a proxy.
     *
     * @param class-string $className
     * @return class-string
     */
    private static function normalizeClassName(string $className): string
    {
        if (false !== $pos = strrpos($className, '\\__CG__\\')) {
            // Doctrine Common proxy marker
            $realClass = substr($className, $pos + 8);
        } elseif (false !== $pos = strrpos($className, '\\__PM__\\')) {
            // Ocramius Proxy Manager
            $className = ltrim($className, '\\');
            $rpos = strrpos($className, '\\');

            if (false === $rpos) {
                return $className;
            }

            $realClass = substr(
                $className,
                8 + $pos,
                $rpos - ($pos + 8),
            );
        } else {
            return $className;
        }

        if (!class_exists($realClass)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Class "%s" is determined to be a proxy for "%s", but the class "%s" does not exist.',
                    $className,
                    $realClass,
                    $realClass,
                ),
            );
        }

        return $realClass;
    }
}
