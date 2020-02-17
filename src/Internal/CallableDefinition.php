<?php

namespace Krak\StructGen\Internal;

final class CallableDefinition
{
    private $paramTypes;
    private $returnType;

    /** @param UnionType[] $paramTypes */
    public function __construct(array $paramTypes, ?UnionType $returnType = null) {
        $this->paramTypes = $paramTypes;
        $this->returnType = $returnType;
    }
}
