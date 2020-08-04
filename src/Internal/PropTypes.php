<?php

namespace Krak\StructGen\Internal;

use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinterAbstract;

final class PropTypes
{
    private function __construct() {}

    /** @param Property[] $props */
    public static function getParamsDocCommentForProps(array $props): ?string {
        $partialDocComments = array_values(array_filter(array_map(function(Property $property) {
            $varDefinition = self::getVarDefinitionFromProperty($property);
            if (!$varDefinition) {
                return null;
            }

            $type = (new TypeParser())->parse($varDefinition);
            return $type->toString() !== $type->toPhpString() ? '@param ' . $type->toString() . ' $' . $property->props[0]->name : null;
        }, $props)));
        if (!count($partialDocComments)) {
            return null;
        }

        if (count($partialDocComments) === 1) {
            return "/** {$partialDocComments[0]} */";
        }

        return "/**\n * " . implode("\n * ", $partialDocComments) . "\n */";
    }

    public static function paramBuilderFromProp(BuilderFactory $builderFactory, PrettyPrinterAbstract $printer): \Closure {
        return function(Property $prop) use ($builderFactory, $printer): Param {
            $propName = (string) $prop->props[0]->name;
            $param = $builderFactory->param($propName);
            $varDef = PropTypes::getVarDefinitionFromProperty($prop);
            if ($varDef) {
                $type = (new TypeParser())->parse($varDef);
                $param->setType($type->toPhpString());
            }
            if ($prop->props[0]->default) {
                $param->setDefault($prop->props[0]->default);
            }
            return $param;
        };
    }

    /** Is the property's type an object that likely has it's own methods that can be called? */
    public static function canCallMethodsOnType(?UnionType $type): bool {
        if (!$type) {
            return false;
        }
        if (count($type->atomicTypes()) > 1) {
            return false;
        }
        $atomicType = $type->atomicTypes()[0];
        return !$atomicType->isPrimitiveType();
    }


    public static function getVarDefinitionFromProperty(Property $prop): ?string {
        if (!$prop->getDocComment()) {
            return null;
        }

        $docBlock = DocBlock::stripComments($prop->getDocComment()->getText());

        $matches = [];
        if (!preg_match('/@var ((?:(?!\*\/).)+)(\*\/)?$/', $docBlock, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }
}
