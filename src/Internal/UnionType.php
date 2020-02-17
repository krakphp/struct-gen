<?php

namespace Krak\StructGen\Internal;

final class UnionType
{
    private $atomicTypes;

    /** @param AtomicType[] $atomicTypes */
    public function __construct(array $atomicTypes) {
        $this->atomicTypes = $atomicTypes;
    }

    public function toString(): string {
        return implode('|', array_map(function(AtomicType $type) {
            return $type->toString();
        }, $this->atomicTypes));
    }

    /** Converts type into a valid php type representation */
    public function toPhpString(): string {
        return $this->atomicTypes[0]->toPhpString();
    }

    public function atomicTypes(): array {
        return $this->atomicTypes;
    }
}
