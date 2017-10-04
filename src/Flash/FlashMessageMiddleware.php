<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-session for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-session/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Session\Flash;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;

class FlashMessageMiddleware implements MiddlewareInterface
{
    const FLASH_ATTRIBUTE = 'flash';

    /**
     * @var string
     */
    private $attributeKey;

    /**
     * @var callable
     */
    private $flashMessageFactory;

    /**
     * @var string
     */
    private $sessionKey;

    public function __construct(
        string $flashMessagesClass = FlashMessages::class,
        string $sessionKey = FlashMessagesInterface::FLASH_NEXT,
        string $attributeKey = self::FLASH_ATTRIBUTE
    ) {
        if (! class_exists($flashMessagesClass)
            || ! in_array(FlashMessagesInterface::class, class_implements($flashMessagesClass))
        ) {
            throw Exception\InvalidFlashMessagesImplementationException::forClass($flashMessagesClass);
        }

        $this->flashMessageFactory = [$flashMessagesClass, 'createFromSession'];
        $this->sessionKey = $sessionKey;
        $this->attributeKey = $attributeKey;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE, false);
        if (! $session instanceof SessionInterface) {
            throw Exception\MissingSessionException::forMiddleware($this);
        }

        $flashMessages = ($this->flashMessageFactory)($session, $this->sessionKey);

        return $delegate->process($request->withAttribute($this->attributeKey, $flashMessages));
    }
}
