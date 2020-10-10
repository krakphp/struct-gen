<?php

namespace Krak\StructGen\Tests\Unit;

use Krak\StructGen\Internal\AtomicType;
use Krak\StructGen\Internal\UnionType;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    /** @dataProvider provide_types_to_strings */
    public function test_type_to_string(UnionType $type, string $expected, ?string $expectedPhpString) {
        $this->assertEquals($expected, $type->toString());
        $this->assertEquals($expectedPhpString, $type->toPhpString());
    }

    public function provide_types_to_strings() {
        yield 'scalar' => [
            'type' => AtomicType::asUnion('string'),
            'toString' => 'string',
            'toPhpString' => 'string'
        ];
        yield 'union scalar' => [
            'type' => new UnionType([new AtomicType('int'), new AtomicType('string')]),
            'toString' => 'int|string',
            'toPhpString' => null,
        ];
        yield 'nullable' => [
            'type' => AtomicType::asUnion('string', [], true),
            'toString' => '?string',
            'toPhpString' => '?string',
        ];
        yield 'scalar array' => [
            'type' => AtomicType::asUnion('string', [], false, true),
            'toString' => 'string[]',
            'toPhpString' => 'array',
        ];
        yield 'nullable scalar array' => [
            'type' => AtomicType::asUnion('string', [], true, true),
            'toString' => '?string[]',
            'toPhpString' => '?array',
        ];
        yield 'generic array' => [
            'type' => AtomicType::asUnion('array', [
                AtomicType::asUnion('string', []),
                AtomicType::asUnion('int', [], true)
            ]),
            'toString' => 'array<string, ?int>',
            'toPhpString' => 'array',
        ];
    }
}
