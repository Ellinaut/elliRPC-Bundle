<?php

namespace Ellinaut\ElliRPCBundle\Autoconfigure;

/**
 * @author Philipp Marien
 */
class ProcedureProcessorRegistry extends \Ellinaut\ElliRPC\Procedure\Processor\ProcedureProcessorRegistry
{
    public function autoRegister(DetectableProcedureProcessor $processor): void
    {
        $this->register($processor->getPackageName(), $processor->getProcedureName(), $processor);
    }
}
