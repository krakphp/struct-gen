<?php

namespace Krak\StructGen\Internal;

final class AtomicType
{
    private $nullable;
    private $typeDefinition;
    private $genericTypeConstraints;
    private $isArray;
    private $callableDefinition;

    /** @param UnionType[] $genericTypeConstraints */
    public function __construct(string $typeDefinition, array $genericTypeConstraints = [], bool $nullable = false, bool $isArray = false, ?CallableDefinition $callableDefinition = null) {
        $this->nullable = $nullable;
        $this->typeDefinition = $typeDefinition;
        $this->genericTypeConstraints = $genericTypeConstraints;
        $this->isArray = $isArray;
        $this->callableDefinition = $callableDefinition;
    }

    public static function asUnion(string $typeDefinition, array $genericTypeConstraints = [], bool $nullable = false, bool $isArray = false, ?CallableDefinition $callableDefinition = null): UnionType {
        return new UnionType([new self($typeDefinition, $genericTypeConstraints, $nullable, $isArray, $callableDefinition)]);
    }

    public function toString(): string {
        $res = '';
        if ($this->nullable) {
            $res .= '?';
        }
        $res .= $this->typeDefinition;
        if ($this->genericTypeConstraints) {
            $res .= '<' . implode(', ', array_map(function(UnionType $type) {
                return $type->toString();
            }, $this->genericTypeConstraints)) . '>';
        }
        if ($this->isArray) {
            $res .= '[]';
        }

        return $res;
    }

    /** converts the type into a valid php string */
    public function toPhpString(): ?string {
        $res = '';
        if ($this->nullable) {
            $res .= '?';
        }
        if ($this->isArray) {
            return $res . 'array';
        }

        $res .= $this->typeDefinition;
        return $res;
    }

    public function nullable(): bool {
        return $this->nullable;
    }

    public function typeDefinition(): string {
        return $this->typeDefinition;
    }

    public function isScalar(): bool {
        return $this->typeDefinition === 'int'
            || $this->typeDefinition === 'float'
            || $this->typeDefinition === 'string'
            || $this->typeDefinition === 'bool';
    }

    public function isPrimitiveType(): bool {
        return $this->typeDefinition === 'array'
            || $this->typeDefinition === 'iterable'
            || $this->typeDefinition === 'callable'
            || $this->typeDefinition === 'object'
            || $this->isScalar();
    }

    /** @return UnionType[] */
    public function genericTypeConstraints(): array {
        return $this->genericTypeConstraints;
    }

    public function isArray(): bool {
        return $this->isArray;
    }
    
    public function withIsArray(bool $isArray): self {
        $self = clone $this;
        $self->isArray = $isArray;
        return $self;
    }

    public function callableDefinition(): ?CallableDefinition {
        return $this->callableDefinition;
    }
}
