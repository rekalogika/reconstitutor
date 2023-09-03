<?php

/*
 * This file is part of rekalogika/reconstitutor package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Reconstitutor\Tests\Reconstitutor;

trait CommonTrait
{
    final public function __construct(private string $fileStorage)
    {
    }
    
    public function onSave(object $object): void
    {
        $value = $this->get($object, 'attribute');
        assert(is_string($value));

        file_put_contents($this->fileStorage, $value);
    }

    public function onLoad(object $object): void
    {
        if (!file_exists($this->fileStorage)) {
            $value = null;
        } else {
            $value = file_get_contents($this->fileStorage);
            assert(is_string($value));
        }

        $this->set($object, 'attribute', $value);
    }

    public function onRemove(object $object): void
    {
        if (file_exists($this->fileStorage)) {
            unlink($this->fileStorage);
        }
    }
}
