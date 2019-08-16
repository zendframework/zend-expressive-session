<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Session\Exception;

use RuntimeException;
use Zend\Expressive\Session\SessionPersistenceInterface;
use Zend\Expressive\Session\InitializePersistenceIdInterface;

final class NotInitializableException extends RuntimeException implements ExceptionInterface
{
    public static function invalidPersistence(SessionPersistenceInterface $persistence) : self
    {
        return new self(sprintf(
            "Persistence '%s' does not implement '%s'",
            get_class($persistence),
            InitializePersistenceIdInterface::class
        ));
    }
}
