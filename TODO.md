# TODO

## Persistence implementations

- [ ] Externalize `PhpSessionPersistence` to a separate package that requires
  `ext-session`.

## Segments

Evaluate the `SegmentInterface` and `Segment` class and their responsibilities
to see if they should be included in the base session package.

Marco argues that segments could be accomplished as _additional session
containers_, each with their own cookie. This obviously will not work with
ext-session, however.

The main reason for session segments/namespaces is to allow grouping contextual
data, and preventing conflicts. This can still be achieved by having nested
arrays; the access is just not as pretty:

```php
// With segments:
$username = $session->segment('authentication')->get('username');
// versus without:
$username = $session->get('authentication')['username']);
```

Overall, _separate sessions_ provides a cleaner approach to this; it's just not
something that can be done with ext-session. The interfaces as modeled, however,
would allow for a non-ext-session approach to sessions (e.g., using caching
storage such as redis, memcached, or couchdb) that could accommodate the
approach.

- [x] Remove segment implementation

### Flash messages

Marco suggests that flash messages are something to build _on top of_ a
session package. A "Flash" class (or similar) would receive a session
container to its constructor, and manipulate it in order to extract,
persist, or expire messages. That instance could then be persisted as a
session attribute.

The main issue I see with this approach is that segregating flash messages by
context is harder. This can be accomplished by having different instances
operating on different session variables, however. Doing so leads to a need for
different request attributes, though, which might become confusing in large
applications — though likely no more confusing than learning which session
"segments" are used in which contexts of the application.

- [x] Create a separate package for flash messages
- [x] Introduce an interface just for flash message access and manipulation.
  - [x] Rename `persistFlash()` to something more appropriate.
- [x] Create middleware for creating the `Flash` instance and propagating it
  into the request delegated by the middleware.
- [x] Externalize the flash message support to its own package

### CSRF protection

Marco has a middleware-based implementation in Ocramius/PSR7Csrf that
essentially pulls from a specific `Session` variable, and acts as a guard for
non-GET|HEAD|OPTIONS method calls, returning a specific response if validation
of the CSRF token fails. Essentially, the argument is that this can be built _on
top of_ a session package.

Ocramius/PSR7Csrf could potentially be adapted later to work with
zend-expressive-session, meaning we wouldn't need to add any such capabilities
to our own package, or even write our own package, for handling CSRF.

- [x] Extract an interface for generating, validating CSRF values
- [x] Create one or more implementations of the interface
- [x] Create middleware for generating and injecting the CSRF guard into the request
- [x] Externalize the CSRF support to its own package
