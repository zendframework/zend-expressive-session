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

/**
 * Session persistence using ext-session.
 *
 * Adapts ext-session to work with PSR-7 by disabling its auto-cookie creation
 * (`use_cookies => false`), while simultaneously requiring cookies for session
 * handling (`use_only_cookies => true`). The implementation pulls cookies
 * manually from the request, and injects a `Set-Cookie` header into the
 * response.
 *
 * Session identifiers are generated using random_bytes (and casting to hex).
 * During persistence, if the session regeneration flag is true, a new session
 * identifier is created, and the session re-started.
 */
class PhpSessionPersistence implements SessionPersistenceInterface
{
    public function initializeSessionFromRequest(ServerRequestInterface $request) : SessionInterface
    {
        $id = FigRequestCookies::get($request, session_name())->getValue() ?: $this->generateSessionId();
        $this->startSession($id);
        return new Session($_SESSION);
    }

    public function persistSession(SessionInterface $session, ResponseInterface $response) : ResponseInterface
    {
        if ($session->isRegenerated()) {
            $this->regenerateSession();
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
     * Regenerates the session safely.
     *
     * @link http://php.net/manual/en/function.session-regenerate-id.php (Example #2)
     */
    private function regenerateSession() : void
    {
        session_commit();
        ini_set('session.use_strict_mode', 0);
        $this->startSession($this->generateSessionId());
        ini_set('session.use_strict_mode', 1);
    }

    /**
     * Generate a session identifier.
     */
    private function generateSessionId() : string
    {
        return bin2hex(random_bytes(16));
    }
}
