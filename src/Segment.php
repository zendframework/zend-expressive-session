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
 * Other proposed methods/functionality:
 *
 * - setFlash(string $name, string $message) :void - on a namespace instance, to set a flash value
 * - getFlash(string $name) : ?string - on a namespace instance, to retrieve a flash value
 * - keepFlash() : void - on a namespace instance, persist flash values for another request
 * - clearFlash() : void - on a namespace instance, to remove any existing flash values
 *
 * The above would require some coordination by the Session class (for purposes
 * of managing which items are current, and which need to persist to next
 * request).
 */
class Segment
{
    use SessionDataTrait;

    private $data;
    private $id;

    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
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

    public function set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function clear() : void
    {
        $this->data = [];
    }
}
