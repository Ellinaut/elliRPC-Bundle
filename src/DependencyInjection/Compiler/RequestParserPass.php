<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection\Compiler;

use Ellinaut\ElliRPC\RequestParser\RequestParserChain;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien
 */
class RequestParserPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    protected function getServiceId(): string
    {
        return RequestParserChain::class;
    }

    /**
     * @return string
     */
    protected function getTagName(): string
    {
        return 'ellirpc.request_parser';
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
                $config['priority'] ?? 5
            ]
        );
    }
}
