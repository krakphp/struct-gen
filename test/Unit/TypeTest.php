<?php

namespace Krak\StructGen\Tests\Unit;

use Krak\StructGen\Internal\AtomicType;
use Krak\StructGen\Internal\UnionType;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    /** @dataProvider provide_types_to_strings */
    public function test_type_to_string(UnionType $type, string $expected) {
        $this->assertEquals($expected, $type->toString());
    }

    public function provide_types_to_strings() {
        yield 'scalar' => [AtomicType::asUnion('string'), 'string'];
        yield 'union scalar' => [new UnionType([new AtomicType('int'), new AtomicType('string')]), 'int|string'];
        yield 'nullable' => [AtomicType::asUnion('string', [], true), '?string'];
        yield 'scalar array' => [AtomicType::asUnion('string', [], false, true), 'string[]'];
        yield 'generics' => [AtomicType::asUnion('array', [
            AtomicType::asUnion('string', []),
            AtomicType::asUnion('int', [], true)
        ]), 'array<string, ?int>'];
    }
}
