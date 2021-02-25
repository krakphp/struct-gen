<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\Props\ClassProp;
use Krak\StructGen\Internal\Props\ClassPropSet;
use Krak\StructGen\Internal\PropTypes;
use PhpParser\Node\{Stmt, Expr};

final class ImmutableWithersCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        return array_map(function(ClassProp $prop) use ($args) {
            $method = $args->factory()->method('with' . ucfirst($prop->name()))
                ->makePublic()
                ->addStmts([
                    new Expr\Assign($args->factory()->var('self'), new Expr\Clone_($args->factory()->var('this'))),
                    new Expr\Assign(
                        $args->factory()->propertyFetch($args->factory()->var('self'), $prop->name()),
                        $args->factory()->var($prop->name())
                    ),
                    new Stmt\Return_($args->factory()->var('self'))
                ])
                ->setReturnType('self')
                ->addParam($prop->toParam($args->factory()))
            ;

            if ($docComment = $prop->getParamDocCommentIfNeeded()) {
                $method->setDocComment($docComment);
            }
            return $method->getNode();
        }, $args->props()->toArray());
    }
}
