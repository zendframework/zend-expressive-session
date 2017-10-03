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
     * Retrieve the session identifier.
     *
     * This is generally used to provide an identifier via cookie, query string
     * argument, etc.
     */
    public function getId() : string;

    /**
     * Regenerate the session identifier.
     *
     * This can be done to prevent session fixation.
     */
    public function regenerateId(): void;

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
