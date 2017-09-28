<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

trait SessionDataTrait
{
    public static function generateToken() : string
    {
        return bin2hex(random_bytes(16));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function generateCsrfToken(string $keyName = '__csrf') : string
    {
        $this->data[$keyName] = static::generateToken();
        return $this->data[$keyName];
    }

    public function validateCsrfToken(string $token, string $csrfKey = '__csrf') : bool
    {
        if (! isset($this->data[$csrfKey])) {
            return false;
        }

        return $token === $this->data[$csrfKey];
    }
}
