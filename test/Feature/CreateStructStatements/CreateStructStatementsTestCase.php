<?php

namespace Krak\StructGen\Tests\Feature\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PHPUnit\Framework\TestCase;

abstract class CreateStructStatementsTestCase extends TestCase
{
    abstract public function createStructStatements(): CreateStructStatements;

    public function assertStructStatements(string $beforeClass, string $expectedTrait) {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($beforeClass);
        $nodeFinder = new NodeFinder();
        /** @var Node\Stmt\Class_ $class */
        $class = $nodeFinder->findFirst($ast, function(Node $node) {
            return $node instanceof Node\Stmt\Class_;
        });
        if (!$class) {
            throw new \RuntimeException('No class is found from initial code.');
        }
        $stmts = ($this->createStructStatements())(CreateStructStatementsArgs::createFromClass($class));
        $printer = new PrettyPrinter\Standard();
        $this->assertEquals($expectedTrait, $printer->prettyPrint($stmts));
    }
}
