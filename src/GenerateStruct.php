<?php

namespace Krak\StructGen;

use Krak\StructGen\Internal\OptionsMap;
use PhpParser\Builder\Namespace_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinterAbstract;

/**
 * Takes the contents of a file that contains class definitions
 * and generates struct data for any classes which include a trait
 * suffixed with Struct
 */
final class GenerateStruct
{
    private $createStructStatements;

    public function __construct(?CreateStructStatements $createStructStatements = null) {
        $this->createStructStatements = $createStructStatements ?: new CreateStructStatements\ChainCreateStructStatements([
            'constructor' => new CreateStructStatements\ConstructorCreateStructStatements(),
            'from-validated-array' => new CreateStructStatements\FromValidatedArrayConstructorCreateStructStatements(),
            'to-array' => new CreateStructStatements\ToArrayCreateStructStatements(),
            'getters' => new CreateStructStatements\GettersCreateStructStatements(),
            'withers' => new CreateStructStatements\ImmutableWithersCreateStructStatements()
        ]);
    }

    public function __invoke(GenerateStructArgs $args): GenerateStructResult {
        $code = $args->code();
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        if (!$ast) {
            return $args->generateInline() ? GenerateStructResult::inlineGeneratedCode($code) : GenerateStructResult::astNodes([]);
        }
        $nodeFinder = new NodeFinder;
        $factory = new BuilderFactory();
        $classesToGenerateStructs = $this->findClassesToGenerateStructs($nodeFinder, $ast);
        if (!$classesToGenerateStructs) {
            return $args->generateInline() ? GenerateStructResult::inlineGeneratedCode($code) : GenerateStructResult::astNodes([]);
        }

        $alreadyExistingGeneratedTraits = $this->findAlreadyExistingGeneratedTraits($nodeFinder, $ast, $classesToGenerateStructs);
        $code = $this->removeExistingTraitsFromOriginalCode($code, $alreadyExistingGeneratedTraits);

        $printer = new \PhpParser\PrettyPrinter\Standard();
        $traits = array_map(function(Class_ $class) use ($factory, $printer) {
            assert($class->name !== null); // Should not be null as we've checked from above that it's not null
            return $factory->trait($class->name . 'Struct')
                ->addStmts(($this->createStructStatements)(new CreateStructStatementsArgs(
                    $factory,
                    $printer,
                    $class,
                    $this->createOptionsForClass($class))
                ))
                ->getNode();
        }, $classesToGenerateStructs);

        return $args->generateInline()
            ? $this->prepareInlineCode($code, $traits, $printer)
            : $this->prepareExternalCode($ast, $traits);
    }

    /** appends the traits directly to the original code */
    private function prepareInlineCode(string $code, array $traits, PrettyPrinterAbstract $printer): GenerateStructResult {
        return GenerateStructResult::inlineGeneratedCode(trim($code) . "\n\n" . $printer->prettyPrint($traits));
    }

    /**
     * returns the traits as is in order to be compiled into an external file
     * @param Node[] $ast
     * @param Node[] $traits
     */
    private function prepareExternalCode(array $ast, array $traits): GenerateStructResult {
        $this->assertZeroOrOneNamespaceInAST($ast);

        /** @var Node\Stmt\Namespace_ $namespaceStmt */
        $namespaceStmt = (new NodeFinder())->findFirstInstanceOf($ast, Node\Stmt\Namespace_::class) ?? (new Namespace_(null))->getNode();
        $namespaceStmt->stmts = $traits;

        return GenerateStructResult::astNodes([$namespaceStmt]);
    }

    /** @param Node[] $ast */
    private function assertZeroOrOneNamespaceInAST(array $ast) {
        $res = (new NodeFinder())->findInstanceOf($ast, Node\Stmt\Namespace_::class);
        if (count($res) <= 1) {
            return;
        }

        throw new \RuntimeException('External struct generation does not currently support multiple namespaces in one source.');
    }

    /** @return Class_[] */
    private function findClassesToGenerateStructs(NodeFinder $nodeFinder, array $ast): array {
        return $nodeFinder->find($ast, function(Node $node) {
            if (!$node instanceof Class_ || !$node->name) {
                return false;
            }

            $traitUse = $this->findTraitUseWithGeneratedStructTrait($node);
            return $traitUse !== null;
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

    private function createOptionsForClass(Class_ $class): OptionsMap {
        $traitUse = $this->findTraitUseWithGeneratedStructTrait($class);
        return $traitUse->getDocComment()
            ? OptionsMap::fromDocBlock($traitUse->getDocComment()->getText())
            : OptionsMap::empty();
    }

    private function findTraitUseWithGeneratedStructTrait(Class_ $node): ?Node\Stmt\TraitUse {
        foreach ($node->getTraitUses() as $usedTrait) {
            foreach ($usedTrait->traits as $trait) {
                if ($trait->getFirst() === $node->name . 'Struct') {
                    return $usedTrait;
                }
            }
        }

        return null;
    }
}
