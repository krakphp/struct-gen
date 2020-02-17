<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
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
                ->addStmt(new Stmt\Return_($args->factory()->new('self', array_map(function(Stmt\Property $prop) use ($args) {
                    $varDef = PropTypes::getVarDefinitionFromProperty($prop);
                    $type = $varDef ? $type = (new TypeParser())->parse($varDef) : null;
                    $shouldFromArray = PropTypes::canCallMethodsOnType($type);
                    $shouldCheckNullable = $shouldFromArray && $type->atomicTypes()[0]->nullable();
                    $shouldArrayMap = $shouldFromArray && $type->atomicTypes()[0]->isArray();

                    $propName = (string) $prop->props[0]->name;
                    $fetchExpr = $fetchFromArray = new Expr\ArrayDimFetch(
                        $args->factory()->var('data'),
                        $args->factory()->val($propName)
                    );
                    if ($shouldFromArray) {
                        $fetchExpr = $args->factory()->staticCall($type->atomicTypes()[0]->typeDefinition(), 'fromValidatedArray', [
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
                        $callFromArray = $args->factory()->staticCall($type->atomicTypes()[0]->typeDefinition(), 'fromValidatedArray', [
                            $args->factory()->var('value')
                        ]);
                        $fetchExpr = $args->factory()->funcCall('\array_map', [
                            new Expr\Closure([
                                'params' => [
                                    $args->factory()->param('value')
                                        ->setType($type->atomicTypes()[0]->nullable() ? '?array' : 'array')
                                        ->getNode()
                                ],
                                'returnType' => $type->atomicTypes()[0]->withIsArray(false)->toPhpString(),
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
                    return $prop->props[0]->default === null
                        ? $fetchExpr
                        : new Expr\Ternary(
                            $args->factory()->funcCall('array_key_exists', [$args->factory()->val($propName), $args->factory()->var('data')]),
                            $fetchExpr,
                            $prop->props[0]->default
                        );
                }, $args->class()->getProperties()))))
                ->getNode()
        ];
    }
}
