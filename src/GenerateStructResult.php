<?php

namespace Krak\StructGen;

use PhpParser\Node;

abstract class GenerateStructResult
{
    public static function inlineGeneratedCode(string $code): self {
        return new GenerateStructResultInlineGeneratedCode($code);
    }

    /** @param Node[] */
    public static function astNodes(array $ast) {
        return new GenerateStructResultASTNodes($ast);
    }
}

final class GenerateStructResultInlineGeneratedCode extends GenerateStructResult
{
    private $code;

    public function __construct(string $code) {
        $this->code = $code;
    }

    public function code(): string {
        return $this->code;
    }
}

final class GenerateStructResultASTNodes extends GenerateStructResult
{
    private $ast;

    /** @param Node[] $ast */
    public function __construct(array $ast) {
        $this->ast = $ast;
    }

    /** @return Node[] */
    public function ast(): array {
        return $this->ast;
    }

    public function isEmpty(): bool {
        return count($this->ast) === 0;
    }
}
