<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Session\PhpSessionPersistence;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionMiddlewareFactory;

class SessionMiddlewareFactoryTest extends TestCase
{
    public function testFactoryProducesMiddlewareWithPhpSessionPersistence()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new SessionMiddlewareFactory();

        $middleware = $factory($container);

        $this->assertInstanceOf(SessionMiddleware::class, $middleware);
        $this->assertAttributeInstanceOf(PhpSessionPersistence::class, 'persistence', $middleware);
    }
}
