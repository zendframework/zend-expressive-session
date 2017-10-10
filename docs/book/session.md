# Session Containers

Session containers are the primary interface with which most application
developers will work; they contain the data currently in the session, and allow
you to push data to the session.

All session containers implement `Zend\Expressive\Session\SessionInterface`:

```php
namespace Zend\Expressive\Session;

interface SessionInterface
{
    /**
     * Serialize the session data to an array for storage purposes.
     */
    public function toArray() : array;

    /**
     * Retrieve a value from the session.
     *
     * @param mixed $default Default value to return if $name does not exist.
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * Whether or not the container has the given key.
     */
    public function has(string $name) : bool;

    /**
     * Set a value within the session.
     *
     * Values MUST be serializable in any format; we recommend ensuring the
     * values are JSON serializable for greatest portability.
     *
     * @param mixed $value
     */
    public function set(string $name, $value) : void;

    /**
     * Remove a value from the session.
     */
    public function unset(string $name) : void;

    /**
     * Clear all values.
     */
    public function clear() : void;

    /**
     * Does the session contain changes? If not, the middleware handling
     * session persistence may not need to do more work.
     */
    public function hasChanged() : bool;

    /**
     * Regenerate the session.
     *
     * This can be done to prevent session fixation. When executed, it SHOULD
     * return a new instance; that instance should always return true for
     * isRegenerated().
     *
     * An example of where this WOULD NOT return a new instance is within the
     * shipped LazySession, where instead it would return itself, after
     * internally re-setting the proxied session.
     */
    public function regenerate(): SessionInterface;

    /**
     * Method to determine if the session was regenerated; should return
     * true if the instance was produced via regenerate().
     */
    public function isRegenerated() : bool;
}
```

The default implementation, and the one you'll most likely interact with, is
`Zend\Expressive\Session\Session`.

## Usage

Session containers will typically be passed to your middleware using the
[SessionMiddleware](middleware.md), via the
`Zend\Expressive\Session\SessionMiddleware::SESSION_ATTRIBUTE` ("session")
request attribute.

Once you have the container, you can check for data:

```php
if ($session->has('user')) {
}
```

and retrieve it:

```php
$user = $session->get('user');
```

You can combine those operations, by passing a default value as a second
argument to the `get()` method:

```php
$user = $session->get('user', new GuestUser());
```

If a datum is no longer relevant in the session, `unset()` it:

```php
$session->unset('user');
```

If none of the data is relevant, `clear()` the session:

```php
$session->clear();
```

## Lazy Sessions

This package provides another implementation of `SessionInterface` via
`Zend\Expressive\Session\LazySession`. This implementation does the following:

- It composes a [persistence](persistence.md) instance, along with the current
  request.
- On _first access_ (e.g., `get()`, `set()`, etc.), it uses the composed
  persistence and request instances to generate the _actual_ session container.
  All methods then _proxy_ to this container.

This approach helps delay any I/O or network operations, and/or
deserialization, until they are actually needed.

The shipped [SessionMiddleware](middleware.md) produces a `LazySession`.

## Session Regeneration

Some application events benefit from _session regeneration_. In particular,
after a user has successfully logged in or out, you will generally want to
regenerate the session in order to prevent session fixation and the attack
vectors it invites.

In those situations, call `regenerate()`:

```php
$newSession = $session->regenerate();
```

The interface indicates that a new instance _should_ be returned. However, in
the default usage, you will have a `LazySession` instance (as described above),
which _decorates_ the underlying session storage. This is done for two reasons:

- First, the stated reasons of preventing the need to deserialize data and/or
  perform I/O access until the last moment.
- Second, to ensure that the `SessionMiddleware` _always has a pointer to the
  session_.

This latter is what allows you to regenerate the session in middleware nested
deep in your application, but still have the data persisted correctly.
