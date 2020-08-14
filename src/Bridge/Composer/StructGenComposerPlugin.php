<?php

namespace Krak\StructGen\Bridge\Composer;

require_once __DIR__ . '/autoload.php';

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;

final class StructGenComposerPlugin implements PluginInterface, Capable, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io) {}

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents() {
        return [
            'pre-autoload-dump' => 'onPreAutoloadDump',
        ];
    }

    public function onPreAutoloadDump(Event $event) {
        $config = StructGenComposerConfig::fromComposer($event->getComposer());
        if ($config->generatedPath() === null || !file_exists($config->generatedPath())) {
            $this->writeLog($event, "<warning>Skipping struct-gen autoload modification because generatedPath does not exist.</warning>", IOInterface::DEBUG);
            return;
        }

        $package = $event->getComposer()->getPackage();
        $autoload = $package->getAutoload();
        $classmap = $autoload['classmap'] ?? [];
        if (!in_array($config->generatedPath(), $classmap)) {
            $this->writeLog($event, "<info>Adding {$config->generatedPath()} to autoload.classmap</info>");
            $classmap[] = $config->generatedPath();
            $autoload['classmap'] = $classmap;
        }
        $package->setAutoload($autoload);

        $devAutoload = $package->getDevAutoload();
        $files = $devAutoload['files'] ?? [];
        if (!in_array($config->generatedPath(), $files)) {
            $this->writeLog($event, "<info>Adding {$config->generatedPath()} to autoload-dev.files</info>");
            $files[] = $config->generatedPath();
            $devAutoload['files'] = $files;
        }
        $package->setDevAutoload($devAutoload);
    }

    public function getCapabilities() {
        return [CommandProvider::class => StructGenCommandProvider::class];
    }

    private function writeLog(Event $event, string $message, int $verbosity = IOInterface::NORMAL) {
        $event->getIO()->write("[struct-gen] " . $message, true, $verbosity);
    }
}
