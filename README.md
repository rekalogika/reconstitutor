# rekalogika/reconstitutor

This library provides a thin layer that sits above Doctrine events to help you
reconstitute/hydrate your entities. It lets you augment Doctrine's hydration
with your logic in a concise and expressive class.

Full documentation: [rekalogika.dev/reconstitutor](https://rekalogika.dev/reconstitutor)

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

These days, we usually call the process *hydration*. *Reconstitution* is the
term used by Eric Evans in *"Domain-Driven Design: Tackling Complexity in the
Heart of Software"*.

## Features

* Streamlines a very specific, yet very common use case of Doctrine events.
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
  Doctrine seeing the object being dirty before `flush()`-ing. i.e. your
  entities don't have to modify a Doctrine-managed property —like
  `$lastUpdated`— just to make sure the correct Doctrine event will be fired.

## Example

A very simple file upload handling:

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
            $this->set($order, 'paymentReceipt', new File($path));
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

## Documentation

[rekalogika.dev/reconstitutor](https://rekalogika.dev/reconstitutor)

## License

MIT

## Contributing

Issues and pull requests should be filed in the GitHub repository
[rekalogika/reconstitutor](https://github.com/rekalogika/reconstitutor).