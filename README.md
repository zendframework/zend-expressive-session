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

### Flash Messages

This package provides facilities for creating, accessing, and manipulating flash
messages.

> ### Deprecated
>
> The `Zend\Expressive\Session\Flash` namespace will be extracted to a
> separate package that depends on this one in the future.

These facilities are provided via two mechanisms:

- `FlashMessagesInterface` and its implementation `FlashMessages`; these accept
  a `Zend\Expressive\Session\SessionInterface` instance, and a key that
  represents the location of flash message data within the session.
- `FlashMessageMiddleware`, which accepts the name of a `FlashMessagesInterface`
  implementation, a session key to use for flash messages, and a request
  attribute under which to store the `FlashMessagesInterface` instance.

Generally speaking, just pipe the middleware to your application:

```php
$app->pipe(\Zend\Expressive\Session\SessionMiddleware::class);
$app->pipe(\Zend\Expressive\Session\Flash\FlashMessageMiddleware::class);
```

Or, with routed middleware:

```php
$app->post('/contact/process', [
    \Zend\Expressive\Session\SessionMiddleware::class,
    \Zend\Expressive\Session\Flash\FlashMessageMiddleware::class,
    ProcessContactHandler::class,
]);
```

Within your middleware, access flash messages via the configured attribute; by
default this is
`Zend\Expressive\Session\Flash\FlashMessageMiddleware::FLASH_ATTRIBUTE`, or more
simply "flash":

```php
$flashMessages = $request->getAttribute('flash');
```

To create a flash message available on the next request:

```php
$flashMessages->flash('errors', $errors);
```

To allow access to a flash message over 3 requests:

```php
$flashMessages->flash('errors', $errors, 3);
```

Flash messages are only accessible on the _next_ request; to allow access to
them in the _current_ request, use `flashNow()`, which has the same signature:

```php
$flashMessages->flashNow('errors', $errors);
```

If you decide you want to keep flash messages for an additional hop:

```php
$flashMessages->prolongFlash();
```

### CSRF

CSRF tokens are useful for preventing CSRF attacks. This package provides an
interface for generating and validating tokens, an interface for producing these
instances based on the current request, and middleware that will invoke the
factory to create and inject a CSRF guard into your request.

> ### Deprecated
>
> This functionality will soon be extracted to its own package and removed from
> this one.

Typically, you can do the following:

```php
$app->pipe(\Zend\Expressive\Session\SessionMiddleware::class);
$app->pipe(\Zend\Expressive\Session\Flash\FlashMessageMiddleware::class);
$app->pipe(\Zend\Expressive\Session\Csrf\CsrfMiddleware::class);
```

Alternately, do this with routed middlewar:

```php
$app->post('/contact/process', [
    \Zend\Expressive\Session\SessionMiddleware::class,
    \Zend\Expressive\Session\Flash\FlashMessageMiddleware::class,
    \Zend\Expressive\Session\Csrf\CsrfMiddleware::class,
    ProcessContactHandler::class,
]);
```

Once you have done this, you can pull the CSRF guard from the request:

```php
$guard = $request->getAttribute(CsrfMiddleware::CSRF_ATTRIBUTE);
```

To generate a token:

```php
$csrf = $guard->generateToken();
```

To validate a token submitted to you:

```php
if (! $guard->validateToken($submittedToken)) {
    // ERROR!
}
```

By default, these use the key `__csrf`; you may specify a different key by
passing a key to either method:

```php
$csrf = $guard->generateToken('CSRF');

// next request:
if (! $guard->validateToken($submittedToken, 'CSRF')) {
    // ERROR!
}
```

We also ship a pure-session-based variant; we recommend the flash message based
one, however, to ensure the token expires in a timely fashion.

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
