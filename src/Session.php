<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

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
    private $isRegenerated;

    /**
     * Original data provided to the constructor.
     *
     * @var array
     */
    private $originalData;

    /**
     * @var SegmentInterface[]
     */
    private $segments = [];

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
     * @return mixed
     * @throws Exception\SessionSegmentConflictException if $name refers to a known session segment.
     */
    public function get(string $name, $default = null)
    {
        $this->assertNotSegment($name, 'whenRetrieving');
        return $this->data[$name] ?? $default;
    }

    public function has(string $name) : bool
    {
        return array_key_exist($name, $this->data);
    }

    /**
     * @param mixed $value
     * @throws Exception\SessionSegmentConflictException if $name refers to a known session segment.
     */
    public function set(string $name, $value) : void
    {
        $this->assertNotSegment($name, 'whenSetting');
        $this->data[$name] = self::extractSerializableValue($value);
    }

    /**
     * @throws Exception\SessionSegmentConflictException if $name refers to a known session segment.
     */
    public function unset(string $name) : void
    {
        $this->assertNotSegment($name, 'whenDeleting');
        unset($this->data[$name]);
    }

    /**
     * @throws Exception\InvalidSessionSegmentDataException when data exists for the
     *     segment, but it is not an array.
     */
    public function segment(string $name) : SegmentInterface
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

    public function hasChanged() : bool
    {
        if ($this->isRegenerated) {
            return true;
        }

        if ($this->data !== $this->originalData) {
            return true;
        }

        return array_reduce($this->segments, function (bool $hasChanged, Segment $segment) {
            if ($hasChanged) {
                return $hasChanged;
            }
            return $segment->hasChanged();
        }, false);
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

    /**
     * Assert that a value by $name is not a segment.
     *
     * @throws Exception\SessionSegmentConflictException if a segment by $name is found.
     */
    private function assertNotSegment(string $name, string $event)
    {
        if (! isset($this->segments[$name])) {
            return;
        }
        $factory = [Exception\SessionSegmentConflictException::class, $event];
        throw $factory($name);
    }
}
