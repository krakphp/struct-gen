<?php

namespace Krak\StructGen\Tests\Unit;

use Krak\StructGen\Internal\DocBlock;
use PHPUnit\Framework\TestCase;

final class DocBlockTest extends TestCase
{
    /**
     * @test
     * @dataProvider provide_for_strips_comments
     */
    public function strips_comments(string $docBlock, string $expected) {
        $this->assertEquals($expected, DocBlock::stripComments($docBlock));
    }

    public function provide_for_strips_comments() {
        yield 'empty string returns empty string' => ['', ''];
        yield 'single line doc block' => ['/** @var string */', '@var string'];
        yield 'disjoint single line doc block' => ["/** @\n  var\n  string\n  */", "@\n  var\n  string"];
        yield 'multi line doc block' => ["/**\n  * @var string\n  */", '@var string'];
    }
}
