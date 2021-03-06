<?php

namespace Krak\StructGen\Bridge\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Krak\StructGen\Command\GenerateStructsCommand;

final class StructGenCommandProvider implements CommandProvider
{
    /** @var Composer */
    private $composer;
    /** @var IOInterface */
    private $io;

    public function __construct(array $args) {
        $this->composer = $args['composer'];
        $this->io = $args['io'];
    }

    public function getCommands() {
        $structGenConfig = StructGenComposerConfig::fromComposer($this->composer);
        return [
            new GenerateStructsCommand($structGenConfig)
        ];
    }
}
