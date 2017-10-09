<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session\Csrf;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Session\Csrf\CsrfGuardFactoryInterface;
use Zend\Expressive\Session\Csrf\CsrfMiddleware;
use Zend\Expressive\Session\Csrf\CsrfMiddlewareFactory;

class CsrfMiddlewareFactoryTest extends TestCase
{
    public function testFactoryReturnsMiddlewareUsingDefaultAttributeAndConfiguredGuardFactory()
    {
        $guardFactory = $this->prophesize(CsrfGuardFactoryInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(CsrfGuardFactoryInterface::class)->willReturn($guardFactory);

        $factory = new CsrfMiddlewareFactory();

        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(CsrfMiddleware::class, $middleware);
        $this->assertAttributeSame($guardFactory, 'guardFactory', $middleware);
        $this->assertAttributeSame($middleware::GUARD_ATTRIBUTE, 'attributeKey', $middleware);
    }
}
