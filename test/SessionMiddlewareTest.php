<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\PhpSessionPersistence;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionPersistenceInterface;

class SessionMiddlewareTest extends TestCase
{
    public function testConstructorAcceptsConcretePersistenceInstances()
    {
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();
        $session = $this->prophesize(SessionInterface::class)->reveal();
        $middleware = new SessionMiddleware($persistence, $session);
        $this->assertAttributeSame($persistence, 'persistence', $middleware);
        $this->assertAttributeSame($session, 'session', $middleware);
    }

    public function testMiddlewareCreatesLazySessionAndPassesItToDelegateAndPersistsSessionInResponse()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, Argument::type(LazySession::class))
            ->will([$request, 'reveal']);

        $response = $this->prophesize(ResponseInterface::class);

        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->process(Argument::that([$request, 'reveal']))->will([$response, 'reveal']);

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

        $session = new LazySession($persistence->reveal());
        $middleware = new SessionMiddleware($persistence->reveal(), $session);
        $this->assertSame($response->reveal(), $middleware->process($request->reveal(), $delegate->reveal()));
    }
}
