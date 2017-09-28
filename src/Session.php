<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

class Session
{
    use SessionDataTrait;

    private $data;
    private $id;
    private $segments = [];

    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Retrieve all data, including segments, as a nested set of arrays, for
     * purposes of persistence.
     */
    public function toArray() : array
    {
        $data = $this->data;
        foreach ($this->segments as $key => $segment) {
            $segmentData = $segment->toArray();
            if (empty($segmentData)) {
                unset($this->data[$key]);
                continue;
            }
            $data[$key] = $segmentData;
        }
        return $data;
    }

    /**
     * @param mixed $default Default value to return if $name does not exist.
     * @throws Exception\SessionSegmentConflictException if $name refers to a known session segment.
     */
    public function get(string $name, $default = null)
    {
        if (isset($this->segments[$name])) {
            throw Exception\SessionSegmentConflictException::whenRetrieving($name);
        }
        return $this->data[$name] ?? $default;
    }

    /**
     * @param mixed $value
     * @throws Exception\SessionSegmentConflictException if $name refers to a known session segment.
     */
    public function set(string $name, $value) : void
    {
        if (isset($this->segments[$name])) {
            throw Exception\SessionSegmentConflictException::whenSetting($name);
        }
        $this->data[$name] = $value;
    }

    /**
     * @throws Exception\SessionSegmentConflictException if $name refers to a known session segment.
     */
    public function unset(string $name) : void
    {
        if (isset($this->segments[$name])) {
            throw Exception\SessionSegmentConflictException::whenDeleting($name);
        }
        unset($this->data[$name]);
    }

    /**
     * @throws Exception\InvalidSessionSegmentDataException when data exists for the
     *     segment, but it is not an array.
     */
    public function segment(string $name) : Segment
    {
        if (isset($this->segments[$name])) {
            return $this->segments[$name];
        }

        if (array_key_exists($name, $this->data)
            && ! is_array($this->data[$name])
        ) {
            throw Exception\InvalidSessionSegmentDataException::whenRetrieving($data, $this->data[$name]);
        }

        $this->segments[$name] = new Segment($this->data[$name]);
        return $this->segments[$name];
    }

    public function regenerateId(): void
    {
        $this->id = static::generateToken();
    }
}
