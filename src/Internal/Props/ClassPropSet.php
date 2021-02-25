<?php

namespace Krak\StructGen\Internal\Props;

use Krak\StructGen\Internal\TypeParser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;

final class ClassPropSet
{
    private $props;

    /** @param ClassProp[] */
    public function __construct(array $props) {
        $this->props = $props;
    }

    public static function fromClass(Class_ $class): self {
        return new self(array_map(function(Property $prop) {
            return ClassProp::fromProperty($prop);
        }, $class->getProperties()));
    }

    /** Return a set of doc params for all props that have types that can't be reflected completely in php */
    public function getNeededDocParams(): ?string {
        $partialDocComments = array_values(array_filter(array_map(function(ClassProp $prop) {
            if ($prop->type()->isEmpty()) return null;
            return !$prop->type()->canBeFullyExpressedInPhp() ? '@param ' . $prop->type()->toString() . ' $' . $prop->name() : null;
        }, $this->props)));
        if (!count($partialDocComments)) {
            return null;
        }

        if (count($partialDocComments) === 1) {
            return "/** {$partialDocComments[0]} */";
        }

        return "/**\n * " . implode("\n * ", $partialDocComments) . "\n */";
    }

    /** @return ClassProp[] */
    public function toArray(): array {
        return $this->props;
    }
}
