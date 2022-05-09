<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien
 */
abstract class AbstractController
{
    public function __construct(
        private readonly HttpMessageFactoryInterface $httpFactory,
        private readonly HttpFoundationFactoryInterface $foundationFactory
    ) {
    }

    /**
     * @param Request $request
     * @return RequestInterface
     */
    protected function convertFromSymfonyRequest(Request $request): RequestInterface
    {
        return $this->httpFactory->createRequest($request);
    }

    /**
     * @param ResponseInterface $response
     * @return Response
     */
    protected function convertToSymfonyResponse(ResponseInterface $response): Response
    {
        return $this->foundationFactory->createResponse($response);
    }
}
