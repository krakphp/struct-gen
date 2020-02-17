<?php

namespace Krak\StructGen\Bridge\Composer;

require_once __DIR__ . '/autoload.php';

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;

final class StructGenComposerPlugin implements PluginInterface, Capable
{
    public function activate(Composer $composer, IOInterface $io) {}

    public function getCapabilities() {
        return [CommandProvider::class => StructGenCommandProvider::class];
    }
}
