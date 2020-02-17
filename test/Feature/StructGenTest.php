<?php

namespace Krak\StructGen\Tests\Feature;

use Krak\StructGen\GenerateStruct;
use function Krak\StructGen\{
    generateStructsForFiles,
    traversePhpFiles};
use PHPUnit\Framework\TestCase;

class StructGenTest extends TestCase
{
    const GENERATED_FILE_PATH = __DIR__ . '/Fixtures/generated-from-paths.php';

    public static function tearDownAfterClass(): void {
        if (file_exists(self::GENERATED_FILE_PATH)) {
            unlink(self::GENERATED_FILE_PATH);
        }
    }

    /**
     * @dataProvider provideTestCaseNames
     * @test
     */
    public function can_generate_struct_files(string $testCase) {
        $genStruct = new GenerateStruct();
        $result = $genStruct(file_get_contents(__DIR__ . "/Fixtures/{$testCase}.php"));

        $this->assertEquals(
            trim(file_get_contents(__DIR__ . "/Fixtures/{$testCase}-expected.php")),
            trim($result)
        );
    }

    public function provideTestCaseNames() {
        yield 'simple-struct' => ['simple-struct'];
        yield 'namespace-struct' => ['namespace-struct'];
        yield 'generated' => ['generated'];
    }

    /** @test */
    public function can_generate_structs_for_files() {
        $tplPath = __DIR__ . '/Fixtures/generated-from-paths-template.php';
        $expectedPath = __DIR__ . '/Fixtures/generated-from-paths-expected.php';
        copy($tplPath, self::GENERATED_FILE_PATH);
        generateStructsForFiles(traversePhpFiles([__DIR__ . '/Fixtures/generated-from-paths.php']));
        $this->assertEquals(
            trim(file_get_contents($expectedPath)),
            trim(file_get_contents(self::GENERATED_FILE_PATH))
        );
    }
}
