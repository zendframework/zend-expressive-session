# zend-expressive-session

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-session.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-session)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-expressive-session/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-expressive-session?branch=master)

This library provides session handling middleware for PSR-7 applications, using
an adapter-based approach that will allow usage of ext-session, JWT, or other
approaches.

> ### NOT YET RELEASED
>
> This package is an experiment, and undergoing heavy architectural design
> currently. As such, it is not yet on Packagist. You will need to add a
> repository to your `composer.json` if you wish to use it at this time.
>
> Use at your own risk!

## Installation

Run the following to install this library:

```bash
$ composer require zendframework/zend-expressive-session
```

## Documentation

Documentation is [in the doc tree](docs/book/), and can be compiled using [mkdocs](http://www.mkdocs.org):

```bash
$ mkdocs build
```

~~You may also [browse the documentation online](https://docs.zendframework.com/zend-expressive-session/).~~

### Basic usage

The default use case is to use the shipped `PhpSessionPersistence` adapter with
the shipped `SessionMiddleware`. As such, you can pipe it to your application:

```php
$app->pipe(SessionMiddleware::class);
```

You can also pipe it within routed middleware:

```php
$app->post('/contact/process', [
    \Zend\Expressive\Session\SessionMiddleware::class,
    \App\Contact\ProcessHandler::class
]);
```

Once the middleware is in place, you can access the session container from your
other middleware via the request attribute
`Zend\Expressive\Session\SessionMiddleare::SESSION_ATTRIBUTE`:

```php
use Zend\Expressive\Session\SessionMiddleware;

$session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
$session->get('some-key');
$session->unset('some-key');
$session->set('some-key', $value);
```

### Segments, flash, and CSRF

You may also use session _segments_. These are _namespaced_ containers of
session data, and they provide a few additional features besides basic session
data:

- Ability to create and access flash messages, with configurable hops.
- Ability to generate and validate CSRF tokens.

> ### Deprecated
>
> Most functionality listed below will be removed from this package in an
> upcoming commit; see the [TODO](TODO.md) for details. Segments may still be
> around, but would only contain data access, not flash messages or CSRF
> tooling.
>
> Flash messages and CSRF tooling will be provided by separate packages built on
> top of this one.

To create or access a segment:

```php
$formData = $session->segment('form');
```

To create a flash message available on the next request:

```php
$formData->flash('errors', $errors);
```

To allow access to a flash message over 3 requests:

```php
$formData->flash('errors', $errors, 3);
```

Flash messages are only accessible on the _next_ request; to allow access to
them in the _current_ request, use `flashNow()`, which has the same signature:

```php
$formData->flashNow('errors', $errors);
```

If you decide you want to keep flash messages for an additional hop:

```php
$formData->persistFlash();
```

CSRF tokens are useful for preventing CSRF attacks.  To generate a CSRF token:

```php
$csrf = $formData->generateCsrfToken();
```

To validate a token submitted to you:

```php
if (! $formData->validateCsrfToken($submittedToken)) {
    // ERROR!
}
```

By default, these use the key `__csrf`; you may specify a different key by
passing a key to either method:

```php
$csrf = $formData->generateCsrfToken('CSRF');

// next request:
if (! $formData->validateCsrfToken($submittedToken, 'CSRF')) {
    // ERROR!
}
```

### Custom persistence

To use custom persistence — e.g., a JWT-based approach — implement
`Zend\Expressive\Session\SessionPersistenceInterface`:

```php
namespace Zend\Expressive\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface SessionPersistenceInterface
{
    /**
     * Initialize the session data instance associated with the persistence
     * engine based on the current request.
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

Once implemented, create an alternate factory for the
`Zend\Expressive\Session\SessionMiddleware` service:

```php
namespace App\Session;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Session\SessionMiddleware;

class SessionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : SessionMiddleware
    {
        // Where App\Session\JwtPersistence implements SessionPersistenceInterface
        return new SessionMiddleware($container->get(JwtPersistence::class));
    }
}
```

Once the factory exists, configure your application to use this factory; this is
typically done via an override in your `config/autoload/dependencies.global.php`
file:

```php
return [
    'dependencies' => [
        'factories' => [
            \Zend\Expressive\Session\SessionMiddleware::class => App\Session\SessionMiddlewareFactory::class,
        ],
    ],
];
```
