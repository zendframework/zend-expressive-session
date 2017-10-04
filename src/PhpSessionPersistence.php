<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpSessionPersistence implements SessionPersistenceInterface
{
    public function initializeSessionFromRequest(ServerRequestInterface $request) : SessionInterface
    {
        $id = FigRequestCookies::get($request, session_name()) ?: $this->generateSessionId();
        $this->startSession($id);
        return new Session($id, $_SESSION);
    }

    public function persistSession(SessionInterface $session, ResponseInterface $response) : ResponseInterface
    {
        if ($session->isRegenerated()) {
            $this->startSession($this->generateSessionId());
        }

        $_SESSION = $session->toArray();
        session_write_close();

        if (empty($_SESSION)) {
            return $response;
        }

        $sessionCookie = SetCookie::create(session_name())
            ->withValue(session_id())
            ->withPath(ini_get('session.cookie_path'));

        return FigResponseCookies::set($response, $sessionCookie);
    }

    private function startSession(string $id) : void
    {
        session_id($id);
        session_start([
            'use_cookies'      => false,
            'use_only_cookies' => true,
        ]);
    }

    /**
     * Generate a session identifier.
     */
    private function generateSessionId() : string
    {
        return bin2hex(random_bytes(16));
    }
}
