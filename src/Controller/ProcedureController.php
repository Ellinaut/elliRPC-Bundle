<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Ellinaut\ElliRPC\RPCHandler;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @author Philipp Marien
 */
class ProcedureController extends AbstractController
{
    public function __construct(
        HttpMessageFactoryInterface $httpFactory,
        HttpFoundationFactoryInterface $foundationFactory,
        private readonly RPCHandler $rpcHandler
    ) {
        parent::__construct($httpFactory, $foundationFactory);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function executeProcedure(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->rpcHandler->executeExecuteProcedure(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function executeBulk(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->rpcHandler->executeExecuteBulk(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function executeTransaction(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->rpcHandler->executeExecuteTransaction(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }
}
