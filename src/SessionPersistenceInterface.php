<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

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
