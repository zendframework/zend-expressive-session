# Session Persistence

Session persistence within zend-expressive-session refers to one or both of the
following:

- Identifying session information provided by the client making the request.
- Storing session data for access on subsequent requests.
- Providing session information to the client making the request.

In some scenarios, such as usage of JSON Web Tokens (JWT), the serialized
session data is provided _by_ the client, and provided _to_ the client directly,
without any server-side storage whatsoever.

To describe these operations, we provide `Zend\Expressive\Session\SessionPersistenceInterface`:

```php
namespace Zend\Expressive\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface SessionPersistenceInterface
{
    /**
     * Generate a session data instance based on the request.
     */
    public function initializeSessionFromRequest(ServerRequestInterface $request) : SessionInterface;

    /**
     * Persist the session data instance.
     *
     * Persists the session data, returning a response instance with any
     * artifacts required to return to the client.
     */
    public function persistSession(SessionInterface $session, ResponseInterface $response) : ResponseInterface;
}
```

Session initialization pulls data from the request (a cookie, a header value,
etc.) in order to produce a session container. Session persistence pulls data
from the session container, does something with it, and then optionally provides
a response containing session artifacts (a cookie, a header value, etc.).

For sessions to work, _you must provide a persistence implementation_. We
provide one such implementation using PHP's session extension via the package
[zend-expressive-session-ext](https://github.com/zendframework/zend-expressive-session-ext).

## Session identifiers

Typically, the session identifier will be retrieved from the request (usually
via a cookie), and a new identifier created if none was discovered.

During persistence, if an existing session's contents have changed, or
`regenerateId()` was called on the session, the persistence implementation
becomes responsible for:

- Removing the original session.
- Generating a new identifier for the session.

In all situations, it then needs to store the session data in such a way that a
later lookup by the current identifier will retrieve that data.

Prior to version 1.1.0, persistence engines had two ways to determine what the
original session identifier was when it came time to regenerate or persist a
session:

- Store the identifier as a property of the persistence implementation.
- Store the identifier in the session data under a "magic" key (e.g.,
  `__SESSION_ID__`).

The first approach is problematic when using zend-expressive-session in an async
environment such as [Swoole](https://swoole.co.uk) or
[ReactPHP](https://reactphp.org), as the same persistence instance may be used
by several simultaneous requests. As such, version 1.1.0 introduces a new
interface for `Zend\Expressive\Session\SessionInterface` implementations to use:
`Zend\Expressive\Session\SessionIdentifierAwareInterface`. This interface
defines a single method, `getId()`; implementations can thus store the
identifier internally, and, when it comes time to store the session data,
persistence implementations can query that method in order to retrieve the
session identifier.

Considering that persistence implementations also _create_ the session instance,
we recommend that implementations only create instances that implement the
`SessionIdentifierAwareInterface` going forward in order to make themselves
async compatible.

## Persistent sessions

- Since 1.2.0.

If your session persistence supports persistent sessions &mdash; for example, by
setting an `Expires` or `Max-Age` cookie directive &mdash; then you can opt to
globally set a default session duration, or allow developers to hint a desired
session duration via the session container using
`SessionContainerPersistenceInterface::persistSessionFor()`.

Implementations SHOULD honor the value of `SessionContainerPersistenceInterface::getSessionLifetime()`
when persisting the session data. This could mean:

- Ensuring that the session data will not be purged until after the specified
  TTL value.
- Setting an `Expires` or `Max-Age` cookie directive.

In each case, the persistence engine should query the `Session` instance for a
TTL value:

```php
$ttl = $session instanceof SessionContainerPersistenceInterface
    ? $session->getSessionLifetime()
    : $defaultLifetime; // likely 0, to indicate automatic expiry
```
