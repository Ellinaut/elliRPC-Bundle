<?php

namespace Ellinaut\ElliRPCBundle;

use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\ProcedureProcessorPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\RequestParserPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\RequestProcessorPass;
use Ellinaut\ElliRPCBundle\DependencyInjection\Compiler\ResponseFactoryPass;
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
        $container->addCompilerPass(new RequestParserPass());
        $container->addCompilerPass(new RequestProcessorPass());
        $container->addCompilerPass(new ResponseFactoryPass());
    }
}
