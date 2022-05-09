<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection\Compiler;

use Ellinaut\ElliRPC\Procedure\Processor\ProcedureProcessorRegistry;
use Ellinaut\ElliRPC\Procedure\Transaction\TransactionManager;
use Ellinaut\ElliRPC\Procedure\Transaction\TransactionManagerInterface;
use Ellinaut\ElliRPC\Procedure\Validator\ProcedureValidatorChain;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien
 */
class TransactionListenerPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    protected function getServiceId(): string
    {
        return TransactionManagerInterface::class;
    }

    /**
     * @return string
     */
    protected function getTagName(): string
    {
        return 'elli_rpc.transaction_listener';
    }

    /**
     * @param Definition $definition
     * @param string $serviceId
     * @param array $config
     */
    protected function modifyDefinition(Definition $definition, string $serviceId, array $config): void
    {
        $definition->addMethodCall(
            'registerListener',
            [
                new Reference($serviceId),
            ]
        );
    }
}
