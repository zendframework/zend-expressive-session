# TODO

## Persistence implementations

- [ ] Externalize `PhpSessionPersistence` to a separate package that requires
  `ext-session`.

## Segments

Evaluate the `SegmentInterface` and `Segment` class and their responsibilities
to see if they should be included in the base session package.

Marco argues that segments could be accomplished as _additional session
containers_, each with their own cookie.

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

### CSRF protection

Marco has a middleware-based implementation in Ocramius/PSR7Csrf that
essentially pulls from a specific `Session` variable, and acts as a guard for
non-GET|HEAD|OPTIONS method calls, returning a specific response if validation
of the CSRF token fails. Essentially, the argument is that this can be built _on
top of_ a session package.

Ocramius/PSR7Csrf could potentially be adapted later to work with
zend-expressive-session, meaning we wouldn't need to add any such capabilities
to our own package, or even write our own package, for handling CSRF.
