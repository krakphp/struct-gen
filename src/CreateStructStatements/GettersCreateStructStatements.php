<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\Props\ClassProp;
use Krak\StructGen\Internal\PropTypes;
use Krak\StructGen\Internal\TypeParser;
use PhpParser\Node\Stmt;

final class GettersCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        return array_map(function(ClassProp $prop) use ($args) {
            $method = $args->factory()->method((string) $prop->name())
                ->makePublic()
                ->addStmt(new Stmt\Return_($args->factory()->propertyFetch($args->factory()->var('this'), $prop->name())));
            if (!$prop->type()->isEmpty() && !$prop->type()->canBeFullyExpressedInPhp()) {
                $method->setDocComment("/** @return {$prop->type()->toString()} */");
            }
            if ($prop->type()->toPhpString()) {
                $method->setReturnType($prop->type()->toPhpString());
            }
            return $method->getNode();
        }, $args->props()->toArray());
    }
}
