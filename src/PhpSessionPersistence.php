<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpSessionPersistence implements SessionPersistenceInterface
{
    /**
     * @var string
     */
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function createFromRequest(ServerRequestInterface $request) : SessionPersistenceInterface
    {
        return new self(self::getSessionId($request));
    }

    public function createNewInstanceFromRequest(ServerRequestInterface $request) : SessionPersistenceInterface
    {
        $clone = clone $this;
        $clone->id = self::getSessionId($request);
        return $clone;
    }

    public function createSession() : SessionInterface
    {
        $this->startSession($this->id);
        return new Session($this->id, $_SESSION);
    }

    public function persistSession(SessionInterface $session, ResponseInterface $response) : ResponseInterface
    {
        $sessionId = $session->getId();
        if ($this->id !== $sessionId) {
            $this->id = $sessionId;
            $this->startSession($sessionId);
        }

        $_SESSION = $session->toArray();
        session_write_close();

        if (empty($_SESSION)) {
            return $response;
        }

        return $response->withHeader(
            'Set-Cookie',
            sprintf(
                '%s=%s; path=%s',
                session_name(),
                $sessionId,
                ini_get('session.cookie_path')
            )
        );
    }

    private static function getSessionId(ServerRequestInterface $request) : string
    {
        $cookies = $request->getCookieParams();
        return $cookies[session_name()] ?? Session::generateToken();
    }

    private function startSession(string $id) : void
    {
        session_id($id);
        session_start([
            'use_cookies'      => false,
            'use_only_cookies' => true,
        ]);
    }
}
