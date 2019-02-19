<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Session;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SaveHandlerInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionPersistenceInterface;

/**
 * @runTestsInSeparateProcesses
 */
class SessionMiddlewareTest extends TestCase
{
    public function testConstructorAcceptsConcretePersistenceInstances()
    {
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();
        $middleware = new SessionMiddleware($persistence, null);
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

        $middleware = new SessionMiddleware($persistence->reveal(), null);
        $this->assertSame($response->reveal(), $middleware->process($request->reveal(), $handler->reveal()));
    }

    public function testConstructorAcceptsConcreteSaveHandlerInstances()
    {
        $saveHandler = $this->prophesize(SaveHandlerInterface::class)->reveal();
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();

        $middleware = new SessionMiddleware($persistence, $saveHandler);
        $this->assertAttributeSame($persistence, 'persistence', $middleware);
    }
}
