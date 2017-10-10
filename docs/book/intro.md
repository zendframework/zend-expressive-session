# zend-expressive-session

Web applications often need to perist user state between requests, and the
generally accepted way to do so is via _sessions_. While PHP provides its own
session extension, it:

- uses global functions that affect global state.
- relies on a superglobal for access to both read and write the session data.
- incurs either filesystem or network I/O on every request, depending on the
  session storage handler.
- can clobber the `Set-Cookie` header when other processes also set it.

Some projects, such as [psr-7-sessions/storageless](https://github.com/psr7-sessions/storageless),
take a different approach, using [JSON Web Tokens](https://tools.ietf.org/html/rfc7519) (JWT).

The goals of zend-expressive-session are:

- to abstract the way users interact with session storage.
- to abstract how sessions are persisted, to allow both standard ext-session,
  but also other paradigms such as JWT.
- to provide session capabilities that "play nice" with
  [PSR-7](http://www.php-fig.org/psr/psr-7/) and middleware.

## Installation

Use [Composer](https://getcomposer.org) to install this package:

```bash
$ composer require zendframework/zend-expressive-session
```

However, the package is not immediately useful unless you have a persistence
adapter. If you are okay with using ext-session, you can install the following
package as well:

```bash
$ composer require zendframework/zend-expressive-session-ext
```

## Features

zend-expressive-session provides the following:

- Interfaces for:
    - session containers
    - session persistence
- An implementation of the session container.
- A "lazy-loading" implementation of the session container, to allow delaying
  any de/serialization and/or I/O processes until session data is requested;
  this implementation decorates a normal session container.
- PSR-7 middleware that:
    - composes a session persistence implementation.
    - initializes the lazy-loading session container, using the session
      persistence implementation.
    - delegates to the next middleware, passing the session container into the
      request.
    - finalizes the session before returning the response.

Persistence implementations locate session information from the requests (e.g.,
via a cookie) in order to initialize the session. On completion of the request,
they examine the session container for changes and/or to see if it is empty, and
provide data to the response so as to notify the client of the session (e.g.,
via a `Set-Cookie` header).

Note that the goals of this package are solely focused on _session persistence_
and _access to session data by middleware_. If you also need other features
often related to session data, you may want to consider the following packages:

- [zend-expressive-flash](https://github.com/zendframework/zend-expressive-flash): 
  provides flash message capabilities.
- [zend-expressive-csrf](https://github.com/zendframework/zend-expressive-csrf): 
  provides CSRF token generation, storage, and verification, using either a
  session container, or flash messages.
