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
    public function can_generate_struct_files(string $testContent) {
        $genStruct = new GenerateStruct();
        [$test, $expected] = explode('-- EXPECTED --', $testContent);

        $result = $genStruct($test);

        $this->assertEquals(
            trim($expected),
            trim($result)
        );
    }

    public function provideTestCaseNames() {
        $files = new \DirectoryIterator(__DIR__ . '/Fixtures/struct-test-cases');
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }

            yield $file->getFilename() => [file_get_contents($file->getPathname())];
        }
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

    /** @test */
    public function files_without_any_matching_classes_are_left_untouched() {
        $genStruct = new GenerateStruct();
        $input = <<<'PHP'
<?php

interface Acme {}
class Foo {}
function bar() {

}
PHP;
        $this->assertEquals($input, $genStruct($input));
    }
}
