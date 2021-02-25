<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\Props\ClassProp;
use Krak\StructGen\Internal\PropTypes;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class ConstructorCreateStructStatements implements CreateStructStatements
{
    public function __invoke(CreateStructStatementsArgs $args): array {
        if ($this->doesClassAlreadyContainConstructor($args->class())) {
            return [];
        }

        $method = $args->factory()->method('__construct')
            ->addParams(array_map(function(ClassProp $prop) use ($args) {
                return $prop->toParam($args->factory());
            }, $args->props()->toArray()))
            ->addStmts(array_map(function(Property $prop) use ($args) {
                $propName = (string) $prop->props[0]->name;
                return new Expr\Assign(
                    $args->factory()->propertyFetch($args->factory()->var('this'), $propName),
                    $args->factory()->var($propName)
                );
            }, $args->class()->getProperties()))
            ->makePublic();

        if ($docComment = $args->props()->getNeededDocParams()) {
            $method->setDocComment($docComment);
        }

        return [$method->getNode()];
    }

    private function doesClassAlreadyContainConstructor(Class_ $class): bool {
        foreach ($class->stmts as $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }
            if ($stmt->name->toLowerString() === '__construct') {
                return true;
            }
        }
        return false;
    }
}
