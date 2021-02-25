<?php

namespace Krak\StructGen;

use Krak\StructGen\Internal\OptionsMap;
use Krak\StructGen\Internal\Props\ClassPropSet;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinterAbstract;

final class CreateStructStatementsArgs
{
    private $factory;
    private $printer;
    private $class;
    private $props;
    private $options;

    public function __construct(BuilderFactory $factory, PrettyPrinterAbstract $printer, Class_ $class, ?OptionsMap $options = null) {
        $this->factory = $factory;
        $this->printer = $printer;
        $this->class = $class;
        $this->props = ClassPropSet::fromClass($class);
        $this->options = $options ?: OptionsMap::empty();
    }

    public static function createFromClass(Class_ $class, ?OptionsMap $options = null): self {
        return new self(
            new BuilderFactory(),
            new PrettyPrinter\Standard(),
            $class,
            $options
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

    public function props(): ClassPropSet {
        return $this->props;
    }

    public function options(): OptionsMap {
        return $this->options;
    }
}
