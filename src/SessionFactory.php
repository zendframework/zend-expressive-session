<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

use Psr\Container\ContainerInterface;

class SessionFactory
{
    public function __invoke(ContainerInterface $container) : SessionInterface
    {
        if ($container->has(SessionInterface::class)) {
            return $container->get(SessionInterface::class);
        }

        return new LazySession(
            $container->get(SessionPersistenceInterface::class)
        );
    }
}
