<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Csrf;

use Zend\Expressive\Session\Flash\FlashMessagesInterface;

class FlashCsrfGuard implements CsrfGuardInterface
{
    /**
     * @var FlashMessagesInterface
     */
    private $flashMessages;

    public function __construct(FlashMessagesInterface $flashMessages)
    {
        $this->flashMessages = $flashMessages;
    }

    public function generateToken(string $keyName = '__csrf') : string
    {
        $token = bin2hex(random_bytes(16));
        $this->flashMessages->flash($keyName, $token);
        return $token;
    }

    public function validateToken(string $token, string $csrfKey = '__csrf') : bool
    {
        $storedToken = $this->flashMessages->getFlash($csrfKey, '');
        return $token === $storedToken;
    }
}
