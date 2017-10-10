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
