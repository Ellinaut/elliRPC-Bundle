<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection\Compiler;

use Ellinaut\ElliRPC\Error\Factory\ErrorFactoryChain;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien
 */
class ErrorFactoryPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    protected function getServiceId(): string
    {
        return ErrorFactoryChain::class;
    }

    /**
     * @return string
     */
    protected function getTagName(): string
    {
        return 'elli_rpc.error_factory';
    }

    /**
     * @param Definition $definition
     * @param string $serviceId
     * @param array $config
     */
    protected function modifyDefinition(Definition $definition, string $serviceId, array $config): void
    {
        $definition->addMethodCall(
            'register',
            [
                new Reference($serviceId),
            ]
        );
    }
}
