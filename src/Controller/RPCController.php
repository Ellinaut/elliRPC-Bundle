<?php

namespace Ellinaut\ElliRPCBundle\Controller;

use Ellinaut\ElliRPC\Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Philipp Marien
 */
class RPCController
{
    /**
     * @var Server
     */
    private Server $rpcServer;

    /**
     * @param Server $rpcServer
     */
    public function __construct(Server $rpcServer)
    {
        $this->rpcServer = $rpcServer;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handleRPCRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->rpcServer->handleRequest($request);
    }
}
