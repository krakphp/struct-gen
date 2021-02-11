<?php

namespace Krak\StructGen\Tests\Feature\Bridge\Composer;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\NullIO;
use Composer\Package\RootPackage;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Krak\StructGen\Bridge\Composer\StructGenComposerPlugin;
use PHPUnit\Framework\TestCase;

final class ComposerPluginTest extends TestCase
{
    /** @var Composer */
    private $composer;
    /** @var EventDispatcher */
    private $eventDispatcher;

    public static function setUpBeforeClass(): void {
        if (!defined('Krak\StructGen\Bridge\Composer\_INCLUDED')) {
            define('Krak\StructGen\Bridge\Composer\_INCLUDED', 1);
        }
    }

    protected function setUp(): void {
        $this->given_plugin_is_subscribed_to_composer_package();
    }

    /** @test */
    public function adds_generated_file_to_classmap_and_dev_files() {
        $this->given_the_package_defines_extra([
            'struct-gen' => ['generated-path' => __FILE__],
        ]);
        $this->when_pre_autoload_dump_is_dispatched();
        $this->then_the_package_autoload_matches([
            'classmap' => [__FILE__],
        ]);
        $this->then_the_package_dev_autoload_matches([
            'files' => [__FILE__],
        ]);
    }

    /** @test */
    public function skips_autoload_modifications_if_no_generated_path_exists() {
        $this->given_the_package_defines_extra([]);
        $this->when_pre_autoload_dump_is_dispatched();
        $this->then_the_package_autoload_matches([]);
        $this->then_the_package_dev_autoload_matches([]);
    }

    private function given_plugin_is_subscribed_to_composer_package() {
        $composer = new Composer();
        $composer->setConfig(new Config());
        $composer->setPackage(new RootPackage('Test Package', 'v0.1', 'Version 0.1'));
        $eventDispatcher = new EventDispatcher($composer, new NullIO());
        $eventDispatcher->addSubscriber(new StructGenComposerPlugin());
        $this->composer = $composer;
        $this->eventDispatcher = $eventDispatcher;
    }

    private function given_the_package_defines_extra(array $extra) {
        $this->composer->getPackage()->setExtra($extra);
    }

    private function when_pre_autoload_dump_is_dispatched() {
        $this->eventDispatcher->dispatch(ScriptEvents::PRE_AUTOLOAD_DUMP, new Event(ScriptEvents::PRE_AUTOLOAD_DUMP, $this->composer, new NullIO()));
    }

    private function then_the_package_autoload_matches(array $expected) {
        $this->assertEquals($expected, $this->composer->getPackage()->getAutoload());
    }

    private function then_the_package_dev_autoload_matches(array $expected) {
        $this->assertEquals($expected, $this->composer->getPackage()->getDevAutoload());
    }
}
