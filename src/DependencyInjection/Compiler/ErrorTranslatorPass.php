<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection\Compiler;

use Ellinaut\ElliRPC\Error\Factory\ErrorFactoryChain;
use Ellinaut\ElliRPC\Error\Translator\ErrorTranslatorChain;
use Ellinaut\ElliRPC\Procedure\Processor\ProcedureProcessorRegistry;
use Ellinaut\ElliRPC\Procedure\Validator\ProcedureValidatorChain;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien
 */
class ErrorTranslatorPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    protected function getServiceId(): string
    {
        return ErrorTranslatorChain::class;
    }

    /**
     * @return string
     */
    protected function getTagName(): string
    {
        return 'elli_rpc.error_translator';
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
