<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Session\Exception;

use RuntimeException;

class InvalidSessionSegmentDataException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $data
     */
    public static function whenRetrieving(string $name, $data) : self
    {
        return new self(sprintf(
            'Cannot retrieve session segment "%s"; data exists, but as a "%s" instead of an array',
            $name,
            is_object($data) ? get_class($data) : gettype($data)
        ));
    }
}
