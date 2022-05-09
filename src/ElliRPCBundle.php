<?php

namespace Ellinaut\ElliRPCBundle;

use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\ErrorFactoryPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\ErrorTranslatorPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\ProcedureProcessorPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\ProcedureValidatorPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\TransactionListenerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Philipp Marien
 */
class ElliRPCBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ProcedureProcessorPass());
        $container->addCompilerPass(new ProcedureValidatorPass());
        $container->addCompilerPass(new TransactionListenerPass());
        $container->addCompilerPass(new ErrorFactoryPass());
        $container->addCompilerPass(new ErrorTranslatorPass());
    }
}
