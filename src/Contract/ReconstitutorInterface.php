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

namespace Rekalogika\Reconstitutor\Contract;

/**
 * @template T of object
 * @internal
 */
interface ReconstitutorInterface
{
    /**
     * Executed when the object is added to the persistence layer. e.g
     * Doctrine's EntityManager::persist(). Implementors should generally avoid
     * using this method, and instead initialize the properties using a factory,
     * otherwise the object might not be in a consistent state before it is
     * persisted.
     *
     * @param T $object
     */
    public function onCreate(object $object): void;

    /**
     * Executed after the object is loaded from the persistence layer
     * e.g Doctrine's EntityManager::find()
     *
     * @param T $object
     */
    public function onLoad(object $object): void;

    /**
     * Executed after the object is persisted to the persistence layer,
     * e.g Doctrine's EntityManager::flush()
     *
     * @param T $object
     */
    public function onSave(object $object): void;

    /**
     * Executed after the object is being removed from the persistence layer,
     * e.g Doctrine's EntityManager::remove() & flush()
     *
     * @param T $object
     */
    public function onRemove(object $object): void;
}
