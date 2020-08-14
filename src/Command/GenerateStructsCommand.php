<?php

namespace Krak\StructGen\Command;

use Composer\Command\BaseCommand;
use Krak\StructGen\Bridge\Composer\StructGenComposerConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function Krak\StructGen\{
    generateStructsExternallyForFiles,
    generateStructsForFiles,
    traversePhpFiles
};

final class GenerateStructsCommand extends BaseCommand
{
    private $config;

    public function __construct(StructGenComposerConfig $config) {
        $this->config = $config;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('struct-gen:generate')
            ->setDescription('Generate struct info for any matching classes')
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Path globs to search and list directories', $this->config->paths())
            ->addOption('generated-path', 'g', InputOption::VALUE_REQUIRED, 'If set, the file path to generate the structs into', $this->config->generatedPath())
            ->addOption('basic-traverse-files', 'b', InputOption::VALUE_NONE, 'By default, this command will use symfony finder to traverse the list of paths which support glob patterns. If you do not want to include symfony finder, you can use the basic traverse files feature.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $paths = $input->getArgument('paths') ?: $this->config->paths();
        $generatedPath = $input->getOption('generated-path');
        $basicTraverseFiles = $input->getOption('basic-traverse-files');
        $files = $basicTraverseFiles ? traversePhpFiles($paths) : (function() use ($paths) {
            $finder = new Finder();
            $finder->files()->name('*.php')->in($paths);
            return $finder;
        })();

        if ($generatedPath) {
            generateStructsExternallyForFiles($files, $generatedPath, new ConsoleLogger($output));
        } else {
            generateStructsForFiles($files, new ConsoleLogger($output));
        }
    }
}
