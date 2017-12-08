<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Session;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Session\Session;
use Zend\Expressive\Session\SessionInterface;

class SessionTest extends TestCase
{
    public function testImplementsSessionInterface()
    {
        $session = new Session([]);
        $this->assertInstanceOf(SessionInterface::class, $session);
    }

    public function testIsNotChangedAtInstantiation()
    {
        $session = new Session([]);
        $this->assertFalse($session->hasChanged());
    }

    public function testIsNotRegeneratedByDefault()
    {
        $session = new Session([]);
        $this->assertFalse($session->isRegenerated());
    }

    public function testRegenerateProducesANewInstance()
    {
        $session = new Session([]);
        $regenerated = $session->regenerate();
        $this->assertNotSame($session, $regenerated);
        return $regenerated;
    }

    /**
     * @depends testRegenerateProducesANewInstance
     */
    public function testRegeneratedSessionReturnsTrueForIsRegenerated(SessionInterface $session)
    {
        $this->assertTrue($session->isRegenerated());
    }

    /**
     * @depends testRegenerateProducesANewInstance
     */
    public function testRegeneratedSessionIsChanged(SessionInterface $session)
    {
        $this->assertTrue($session->hasChanged());
    }

    public function testSettingDataInSessionMakesItAccessible()
    {
        $session = new Session([]);
        $this->assertFalse($session->has('foo'));
        $session->set('foo', 'bar');
        $this->assertTrue($session->has('foo'));
        $this->assertSame('bar', $session->get('foo'));
        return $session;
    }

    /**
     * @depends testSettingDataInSessionMakesItAccessible
     */
    public function testSettingDataInSessionChangesSession(SessionInterface $session)
    {
        $this->assertTrue($session->hasChanged());
    }

    /**
     * @depends testSettingDataInSessionMakesItAccessible
     */
    public function testToArrayReturnsAllDataPreviouslySet(SessionInterface $session)
    {
        $this->assertSame(['foo' => 'bar'], $session->toArray());
    }

    /**
     * @depends testSettingDataInSessionMakesItAccessible
     */
    public function testCanUnsetDataInSession(SessionInterface $session)
    {
        $session->unset('foo');
        $this->assertFalse($session->has('foo'));
    }

    public function testClearingSessionRemovesAllData()
    {
        $original = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];
        $session = new Session($original);
        $this->assertSame($original, $session->toArray());

        $session->clear();
        $this->assertNotSame($original, $session->toArray());
        $this->assertSame([], $session->toArray());
    }

    public function serializedDataProvider() : iterable
    {
        $data = (object) ['test_case' => $this];
        $expected = json_decode(json_encode($data, \JSON_PRESERVE_ZERO_FRACTION), true);
        yield 'nested-objects' => [$data, $expected];
    }

    /**
     * @dataProvider serializedDataProvider
     */
    public function testSetEnsuresDataIsJsonSerializable($data, $expected)
    {
        $session = new Session([]);
        $session->set('foo', $data);
        $this->assertNotSame($data, $session->get('foo'));
        $this->assertSame($expected, $session->get('foo'));
    }
}
