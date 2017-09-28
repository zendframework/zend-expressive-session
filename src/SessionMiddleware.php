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
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $id = $cookies[session_name()] ?? Session::generateToken();

        $this->startSession($id);

        $session = new Session($id, $_SESSION);

        $response = $delegate->process($request->withAttribute(Session::class, $session));

        if ($id !== $session->getId()) {
            $this->startSession($session->getId());
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
                $session->getId(),
                ini_get('session.cookie_path')
            )
        );
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
