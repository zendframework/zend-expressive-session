<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Csrf\Exception;

use RuntimeException;
use Zend\Expressive\Session\Csrf\FlashCsrfGuard;
use Zend\Expressive\Session\Flash\FlashMessageMiddleware;

class MissingFlashMessagesException extends RuntimeException implements ExceptionInterface
{
    public static function create() : self
    {
        return new self(sprintf(
            'Cannot create %s; could not locate session in request. '
            . 'Make sure the %s is piped to your application.',
            FlashCsrfGuard::class,
            FlashMessageMiddleware::class
        ));
    }
}
