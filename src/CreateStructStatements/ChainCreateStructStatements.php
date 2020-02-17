<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use PhpParser\Node\Stmt;

final class ChainCreateStructStatements implements CreateStructStatements
{
    private $createStructStatements;

    public function __construct(CreateStructStatements ...$createStructStatements) {
        $this->createStructStatements = $createStructStatements;
    }

    public function __invoke(CreateStructStatementsArgs $args): array {
        return array_merge(...array_map(function(CreateStructStatements $css) use ($args) {
            return $css($args);
        }, $this->createStructStatements));
    }
}
