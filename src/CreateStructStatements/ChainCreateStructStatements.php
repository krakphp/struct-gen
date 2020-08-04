<?php

namespace Krak\StructGen\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;
use Krak\StructGen\CreateStructStatementsArgs;
use Krak\StructGen\Internal\OptionsMap;
use PhpParser\Node\Stmt;

final class ChainCreateStructStatements implements CreateStructStatements
{
    private $createStructStatements;

    /**
     * @param CreateStructStatements[] $createStructStatements
     * @psalm-param array<string, CreateStructStatements> $createStructStatements
     */
    public function __construct(array $createStructStatements) {
        $this->createStructStatements = $createStructStatements;
    }

    public function __invoke(CreateStructStatementsArgs $args): array {
        return array_merge(...array_map(function(CreateStructStatements $css) use ($args) {
            return $css($args);
        }, $this->filteredCSSByOptions($args->options())));
    }

    /** return all css according to the options */
    private function filteredCSSByOptions(OptionsMap $options) {
        $namesToGenerate = $options->csv('generate');
        if (!$namesToGenerate) {
            return array_values($this->createStructStatements);
        }

        $filteredCss = [];
        foreach ($this->createStructStatements as $name => $css) {
            if (in_array($name, $namesToGenerate)) {
                $filteredCss[] = $css;
            }
        }

        return $filteredCss;
    }
}
