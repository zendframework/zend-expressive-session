<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

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

    private function __construct(SessionPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function getId() : string
    {
        return $this->getProxiedSession()->getId();
    }

    public function regenerateId(): void
    {
        $this->getProxiedSession()->regenerateId();
    }

    public function segment(string $name) : SegmentInterface
    {
        return $this->getProxiedSession()->segment($name);
    }

    public function toArray() : array
    {
        return $this->getProxiedSession()->toArray();
    }

    public function get(string $name, $default = null)
    {
        return $this->getProxiedSession()->get($name, $default);
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

        return $this->getProxiedSession()->hasChanged();
    }

    private function getProxiedSession() : SessionInterface
    {
        if ($this->proxiedSession) {
            return $this->proxiedSession;
        }

        $this->proxiedSession = $this->persistence->createSession();
        return $this->proxiedSession;
    }
}
