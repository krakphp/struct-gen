<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\Props\ClassProp;
use Krak\StructGen\Internal\PropTypes;
use Krak\StructGen\Internal\TypeParser;
use Krak\StructGen\Internal\UnionType;
use PhpParser\BuilderFactory;
use PhpParser\Node\{Stmt, Expr};

final class ToArrayCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        return [
            $args->factory()->method('toArray')
                ->makePublic()
                ->setReturnType('array')
                ->addStmt(new Stmt\Return_(new Expr\Array_(array_map(function(ClassProp $prop) use ($args) {
                    return new Expr\ArrayItem(
                        $this->accessPropertyExpr($args->factory(), $prop),
                        $args->factory()->val($prop->name())
                    );
                }, $args->props()->toArray()), ['kind' => Expr\Array_::KIND_SHORT])))
                ->getNode()
        ];
    }

    private function accessPropertyExpr(BuilderFactory $factory, ClassProp $prop): Expr {
        $shouldToArray = PropTypes::canCallMethodsOnType($prop->type());
        $shouldCheckNullable = $shouldToArray && $prop->type()->atomicTypes()[0]->nullable();
        $shouldArrayMap = $shouldToArray && $prop->type()->atomicTypes()[0]->isArray();

        $propExpr = $propFetch = $factory->propertyFetch($factory->var('this'), $prop->name());
        if ($shouldToArray) {
            $propExpr = $factory->methodCall($propExpr, 'toArray');
        }
        if ($shouldCheckNullable) {
            $propExpr = new Expr\Ternary(new Expr\BinaryOp\Identical($propFetch, $factory->val(null)), $factory->val(null), $propExpr);
        }
        if ($shouldArrayMap) {
            $callToArray = $factory->methodCall($factory->var('value'), 'toArray');
            $propExpr = $factory->funcCall('\array_map', [
                new Expr\Closure([
                    'params' => [
                        $factory->param('value')
                            ->setType($prop->type()->atomicTypes()[0]->withIsArray(false)->toPhpString())
                            ->getNode()
                    ],
                    'returnType' => $prop->type()->atomicTypes()[0]->nullable() ? '?array' : 'array',
                    'stmts' => [
                        new Stmt\Return_($shouldCheckNullable
                            ? new Expr\Ternary(new Expr\BinaryOp\Identical($factory->var('value'), $factory->val(null)), $factory->val(null), $callToArray) : $callToArray)
                    ]
                ]),
                $propFetch
            ]);
        }

        return $propExpr;
    }
}
