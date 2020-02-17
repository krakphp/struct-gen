<?php

namespace Krak\StructGen;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinterAbstract;

final class CreateStructStatementsArgs
{
    private $factory;
    private $printer;
    private $class;

    public function __construct(BuilderFactory $factory, PrettyPrinterAbstract $printer, Class_ $class) {
        $this->factory = $factory;
        $this->printer = $printer;
        $this->class = $class;
    }

    public static function createFromClass(Class_ $class): self {
        return new self(
            new BuilderFactory(),
            new PrettyPrinter\Standard(),
            $class
        );
    }

    public function factory(): BuilderFactory {
        return $this->factory;
    }

    public function printer(): PrettyPrinterAbstract {
        return $this->printer;
    }

    public function class(): Class_ {
        return $this->class;
    }
}
