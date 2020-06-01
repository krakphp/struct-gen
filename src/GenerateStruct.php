<?php

namespace Krak\StructGen;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

/**
 * Takes the contents of a file that contains class definitions
 * and generates struct data for any classes which include a trait
 * suffixed with Struct
 */
final class GenerateStruct
{
    private $createStructStatements;

    public function __construct(?CreateStructStatements $createStructStatements = null) {
        $this->createStructStatements = $createStructStatements ?: new CreateStructStatements\ChainCreateStructStatements(
            new CreateStructStatements\ConstructorCreateStructStatements(),
            new CreateStructStatements\FromValidatedArrayConstructorCreateStructStatements(),
            new CreateStructStatements\ToArrayCreateStructStatements(),
            new CreateStructStatements\GettersCreateStructStatements(),
            new CreateStructStatements\ImmutableWithersCreateStructStatements()
        );
    }

    public function __invoke(string $code): string {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        if (!$ast) {
            return $code;
        }
        $nodeFinder = new NodeFinder;
        $factory = new BuilderFactory();
        $classesToGenerateStructs = $this->findClassesToGenerateStructs($nodeFinder, $ast);
        if (!$classesToGenerateStructs) {
            return $code;
        }

        $alreadyExistingGeneratedTraits = $this->findAlreadyExistingGeneratedTraits($nodeFinder, $ast, $classesToGenerateStructs);
        $code = $this->removeExistingTraitsFromOriginalCode($code, $alreadyExistingGeneratedTraits);

        $printer = new \PhpParser\PrettyPrinter\Standard();
        $traits = array_map(function(Class_ $class) use ($factory, $printer) {
            assert($class->name !== null); // Should not be null as we've checked from above that it's not null
            return $factory->trait($class->name . 'Struct')
                ->addStmts(($this->createStructStatements)(new CreateStructStatementsArgs($factory, $printer, $class)))
                ->getNode();
        }, $classesToGenerateStructs);

        return trim($code) . "\n\n" . $printer->prettyPrint($traits);
    }

    /** @return Class_[] */
    private function findClassesToGenerateStructs(NodeFinder $nodeFinder, array $ast): array {
        return $nodeFinder->find($ast, function(Node $node) {
            if (!$node instanceof Class_ || !$node->name) {
                return false;
            }

            foreach ($node->getTraitUses() as $usedTrait) {
                foreach ($usedTrait->traits as $trait) {
                    if ($trait->getFirst() === $node->name . 'Struct') {
                        return true;
                    }
                }
            }

            return false;
        });
    }

    /**
     * @param Class_[] $classesToGenerateStructs
     * @return \PhpParser\Node\Stmt\Trait_[]
     */
    private function findAlreadyExistingGeneratedTraits(NodeFinder $nodeFinder, array $ast, array $classesToGenerateStructs): array {
        return $nodeFinder->find($ast, function(Node $node) use ($classesToGenerateStructs) {
            if (!$node instanceof Node\Stmt\Trait_) {
                return false;
            }

            foreach ($classesToGenerateStructs as $class) {
                if ($class->name && $class->name . 'Struct' === (string) $node->name) {
                    return true;
                }
            }

            return false;
        });
    }

    /** @param Node\Stmt\Trait_[] $traits */
    private function removeExistingTraitsFromOriginalCode(string $code, array $traits): string {
        if (!$traits) {
            return $code;
        }

        $linesOfCode = explode("\n", $code);
        $removedLineCount = 0;

        foreach ($traits as $trait) {
            $startLine = $trait->getStartLine() - $removedLineCount - 1;
            $endLine = $trait->getEndLine() - $removedLineCount;

            array_splice($linesOfCode, $startLine, $endLine - $startLine);

            $removedLineCount += $endLine - $startLine;
        }

        return implode("\n", $linesOfCode);
    }
}
