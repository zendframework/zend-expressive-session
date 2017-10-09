<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Session\Csrf;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Session\Csrf\FlashCsrfGuard;
use Zend\Expressive\Session\Flash\FlashMessagesInterface;

class FlashCsrfGuardTest extends TestCase
{
    public function setUp()
    {
        $this->flash = $this->prophesize(FlashMessagesInterface::class);
        $this->guard = new FlashCsrfGuard($this->flash->reveal());
    }

    public function keyNameProvider() : array
    {
        return [
            'default' => ['__csrf'],
            'custom'  => ['CSRF'],
        ];
    }

    /**
     * @dataProvider keyNameProvider
     */
    public function testGenerateTokenStoresTokenInFlashAndReturnsIt(string $keyName)
    {
        $expected = '';
        $this->flash
            ->flash(
                $keyName,
                Argument::that(function ($token) use (&$expected) {
                    $this->assertRegExp('/^[a-f0-9]{32}$/', $token);
                    $expected = $token;
                    return $token;
                })
            )
            ->shouldBeCalled();

        $token = $this->guard->generateToken($keyName);
        $this->assertSame($expected, $token);
    }

    public function tokenValidationProvider() : array
    {
        // @codingStandardsIgnoreStart
        return [
            // case                  => [token,   key,      flash token, assertion    ]
            'default-not-found'      => ['token', '__csrf', '',          'assertFalse'],
            'default-found-not-same' => ['token', '__csrf', 'different', 'assertFalse'],
            'default-found-same'     => ['token', '__csrf', 'token',     'assertTrue'],
            'custom-not-found'       => ['token', 'CSRF',   '',          'assertFalse'],
            'custom-found-not-same'  => ['token', 'CSRF',   'different', 'assertFalse'],
            'custom-found-same'      => ['token', 'CSRF',   'token',     'assertTrue'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider tokenValidationProvider
     */
    public function testValidateTokenValidatesProvidedTokenAgainstOneStoredInFlash(
        string $token,
        string $csrfKey,
        string $flashTokenValue,
        string $assertion
    ) {
        $this->flash->getFlash($csrfKey, '')->willReturn($flashTokenValue);
        $this->$assertion($this->guard->validateToken($token, $csrfKey));
    }
}
