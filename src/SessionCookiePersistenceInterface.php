<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

/**
 * Allow marking session cookies as persistent.
 *
 * It can be useful to mark a session as persistent: e.g., for a "Remember Me"
 * feature when logging a user into your system. PHP provides this capability
 * via ext-session with the $lifetime argument to session_set_cookie_params()
 * as well as by the session.cookie_lifetime INI setting. The latter will set
 * the value for all session cookies sent (or until the value is changed via
 * an ini_set() call), while the former will only affect cookies created during
 * the current script lifetime.
 *
 * Persistence engines may, of course, allow setting a global lifetime. This
 * interface allows developers to set the lifetime programmatically. Persistence
 * implementations are encouraged to use the value to set the cookie lifetime
 * when creating and returning a cookie. Additionally, to ensure the cookie
 * lifetime originally requested is honored when a session is regenerated, we
 * recommend persistence engines to store the TTL in the session data itself,
 * so that it can be re-sent in such scenarios.
 */
interface SessionCookiePersistenceInterface
{
    const SESSION_LIFETIME_KEY = '__SESSION_TTL__';

    /**
     * Define how long the session cookie should live.
     *
     * Use this value to detail to the session persistence engine how long the
     * session cookie should live.
     *
     * This value could be passed as the $lifetime value of
     * session_set_cookie_params(), or used to create an Expires or Max-Age
     * parameter for a session cookie.
     *
     * Since cookie lifetime is communicated by the server to the client, and
     * not vice versa, the value should likely be persisted in the session
     * itself, to ensure that session regeneration uses the same value. We
     * recommend using the SESSION_LIFETIME_KEY value to communicate this.
     *
     * @param int $duration Number of seconds the cookie should persist for.
     */
    public function persistSessionFor(int $duration) : void;

    /**
     * Determine how long the session cookie should live.
     *
     * Generally, this will return the value provided to persistFor().
     *
     * If that method has not been called, the value can return one of the
     * following:
     *
     * - 0 or a negative value, to indicate the cookie should be treated as a
     *   session cookie, and expire when the window is closed. This should be
     *   the default behavior.
     * - If persistFor() was provided during session creation or anytime later,
     *   the persistence engine should pull the TTL value from the session itself
     *   and return it here. Typically, this value should be communicated via
     *   the SESSION_LIFETIME_KEY value of the session.
     */
    public function getSessionLifetime() : int;
}
