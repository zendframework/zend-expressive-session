<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-data for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-data/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Exception;

use InvalidArgumentException;

class InvalidHopsValueException extends InvalidArgumentException implements ExceptionInterface
{
    public static function valueTooLow(string $key, int $hops) : self
    {
        return new self(sprintf(
            'Hops value specified for flash message "%s" was too low; must be greater than 0, received %d',
            $key,
            $hops
        ));
    }
}
