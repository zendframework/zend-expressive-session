<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

/**
 * Notes on lazy session:
 *
 * - implement it pretty much exactly like it is in storageless, but using
 *   our interface. Use a static method as constructor, to ensure it cannot be
 *   overridden.
 * - update the session middleware to create a lazy session, using a callback
 *   that starts the session. When middleware operations are complete, it should
 *   persist the session.
 */
class Session implements SessionInterface
{
    use SessionCommonTrait;

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
        $this->prepareFlashMessages();
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
     * Prepares flash messages for this request.
     *
     * Loops through all data, identifying nested arrays that have the
     * Segment::FLASH_NEXT key, passing those values to
     * prepareFlashMessagesForSegment(), and re-assigning the value returned
     * from that method to the data for that segment.
     */
    private function prepareFlashMessages() : void
    {
        foreach ($this->data as $key => $value) {
            if (! is_array($value)
                || ! isset($value[Segment::FLASH_NEXT])
            ) {
                continue;
            }

            $this->data[$key] = $this->prepareFlashMessagesForSegment($key, $value);
        }
    }

    /**
     * Prepares flash messages for a given segment.
     *
     * Resets the Segment::FLASH_NOW value to an empty array, and loops through
     * the Segment::FLASH_NEXT values, adding them to Segment::FLASH_NOW.
     *
     * If the value contains a `hops` value greater than 1, it reassigns its
     * value in Segment::FLASH_NEXT, after first decrementing the value.
     * Otherwise, it unsets the entry in Segment::FLASH_NEXT.
     */
    private function prepareFlashMessagesForSegment(string $key, array $segmentData) : array
    {
        $segmentData[Segment::FLASH_NOW] = [];
        foreach ($segmentData[Segment::FLASH_NEXT] as $key => $data) {
            $segmentData[Segment::FLASH_NOW][$key] = $data['value'];

            if ($data['hops'] === 1) {
                unset($segmentData[Segment::FLASH_NEXT][$key]);
                continue;
            }

            $data['hops'] -= 1;
            $segmentData[Segment::FLASH_NEXT][$key] = $data;
        }
        return $segmentData;
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
