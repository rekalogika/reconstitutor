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

final class TransactionTest extends EntityTestCase
{
    public function testPersistBeginFlushRollback(): void
    {
        $this->instantiate();

        $this->persist();
        $this->begin();
        $this->flush();
        $this->rollback();

        $this->assertImageNotPresent();

        $this->assertEvents([
            'onCreate',
        ]);
    }

    public function testBeginPersistFlushRollback(): void
    {
        $this->instantiate();

        $this->begin();
        $this->persist();
        $this->flush();
        $this->rollback();

        $this->assertImageNotPresent();

        $this->assertEvents([
            'onCreate',
        ]);
    }

    public function testPersistBeginFlushCommit(): void
    {
        $this->instantiate();

        $this->persist();
        $this->begin();
        $this->flush();
        $this->commit();

        $this->assertImagePresent();

        $this->assertEvents([
            'onCreate',
            'onSave',
        ]);
    }

    public function testBeginPersistFlushCommit(): void
    {
        $this->instantiate();

        $this->begin();
        $this->persist();
        $this->flush();
        $this->commit();

        $this->assertImagePresent();

        $this->assertEvents([
            'onCreate',
            'onSave',
        ]);
    }

    public function testLoadRemoveBeginFlushCommit(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->flush();
        $this->commit();

        $this->assertImageNotPresent();

        $this->assertEvents([
            'onLoad',
            'onRemove',
        ]);
    }

    public function testLoadRemoveBeginFlushRollback(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->flush();
        $this->rollback();

        $this->assertImagePresent();

        $this->assertEvents([
            'onLoad',
        ]);
    }

    public function testLoadRemoveBeginFlushClearCommit(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->flush();
        $this->clear();
        $this->commit();

        $this->assertImageNotPresent();

        $this->assertEvents([
            'onLoad',
            'onRemove',
        ]);
    }

    public function testLoadBeginFlushClearCommit(): void
    {
        $this->init();

        $this->load();
        $this->begin();
        $this->flush();
        $this->clear();
        $this->commit();

        $this->assertImagePresent();

        $this->assertEvents([
            'onLoad',
            'onSave',
            'onClear',
        ]);
    }

    public function testLoadRemoveBeginFlushClearRollback(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->flush();
        $this->clear();
        $this->rollback();

        $this->assertImagePresent();

        $this->assertEvents([
            'onLoad',
        ]);
    }

    public function testLoadRemoveBeginBeginFlushCommitCommit(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->begin();
        $this->flush();
        $this->commit();
        $this->commit();

        $this->assertImageNotPresent();

        $this->assertEvents([
            'onLoad',
            'onRemove',
        ]);
    }

    public function testLoadRemoveBeginBeginFlushCommitRollback(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->begin();
        $this->flush();
        $this->commit();
        $this->rollback();

        $this->assertImagePresent();

        $this->assertEvents([
            'onLoad',
        ]);
    }

    public function testLoadRemoveBeginBeginFlushRollbackCommit(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->begin();
        $this->begin();
        $this->flush();
        $this->rollback();
        $this->commit();

        $this->assertImagePresent();

        $this->assertEvents([
            'onLoad',
        ]);
    }

    /**
     * Caveat: doctrine does not call prePersist in this case
     */
    public function testLoadRemovePersistBeginFlushCommit(): void
    {
        $this->init();

        $this->load();
        $this->remove();
        $this->persist();
        $this->begin();
        $this->flush();
        $this->commit();

        $this->assertImagePresent();

        $this->assertEvents([
            'onLoad',
            'onSave',
        ]);
    }
}
