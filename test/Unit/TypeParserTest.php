<?php

namespace Krak\StructGen\Tests\Unit;

use Krak\Lex\LexException;
use Krak\StructGen\Internal\AtomicType;
use Krak\StructGen\Internal\CallableDefinition;
use Krak\StructGen\Internal\ParseException;
use Krak\StructGen\Internal\TypeParser;
use Krak\StructGen\Internal\UnionType;
use PHPUnit\Framework\TestCase;

final class TypeParserTest extends TestCase
{
    /** @dataProvider provide_valid_inputs */
    public function test_valid_inputs(string $input, UnionType $expectedType) {
        $parser = new TypeParser();
        $this->assertEquals($expectedType, $parser->parse($input));
    }

    public function provide_valid_inputs() {
        yield 'scalar' => ['string', AtomicType::asUnion('string')];
        yield 'nullable scalar' => ['?int', AtomicType::asUnion('int', [], true)];
        yield 'namespaced class' => ['\Acme\Foo', AtomicType::asUnion('\Acme\Foo')];
        yield 'nullable namespaced class' => ['?Acme\Foo', AtomicType::asUnion('Acme\Foo', [], true)];
        yield 'nullable scalar array' => ['?string[]', AtomicType::asUnion('string', [], true, true)];
        yield 'union type' => ['int|?string', new UnionType([
            new AtomicType('int'),
            new AtomicType('string', [], true)
        ])];
        yield 'generic type' => ['array<string>', AtomicType::asUnion('array', [AtomicType::asUnion('string')])];
        yield 'generic union type' => ['array<int|string, string>', AtomicType::asUnion('array', [
            new UnionType([new AtomicType('int'), new AtomicType('string')]),
            AtomicType::asUnion('string')
        ])];
        yield 'nested generic' => ['array<string, array<string>>', AtomicType::asUnion('array', [
            AtomicType::asUnion('string'),
            AtomicType::asUnion('array', [AtomicType::asUnion('string')])
        ])];
        yield 'callable' => ['callable', AtomicType::asUnion('callable')];
        yield 'callable with empty type params' => ['callable()', AtomicType::asUnion('callable', [], false, false, new CallableDefinition([]))];
        yield 'callable with type params' => ['callable(string, int|string)', AtomicType::asUnion('callable', [], false, false, new CallableDefinition([
            AtomicType::asUnion('string'),
            new UnionType([new AtomicType('int'), new AtomicType('string')])
        ]))];
        yield 'callable with return' => ['callable(): int', AtomicType::asUnion('callable', [], false, false, new CallableDefinition([], AtomicType::asUnion('int')))];
        yield 'nested callables' => ['callable(): callable(): callable(): int', AtomicType::asUnion('callable', [], false, false, new CallableDefinition([],
            AtomicType::asUnion('callable', [], false, false, new CallableDefinition([],
                AtomicType::asUnion('callable', [], false, false, new CallableDefinition([], AtomicType::asUnion('int')))
            ))
        ))];
    }

    /** @dataProvider provide_invalid_inputs */
    public function test_invalid_inputs(string $input, string $exceptionClass, string $message) {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);
        (new TypeParser())->parse($input);
    }

    public function provide_invalid_inputs() {
        yield 'lex exception' => ['string*', LexException::class, 'Unrecognized Input'];
        yield 'union without separate' => ['string int', ParseException::class, 'Expected eof, got: type'];
        yield 'dangling union' => ['string |', ParseException::class, 'Expected token type, callable|Closure for atomic type, got: eof'];
        yield 'empty generics' => ['array<>', ParseException::class, 'Generic constraints cannot be empty.'];
        yield 'only nullable' => ['?', ParseException::class, 'Expected token type, callable|Closure for atomic type, got: eof'];
        yield 'unclosed generics' => ['array<int', ParseException::class, 'Expected token > for argument list, got: eof'];
        yield 'trailing comma in generics' => ['array<int,>', ParseException::class, 'Expected token type, callable|Closure for atomic type, got: >'];
    }
}
