<?php

namespace Ellinaut\ElliRPCBundle\Autoconfigure;

/**
 * @author Philipp Marien
 */
abstract class AbstractDetectableProcedureProcessor implements DetectableProcedureProcessor
{
    public function getProcedureName(): string
    {
        $classParts = explode('\\', static::class);
        $className = $classParts[array_key_last($classParts)];

        return lcfirst(
            str_replace(
                ['DetectableProcessor', 'DetectableProcedureProcessor', 'Processor'],
                '',
                $className
            )
        );
    }
}
