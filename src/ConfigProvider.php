<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'aliases' => [
                // Change this to the CsrfGuardFactoryInterface implementation you wish to use:
                Csrf\CsrfGuardFactoryInterface::class => Csrf\FlashCsrfGuardFactory::class,
            ],
            'invokables' => [
                Csrf\FlashCsrfGuardFactory::class   => Csrf\FlashCsrfGuardFactory::class,
                Csrf\SessionCsrfGuardFactory::class => Csrf\SessionCsrfGuardFactory::class,
                Flash\FlashMessageMiddleware::class => Flash\FlashMessageMiddleware::class,
            ],
            'factories' => [
                SessionMiddleware::class => SessionMiddlewareFactory::class,
                Csrf\CsrfMiddleware::class => Csr\CsrfMiddlewareFactory::class,
            ],
        ];
    }
}
