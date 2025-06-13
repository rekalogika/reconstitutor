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

namespace Rekalogika\Reconstitutor\Tests\Tests;

final class DoctrineTest extends EntityTestCase
{
    public function testPersist(): void
    {
        $this->instantiate();

        $this->persist();

        $this->assertImageNotPresent();
        $this->assertEvents([
            'onCreate',
        ]);
    }

    public function testPersistFlush(): void
    {
        $this->instantiate();

        $this->persist();
        $this->assertImageNotPresent();
        $this->flush();

        $this->assertImagePresent();
        $this->assertEvents([
            'onCreate',
            'onSave',
        ]);
    }

    public function testLoadRemoveFlush(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->flush();

        $this->assertImageNotPresent();
        $this->assertEvents([
            'onLoad',
            'onRemove',
        ]);
    }

    /**
     * Note: object is not managed
     */
    public function testFlush(): void
    {
        $this->instantiate();

        $this->flush();

        $this->assertImageNotPresent();
        $this->assertEvents([]);
    }

    /**
     * Note: object is not managed
     */
    public function testRemoveFlush(): void
    {
        $this->instantiate();

        $this->remove();
        $this->flush();

        $this->assertImageNotPresent();
        $this->assertEvents([]);
    }

    public function testReference(): void
    {
        $this->init();

        $this->reference();

        $this->assertIsProxy();
        $this->assertImagePresent();
        $this->assertEvents([]);
    }

    public function testReferenceGetImage(): void
    {
        $this->init();

        $this->reference();
        $this->getImage();

        $this->assertNotProxy();
        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
        ]);
    }

    public function testReferenceFlush(): void
    {
        $this->init();

        $this->reference();
        $this->flush();

        $this->assertIsProxy();
        $this->assertImagePresent();
        $this->assertEvents([]);
    }

    public function testReferenceGetImageFlush(): void
    {
        $this->init();

        $this->reference();
        $this->getImage();
        $this->flush();

        $this->assertNotProxy();
        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
            'onSave',
        ]);
    }

    public function testReferenceRemoveFlush(): void
    {
        $this->init();

        $this->reference();
        $this->remove();
        $this->flush();

        $this->assertImageNotPresent();
        $this->assertEvents([
            'onLoad',
            'onRemove',
        ]);
    }

    public function testLoadClear(): void
    {
        $this->init();

        $this->load();
        $this->clear();

        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
            'onClear',
        ]);
    }

    public function testReferenceClear(): void
    {
        $this->init();

        $this->reference();
        $this->clear();

        $this->assertImagePresent();
        $this->assertEvents([]);
    }

    public function testReferenceGetImageClear(): void
    {
        $this->init();

        $this->reference();
        $this->getImage();
        $this->clear();

        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
            'onClear',
        ]);
    }

    /**
     * Caveat: doctrine does not emit an event on `detach()`
     */
    public function testLoadDetach(): void
    {
        $this->init();

        $this->load();
        $this->detach();

        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
        ]);
    }

    /**
     * Caveat: doctrine does not emit an event on `detach()`, we can only call
     * onClear after `flush()`
     */
    public function testLoadDetachFlush(): void
    {
        $this->init();

        $this->load();
        $this->detach();
        $this->flush();

        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
            'onClear',
        ]);
    }

    public function testReferenceDetachFlush(): void
    {
        $this->init();

        $this->reference();
        $this->detach();

        $this->assertIsProxy();
        $this->assertImagePresent();
        $this->assertEvents([]);
    }

    /**
     * Caveat: doctrine does not call prePersist in this case
     */
    public function testLoadRemovePersistFlush(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->persist();
        $this->flush();

        $this->assertImagePresent();
        $this->assertEvents([
            'onLoad',
            'onSave',
        ]);
    }
}
