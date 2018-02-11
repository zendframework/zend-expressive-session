<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public const SESSION_ATTRIBUTE = 'session';

    /**
     * @var SessionPersistenceInterface
     */
    private $persistence;

    public function __construct(SessionPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = new LazySession($this->persistence, $request);
        $response = $handler->handle($request->withAttribute(self::SESSION_ATTRIBUTE, $session));
        return $this->persistence->persistSession($session, $response);
    }
}
