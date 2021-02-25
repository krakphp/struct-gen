<?php

namespace Krak\StructGen\Internal\Props;

use Krak\StructGen\Internal\DocBlock;
use Krak\StructGen\Internal\TypeParser;
use Krak\StructGen\Internal\UnionType;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter;

/** Internal representation of a class property along with its type */
final class ClassProp
{
    private $name;
    private $type;
    private $default;

    public function __construct(string $name, UnionType $type, ?Expr $default = null) {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
    }

    public static function fromProperty(Property $prop): self {
        if (count($prop->props) !== 1) {
            throw new \RuntimeException('Multiple property definitions are not supported.');
        }

        $firstProp = $prop->props[0];
        $varDef = self::getVarDefinitionFromProperty($prop);
        return new self(
            (string) $firstProp->name,
            TypeParser::fromString($varDef),
            $firstProp->default
        );
    }

    public function getParamDocCommentIfNeeded(): ?string {
        return (new ClassPropSet([$this]))->getNeededDocParams();
    }

    public function toParam(BuilderFactory $builderFactory): Param {
        $param = $builderFactory->param($this->name);
        if ($this->default) {
            $param->setDefault($this->default);
        }
        if ($type = $this->type->toPhpString()) {
            $param->setType($type);
        }
        return $param;
    }

    private static function getVarDefinitionFromProperty(Property $prop): ?string {
        if (!$prop->getDocComment()) {
            return $prop->type ? (new PrettyPrinter\Standard)->prettyPrint([$prop->type]) : null;
        }

        $docBlock = DocBlock::stripComments($prop->getDocComment()->getText());

        $matches = [];
        if (!preg_match('/@var ((?:(?!\*\/).)+)(\*\/)?$/', $docBlock, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    public function name(): string {
        return $this->name;
    }

    public function type(): UnionType {
        return $this->type;
    }

    public function default(): ?Expr {
        return $this->default;
    }
}
