<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Session;

interface InitializePersistenceIdInterface
{
    /**
     * Returns new instance with id generated / regenerated, if required
     *
     * @param SessionInterface $session
     * @return SessionInterface
     */
    public function initializeId(SessionInterface $session) : SessionInterface;
}
