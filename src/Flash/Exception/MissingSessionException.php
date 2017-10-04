<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Flash\Exception;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use RuntimeException;

class MissingSessionException extends RuntimeException implements ExceptionInterface
{
    public static function forMiddleware(MiddlewareInterface $middleware)
    {
        return new self(sprintf(
            'Unable to create flash messages in %s; missing session attribute',
            get_class($middleware)
        ));
    }
}
