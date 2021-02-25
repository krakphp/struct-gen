<?php

namespace Krak\StructGen\Internal;

final class UnionType
{
    private $atomicTypes;

    /** @param AtomicType[] $atomicTypes */
    public function __construct(array $atomicTypes) {
        $this->atomicTypes = $atomicTypes;
    }

    public static function empty(): self {
        return new self([]); // represents no type definition
    }

    public function toString(): string {
        return implode('|', array_map(function(AtomicType $type) {
            return $type->toString();
        }, $this->atomicTypes));
    }

    public function canBeFullyExpressedInPhp(): bool {
        return $this->toString() === $this->toPhpString();
    }

    public function isEmpty(): bool {
        return count($this->atomicTypes) === 0;
    }

    /** Converts type into a valid php type representation */
    public function toPhpString(): ?string {
        if (count($this->atomicTypes) !== 1) {
            return null; // no type is wide enough, use implicit dynamic type with null
        }

        return $this->atomicTypes[0]->toPhpString();
    }

    public function atomicTypes(): array {
        return $this->atomicTypes;
    }
}
