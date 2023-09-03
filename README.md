This library provides a small layer that sits above Doctrine events to help you
reconstitute/hydrate your entities. It lets you augment Doctrine's hydration
with your logic in a concise and expressive class.

After Doctrine hydrates an object from the database, this framework gives you
the control to hydrate additional properties not handled by Doctrine, without
having to deal with the peculiarities of Doctrine events and Unit of Work. Then,
after Doctrine persists the changes to the database, it lets you do similarly
with the properties.

The most common case of this type of tasks is for handling file uploads, of
which many specialized libraries have already been written. But plenty of other
cases exist:

* A lazy-loading proxy that fetches the real resource using an API call.
* Linking objects that are managed by different object managers, or non-Doctrine
  entities.

These days we usually call the process *hydration*. *Reconstitution* is the term
used by Eric Evans in *Domain-Driven Design: Tackling Complexity in the Heart of
Software*.

Features
--------

* Simple declaration in a class. You can create a reconstitutor class to handle
  the reconstitution of a specific entity class, entities that implement a
  specific interface, entities in a class hierarchy, or those with a specific
  PHP attribute.
* Our abstract classes provide `get()` and `set()` methods as a convenience.
  They let you work with the properties directly, bypassing getters and setters.
  It is the best practice in reconstitutions as it frees you to have business
  logic in the getters and setters.
* The `get()` and `set()` methods are forwarders to a custom implementation of
  Symfony's `PropertyAccessorInterface`. Therefore, you can use the same
  exceptions defined in `PropertyAccessorInterface`.
* It has what we think is the correct behavior. It asks your reconstitutor to
  save only after Doctrine has successfully saved the object. It doesn't rely on
  Doctrine seeing the object being dirty before asking your reconstitutor to
  save. i.e. no need to update something like `$lastUpdated` just to make sure
  the correct event will be fired.

Installation
------------

```bash
composer require rekalogika/reconstitutor
```

Usage
-----

Because everyone knows about file uploads, we are going to use that as an
example, even if you probably won't use this framework as a means for handling
file uploads.

Suppose you have an `Order` object that stores a payment receipt in the
`paymentReceipt` property:

```php
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\UuidV7;

class Order
{
    private string $id;
    private ?File $paymentReceipt = null;

    public function __construct()
    {
        $this->id = new UuidV7;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPaymentReceipt(): ?File
    {
        return $this->paymentReceipt;
    }

    public function setPaymentReceipt(File $paymentReceipt): void
    {
        $this->paymentReceipt = $paymentReceipt;
    }
}
```

During the fetching of the object from the database, Doctrine will instantiate
the object and hydrate `$id` and other properties that it manages. Then, it will
be our reconstitutor's turn to handle the `$paymentReceipt` property.

```php
use Rekalogika\Reconstitutor\AbstractClassReconstitutor;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @extends AbstractClassReconstitutor<Order>
 */
final class OrderReconstitutor extends AbstractClassReconstitutor
{
    /**
     * The class that this reconstitutor manages. It can also be a super class
     * or an interface.
     */
    public static function getClass(): string
    {
        return Order::class;
    }

    /**
     * When the object is being saved, we check if the paymentReceipt has been
     * just uploaded. If it is, we save it to a file.
     */
    public function onSave(object $order): void
    {
        $path = sprintf('/tmp/payment_receipt/%s', $order->getId());

        $file = $this->get($order, 'paymentReceipt');

        if ($file instanceof UploadedFile) {
            file_put_contents($path, $file->getContent());
        }
    }

    /**
     * When the object is being loaded from the database, we check if the
     * supposed payment receipt is already saved. If it is, then we load the
     * file to the property.
     */
    public function onLoad(object $order): void
    {
        $path = sprintf('/tmp/payment_receipt/%s', $order->getId());

        if (file_exists($path)) {
            $file = new File($path);
        } else {
            $file = null;
        }

        $this->set($order, 'paymentReceipt', $file);
    }

    /**
     * If the order is being removed, we remove the associated payment receipt
     * here.
     */
    public function onRemove(object $order): void
    {
        $path = sprintf('/tmp/payment_receipt/%s', $order->getId());

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
```