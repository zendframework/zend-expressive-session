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
 * - Flash messages
 */
class Segment implements SegmentInterface
{
    use SessionCommonTrait;

    /**
     * Flash values available over multiple hops
     */
    const FLASH_HOPS = self::class . '::FLASH_HOPS';

    /**
     * Flash values scheduled for next request.
     */
    const FLASH_NEXT = self::class . '::FLASH_NEXT';

    /**
     * Flash values accessible in this request.
     */
    const FLASH_NOW = self::class . '::FLASH_NOW';

    /**
     * Current data within the segment.
     *
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $id;

    /**
     * Original data provided to the constructor.
     *
     * @var array
     */
    private $originalData;

    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $data = $this->prepareSegmentData($data);
        $this->data = $this->originalData = $data;
    }

    /**
     * Retrieve all data, for purposes of persistence.
     */
    public function toArray() : array
    {
        $data = $this->data;

        // Remove current flash messages
        unset($data[self::FLASH_NOW]);

        // Remove next flash messages, if none exist
        if (0 === count($data[self::FLASH_NEXT])) {
            unset($data[self::FLASH_NEXT]);
        }

        return $data;
    }

    /**
     * @param mixed $default Default value to return if value does not exist.
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->data[$name] ?? $default;
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
     * Set a flash value with the given key.
     *
     * Flash values are accessible on the next "hop", where a hop is the
     * next time the session is accessed; you may pass an additional $hops
     * integer to allow access for more than one hop.
     *
     * @param mixed $value
     */
    public function flash(string $key, $value, int $hops = 1) : void
    {
        if ($hops < 1) {
            throw Exception\InvalidHopsValueException::valueTooLow($key, $hops);
        }

        $this->data[self::FLASH_NEXT][$key] = [
            'value' => $value,
            'hops'  => $hops,
        ];
    }

    /**
     * Set a flash value with the given key, but allow access during this request.
     *
     * Flash values are generally accessible only on subsequent requests;
     * using this method, you may make the value available during the current
     * request as well.
     *
     * @param mixed $value
     */
    public function flashNow(string $key, $value, int $hops = 1) : void
    {
        $this->flash($key, $value, $hops);
        $this->data[self::FLASH_NOW][$key] = $value;
    }

    /**
     * Retrieve a flash value.
     *
     * Will return a value only if a flash value was set in a previous request,
     * or if `flashNow()` was called in this request with the same `$key`.
     *
     * WILL NOT return a value if set in the current request via `flash()`.
     *
     * @param mixed $default Default value to return if no flash value exists.
     * @return mixed
     */
    public function getFlash(string $key, $default = null)
    {
        return $this->data[self::FLASH_NOW][$key] ?? $default;
    }

    /**
     * Clear all flash values.
     *
     * Affects the next and subsequent requests.
     */
    public function clearFlash() : void
    {
        unset($this->data[self::FLASH_NEXT]);
    }

    /**
     * Persists any current flash messages for one more hop.
     */
    public function persistFlash() : void
    {
        foreach ($this->data[self::FLASH_NOW] as $key => $value) {
            // Do nothing if the value is already persisted for the next hop.
            if (isset($this->data[self::FLASH_NEXT][$key])) {
                continue;
            }

            $this->flash($key, $value);
        }
    }

    /**
     * Generate a CSRF token.
     *
     * Generates a cryptographically unique and secure token against which the
     * next request can validate in order to prevent CSRF attacks.
     */
    public function generateCsrfToken(string $keyName = '__csrf') : string
    {
        $token = static::generateToken();
        $this->flashNow($keyName, $token);
        return $token;
    }

    /**
     * Validate a submitted CSRF token against one stored in the session.
     */
    public function validateCsrfToken(string $token, string $csrfKey = '__csrf') : bool
    {
        $storedToken = $this->getFlash($csrfKey, '');
        return $token === $storedToken;
    }

    /**
     * Internal preparations of segment data prior to usage.
     *
     * Ensures the FLASH_NOW and FLASH_NEXT keys are present and array
     * values.
     */
    private function prepareSegmentData(array $data) : array
    {
        foreach ([self::FLASH_NOW, self::FLASH_NEXT] as $key) {
            if (! isset($data[$key]) || ! is_array($data[$key])) {
                $data[$key] = [];
            }
        }

        return $data;
    }
}
