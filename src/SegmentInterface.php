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
