<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Session\Exception;

use RuntimeException;

class SessionSegmentConflictException extends RuntimeException implements ExceptionInterface
{
    public static function whenRetrieving(string $name) : self
    {
        return new self(sprintf(
            'Retrieving session data "%s" via get(); however, this data refers to a session segment; aborting',
            $name
        ));
    }

    public static function whenSetting(string $name) : self
    {
        return new self(sprintf(
            'Attempting to set session data "%s"; however, this data refers to a session segment; aborting',
            $name
        ));
    }

    public static function whenDeleting(string $name) : self
    {
        return new self(sprintf(
            'Attempting to unset session data "%s"; however, this data refers to a session segment. '
            . 'Use clear() on the segment instead',
            $name
        ));
    }
}
