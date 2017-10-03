<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var null|SessionPersistenceInterface
     */
    private $persistence;

    /**
     * @var null|string Name of SessionPersistenceInterface implementation
     *     to use, if an instance was not provided.
     */
    private $persistenceClass;

    /**
     * @var string|SessionPersistenceInterface $persistenceClassOrInstance
     * @throws Exception\InvalidSessionPersistenceException if $persistenceClassOrInstance is of an invalid type
     * @throws Exception\InvalidSessionPersistenceException if $persistenceClassOrInstance is an object or class
     *     that does not implement SessionPersistenceInterface
     */
    public function __construct($persistenceClassOrInstance = PhpSessionPersistence::class)
    {
        if ($persistenceClassOrInstance instanceof SessionPersistenceInterface) {
            $this->persistence = $persistenceClassOrInstance;
            return;
        }

        if (! is_string($persistenceClassOrInstance) && ! is_object($persistenceClassOrInstance)) {
            throw Exception\InvalidSessionPersistenceException::forType($persistenceClassOrInstance);
        }

        if (! class_exists($persistenceClassOrInstance)
            || ! in_array(SessionPersistenceInterface::class, class_implements($persistenceClassOrInstance, true))
        ) {
            throw Exception\InvalidSessionPersistenceException::forClass($persistenceClassOrInstance);
        }

        $this->persistenceClass = $persistenceClassOrInstance;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $persistence = $this->createPersistence($request);
        $session = new LazySession($persistence);

        $response = $delegate->process($request->withAttribute(SessionInterface::class, $session));

        return $persistence->persistSession($session, $response);
    }

    private function createPersistence(ServerRequestInterface $request) : SessionPersistenceInterface
    {
        $factory = $this->persistence
            ? [$this->persistence, 'createNewInstanceFromRequest']
            : [$this->persistenceClass, 'createFromRequest'];

        return $factory($request);
    }
}
