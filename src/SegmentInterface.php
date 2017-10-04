<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

interface SegmentInterface extends SessionDataInterface
{
    /**
     * Retrieve the segment name.
     *
     * This is generally the key within the session under which the
     * segment exists. It should never change.
     */
    public function getName() : string;

    /**
     * Set a flash value with the given key.
     *
     * Flash values are accessible on the next "hop", where a hop is the
     * next time the session is accessed; you may pass an additional $hops
     * integer to allow access for more than one hop.
     *
     * @param mixed $value
     */
    public function flash(string $key, $value, int $hops = 1) : void;

    /**
     * Set a flash value with the given key, but allow access during this request.
     *
     * Flash values are generally accessible only on subsequent requests;
     * using this method, you may make the value available during the current
     * request as well.
     *
     * @param mixed $value
     */
    public function flashNow(string $key, $value, int $hops = 1) : void;

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
    public function getFlash(string $key, $default = null);

    /**
     * Clear all flash values.
     *
     * Affects the next and subsequent requests.
     */
    public function clearFlash() : void;

    /**
     * Persists any current flash messages for one more hop.
     */
    public function persistFlash() : void;

    /**
     * Generate a CSRF token.
     *
     * Typically, implementations should generate a one-time CSRF token,
     * store it within the session, and return it so that developers may
     * then inject it in a form, a response header, etc.
     *
     * CSRF tokens should EXPIRE after the first hop. As such, this should
     * delegate to a flash value.
     */
    public function generateCsrfToken(string $keyName = '__csrf') : string;

    /**
     * Validate whether a submitted CSRF token is the same as the one stored in
     * the session.
     *
     * CSRF tokens should EXPIRE after the first hop. As such, this should
     * delegate to a flash value.
     */
    public function validateCsrfToken(string $token, string $csrfKey = '__csrf') : bool;
}
