<?php

namespace Ellinaut\ElliRPCBundle\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @author Philipp Marien
 */
class AccessControlAllowOriginListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('Access-Control-Allow-Origin', '*');
        $event->getResponse()->headers->set('Access-Control-Allow-Credentials', 'true');
        $event->getResponse()->headers->set(
            'Access-Control-Allow-Methods',
            'GET,OPTIONS,POST,PUT,DELETE'
        );
        $event->getResponse()->headers->set(
            'Access-Control-Allow-Headers',
            implode(', ', [
                'Access-Control-Allow-Headers',
                'Access-Control-Request-Method',
                'Access-Control-Request-Headers',
                'Access-Control-Allow-Credentials',
                'Origin',
                'Authorization',
                'Content-Type',
                'Accept',
                'Accept-Language',
                'X-Requested-With',
            ])
        );
    }
}
