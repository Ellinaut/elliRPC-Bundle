<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien
 */
class CORSController
{
    public function respondSuccess(): Response
    {
        return new Response();
    }
}
