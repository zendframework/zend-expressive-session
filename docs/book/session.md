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

Since version 1.1.0, we provide `Zend\Expressive\Session\SessionIdentifierAwareInterface`:

```php
namespace Zend\Expressive\Session;

interface SessionIdentifierAwareInterface
{
    /**
     * Retrieve the session identifier.
     *
     * This feature was added in 1.1.0 to allow the session persistence to be
     * stateless. Previously, persistence implementations had to store the
     * session identifier between calls to initializeSessionFromRequest() and
     * persistSession(). When SessionInterface implementations also implement
     * this method, the persistence implementation no longer needs to store it.
     *
     * This method will become a part of the SessionInterface in 2.0.0.
     *
     * @since 1.1.0
     */
    public function getId() : string;
}
```

Since version 1.2.0, we provide `Zend\Expressive\Session\SessionCookiePersistenceInterface`:

```php
namespace Zend\Expressive\Session;

/**
 * Allow marking session cookies as persistent.
 *
 * It can be useful to mark a session as persistent: e.g., for a "Remember Me"
 * feature when logging a user into your system. PHP provides this capability
 * via ext-session with the $lifetime argument to session_set_cookie_params()
 * as well as by the session.cookie_lifetime INI setting. The latter will set
 * the value for all session cookies sent (or until the value is changed via
 * an ini_set() call), while the former will only affect cookies created during
 * the current script lifetime.
 *
 * Persistence engines may, of course, allow setting a global lifetime. This
 * interface allows developers to set the lifetime programmatically. Persistence
 * implementations are encouraged to use the value to set the cookie lifetime
 * when creating and returning a cookie. Additionally, to ensure the cookie
 * lifetime originally requested is honored when a session is regenerated, we
 * recommend persistence engines to store the TTL in the session data itself,
 * so that it can be re-sent in such scenarios.
 */
interface SessionCookiePersistenceInterface
{
    const SESSION_LIFETIME_KEY = '__SESSION_TTL__';

    /**
     * Define how long the session cookie should live.
     *
     * Use this value to detail to the session persistence engine how long the
     * session cookie should live.
     *
     * This value could be passed as the $lifetime value of
     * session_set_cookie_params(), or used to create an Expires or Max-Age
     * parameter for a session cookie.
     *
     * Since cookie lifetime is communicated by the server to the client, and
     * not vice versa, the value should likely be persisted in the session
     * itself, to ensure that session regeneration uses the same value. We
     * recommend using the SESSION_LIFETIME_KEY value to communicate this.
     *
     * @param int $duration Number of seconds the cookie should persist for.
     */
    public function persistSessionFor(int $duration) : void;

    /**
     * Determine how long the session cookie should live.
     *
     * Generally, this will return the value provided to persistFor().
     *
     * If that method has not been called, the value can return one of the
     * following:
     *
     * - 0 or a negative value, to indicate the cookie should be treated as a
     *   session cookie, and expire when the window is closed. This should be
     *   the default behavior.
     * - If persistFor() was provided during session creation or anytime later,
     *   the persistence engine should pull the TTL value from the session itself
     *   and return it here. Typically, this value should be communicated via
     *   the SESSION_LIFETIME_KEY value of the session.
     */
    public function getSessionLifetime() : int;
}
```

`Zend\Expressive\Session\Session` and `Zend\Expressive\Session\LazySession` both
implement each of the interfaces listed above. `Session` accepts an optional
identifier to its constructor, and will use the value of the
`SessionCookiePersistenceInterface::SESSION_LIFETIME_KEY` in the provided data
to seed the session cookie lifetime, if present.

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

### Persistent Sessions

- Since 1.2.0

You can hint to the session persistence engine that the session cookie should
persist:

```php
$session->persistSessionFor(60 * 60 * 24 * 7); // persist for 7 days
```

To make the session cookie expire when the browser session is terminated
(default behavior), use zero or a negative integer:

```php
$session->persistSessionFor(0); // expire cookie after session is over
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
