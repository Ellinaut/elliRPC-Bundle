<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection;

use Ellinaut\ElliRPC\Processor\ProcessorInterface;
use Ellinaut\ElliRPC\Processor\ProcessorRegistry;
use Ellinaut\ElliRPC\RequestParser\RequestParserChain;
use Ellinaut\ElliRPC\RequestParser\RequestParserInterface;
use Ellinaut\ElliRPC\RequestProcessor\RequestProcessorInterface;
use Ellinaut\ElliRPC\RequestProcessor\RequestProcessorRegistry;
use Ellinaut\ElliRPC\ResponseFactory\ResponseFactoryInterface;
use Ellinaut\ElliRPC\ResponseFactory\ResponseFactoryRegistry;
use Ellinaut\ElliRPC\Server;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Philipp Marien
 */
class ElliRPCExtension extends ConfigurableExtension
{
    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @return void
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->autowire(RequestParserChain::class)->setPublic(false);
        $container->setAlias(RequestParserInterface::class, RequestParserChain::class)->setPublic(false);

        $container->autowire(RequestProcessorRegistry::class)->setPublic(false);
        $container->setAlias(RequestProcessorInterface::class, RequestProcessorRegistry::class)->setPublic(false);

        $container->autowire(ProcessorRegistry::class)->setPublic(false);
        $container->setAlias(ProcessorInterface::class, ProcessorRegistry::class)->setPublic(false);

        $container->autowire(ResponseFactoryRegistry::class)->setPublic(false);
        $container->setAlias(ResponseFactoryInterface::class, ResponseFactoryRegistry::class)->setPublic(false);

        $container->autowire(Server::class)->setPublic(false);
    }
}
