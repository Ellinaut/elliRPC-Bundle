<?php

namespace Ellinaut\ElliRPCBundle\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @author Philipp Marien
 */
class AccessControlAllowOriginListener
{
    public function __construct(
        private readonly array $allowedOrigins
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $origin = $event->getRequest()->headers->get('Origin');
        if (!$origin) {
            return;
        }

        foreach ($this->allowedOrigins as $allowedOrigin) {
            if ($allowedOrigin === '*' || $origin === $allowedOrigin) {
                $event->getResponse()->setVary(['Origin']);
                $event->getResponse()->headers->set('Access-Control-Allow-Origin', $origin);
                $event->getResponse()->headers->set('Access-Control-Allow-Credentials', 'true');
                $event->getResponse()->headers->set(
                    'Access-Control-Allow-Methods',
                    'GET,HEAD,OPTIONS,POST,PUT,DELETE'
                );
                $event->getResponse()->headers->set(
                    'Access-Control-Allow-Headers',
                    implode(', ', $event->getRequest()->headers->keys())
                );
                break;
            }
        }
    }
}
