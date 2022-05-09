<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Ellinaut\ElliRPC\DefinitionHandler;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @author Philipp Marien
 */
class DefinitionController extends AbstractController
{
    public function __construct(
        HttpMessageFactoryInterface $httpFactory,
        HttpFoundationFactoryInterface $foundationFactory,
        private readonly DefinitionHandler $definitionHandler
    ) {
        parent::__construct($httpFactory, $foundationFactory);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function getDocumentation(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->definitionHandler->executeGetDocumentation(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function getPackageDefinition(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->definitionHandler->executeGetPackageDefinition(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }
}
