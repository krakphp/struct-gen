<?php

namespace Krak\StructGen;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinterAbstract;

interface CreateStructStatements
{
    /** @return Stmt[] */
    public function __invoke(CreateStructStatementsArgs $args): array;
}
