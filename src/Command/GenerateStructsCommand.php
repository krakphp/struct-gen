<?php

namespace Krak\StructGen\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function Krak\StructGen\{generateStructsForFiles, traversePhpFiles};

final class GenerateStructsCommand extends BaseCommand
{
    private $defaultPaths;
    private $basicTraverseFiles;

    public function __construct(array $defaultPaths = [], bool $basicTraverseFiles = false) {
        parent::__construct();
        $this->defaultPaths = $defaultPaths;
        $this->basicTraverseFiles = $basicTraverseFiles;
    }

    protected function configure() {
        $this->setName('struct-gen:generate')
            ->setDescription('Generate struct info for any matching classes')
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Path globs to search and list directories')
            ->addOption('basic-traverse-files', 'b', InputOption::VALUE_NONE, 'By default, this command will use symfony finder to traverse the list of paths which support glob patterns. If you do not want to include symfony finder, you can use the basic traverse files feature.', $this->basicTraverseFiles);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $paths = $input->getArgument('paths') ?: $this->defaultPaths;
        $basicTraverseFiles = $input->getOption('basic-traverse-files');
        $files = $basicTraverseFiles ? traversePhpFiles($paths) : (function() use ($paths) {
            $finder = new Finder();
            $finder->files()->name('*.php')->in($paths);
            return $finder;
        })();
        generateStructsForFiles($files, new ConsoleLogger($output));
    }
}
