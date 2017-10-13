<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\Exception\InvalidRequestException;

/**
 * Proxy to an underlying SessionInterface implementation.
 *
 * In order to delay parsing of session data until it is accessed, use this
 * class. It will call the composed SessionPersistenceInterface's createSession()
 * method only on access to any of the various session data methods; otherwise,
 * the session will not be accessed, and, in most cases, started.
 */
final class LazySession implements SessionInterface
{
    /**
     * @var SessionPersistenceInterface
     */
    private $persistence;

    /**
     * @var null|SessionInterface
     */
    private $proxiedSession;

    /**
     * Request instance to use when calling $persistence->initializeSessionFromRequest()
     *
     * @var null|ServerRequestInterface
     */
    private $request;

    public function __construct(SessionPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function regenerate() : SessionInterface
    {
        $this->proxiedSession = $this->getProxiedSession()->regenerate();
        return $this;
    }

    public function isRegenerated() : bool
    {
        return $this->getProxiedSession()->isRegenerated();
    }

    public function toArray() : array
    {
        return $this->getProxiedSession()->toArray();
    }

    public function get(string $name, $default = null)
    {
        return $this->getProxiedSession()->get($name, $default);
    }

    public function has(string $name) : bool
    {
        return $this->getProxiedSession()->has($name);
    }

    public function set(string $name, $value) : void
    {
        $this->getProxiedSession()->set($name, $value);
    }

    public function unset(string $name) : void
    {
        $this->getProxiedSession()->unset($name);
    }

    public function clear() : void
    {
        $this->getProxiedSession()->clear();
    }

    public function hasChanged() : bool
    {
        if (! $this->proxiedSession) {
            return false;
        }

        $proxy = $this->getProxiedSession();

        if ($proxy->isRegenerated()) {
            return true;
        }

        return $proxy->hasChanged();
    }

    private function getProxiedSession() : SessionInterface
    {
        if ($this->proxiedSession) {
            return $this->proxiedSession;
        }

        if (! $this->request) {
            throw InvalidRequestException::requestNotSet();
        }

        $this->proxiedSession = $this->persistence->initializeSessionFromRequest($this->request);
        return $this->proxiedSession;
    }
}
