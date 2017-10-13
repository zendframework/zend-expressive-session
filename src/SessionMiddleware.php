<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SessionMiddleware implements MiddlewareInterface
{
    const SESSION_ATTRIBUTE = 'session';

    /**
     * @var SessionPersistenceInterface
     */
    private $persistence;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionPersistenceInterface $persistence, SessionInterface $request)
    {
        $this->persistence = $persistence;
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $response = $delegate->process($request->withAttribute(self::SESSION_ATTRIBUTE, $this->session));
        return $this->persistence->persistSession($session, $response);
    }
}
