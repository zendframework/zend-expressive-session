<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

/**
 * Class representing a session segment (essentially, a "namespace" within the session).
 *
 * This class also provides a number of convenience mechanisms:
 *
 * - CSRF token generation and validation
 */
class Segment implements SegmentInterface
{
    use SessionCommonTrait;

    /**
     * Current data within the segment.
     *
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $name;

    /**
     * Original data provided to the constructor.
     *
     * @var array
     */
    private $originalData;

    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $this->originalData = $data;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Retrieve all data, for purposes of persistence.
     */
    public function toArray() : array
    {
        return $this->data;
    }

    /**
     * @param mixed $default Default value to return if value does not exist.
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }

    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->data);
    }

    public function set(string $name, $value): void
    {
        $this->data[$name] = self::extractSerializableValue($value);
    }

    public function unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function clear() : void
    {
        $this->data = [];
    }

    public function hasChanged() : bool
    {
        return $this->data !== $this->originalData;
    }

    /**
     * Generate a CSRF token.
     *
     * Generates a cryptographically unique and secure token against which the
     * next request can validate in order to prevent CSRF attacks.
     */
    public function generateCsrfToken(string $keyName = '__csrf') : string
    {
        $token = $this->generateToken();
        $this->set($keyName, $token);
        return $token;
    }

    /**
     * Validate a submitted CSRF token against one stored in the session.
     */
    public function validateCsrfToken(string $token, string $csrfKey = '__csrf') : bool
    {
        $storedToken = $this->get($csrfKey, '');
        $this->unset($csrfKey);
        return $token === $storedToken;
    }

    /**
     * Generate a CSRF token value.
     */
    private function generateToken() : string
    {
        return bin2hex(random_bytes(16));
    }
}
