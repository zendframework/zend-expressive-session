<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionMiddlewareFactory;
use Zend\Expressive\Session\SessionPersistenceInterface;

class SessionMiddlewareFactoryTest extends TestCase
{
    public function testFactoryProducesMiddlewareWithSessionPersistenceInterfaceService()
    {
        $persistence = $this->prophesize(SessionPersistenceInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(SessionPersistenceInterface::class)->willReturn($persistence);

        $session = $this->prophesize(SessionInterface::class)->reveal();
        $container->get(SessionInterface::class)->willReturn($session);

        $factory = new SessionMiddlewareFactory();

        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(SessionMiddleware::class, $middleware);
        $this->assertAttributeSame($persistence, 'persistence', $middleware);
    }
}
