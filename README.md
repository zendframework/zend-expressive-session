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
     * Named constructor for building an instance.
     */
    public static function createFromRequest(ServerRequestInterface $request) : SessionPersistenceInterface;

    /**
     * Allows building a new instance based on the request provided.
     *
     * Use this method if the session needs some sort of backing -- redis,
     * memcached, etc. -- that requires injection in the constructor, but
     * the session identifier may vary based on request data (e.g., a cookie
     * value).
     *
     * This method DOES NOT require that a new instance is returned, though
     * it is suggested.
     */
    public function createNewInstanceFromRequest(ServerRequestInterface $request) : SessionPersistenceInterface;

    /**
     * Generate the session data instance associated with the persistence
     * engine.
     */
    public function createSession() : SessionInterface;

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
