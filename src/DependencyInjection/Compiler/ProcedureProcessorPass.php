<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection\Compiler;

use Ellinaut\ElliRPCBundle\Autoconfigure\ProcedureProcessorRegistry;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien
 */
class ProcedureProcessorPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    protected function getServiceId(): string
    {
        return ProcedureProcessorRegistry::class;
    }

    /**
     * @return string
     */
    protected function getTagName(): string
    {
        return 'elli_rpc.procedure_processor';
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
                $config['package'],
                $config['procedure'],
                new Reference($serviceId),
            ]
        );
    }
}
