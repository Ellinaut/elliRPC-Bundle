<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Ellinaut\ElliRPC\FileHandler;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien
 */
class FileController extends AbstractController
{
    public function __construct(
        HttpMessageFactoryInterface $httpFactory,
        HttpFoundationFactoryInterface $foundationFactory,
        private readonly FileHandler $fileHandler
    ) {
        parent::__construct($httpFactory, $foundationFactory);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getFile(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->fileHandler->executeGetFile(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function uploadFile(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->fileHandler->executeUploadFile(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function deleteFile(Request $request): Response
    {
        return $this->convertToSymfonyResponse(
            $this->fileHandler->executeDeleteFile(
                $this->convertFromSymfonyRequest($request)
            )
        );
    }
}
