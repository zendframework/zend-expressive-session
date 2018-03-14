<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Session;

class Session implements SessionInterface
{
    /**
     * Current data within the session.
     *
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $isRegenerated = false;

    /**
     * Original data provided to the constructor.
     *
     * @var array
     */
    private $originalData;

    public function __construct(array $data)
    {
        $this->data = $this->originalData = $data;
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

    /**
     * Retrieve all data for purposes of persistence.
     */
    public function toArray() : array
    {
        return $this->data;
    }

    /**
     * @param mixed $default Default value to return if $name does not exist.
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

    /**
     * @param mixed $value
     */
    public function set(string $name, $value) : void
    {
        $this->data[$name] = self::extractSerializableValue($value);
    }

    public function unset(string $name) : void
    {
        unset($this->data[$name]);
    }

    public function clear() : void
    {
        $this->data = [];
    }

    public function hasChanged() : bool
    {
        if ($this->isRegenerated) {
            return true;
        }

        return $this->data !== $this->originalData;
    }

    public function regenerate() : SessionInterface
    {
        $session = clone $this;
        $session->isRegenerated = true;
        return $session;
    }

    public function isRegenerated() : bool
    {
        return $this->isRegenerated;
    }
}
