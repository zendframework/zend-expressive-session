<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

trait SessionCommonTrait
{
    /**
     * Generate a token for use as an identifier, CSRF, etc.
     *
     * Generates a unique, 32 character, cryptographically secure token for
     * use as either a session identifier, CSRF token, etc.
     */
    public static function generateToken() : string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Convert a value to a JSON-serializable value.
     *
     * This value should be used by `set()` operations to ensure that the values
     * within a session are serializable across any session adapter.
     *
     * @param mixed $value
     * @return null|bool|int|float|string|array|\stdClass
     */
    public static function extractSerializableValue($value)
    {
        return json_decode(json_encode($value, \JSON_PRESERVE_ZERO_FRACTION), true);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
