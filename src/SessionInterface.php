<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

interface SessionInterface extends SessionDataInterface
{
    /**
     * Regenerate the session.
     *
     * This can be done to prevent session fixation. When executed, it SHOULD
     * return a new instance; that instance should always return true for
     * isRegenerated().
     *
     * An example of where this WOULD NOT return a new instance is within the
     * shipped LazySession, where instead it would return itself, after
     * internally re-setting the proxied session.
     */
    public function regenerate(): SessionInterface;

    /**
     * Method to determine if the session was regenerated; should return
     * true if the instance was produced via regenerate().
     */
    public function isRegenerated() : bool;

    /**
     * Retrieve a namespaced segment from the session.
     *
     * Typically, this is a nested array of values under a well known
     * name within the session. Providing segments allows you to
     * compartmentalize data based on context: authentication identity,
     * specific forms, etc.
     */
    public function segment(string $name) : SegmentInterface;
}
