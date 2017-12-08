<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session;

use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionPersistenceInterface;

class SessionMiddlewareTest extends TestCase
{
    public function testConstructorAcceptsConcretePersistenceInstances()
    {
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();
        $middleware = new SessionMiddleware($persistence);
        $this->assertAttributeSame($persistence, 'persistence', $middleware);
    }

    public function testMiddlewareCreatesLazySessionAndPassesItToDelegateAndPersistsSessionInResponse()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, Argument::type(LazySession::class))
            ->will([$request, 'reveal']);

        $response = $this->prophesize(ResponseInterface::class);

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::that([$request, 'reveal']))->will([$response, 'reveal']);

        $persistence = $this->prophesize(SessionPersistenceInterface::class);
        $persistence
            ->persistSession(
                Argument::that(function ($session) use ($persistence, $request) {
                    $this->assertInstanceOf(LazySession::class, $session);
                    $this->assertAttributeSame($persistence->reveal(), 'persistence', $session);
                    $this->assertAttributeSame($request->reveal(), 'request', $session);
                    return $session;
                }),
                Argument::that([$response, 'reveal'])
            )
            ->will([$response, 'reveal']);

        $middleware = new SessionMiddleware($persistence->reveal());
        $this->assertSame($response->reveal(), $middleware->process($request->reveal(), $handler->reveal()));
    }
}
