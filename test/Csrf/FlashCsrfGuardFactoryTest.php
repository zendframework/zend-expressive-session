<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session\Csrf;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\Csrf\Exception;
use Zend\Expressive\Session\Csrf\FlashCsrfGuard;
use Zend\Expressive\Session\Csrf\FlashCsrfGuardFactory;
use Zend\Expressive\Session\Flash\FlashMessageMiddleware;
use Zend\Expressive\Session\Flash\FlashMessagesInterface;

class FlashCsrfGuardFactoryTest extends TestCase
{
    public function testConstructionUsesSaneDefaults()
    {
        $factory = new FlashCsrfGuardFactory();
        $this->assertAttributeSame(FlashMessageMiddleware::FLASH_ATTRIBUTE, 'attributeKey', $factory);
    }

    public function testConstructionAllowsPassingAttributeKey()
    {
        $factory = new FlashCsrfGuardFactory('alternate-attribute');
        $this->assertAttributeSame('alternate-attribute', 'attributeKey', $factory);
    }

    public function attributeKeyProvider() : array
    {
        return [
            'default' => [FlashMessageMiddleware::FLASH_ATTRIBUTE],
            'custom'  => ['custom-flash-attribute'],
        ];
    }

    /**
     * @dataProvider attributeKeyProvider
     */
    public function testCreateGuardFromRequestRaisesExceptionIfAttributeDoesNotContainFlash(string $attribute)
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute($attribute, false)->willReturn(false);

        $factory = new FlashCsrfGuardFactory($attribute);

        $this->expectException(Exception\MissingFlashMessagesException::class);
        $factory->createGuardFromRequest($request->reveal());
    }

    /**
     * @dataProvider attributeKeyProvider
     */
    public function testCreateGuardFromRequestReturnsCsrfGuardWithSessionWhenPresent(string $attribute)
    {
        $flash = $this->prophesize(FlashMessagesInterface::class)->reveal();
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute($attribute, false)->willReturn($flash);

        $factory = new FlashCsrfGuardFactory($attribute);

        $guard = $factory->createGuardFromRequest($request->reveal());
        $this->assertInstanceOf(FlashCsrfGuard::class, $guard);
    }
}
