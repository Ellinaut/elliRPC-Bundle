<?php

namespace Ellinaut\ElliRPCBundle\Autoconfigure;

use Ellinaut\ElliRPC\Procedure\Processor\ProcedureProcessorInterface;

/**
 * @author Philipp Marien
 */
interface DetectableProcedureProcessor extends ProcedureProcessorInterface
{
    public function getPackageName(): string;

    public function getProcedureName(): string;
}
