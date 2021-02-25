<?php

namespace Krak\StructGen\Internal;

use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinterAbstract;

final class PropTypes
{
    private function __construct() {
    }

    /** Is the property's type an object that likely has it's own methods that can be called? */
    public static function canCallMethodsOnType(?UnionType $type): bool {
        if (!$type || $type->isEmpty()) {
            return false;
        }
        if (count($type->atomicTypes()) > 1) {
            return false;
        }
        $atomicType = $type->atomicTypes()[0];
        return !$atomicType->isPrimitiveType();
    }
}
