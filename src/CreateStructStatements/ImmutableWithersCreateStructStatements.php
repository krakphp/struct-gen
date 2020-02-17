<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\PropTypes;
use PhpParser\Node\{Stmt, Expr};

final class ImmutableWithersCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        $methods = [];
        foreach ($args->class()->getProperties() as $prop) {
            if (count($prop->props) !== 1) {
                throw new \RuntimeException('Does not support property definitions that define more than one property.');
            }
            $propName = (string) $prop->props[0]->name;
            $method = $args->factory()->method('with' . ucfirst($propName))
                ->makePublic()
                ->addStmts([
                    new Expr\Assign($args->factory()->var('self'), new Expr\Clone_($args->factory()->var('this'))),
                    new Expr\Assign(
                        $args->factory()->propertyFetch($args->factory()->var('self'), $propName),
                        $args->factory()->var((string) $propName)
                    ),
                    new Stmt\Return_($args->factory()->var('self'))
                ])
                ->setReturnType('self')
                ->addParam(PropTypes::paramBuilderFromProp($args->factory(), $args->printer())($prop));

            if ($docComment = PropTypes::getParamsDocCommentForProps([$prop])) {
                $method->setDocComment($docComment);
            }

            $methods[] = $method->getNode();
        }
        return $methods;
    }
}
