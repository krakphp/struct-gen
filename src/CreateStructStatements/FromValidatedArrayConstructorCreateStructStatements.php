<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\Props\ClassProp;
use Krak\StructGen\Internal\PropTypes;
use Krak\StructGen\Internal\TypeParser;
use PhpParser\Node\{Stmt, Expr};

final class FromValidatedArrayConstructorCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        return [
            $args->factory()->method('fromValidatedArray')
                ->makeStatic()
                ->makePublic()
                ->addParam($args->factory()->param('data')->setType('array')->getNode())
                ->setReturnType('self')
                ->addStmt(new Stmt\Return_($args->factory()->new('self', array_map(function(ClassProp $prop) use ($args) {
                    $shouldFromArray = PropTypes::canCallMethodsOnType($prop->type());
                    $shouldCheckNullable = $shouldFromArray && $prop->type()->atomicTypes()[0]->nullable();
                    $shouldArrayMap = $shouldFromArray && $prop->type()->atomicTypes()[0]->isArray();

                    $fetchExpr = $fetchFromArray = new Expr\ArrayDimFetch(
                        $args->factory()->var('data'),
                        $args->factory()->val($prop->name())
                    );
                    if ($shouldFromArray) {
                        $fetchExpr = $args->factory()->staticCall($prop->type()->atomicTypes()[0]->typeDefinition(), 'fromValidatedArray', [
                            $fetchFromArray
                        ]);
                    }
                    if ($shouldCheckNullable) {
                        $fetchExpr = new Expr\Ternary(
                            new Expr\BinaryOp\Identical($fetchFromArray, $args->factory()->val(null)),
                            $args->factory()->val(null),
                            $fetchExpr
                        );
                    }
                    if ($shouldArrayMap) {
                        $callFromArray = $args->factory()->staticCall($prop->type()->atomicTypes()[0]->typeDefinition(), 'fromValidatedArray', [
                            $args->factory()->var('value')
                        ]);
                        $fetchExpr = $args->factory()->funcCall('\array_map', [
                            new Expr\Closure([
                                'params' => [
                                    $args->factory()->param('value')
                                        ->setType($prop->type()->atomicTypes()[0]->nullable() ? '?array' : 'array')
                                        ->getNode()
                                ],
                                'returnType' => $prop->type()->atomicTypes()[0]->withIsArray(false)->toPhpString(),
                                'stmts' => [
                                    new Stmt\Return_($shouldCheckNullable
                                        ? new Expr\Ternary(
                                            new Expr\BinaryOp\Identical($args->factory()->var('value'), $args->factory()->val(null)),
                                            $args->factory()->val(null),
                                            $callFromArray
                                        )
                                        : $callFromArray
                                    )
                                ]
                            ]),
                            $fetchFromArray
                        ]);
                    }
                    return $prop->default() === null
                        ? $fetchExpr
                        : new Expr\Ternary(
                            $args->factory()->funcCall('array_key_exists', [$args->factory()->val($prop->name()), $args->factory()->var('data')]),
                            $fetchExpr,
                            $prop->default()
                        );
                }, $args->props()->toArray()))))
                ->getNode()
        ];
    }
}
