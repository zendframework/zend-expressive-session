<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Exception;

use InvalidArgumentException;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionPersistenceInterface;

class InvalidSessionPersistenceException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forClass(string $class) : self
    {
        return new self(sprintf(
            'Cannot use class "%s" with %s; does not implement %s',
            $class,
            SessionMiddleware::class,
            SessionPersistenceInterface::class
        ));
    }
}
