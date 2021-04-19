<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Ellinaut\ElliRPC\Server;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien
 */
class RPCController
{
    /**
     * @var HttpMessageFactoryInterface
     */
    private HttpMessageFactoryInterface $httpFactory;

    /**
     * @var HttpFoundationFactoryInterface
     */
    private HttpFoundationFactoryInterface $foundationFactory;

    /**
     * @var Server
     */
    private Server $rpcServer;

    /**
     * @param HttpMessageFactoryInterface $httpFactory
     * @param HttpFoundationFactoryInterface $foundationFactory
     * @param Server $rpcServer
     */
    public function __construct(
        HttpMessageFactoryInterface $httpFactory,
        HttpFoundationFactoryInterface $foundationFactory,
        Server $rpcServer
    ) {
        $this->httpFactory = $httpFactory;
        $this->foundationFactory = $foundationFactory;
        $this->rpcServer = $rpcServer;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handleRPCRequest(Request $request): Response
    {
        return $this->foundationFactory->createResponse(
            $this->rpcServer->handleRequest(
                $this->httpFactory->createRequest($request)
            )
        );
    }
}
