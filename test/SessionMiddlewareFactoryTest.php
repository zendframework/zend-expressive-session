<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Session;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Session\SaveHandlerInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionMiddlewareFactory;
use Zend\Expressive\Session\SessionPersistenceInterface;

/**
 * @runTestsInSeparateProcesses
 */
class SessionMiddlewareFactoryTest extends TestCase
{
    public function testWithSessionPersistenceInterfaceServiceAndWithoutSaveHandlerInterfaceService()
    {
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(SessionPersistenceInterface::class)->willReturn($persistence);
        $container->has(SaveHandlerInterface::class)->willReturn(false);

        $factory = new SessionMiddlewareFactory();
        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(SessionMiddleware::class, $middleware);
        $this->assertAttributeSame($persistence, 'persistence', $middleware);
    }

    public function testWithSessionPersistenceInterfaceServiceAndWithSaveHandlerInterfaceService()
    {
        $saveHandler = $this->prophesize(SaveHandlerInterface::class)->reveal();
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(SessionPersistenceInterface::class)->willReturn($persistence);
        $container->has(SaveHandlerInterface::class)->willReturn(true);
        $container->get(SaveHandlerInterface::class)->willReturn($saveHandler);

        $factory = new SessionMiddlewareFactory();
        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(SessionMiddleware::class, $middleware);
        $this->assertAttributeSame($persistence, 'persistence', $middleware);
    }
}
