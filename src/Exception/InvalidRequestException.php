<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Exception;

use RuntimeException;

class InvalidRequestException extends RuntimeException implements ExceptionInterface
{
    public static function requestNotSet() : self
    {
        return new self(sprintf(
            'An object of Psr\Http\Message\ServerRequestInterface not set'
        ));
    }
}
