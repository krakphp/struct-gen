<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\PropTypes;
use Krak\StructGen\Internal\TypeParser;
use PhpParser\Node\Stmt;

final class GettersCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        $methods = [];
        foreach ($args->class()->getProperties() as $prop) {
            if (count($prop->props) !== 1) {
                throw new \RuntimeException('Does not support property definitions that define more than one property.');
            }
            $method = $args->factory()->method((string) $prop->props[0]->name)
                ->makePublic()
                ->addStmt(new Stmt\Return_($args->factory()->propertyFetch($args->factory()->var('this'), $prop->props[0]->name)));

            $varDef = PropTypes::getVarDefinitionFromProperty($prop);
            $type = $varDef ? (new TypeParser())->parse($varDef) : null;
            if ($type && $type->toPhpString() !== $type->toString()) {
                $method->setDocComment("/** @return {$type->toString()} */");
            }
            if ($type) {
                $method->setReturnType($type->toPhpString());
            }

            $methods[] = $method->getNode();
        }
        return $methods;
    }
}
