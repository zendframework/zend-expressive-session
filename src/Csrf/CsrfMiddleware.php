<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Csrf;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Create a CSRF guard and inject it as a request attribute.
 *
 * Uses the provided CsrfGuardFactoryInterface implementation to create a
 * CsrfGuardInterface instance and inject it into the request provided to the
 * delegate.
 *
 * Later middleware can then access the CsrfGuardInterface instance in order to
 * either generate or validate a token.
 */
class CsrfMiddleware implements MiddlewareInterface
{
    const GUARD_ATTRIBUTE = 'csrf';

    /**
     * @var string
     */
    private $attributeKey;

    /**
     * @var CsrfGuardFactoryInterface
     */
    private $guardFactory;

    public function __construct(
        CsrfGuardFactoryInterface $guardFactory,
        string $attributeKey = self::GUARD_ATTRIBUTE
    ) {
        $this->guardFactory = $guardFactory;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $guard = $this->guardFactory->createGuardFromRequest($request);
        return $delegate->process($request->withAttribute($this->attributeKey, $guard));
    }
}
